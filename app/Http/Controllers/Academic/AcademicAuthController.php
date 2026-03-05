<?php

namespace App\Http\Controllers\Academic;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicAuthController extends AcademicBaseController
{
    /**
     * Show the academic login form.
     */
    public function showLoginForm(Request $request, $locale)
    {
        if (Auth::guard('academic')->check()) {
            return redirect()->route('academic.dashboard', ['locale' => $locale]);
        }

        return view('academic.auth.login');
    }

    /**
     * Handle academic login request.
     */
    public function login(Request $request, $locale)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('academic')->attempt($credentials, $remember)) {
            $account = Auth::guard('academic')->user();

            // Check if account is active
            if (!$account->is_active) {
                Auth::guard('academic')->logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the administrator.',
                ])->withInput($request->only('email', 'remember'));
            }

            $request->session()->regenerate();

            if ($request->expectsJson()) {
                return $this->successResponse('Login successful', [
                    'redirect' => route('academic.dashboard', ['locale' => $locale])
                ]);
            }

            return redirect()->intended(route('academic.dashboard', ['locale' => $locale]));
        }

        if ($request->expectsJson()) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Handle academic logout request.
     */
    public function logout(Request $request, $locale)
    {
        Auth::guard('academic')->logout();

        // Clear intended URL to prevent redirect issues when logging in with different account
        $request->session()->forget('url.intended');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return $this->successResponse('Logged out successfully');
        }

        // Redirect to home page instead of login to clear referer
        return redirect()->route('home', ['locale' => $locale]);
    }
}
