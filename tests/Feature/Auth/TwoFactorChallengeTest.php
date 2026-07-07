<?php

namespace Tests\Feature\Auth;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TwoFactorChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_challenge_screen_redirects_to_login_without_a_pending_challenge(): void
    {
        $response = $this->get('/two-factor-challenge');

        $response->assertRedirect(route('login'));
    }

    public function test_expired_code_forces_the_user_back_to_login(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $user->forceFill(['two_factor_expires_at' => now()->subMinute()])->save();

        $response = $this->post('/two-factor-challenge', ['code' => '1234']);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_exceeding_the_attempt_cap_invalidates_the_code(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/two-factor-challenge', ['code' => '0000']);
        }

        $this->assertGuest();
        $this->assertNull($user->fresh()->two_factor_code);
    }

    public function test_resend_is_rate_limited_right_after_a_fresh_code_was_sent(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        // Logging in already sends a code, so an immediate resend should
        // still be inside the 60-second cooldown.
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response = $this->post('/two-factor-challenge/resend');

        $response->assertSessionHasErrors('code');
        Mail::assertSent(TwoFactorCodeMail::class, 1);
    }
}
