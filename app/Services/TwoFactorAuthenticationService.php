<?php

namespace App\Services;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TwoFactorAuthenticationService
{
    /**
     * How long a generated code stays valid.
     */
    private const CODE_TTL_MINUTES = 10;

    /**
     * How many wrong codes are tolerated before the challenge is invalidated
     * and the user is forced to restart the login from scratch.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Minimum gap between two "resend code" requests, to stop mailbox spamming.
     */
    private const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Generate a fresh one-time code for the user and email it to them.
     */
    public function generateAndSend(User $user): void
    {
        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $user->forceFill([
            'two_factor_code' => Hash::make($code),
            'two_factor_expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            'two_factor_attempts' => 0,
            'two_factor_last_sent_at' => now(),
        ])->save();

        Mail::to($user)->send(new TwoFactorCodeMail($code, self::CODE_TTL_MINUTES, App::getLocale()));
    }

    /**
     * Attempt to verify a submitted code.
     *
     * @return 'success'|'invalid'|'expired'|'too_many_attempts'
     */
    public function attempt(User $user, string $code): string
    {
        if (! $user->two_factor_code || ! $user->two_factor_expires_at) {
            return 'expired';
        }

        if (now()->greaterThan($user->two_factor_expires_at)) {
            $this->clear($user);

            return 'expired';
        }

        if ($user->two_factor_attempts >= self::MAX_ATTEMPTS) {
            $this->clear($user);

            return 'too_many_attempts';
        }

        if (! Hash::check($code, $user->two_factor_code)) {
            $user->increment('two_factor_attempts');

            if ($user->two_factor_attempts >= self::MAX_ATTEMPTS) {
                $this->clear($user);

                return 'too_many_attempts';
            }

            return 'invalid';
        }

        $this->clear($user);

        return 'success';
    }

    /**
     * Attempts the user has left before the code is invalidated.
     */
    public function attemptsRemaining(User $user): int
    {
        return max(0, self::MAX_ATTEMPTS - $user->two_factor_attempts);
    }

    /**
     * Seconds remaining before another resend is allowed (0 if allowed now).
     */
    public function secondsUntilResendAvailable(User $user): int
    {
        if (! $user->two_factor_last_sent_at) {
            return 0;
        }

        $elapsed = now()->diffInSeconds($user->two_factor_last_sent_at);

        return max(0, self::RESEND_COOLDOWN_SECONDS - $elapsed);
    }

    /**
     * Wipe any pending 2FA state from the user record.
     */
    private function clear(User $user): void
    {
        $user->forceFill([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_attempts' => 0,
        ])->save();
    }
}
