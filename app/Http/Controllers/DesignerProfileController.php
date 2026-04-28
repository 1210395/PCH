<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\ImageUploadController;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\CacheService;
use App\Services\GmailOAuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * DesignerProfileController
 *
 * Handles authenticated designer profile management:
 * - Profile viewing and editing
 * - Bio, skills, certifications updates
 * - Account settings (password, privacy, email preferences)
 *
 * Security measures:
 * - Authentication checks on all methods
 * - Rate limiting on profile updates (10 per hour)
 * - Input validation with regex patterns
 * - Path traversal protection for file paths
 * - XSS protection via strip_tags
 * - Skill name sanitization (strip_tags, length limits)
 * - Skills limit (max 20 per user)
 */
class DesignerProfileController extends Controller
{
    /**
     * Display the authenticated designer's portfolio (same view as public portfolio).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showProfile()
    {
        // Get authenticated designer with relationships
        $designer = auth('designer')->user();

        if (!$designer) {
            return redirect()->route('login', ['locale' => app()->getLocale()])
                ->with('error', 'Please login to view your profile');
        }

        // Redirect admin to admin dashboard - admins don't have public profiles
        if ($designer->is_admin) {
            return redirect()->route('admin.dashboard', ['locale' => app()->getLocale()]);
        }

        $designer->load([
            'skills',
            'projects.images',
            'products.images',
            'services',
            'marketplacePosts'
        ]);

        // Format projects data for Alpine.js with defensive checks and proper image path handling
        $projectsData = ($designer->projects ?? collect())->map(function($p) {
            $images = $p->images ?? collect();
            // Convert relative paths to full asset URLs
            $imagePaths = $images->pluck('image_path')->filter()->map(function($path) {
                if (empty($path)) return null;
                return url('media/' . $path);
            })->filter()->values()->toArray();

            return [
                'id' => $p->id ?? null,
                'title' => $p->title ?? '',
                'description' => $p->description ?? '',
                'category' => $p->category ?? '',
                'role' => $p->role ?? '',
                'image_paths' => $imagePaths,
            ];
        })->toArray();

        // Format products data for Alpine.js
        $productsData = ($designer->products ?? collect())->map(function($p) {
            $images = $p->images ?? collect();
            // Convert relative paths to full asset URLs
            $imagePaths = $images->pluck('image_path')->filter()->map(function($path) {
                if (empty($path)) return null;
                return url('media/' . $path);
            })->filter()->values()->toArray();

            return [
                'id' => $p->id ?? null,
                'name' => $p->title ?? '',  // Database uses 'title' field for products
                'description' => $p->description ?? '',
                'category' => $p->category ?? '',
                'image_paths' => $imagePaths,
            ];
        })->toArray();

        // Format services data for Alpine.js
        $servicesData = ($designer->services ?? collect())->map(function($s) {
            return [
                'id' => $s->id ?? null,
                'name' => $s->name ?? '',
                'description' => $s->description ?? '',
                'category' => $s->category ?? '',
            ];
        })->toArray();

        // Format marketplace posts data for Alpine.js (owner sees all their posts)
        $marketplaceData = ($designer->marketplacePosts ?? collect())->map(function($m) {
            return [
                'id' => $m->id ?? null,
                'title' => $m->title ?? '',
                'description' => $m->description ?? '',
                'category' => $m->category ?? '',
                'type' => $m->type ?? '',
                'image' => $m->image ? url('media/' . $m->image) : null,
                'tags' => $m->tags ?? [],
                'approval_status' => $m->approval_status ?? 'pending',
                'rejection_reason' => $m->rejection_reason ?? null,
            ];
        })->toArray();

        // Get similar designers (same sector or skills) - exclude admins and inactive
        $similarDesigners = CacheService::getSimilarDesigners($designer->id, $designer->sector ?? null, 4);

        // Use original avatar and cover images
        $avatarThumb = $designer->avatar ?? '';
        $coverThumb = $designer->cover_image ?? '';

        return view('designer-portfolio-new', compact(
            'designer',
            'projectsData',
            'productsData',
            'servicesData',
            'marketplaceData',
            'similarDesigners',
            'avatarThumb',
            'coverThumb'
        ));
    }

    /**
     * Show the profile editing form for the authenticated designer.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function editProfile()
    {
        // Get authenticated designer with relationships
        $designer = auth('designer')->user();

        if (!$designer) {
            return redirect()->route('login', ['locale' => app()->getLocale()])
                ->with('error', 'Please login to edit your profile');
        }

        // Redirect admin to admin dashboard - admins don't have editable profiles
        if ($designer->is_admin) {
            return redirect()->route('admin.dashboard', ['locale' => app()->getLocale()]);
        }

        // Redirect guests to the upgrade page
        if ($designer->sector === 'guest') {
            return redirect()->route('account.upgrade', ['locale' => app()->getLocale()]);
        }

        $designer->load([
            'skills',
            'projects.images',
            'products.images',
            'services',
            'marketplacePosts'
        ]);

        // Format projects data for Alpine.js with defensive checks and proper image path handling
        $projectsData = ($designer->projects ?? collect())->map(function($p) {
            $images = $p->images ?? collect();
            // Convert relative paths to full asset URLs (no thumbnails for editing)
            $imagePaths = $images->pluck('image_path')->filter()->map(function($path) {
                if (empty($path)) return null;
                return url('media/' . $path);
            })->filter()->values()->toArray();

            return [
                'id' => $p->id ?? null,
                'title' => $p->title ?? '',
                'description' => $p->description ?? '',
                'category' => $p->category ?? '',
                'role' => $p->role ?? '',
                'image_paths' => $imagePaths,
            ];
        })->toArray();

        // Format products data for Alpine.js
        $productsData = ($designer->products ?? collect())->map(function($p) {
            $images = $p->images ?? collect();
            // Convert relative paths to full asset URLs (no thumbnails for editing)
            $imagePaths = $images->pluck('image_path')->filter()->map(function($path) {
                if (empty($path)) return null;
                return url('media/' . $path);
            })->filter()->values()->toArray();

            return [
                'id' => $p->id ?? null,
                'name' => $p->title ?? '',  // Database uses 'title' field
                'description' => $p->description ?? '',
                'category' => $p->category ?? '',
                'image_paths' => $imagePaths,
            ];
        })->toArray();

        // Format services data for Alpine.js
        $servicesData = ($designer->services ?? collect())->map(function($s) {
            return [
                'id' => $s->id ?? null,
                'name' => $s->name ?? '',
                'description' => $s->description ?? '',
                'category' => $s->category ?? '',
            ];
        })->toArray();

        // Format marketplace posts data for Alpine.js
        $marketplaceData = ($designer->marketplacePosts ?? collect())->map(function($m) {
            return [
                'id' => $m->id ?? null,
                'title' => $m->title ?? '',
                'description' => $m->description ?? '',
                'category' => $m->category ?? '',
                'type' => $m->type ?? '',
                'image' => $m->image ? url('media/' . $m->image) : null,
                'image_path' => $m->image ?? null,
                'tags' => $m->tags ?? [],
                'approval_status' => $m->approval_status ?? 'pending',
                'rejection_reason' => $m->rejection_reason ?? null,
            ];
        })->toArray();

        return view('profile-edit', compact('designer', 'projectsData', 'productsData', 'servicesData', 'marketplaceData'));
    }

    /**
     * Persist updated profile data (name, bio, social links, avatar, cover image, skills).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Rate limiting: max 10 profile updates per hour per user
            $key = 'profile-update:' . $designer->id;
            if (RateLimiter::tooManyAttempts($key, 10)) {
                $seconds = RateLimiter::availableIn($key);
                return response()->json([
                    'success' => false,
                    'message' => 'Too many profile update attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.'
                ], 429);
            }

            RateLimiter::hit($key, 3600); // 1 hour

            // Convert empty strings to null for optional URL fields
            $socialFields = ['linkedin', 'instagram', 'facebook', 'behance'];
            foreach ($socialFields as $field) {
                if ($request->has($field) && $request->input($field) === '') {
                    $request->merge([$field => null]);
                }
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sector' => 'nullable|string|max:100',
                'sub_sector' => 'nullable|string|max:100',
                'bio' => 'nullable|string|max:1000',
                'phone' => 'nullable|string|max:20|regex:/^[0-9\s\+\-\(\)]+$/',
                'city' => 'nullable|string|max:100',
                'address' => 'nullable|string|max:255',
                'linkedin' => 'nullable|url|max:255',
                'instagram' => 'nullable|url|max:255',
                'facebook' => 'nullable|url|max:255',
                'behance' => 'nullable|url|max:255',
                'skills' => 'nullable|string|max:500',
                'avatar' => 'nullable|string|max:500|regex:/^[a-zA-Z0-9\/_\-\.]+$/',
                'cover_image' => 'nullable|string|max:500|regex:/^[a-zA-Z0-9\/_\-\.]+$/',
            ]);

            // Update basic info (email is not updatable)
            $updateData = [
                'name' => strip_tags($validated['name']),
                'sector' => strip_tags($validated['sector'] ?? '') ?: null,
                'sub_sector' => strip_tags($validated['sub_sector'] ?? '') ?: null,
                'bio' => strip_tags($validated['bio'] ?? '') ?: null,
                'phone_number' => $validated['phone'] ?? null,
                'city' => strip_tags($validated['city'] ?? '') ?: null,
                'address' => strip_tags($validated['address'] ?? '') ?: null,
                'linkedin' => $validated['linkedin'] ?? null,
                'instagram' => $validated['instagram'] ?? null,
                'facebook' => $validated['facebook'] ?? null,
                'behance' => $validated['behance'] ?? null,
            ];

            // Add avatar if provided - with security checks
            if (isset($validated['avatar']) && !empty($validated['avatar'])) {
                // Prevent path traversal attacks
                $avatarPath = str_replace(['../', '..\\', './'], '', $validated['avatar']);
                // Ensure path doesn't start with /
                $avatarPath = ltrim($avatarPath, '/\\');

                // Check if this is a temp file that needs to be moved to permanent storage
                if (strpos($avatarPath, 'uploads/temp/') === 0) {
                    $imageController = new ImageUploadController();
                    $permanentPath = $imageController->moveToPermStorage($avatarPath, 'avatar', $designer->id);

                    if (!empty($permanentPath)) {
                        $updateData['avatar'] = $permanentPath;
                    }
                } else if (\Storage::disk('public')->exists($avatarPath)) {
                    // File is already in permanent storage
                    $updateData['avatar'] = $avatarPath;
                }
            }

            // Add cover_image if provided - with security checks
            if (isset($validated['cover_image']) && !empty($validated['cover_image'])) {
                // Prevent path traversal attacks
                $coverPath = str_replace(['../', '..\\', './'], '', $validated['cover_image']);
                // Ensure path doesn't start with /
                $coverPath = ltrim($coverPath, '/\\');

                // Check if this is a temp file that needs to be moved to permanent storage
                if (strpos($coverPath, 'uploads/temp/') === 0) {
                    $imageController = new ImageUploadController();
                    $permanentPath = $imageController->moveToPermStorage($coverPath, 'cover', $designer->id);

                    if (!empty($permanentPath)) {
                        $updateData['cover_image'] = $permanentPath;
                    }
                } else if (\Storage::disk('public')->exists($coverPath)) {
                    // File is already in permanent storage
                    $updateData['cover_image'] = $coverPath;
                }
            }

            $designer->update($updateData);

            // Update skills (many-to-many relationship)
            if ($request->has('skills') && !empty($validated['skills'])) {
                $skillNames = array_filter(array_map('trim', explode(',', $validated['skills'])));
                $skillIds = [];

                // Limit number of skills to prevent abuse
                $skillNames = array_slice($skillNames, 0, 20);

                foreach ($skillNames as $skillName) {
                    if (empty($skillName)) continue;

                    // Sanitize skill name - remove HTML tags and limit length
                    $skillName = strip_tags($skillName);
                    $skillName = substr($skillName, 0, 50);

                    // Validate skill name contains only allowed characters (Latin, Arabic, digits, common punctuation)
                    if (!preg_match('/^[\p{L}\p{N}\s\.\-\/\+\#]+$/u', $skillName)) {
                        continue; // Skip invalid skill names
                    }

                    $slug = \Illuminate\Support\Str::slug($skillName);
                    // For non-Latin scripts (e.g. Arabic), Str::slug may return empty — use a fallback
                    if (empty($slug)) {
                        $slug = mb_strtolower(trim(preg_replace('/[\s\-]+/', '-', $skillName)));
                    }
                    if (empty($slug)) continue;

                    $skill = \App\Models\Skill::firstOrCreate(
                        ['slug' => $slug],
                        ['name' => $skillName, 'slug' => $slug]
                    );
                    $skillIds[] = $skill->id;
                }

                if (!empty($skillIds)) {
                    $designer->skills()->sync($skillIds);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Profile update failed', [
                'designer_id' => auth('designer')->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating your profile. Please try again.'
            ], 500);
        }
    }

    /**
     * Update only the bio field for the authenticated designer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBio(Request $request)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validated = $request->validate([
                'bio' => 'required|string|max:1000',
            ]);

            // Sanitize bio to prevent XSS
            $sanitizedBio = strip_tags($validated['bio']);
            $designer->update(['bio' => $sanitizedBio]);

            return response()->json([
                'success' => true,
                'message' => 'Bio updated successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Bio update failed', [
                'designer_id' => auth('designer')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating bio'
            ], 500);
        }
    }

    /**
     * Replace the authenticated designer's skills list (comma-separated, max 20, sanitized).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSkills(Request $request)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validated = $request->validate([
                'skills' => 'required|string',
            ]);

            $skillNames = array_filter(array_map('trim', explode(',', $validated['skills'])));
            $skillIds = [];

            // Limit number of skills to prevent abuse
            $skillNames = array_slice($skillNames, 0, 20);

            foreach ($skillNames as $skillName) {
                if (empty($skillName)) continue;

                // Sanitize skill name - remove HTML tags and limit length
                $skillName = strip_tags($skillName);
                $skillName = substr($skillName, 0, 50);

                // Validate skill name contains only allowed characters (Latin, Arabic, digits, common punctuation)
                if (!preg_match('/^[\p{L}\p{N}\s\.\-\/\+\#]+$/u', $skillName)) {
                    continue; // Skip invalid skill names
                }

                $slug = \Illuminate\Support\Str::slug($skillName);
                // For non-Latin scripts (e.g. Arabic), Str::slug may return empty — use a fallback
                if (empty($slug)) {
                    $slug = mb_strtolower(trim(preg_replace('/[\s\-]+/', '-', $skillName)));
                }
                if (empty($slug)) continue;

                $skill = \App\Models\Skill::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $skillName, 'slug' => $slug]
                );
                $skillIds[] = $skill->id;
            }

            if (!empty($skillIds)) {
                $designer->skills()->sync($skillIds);
            } else {
                // If no valid skills, clear all skills
                $designer->skills()->sync([]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Skills updated successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Skills update failed', [
                'designer_id' => auth('designer')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating skills'
            ], 500);
        }
    }

    /**
     * Update certifications — upload new PDF or remove existing one.
     * Certifications are stored as a JSON array of file paths on the Designer model.
     * Maximum 3 certifications allowed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCertifications(Request $request)
    {
        $designer = auth('designer')->user();
        $certifications = $designer->certifications ?? [];

        // Handle remove
        if ($request->has('remove_index')) {
            $index = (int) $request->input('remove_index');
            if (isset($certifications[$index])) {
                // Delete the file from storage
                $path = $certifications[$index];
                if (is_string($path)) {
                    $storagePath = storage_path('app/public/' . $path);
                    if (file_exists($storagePath)) {
                        @unlink($storagePath);
                    }
                } elseif (is_array($path) && isset($path['path'])) {
                    $storagePath = storage_path('app/public/' . $path['path']);
                    if (file_exists($storagePath)) {
                        @unlink($storagePath);
                    }
                }
                array_splice($certifications, $index, 1);
                $designer->update(['certifications' => array_values($certifications)]);

                return response()->json([
                    'success' => true,
                    'message' => __('Certification removed successfully.'),
                    'certifications' => array_values($certifications),
                ]);
            }

            return response()->json(['success' => false, 'message' => __('Certification not found.')], 404);
        }

        // Handle upload
        if ($request->hasFile('new_certification')) {
            if (count($certifications) >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => __('Maximum 3 certifications allowed.'),
                ], 422);
            }

            $request->validate([
                'new_certification' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB max
            ]);

            $file = $request->file('new_certification');
            $filename = 'cert_' . $designer->id . '_' . time() . '_' . uniqid() . '.pdf';
            $path = $file->storeAs('certifications', $filename, 'public');

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to upload certification file.'),
                ], 500);
            }

            $certifications[] = $path;
            $designer->update(['certifications' => $certifications]);

            return response()->json([
                'success' => true,
                'message' => __('Certification uploaded successfully.'),
                'certifications' => $certifications,
                'new_cert' => [
                    'id' => count($certifications) - 1,
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'url' => url("media/certifications/{$filename}"),
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => __('No file provided.')], 400);
    }

    /**
     * Show the account settings page (password, privacy, email preferences).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function accountSettings()
    {
        $designer = auth('designer')->user();

        if (!$designer) {
            return redirect()->route('login', ['locale' => app()->getLocale()]);
        }

        // Redirect admin to admin dashboard - admins don't have account settings page
        if ($designer->is_admin) {
            return redirect()->route('admin.dashboard', ['locale' => app()->getLocale()]);
        }

        return view('account.settings', [
            'designer' => $designer
        ]);
    }

    /**
     * Validate and update the authenticated designer's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return redirect()->route('login', ['locale' => app()->getLocale()])
                    ->with('error', 'Please login to update your password');
            }

            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            // Verify current password
            if (!\Hash::check($validated['current_password'], $designer->password)) {
                return redirect()->back()
                    ->with('error', 'Current password is incorrect')
                    ->withInput();
            }

            // Update password and rotate remember_token so any "remember me"
            // cookies issued before this change are invalidated. (bugs.md H-26)
            $designer->password = \Hash::make($validated['new_password']);
            $designer->remember_token = \Illuminate\Support\Str::random(60);
            $designer->save();

            return redirect()->route('account.settings', ['locale' => app()->getLocale()])
                ->with('success', 'Password updated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->with('error', 'Validation failed: ' . implode(', ', $e->errors()))
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Password update failed', [
                'designer_id' => auth('designer')->id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'An error occurred while updating password')
                ->withInput();
        }
    }

    /**
     * Update privacy settings
     */
    public function updatePrivacySettings(Request $request)
    {
        try {
            $designerId = auth('designer')->id();

            if (!$designerId) {
                return redirect()->route('login', ['locale' => app()->getLocale()])
                    ->with('error', 'Please login to update privacy settings');
            }

            // Retrieve fresh model from database and update
            $designer = Designer::findOrFail($designerId);

            // Update privacy settings.
            // boolean() coerces "0"/"false"/empty/missing to false (unlike has(), which
            // returns true for any present value including the literal string "0").
            $designer->show_email = $request->boolean('show_email');
            $designer->show_phone = $request->boolean('show_phone');
            $designer->show_location = $request->boolean('show_location');
            $designer->allow_messages = $request->boolean('allow_messages');
            $designer->save();

            return redirect()->route('account.settings', ['locale' => app()->getLocale()])
                ->with('success', 'Privacy settings updated successfully');

        } catch (\Exception $e) {
            \Log::error('Privacy settings update failed', [
                'designer_id' => auth('designer')->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'An error occurred while updating privacy settings');
        }
    }

    /**
     * Update email preferences
     */
    public function updateEmailPreferences(Request $request)
    {
        try {
            $designer = auth('designer')->user();

            if (!$designer) {
                return redirect()->route('login', ['locale' => app()->getLocale()])
                    ->with('error', 'Please login to update email preferences');
            }

            // Update email preferences. boolean() coerces "0"/"false"/empty/missing to
            // false (unlike has(), which would return true for the literal string "0").
            $designer->email_marketing = $request->boolean('email_marketing');
            // email_notifications is always true for security, but we'll set it anyway
            $designer->email_notifications = true;
            $designer->save();

            return redirect()->route('account.settings', ['locale' => app()->getLocale()])
                ->with('success', 'Email preferences updated successfully');

        } catch (\Exception $e) {
            \Log::error('Email preferences update failed', [
                'designer_id' => auth('designer')->id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'An error occurred while updating email preferences');
        }
    }

    /**
     * Show the account upgrade form for guest accounts.
     */
    public function upgradeForm()
    {
        $designer = auth('designer')->user();

        if ($designer->sector !== 'guest') {
            return redirect()->route('profile.edit', ['locale' => app()->getLocale()]);
        }

        return view('account.upgrade', compact('designer'));
    }

    /**
     * Process the account upgrade from guest to full account.
     */
    public function upgradeSubmit(Request $request)
    {
        $designer = auth('designer')->user();

        if ($designer->sector !== 'guest') {
            return response()->json(['message' => __('Account is already upgraded')], 422);
        }

        $validated = $request->validate([
            'sector' => 'required|string|in:designer,manufacturer,showroom',
            'sub_sector' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'city' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'phone_country' => 'nullable|string|max:10',
            'website' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'behance' => 'nullable|url|max:255',
            'avatar' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
        ]);

        // Extract skills before update
        $skills = $validated['skills'] ?? [];
        unset($validated['skills']);

        // Update designer
        $designer->update($validated);

        // Sync skills
        if (!empty($skills)) {
            $skillIds = [];
            foreach ($skills as $skillName) {
                $skill = \App\Models\Skill::firstOrCreate(['name' => trim($skillName)]);
                $skillIds[] = $skill->id;
            }
            $designer->skills()->sync($skillIds);
        }

        // Clear caches
        CacheService::clearDesignerCache($designer->id);
        CacheService::clearDashboardCache();

        return response()->json([
            'success' => true,
            'message' => __('Account upgraded successfully!'),
            'redirect' => route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]),
        ]);
    }

    /**
     * Send a verification code for account deletion.
     */
    public function sendDeleteCode(Request $request)
    {
        $designer = auth('designer')->user();

        if (!Hash::check($request->password, $designer->password)) {
            return response()->json(['message' => __('Invalid password')], 422);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in cache for 10 minutes
        Cache::put('delete_code_' . $designer->id, $code, 600);

        // Send code via email
        try {
            $locale = app()->getLocale();
            $subject = $locale === 'ar'
                ? 'رمز تأكيد حذف الحساب - ' . config('app.name')
                : 'Account Deletion Verification Code - ' . config('app.name');

            $htmlBody = view('emails.delete-code', [
                'code' => $code,
                'name' => $designer->first_name ?? $designer->name,
                'locale' => $locale,
            ])->render();

            app(GmailOAuthService::class)->sendEmail(
                $designer->email,
                $subject,
                $htmlBody
            );
        } catch (\Exception $e) {
            Log::error('Failed to send delete verification code', [
                'designer_id' => $designer->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => __('Failed to send verification code. Please try again.')], 500);
        }

        return response()->json(['message' => __('Verification code sent')]);
    }

    /**
     * Confirm account deletion with verification code (soft delete).
     */
    public function confirmDelete(Request $request)
    {
        $designer = auth('designer')->user();

        if (!Hash::check($request->password, $designer->password)) {
            return response()->json(['message' => __('Invalid password')], 422);
        }

        $storedCode = Cache::get('delete_code_' . $designer->id);
        if (!$storedCode || $storedCode !== $request->code) {
            return response()->json(['message' => __('Invalid or expired verification code')], 422);
        }

        // Clear the code
        Cache::forget('delete_code_' . $designer->id);

        try {
            // Soft delete: deactivate account
            $designer->update([
                'is_active' => false,
                'approval_status' => 'rejected',
            ]);

            // Log out
            Auth::guard('designer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $locale = app()->getLocale();
            return response()->json([
                'message' => __('Account deleted successfully'),
                'redirect' => route('home', ['locale' => $locale]),
            ]);
        } catch (\Exception $e) {
            Log::error('Account deletion failed', [
                'designer_id' => $designer->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => __('An error occurred. Please try again.')], 500);
        }
    }
}
