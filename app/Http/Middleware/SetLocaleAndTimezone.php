<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the user's preferred locale (falling back to the company's) and
 * shares the display timezone with views. Storage always stays in UTC.
 */
final class SetLocaleAndTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $supported = array_keys(config('locales.supported', []));

        $locale = $user?->locale
            ?? ($request->hasSession() ? $request->session()->get('locale') : null)
            ?? $request->getPreferredLanguage($supported)
            ?? config('app.locale');

        if (in_array($locale, $supported, true)) {
            App::setLocale($locale);
        }

        $direction = config("locales.supported.{$locale}.dir", 'ltr');

        view()->share('textDirection', $direction);
        view()->share('displayTimezone', $user?->timezone ?? config('app.timezone'));

        return $next($request);
    }
}
