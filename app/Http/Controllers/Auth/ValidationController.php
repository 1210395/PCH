<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    /**
     * Check if email is already taken
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
