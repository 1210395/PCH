<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
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
