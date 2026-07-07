<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * A logged-out user's language choice shouldn't be lost the moment they
     * log out — session::invalidate() on logout wipes the session, so the
     * preference also needs to live in a long-lived cookie.
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        session(['locale' => $locale]);

        return back()->withCookie(cookie('locale', $locale, 60 * 24 * 365));
    }
}
