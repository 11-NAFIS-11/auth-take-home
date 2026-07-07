<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['en', 'he'];

    private const DEFAULT = 'he';

    /**
     * The session is authoritative during a browsing session, but session
     * data is wiped on logout, so a long-lived cookie is the fallback that
     * keeps a signed-out user's language choice from reverting to English.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', $request->cookie('locale', self::DEFAULT));

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = self::DEFAULT;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
