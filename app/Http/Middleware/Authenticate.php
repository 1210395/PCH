<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

/**
 * Extends Laravel's built-in Authenticate middleware to support locale-prefixed login redirects.
 *
 * Stores the intended URL in the session before redirecting so users are returned
 * to their original destination after a successful login.
 */
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * Saves the full intended URL to the session and redirects to the locale-prefixed
     * login route. Returns null for JSON/API requests so a 401 response is sent instead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            session()->put('url.intended', $request->fullUrl());

            return route('login', ['locale' => app()->getLocale()]);
        }
    }
}
