<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects routes that require an authenticated designer with admin privileges.
 *
 * Checks authentication via the 'designer' guard, verifies the is_admin flag,
 * and ensures the account is active. Returns JSON error responses for API requests.
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated designer has admin privileges.
     * Returns a 401 if unauthenticated, 403 if not an admin or account is deactivated.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $designer = auth('designer')->user();

        // Check if user is authenticated
        if (!$designer) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login.'
                ], 401);
            }
            return redirect()->route('login', ['locale' => app()->getLocale()]);
        }

        // Check if user is an admin
        if (!$designer->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }
            abort(403, 'Unauthorized. Admin access required.');
        }

        // Check if admin account is active
        if (!$designer->isActive()) {
            auth('designer')->logout();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated.'
                ], 403);
            }
            return redirect()->route('login', ['locale' => app()->getLocale()])
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        return $next($request);
    }
}
