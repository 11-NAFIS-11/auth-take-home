<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorChallengeController extends Controller
{
    public function __construct(private readonly TwoFactorAuthenticationService $twoFactor) {}

    /**
     * Show the 2FA challenge screen, if a login/registration is pending one.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $userId = $request->session()->get('two_factor.user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (! $user) {
            $request->session()->forget(['two_factor.user_id', 'two_factor.remember', 'two_factor.email']);

            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge', [
            'email' => $request->session()->get('two_factor.email'),
            'resendAvailableInSeconds' => $this->twoFactor->secondsUntilResendAvailable($user),
            'status' => session('status'),
        ]);
    }

    /**
     * Verify the submitted one-time code and, on success, establish the session.
     */
    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor.user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.size' => __('auth.two_factor_code_format'),
        ]);

        $user = User::find($userId);

        if (! $user) {
            $request->session()->forget(['two_factor.user_id', 'two_factor.remember', 'two_factor.email']);

            return redirect()->route('login');
        }

        $result = $this->twoFactor->attempt($user, $request->string('code'));

        if ($result === 'success') {
            Auth::login($user, (bool) $request->session()->get('two_factor.remember', false));
            $request->session()->forget(['two_factor.user_id', 'two_factor.remember', 'two_factor.email']);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        }

        if ($result === 'invalid') {
            return back()->withErrors([
                'code' => __('auth.two_factor_invalid', ['attempts' => $this->twoFactor->attemptsRemaining($user)]),
            ]);
        }

        // expired or too_many_attempts: the challenge is dead, send the user back to log in again.
        $request->session()->forget(['two_factor.user_id', 'two_factor.remember', 'two_factor.email']);

        return redirect()->route('login')->withErrors([
            'email' => $result === 'too_many_attempts'
                ? __('auth.two_factor_too_many_attempts')
                : __('auth.two_factor_expired'),
        ]);
    }

    /**
     * Resend a fresh one-time code, subject to a cooldown.
     */
    public function resend(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor.user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (! $user) {
            $request->session()->forget(['two_factor.user_id', 'two_factor.remember', 'two_factor.email']);

            return redirect()->route('login');
        }

        $seconds = $this->twoFactor->secondsUntilResendAvailable($user);

        if ($seconds > 0) {
            return back()->withErrors([
                'code' => __('auth.two_factor_resend_throttled', ['seconds' => $seconds]),
            ]);
        }

        $this->twoFactor->generateAndSend($user);

        return back()->with('status', __('auth.two_factor_resent'));
    }
}
