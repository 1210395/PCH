<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Handles email address verification for designer accounts.
 * Provides the verification notice page, processes signed verification links, and resends verification emails.
 */
class EmailVerificationController extends Controller
{
    /**
     * Show the email verification notice page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function notice(Request $request)
    {
        // If user is logged in and already verified, redirect to profile
        if (Auth::guard('designer')->check() && Auth::guard('designer')->user()->hasVerifiedEmail()) {
            return redirect()->route('profile', ['locale' => app()->getLocale()]);
        }

        return view('auth.verify-email');
    }

    /**
     * Handle email verification link click.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @param  string  $hash
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, $locale, $id, $hash)
    {
        $designer = Designer::findOrFail($id);

        // Verify the hash matches the designer's email
        if (! hash_equals(sha1($designer->getEmailForVerification()), $hash)) {
            return redirect()->route('login', ['locale' => $locale])
                ->withErrors(['email' => __('Invalid verification link.')]);
        }

        // Check if already verified
        if ($designer->hasVerifiedEmail()) {
            return redirect()->route('login', ['locale' => $locale])
                ->with('status', __('Your email is already verified. You can log in.'));
        }

        // Mark email as verified
        $designer->markEmailAsVerified();

        return redirect()->route('login', ['locale' => $locale])
            ->with('status', __('Your email has been verified successfully! You can now log in.'));
    }

    /**
     * Resend verification email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $designer = Designer::where('email', $request->email)->first();

        if (! $designer) {
            // Don't reveal whether user exists - show success regardless
            return back()->with('status', __('If an account exists with that email, a verification link has been sent.'));
        }

        if ($designer->hasVerifiedEmail()) {
            return redirect()->route('login', ['locale' => app()->getLocale()])
                ->with('status', __('Your email is already verified. You can log in.'));
        }

        $designer->sendEmailVerificationNotification();

        return back()->with('status', __('If an account exists with that email, a verification link has been sent.'));
    }
}
