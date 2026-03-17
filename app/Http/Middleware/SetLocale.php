<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reads the {locale} route parameter and configures the application locale.
 *
 * Validates the locale against the supported list ('en', 'ar'), falls back to 'en'
 * for unsupported values, and persists the preference in a long-lived cookie.
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * Sets the application locale from the {locale} route segment and saves
     * the resolved locale in a cookie valid for one year (525,600 minutes).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from route parameter or fall back to default
        $locale = $request->route('locale') ?? config('app.locale', 'en');

        // Validate locale is supported
        $supportedLocales = ['en', 'ar'];
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'en';
        }

        // Set the application locale
        App::setLocale($locale);

        $response = $next($request);

        // Save locale preference in cookie (1 year)
        if ($response instanceof Response) {
            $response->headers->setCookie(
                cookie('locale', $locale, 525600, '/', null, false, false)
            );
        }

        return $response;
    }
}
