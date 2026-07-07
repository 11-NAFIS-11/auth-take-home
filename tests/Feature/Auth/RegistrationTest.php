<?php

namespace Tests\Feature\Auth;

use App\Mail\TwoFactorCodeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_are_sent_to_the_two_factor_challenge_instead_of_being_logged_in_immediately(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('two-factor.challenge'));
        Mail::assertSent(TwoFactorCodeMail::class);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }
}
