<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Handles the forgot-password and password-reset flows for designer accounts.
 * Uses Laravel's built-in "designers" password broker to send signed reset links and validate reset tokens.
 */
class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     *
     * @return \Illuminate\View\View
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Use the designers password broker
        $status = Password::broker('designers')->sendResetLink(
            $request->only('email')
        );

        // Always return the same generic message regardless of whether the email
        // exists, was sent, or hit the throttle. The throttle response is only
        // possible AFTER a real send for that email — distinguishing it would
        // confirm the account exists. (anti-enumeration)
        return back()->with('status', __('If an account exists with that email, a password reset link has been sent.'));
    }

    /**
     * Show the password reset form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request, $locale, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Handle the password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        $status = Password::broker('designers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Designer $designer, string $password) {
                $designer->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        $locale = app()->getLocale();

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login', ['locale' => $locale])
                ->with('status', __('Your password has been reset successfully! You can now log in.'));
        }

        // Handle specific error cases
        if ($status === Password::INVALID_TOKEN) {
            $message = $locale === 'ar'
                ? 'رابط إعادة تعيين كلمة المرور غير صالح أو منتهي الصلاحية.'
                : 'This password reset link is invalid or has expired.';
            return back()->withErrors(['email' => $message]);
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
