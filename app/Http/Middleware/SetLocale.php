<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active locale for each request: an explicit ?lang= choice
 * (persisted to the session) wins, then the session, then the browser's
 * Accept-Language, then the configured default.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('locales.supported'));
        $default = config('locales.default');

        $hasSession = $request->hasSession();
        $locale = $request->query('lang');

        if (is_string($locale) && in_array($locale, $supported, true)) {
            if ($hasSession) {
                $request->session()->put('locale', $locale);
            }
        } else {
            $locale = $hasSession ? $request->session()->get('locale') : null;
        }

        if (! is_string($locale) || ! in_array($locale, $supported, true)) {
            $locale = $request->getPreferredLanguage($supported) ?? $default;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
