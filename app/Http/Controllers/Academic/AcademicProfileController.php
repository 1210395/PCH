<?php

namespace App\Http\Controllers\Academic;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AcademicProfileController extends AcademicBaseController
{
    /**
     * Show the profile edit form.
     */
    public function edit(Request $request, $locale)
    {
        $account = $this->getAccount();

        return view('academic.profile.edit', compact('account'));
    }

    /**
     * Update the profile information.
     */
    public function update(Request $request, $locale)
    {
        $account = $this->getAccount();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
        ]);

        $account->update($validated);

        if ($request->expectsJson()) {
            return $this->successResponse('Profile updated successfully');
        }

        return back()->with('success', 'Profile updated successfully');
    }

    /**
     * Upload a new logo.
     */
    public function uploadLogo(Request $request, $locale)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $account = $this->getAccount();

        // Delete old logo if exists
        if ($account->logo) {
            Storage::disk('public')->delete($account->logo);
        }

        $logo = $request->file('logo');
        $filename = 'academic_' . $account->id . '_' . Str::random(16) . '_logo.' . ($logo->guessExtension() ?? $logo->getClientOriginalExtension());
        $path = $logo->storeAs('academic-accounts', $filename, 'public');

        $account->logo = $path;
        $account->save();

        if ($request->expectsJson()) {
            return $this->successResponse('Logo uploaded successfully', [
                'logo_url' => $account->logo_url
            ]);
        }

        return back()->with('success', 'Logo uploaded successfully');
    }

    /**
     * Delete the current logo.
     */
    public function deleteLogo(Request $request, $locale)
    {
        $account = $this->getAccount();

        if ($account->logo) {
            Storage::disk('public')->delete($account->logo);
            $account->logo = null;
            $account->save();
        }

        if ($request->expectsJson()) {
            return $this->successResponse('Logo deleted successfully');
        }

        return back()->with('success', 'Logo deleted successfully');
    }

    /**
     * Update the password.
     */
    public function updatePassword(Request $request, $locale)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $account = $this->getAccount();

        // Verify current password
        if (!Hash::check($request->current_password, $account->password)) {
            if ($request->expectsJson()) {
                return $this->errorResponse('Current password is incorrect', 422);
            }

            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $account->password = Hash::make($request->password);
        $account->save();

        if ($request->expectsJson()) {
            return $this->successResponse('Password updated successfully');
        }

        return back()->with('success', 'Password updated successfully');
    }
}
