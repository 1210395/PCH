<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification notice page.
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
