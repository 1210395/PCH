<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks unverified users from accessing protected routes.
 *
 * Admin accounts bypass this check regardless of verification status.
 * Accepts an optional guard parameter, defaulting to 'designer'.
 */
class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * Redirects authenticated, unverified, non-admin users to the email
     * verification notice page. Admin users always pass through.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $guard  The auth guard to check (defaults to 'designer')
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $guard = 'designer'): Response
    {
        $user = Auth::guard($guard)->user();

        if ($user && ! $user->hasVerifiedEmail() && ! $user->is_admin) {
            return redirect()->route('verification.notice', ['locale' => app()->getLocale()]);
        }

        return $next($request);
    }
}
