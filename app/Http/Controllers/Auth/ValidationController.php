<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use App\Models\AcademicAccount;
use Illuminate\Http\Request;

/**
 * Provides live-validation endpoints used by the registration wizard front-end.
 * Checks uniqueness constraints without exposing sensitive data.
 */
class ValidationController extends Controller
{
    /**
     * Check if email is already taken.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');

        if (empty($email)) {
            return response()->json([
                'available' => false,
                'message' => 'Email is required'
            ]);
        }

        // Normalize email: strip +alias part (user+tag@gmail.com → user@gmail.com)
        // Prevents multiple accounts using Gmail's +alias trick
        $normalizedEmail = preg_replace('/\+[^@]*@/', '@', strtolower($email));
        $inputEmail = strtolower($email);

        // Check both the exact email and the normalized version
        $exists = Designer::whereRaw('LOWER(email) = ?', [$inputEmail])
            ->orWhereRaw("LOWER(REPLACE(SUBSTRING_INDEX(email, '+', 1), '', '') || '@' || SUBSTRING_INDEX(email, '@', -1)) = ?", [$normalizedEmail])
            ->exists();

        // Also block if an academic account exists with the same email (cross-guard uniqueness)
        if (!$exists) {
            $exists = AcademicAccount::whereRaw('LOWER(email) = ?', [$inputEmail])->exists();
        }

        // Simpler fallback: also check if stripping + from all existing emails matches
        if (!$exists && str_contains($email, '+')) {
            $exists = Designer::whereRaw("LOWER(CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(email, '@', 1), '+', 1), '@', SUBSTRING_INDEX(email, '@', -1))) = ?", [$normalizedEmail])
                ->exists();
        }

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? __('This email is already registered') : __('Email is available')
        ]);
    }
}
