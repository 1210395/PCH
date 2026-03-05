<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\ImageUploadController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\CacheService;

/**
 * DesignerController
 *
 * Security measures implemented:
 * - Authentication checks on all sensitive methods
 * - Authorization checks (IDOR prevention) in Project/Product controllers
 * - Rate limiting on profile updates (10 per hour)
 * - Input validation with regex patterns
 * - Path traversal protection for file paths
 * - XSS protection via Laravel's automatic escaping
 * - CSRF protection via Laravel middleware
 * - Mass assignment protection via guarded fields
 * - SQL injection protection via Eloquent ORM
 * - File existence verification before updating paths
 * - Skill name sanitization (strip_tags, length limits)
 * - Skills limit (max 20 per user)
 */
class DesignerController extends Controller
{
    public function show($locale, $id)
    {
        // Allow designers to view their own portfolio via the public route
        // This is now the primary way designers view their portfolio after login
        // Note: $locale is automatically passed from the route group but not needed here

        // Check if viewer is the profile owner BEFORE loading data
        $currentDesignerId = auth('designer')->id();
        $isProfileOwner = $currentDesignerId !== null && $currentDesignerId == $id;

        // Load data - owners see all their content, others see only approved
        $designer = Designer::with([
            'skills',
            'projects' => fn($q) => $isProfileOwner
                ? $q->latest()->with('images')
                : $q->where('approval_status', 'approved')->latest()->limit(6)->with('images'),
            'products' => fn($q) => $isProfileOwner
                ? $q->latest()->with('images')
                : $q->where('approval_status', 'approved')->latest()->limit(6)->with('images'),
            'services' => fn($q) => $isProfileOwner
                ? $q->latest()
                : $q->where('approval_status', 'approved')->latest()->limit(6),
            'marketplacePosts' => fn($q) => $isProfileOwner
                ? $q->latest()
                : $q->where('approval_status', 'approved')->latest()->limit(6),
        ])->findOrFail($id);

        // Add counts for "Load More" functionality
        $designer->loadCount(['projects', 'products', 'services', 'marketplacePosts']);

        // Increment view count only if viewer is not the profile owner
        if (!$isProfileOwner) {
            $designer->increment('views_count');

            // Send notification to the profile owner
            NotificationController::createNotification(
                $id,
                'profile_view',
                'Someone viewed your profile!',
                'Your profile is getting attention. Keep it updated!'
            );
        }

    // Format projects data for Alpine.js with defensive checks and proper image path handling
    $projectsData = ($designer->projects ?? collect())->map(function($p) {
        $images = $p->images ?? collect();
        // Convert relative paths to full asset URLs
        $imagePaths = $images->pluck('image_path')->filter()->map(function($path) {
            if (empty($path)) return null;
            return asset('storage/' . $path);
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
            return asset('storage/' . $path);
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

    // Format marketplace posts data for Alpine.js
    // Owner sees all their posts, others only see approved (already filtered in eager loading)
    $marketplaceData = ($designer->marketplacePosts ?? collect())
        ->map(function($m) {
            return [
                'id' => $m->id ?? null,
                'title' => $m->title ?? '',
                'description' => $m->description ?? '',
                'category' => $m->category ?? '',
                'type' => $m->type ?? '',
                'image' => $m->image ? asset('storage/' . $m->image) : null,
                'tags' => $m->tags ?? [],
                'approval_status' => $m->approval_status ?? 'pending',
                'rejection_reason' => $m->rejection_reason ?? null,
            ];
        })->values()->toArray();

    // Get similar designers (same sector or skills) - exclude admins and inactive
    $similarDesigners = CacheService::getSimilarDesigners($id, $designer->sector ?? null, 4);

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

    public function index(Request $request, $locale)
    {
        $query = Designer::query()->with('skills');

        // Exclude admin and inactive accounts from public listings
        $query->where('is_admin', false)->where('is_active', true);

        // Filter by specific sector (e.g., manufacturer or showroom only)
        // "vendor" is a virtual sector — vendors are designers with sub_sector containing "Supplier"
        $sector = $request->get('sector');

        // Auto-set type based on sector when type is not explicitly provided
        $type = $request->get('type', null);
        if ($type === null) {
            if ($sector && in_array($sector, ['manufacturer', 'showroom', 'vendor'])) {
                $type = 'manufacturers';
            } elseif ($sector && in_array($sector, ['designer', 'freelancer'])) {
                $type = 'designers';
            } else {
                $type = 'all';
            }
        }

        // Filter by type: designers (excludes manufacturers/showrooms/vendors) or manufacturers (only manufacturers/showrooms/vendors)
        // Vendors = anyone with "supplier" in their sector or sub_sector
        if ($type === 'designers') {
            $query->whereNotIn('sector', ['manufacturer', 'showroom'])
                  ->where('sector', 'NOT LIKE', '%supplier%')
                  ->where('sub_sector', 'NOT LIKE', '%supplier%');
        } elseif ($type === 'manufacturers') {
            $query->where(function($q) {
                $q->whereIn('sector', ['manufacturer', 'showroom'])
                  ->orWhere('sector', 'LIKE', '%supplier%')
                  ->orWhere('sub_sector', 'LIKE', '%supplier%');
            });
        }

        // Apply specific sector filter
        if ($sector && in_array($sector, ['manufacturer', 'showroom', 'designer', 'freelancer'])) {
            $query->where('sector', $sector);
        } elseif ($sector === 'vendor') {
            // Vendors = anyone with "supplier" in their sector or sub_sector
            $query->where(function($q) {
                $q->where('sector', 'LIKE', '%supplier%')
                  ->orWhere('sub_sector', 'LIKE', '%supplier%');
            });
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strip_tags($request->search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sector', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sub_sector', 'like', '%' . $searchTerm . '%')
                  ->orWhere('city', 'like', '%' . $searchTerm . '%');
            });
        }

        // Add counts for sorting
        $query->withCount(['projects', 'products']);

        // Sort
        $sort = $request->get('sort', 'popular');
        switch ($sort) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'most_projects':
                $query->orderBy('projects_count', 'desc');
                break;
            case 'most_products':
                $query->orderBy('products_count', 'desc');
                break;
            default:
                $query->orderBy('followers_count', 'desc');
        }

        $designers = $query->paginate(12)->appends($request->query());

        // Get counts for each type (excluding admin and inactive accounts)
        $stats = CacheService::getHomepageStats();
        $allCount = $stats['designers'];
        $designersCount = $stats['designers_only'];
        $manufacturersCount = $stats['companies'];
        $vendorsCount = $stats['vendors'] ?? 0;

        return view('designers', compact('designers', 'type', 'sector', 'sort', 'allCount', 'designersCount', 'manufacturersCount', 'vendorsCount'));
    }

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
                return asset('storage/' . $path);
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
                return asset('storage/' . $path);
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
                'image' => $m->image ? asset('storage/' . $m->image) : null,
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

        // Prevent guests from accessing profile edit
        if ($designer->sector === 'guest') {
            return redirect()->route('designer.portfolio', [
                'locale' => app()->getLocale(),
                'id' => $designer->id
            ])->with('error', 'Guest accounts cannot edit their profile. Please register as a professional to access all features.');
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
                return asset('storage/' . $path);
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
                return asset('storage/' . $path);
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
                'image' => $m->image ? asset('storage/' . $m->image) : null,
                'image_path' => $m->image ?? null,
                'tags' => $m->tags ?? [],
                'approval_status' => $m->approval_status ?? 'pending',
                'rejection_reason' => $m->rejection_reason ?? null,
            ];
        })->toArray();

        return view('profile-edit', compact('designer', 'projectsData', 'productsData', 'servicesData', 'marketplaceData'));
    }

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

                    // Validate skill name contains only allowed characters
                    if (!preg_match('/^[a-zA-Z0-9\s\.\-\/\+\#]+$/', $skillName)) {
                        continue; // Skip invalid skill names
                    }

                    $slug = \Illuminate\Support\Str::slug($skillName);
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

                // Validate skill name contains only allowed characters
                if (!preg_match('/^[a-zA-Z0-9\s\.\-\/\+\#]+$/', $skillName)) {
                    continue; // Skip invalid skill names
                }

                $slug = \Illuminate\Support\Str::slug($skillName);
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

            // Update password
            $designer->password = \Hash::make($validated['new_password']);
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

            // Update privacy settings (checkboxes that aren't checked won't be in request)
            $designer->show_email = $request->has('show_email');
            $designer->show_phone = $request->has('show_phone');
            $designer->show_location = $request->has('show_location');
            $designer->allow_messages = $request->has('allow_messages');
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

            // Update email preferences (checkboxes that aren't checked won't be in request)
            $designer->email_marketing = $request->has('email_marketing');
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
     * Follow a designer
     */
    public function follow(Request $request, $locale, $id)
    {
        try {
            $currentUser = auth('designer')->user();

            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to follow designers'
                ], 401);
            }

            // Prevent self-following
            if ($currentUser->id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot follow yourself'
                ], 400);
            }

            $designerToFollow = Designer::findOrFail($id);

            // Check if already following
            if ($currentUser->following()->where('following_id', $id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already following this designer'
                ], 400);
            }

            // Create follow relationship
            $currentUser->following()->attach($id, [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update counts
            $currentUser->increment('following_count');
            $designerToFollow->increment('followers_count');

            // Send notification to the followed designer
            NotificationController::createNotification(
                $id,
                'new_follower',
                'Someone started following you!',
                'You have a new follower. Check out your growing community!'
            );

            return response()->json([
                'success' => true,
                'message' => 'Successfully followed ' . $designerToFollow->name,
                'followers_count' => $designerToFollow->followers_count
            ]);

        } catch (\Exception $e) {
            \Log::error('Follow failed', [
                'user_id' => auth('designer')->id(),
                'designer_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while following this designer'
            ], 500);
        }
    }

    /**
     * Unfollow a designer
     */
    public function unfollow(Request $request, $locale, $id)
    {
        try {
            $currentUser = auth('designer')->user();

            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to unfollow designers'
                ], 401);
            }

            $designerToUnfollow = Designer::findOrFail($id);

            // Check if actually following
            if (!$currentUser->following()->where('following_id', $id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not following this designer'
                ], 400);
            }

            // Remove follow relationship
            $currentUser->following()->detach($id);

            // Update counts
            $currentUser->decrement('following_count');
            $designerToUnfollow->decrement('followers_count');

            return response()->json([
                'success' => true,
                'message' => 'Successfully unfollowed ' . $designerToUnfollow->name,
                'followers_count' => $designerToUnfollow->followers_count
            ]);

        } catch (\Exception $e) {
            \Log::error('Unfollow failed', [
                'user_id' => auth('designer')->id(),
                'designer_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while unfollowing this designer'
            ], 500);
        }
    }

    /**
     * Check if current user is following a designer
     */
    public function checkFollowing($locale, $id)
    {
        try {
            $currentUser = auth('designer')->user();

            if (!$currentUser) {
                return response()->json([
                    'success' => true,
                    'is_following' => false
                ]);
            }

            $isFollowing = $currentUser->following()->where('following_id', $id)->exists();

            return response()->json([
                'success' => true,
                'is_following' => $isFollowing
            ]);

        } catch (\Exception $e) {
            \Log::error('Check following failed', [
                'user_id' => auth('designer')->id(),
                'designer_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Track profile view
     */
    public function trackView($locale, $id)
    {
        try {
            $designer = Designer::findOrFail($id);

            // Only increment if viewer is not the profile owner
            $currentDesignerId = auth('designer')->id();
            if (!$currentDesignerId || $currentDesignerId != $id) {
                $designer->increment('views_count');
            }

            return response()->json([
                'success' => true,
                'views_count' => $designer->views_count
            ]);

        } catch (\Exception $e) {
            \Log::error('Track view failed', [
                'designer_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Toggle like on a designer profile
     */
    public function toggleLike($locale, $id)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Prevent self-liking
        if ($currentDesigner->id == $id) {
            return response()->json(['success' => false, 'message' => 'You cannot like your own profile'], 400);
        }

        $designer = Designer::findOrFail($id);

        $existingLike = \App\Models\Like::where('designer_id', $currentDesigner->id)
            ->where('likeable_type', 'App\Models\Designer')
            ->where('likeable_id', $id)
            ->first();

        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            $designer->decrement('likes_count');
            $liked = false;
        } else {
            // Like
            \App\Models\Like::create([
                'designer_id' => $currentDesigner->id,
                'likeable_type' => 'App\Models\Designer',
                'likeable_id' => $id,
            ]);
            $designer->increment('likes_count');
            $liked = true;

            // Send notification to the liked designer
            NotificationController::createNotification(
                $id,
                'profile_like',
                'Someone liked your profile!',
                'Your work is being appreciated. Keep creating!'
            );
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $designer->fresh()->likes_count
        ]);
    }

    /**
     * Search users by name (for sharing marketplace posts)
     */
    public function searchUsers(Request $request, $locale)
    {
        $currentDesigner = auth('designer')->user();

        if (!$currentDesigner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $query = trim($request->input('q', ''));
        if (strlen($query) < 2) {
            return response()->json(['success' => true, 'users' => []]);
        }

        $users = Designer::where('is_active', true)
            ->where('id', '!=', $currentDesigner->id)
            ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $query . '%'])
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'avatar', 'city']);

        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'city' => $user->city,
            ];
        });

        return response()->json(['success' => true, 'users' => $results]);
    }
}
