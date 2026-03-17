<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Designer;
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

        $exists = Designer::where('email', $email)->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'This email is already registered' : 'Email is available'
        ]);
    }
}
