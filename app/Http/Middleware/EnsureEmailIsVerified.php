<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     * Redirect unverified users to the email verification notice page.
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
