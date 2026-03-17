<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects routes that require an authenticated and active academic account.
 *
 * Uses the 'academic' guard and redirects deactivated accounts to the login page.
 */
class AcademicMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Verifies the user is authenticated via the 'academic' guard and that the
     * account is active. Deactivated accounts are logged out before redirecting.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via academic guard
        if (!auth('academic')->check()) {
            return redirect()->route('login', ['locale' => app()->getLocale()]);
        }

        // Check if account is active
        $account = auth('academic')->user();
        if (!$account->isActive()) {
            auth('academic')->logout();
            return redirect()->route('login', ['locale' => app()->getLocale()])
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}
