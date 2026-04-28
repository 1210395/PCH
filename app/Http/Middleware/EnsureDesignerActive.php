<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kicks out designers whose account has been deactivated mid-session.
 *
 * AuthController::login refuses login for inactive non-admins, but a designer
 * deactivated by an admin (or via the self-delete soft-delete flow) keeps an
 * already-issued session cookie working until logout. This middleware re-checks
 * is_active on every authenticated request and forces logout if it's false.
 *
 * Admins are exempt — matches the carve-out in AuthController::login so an
 * admin self-deactivation doesn't lock them out of admin recovery.
 */
class EnsureDesignerActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $designer = Auth::guard('designer')->user();

        if ($designer && !$designer->is_active && !$designer->is_admin) {
            Auth::guard('designer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = __('Your account is no longer active. Please contact support.');

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()
                ->route('login', ['locale' => app()->getLocale()])
                ->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
