<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\ImageUploadController;
use Illuminate\Support\Facades\RateLimiter;

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
    public function show($id)
{
    $designer = Designer::with(['projects.images', 'skills', 'products.images', 'services'])->findOrFail($id);

    // Format projects data for Alpine.js with defensive checks and proper image path handling
    $projectsData = ($designer->projects ?? collect())->map(function($p) {
        $images = $p->images ?? collect();
        // Convert relative paths to full asset URLs with thumbnails for optimization
        $imagePaths = $images->pluck('image_path')->filter()->map(function($path) {
            if (empty($path)) return null;
            try {
                $thumbPath = ImageUploadController::getThumbnailPath($path);
                return !empty($thumbPath) ? asset('storage/' . $thumbPath) : null;
            } catch (\Exception $e) {
                \Log::warning('Thumbnail generation failed for project image', ['path' => $path, 'error' => $e->getMessage()]);
                return asset('storage/' . $path);
            }
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
        // Convert relative paths to full asset URLs with thumbnails for optimization
        $imagePaths = $images->pluck('image_path')->filter()->map(function($path) {
            if (empty($path)) return null;
            try {
                $thumbPath = ImageUploadController::getThumbnailPath($path);
                return !empty($thumbPath) ? asset('storage/' . $thumbPath) : null;
            } catch (\Exception $e) {
                \Log::warning('Thumbnail generation failed for product image', ['path' => $path, 'error' => $e->getMessage()]);
                return asset('storage/' . $path);
            }
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

    // Get similar designers (same sector or skills) - optional
    $similarDesigners = Designer::where('id', '!=', $id)
        ->where('sector', $designer->sector ?? '')
        ->take(4)
        ->get();

    // Get optimized image paths for avatar and cover with fallback
    $avatarThumb = '';
    $coverThumb = '';

    try {
        if (!empty($designer->avatar)) {
            $avatarThumb = ImageUploadController::getThumbnailPath($designer->avatar);
            if (empty($avatarThumb)) {
                $avatarThumb = $designer->avatar;
            }
        }
    } catch (\Exception $e) {
        \Log::warning('Avatar thumbnail generation failed', ['designer_id' => $id, 'error' => $e->getMessage()]);
        $avatarThumb = $designer->avatar ?? '';
    }

    try {
        if (!empty($designer->cover_image)) {
            $coverThumb = ImageUploadController::getThumbnailPath($designer->cover_image);
            if (empty($coverThumb)) {
                $coverThumb = $designer->cover_image;
            }
        }
    } catch (\Exception $e) {
        \Log::warning('Cover thumbnail generation failed', ['designer_id' => $id, 'error' => $e->getMessage()]);
        $coverThumb = $designer->cover_image ?? '';
    }

    return view('designer-portfolio-new', compact(
        'designer',
        'projectsData',
        'productsData',
        'servicesData',
        'similarDesigners',
        'avatarThumb',
        'coverThumb'
    ));
}

    public function index(Request $request)
    {
        $query = Designer::query();

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sort = $request->get('sort', 'popular');
        switch ($sort) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'most_projects':
                $query->orderBy('projects_count', 'desc');
                break;
            default:
                $query->orderBy('followers_count', 'desc');
        }

        $designers = $query->paginate(12);

        return view('designers', compact('designers'));
    }

    public function showProfile()
    {
        // Get authenticated designer with relationships
        $designer = auth('designer')->user();

        if (!$designer) {
            return redirect()->route('login', ['locale' => app()->getLocale()])
                ->with('error', 'Please login to view your profile');
        }

        $designer->load([
            'skills',
            'projects.images',
            'products.images',
            'services'
        ]);

        // Format projects data for Alpine.js with defensive checks and proper image path handling
        $projectsData = ($designer->projects ?? collect())->map(function($p) {
            $images = $p->images ?? collect();
            // Convert relative paths to full asset URLs with thumbnails
            $imagePaths = $images->pluck('image_path')->filter()->map(function($path) {
                if (empty($path)) return null;
                try {
                    $thumbPath = ImageUploadController::getThumbnailPath($path);
                    return !empty($thumbPath) ? asset('storage/' . $thumbPath) : null;
                } catch (\Exception $e) {
                    \Log::warning('Thumbnail generation failed for project image', ['path' => $path, 'error' => $e->getMessage()]);
                    return asset('storage/' . $path);
                }
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
            // Convert relative paths to full asset URLs with thumbnails
            $imagePaths = $images->pluck('image_path')->filter()->map(function($path) {
                if (empty($path)) return null;
                try {
                    $thumbPath = ImageUploadController::getThumbnailPath($path);
                    return !empty($thumbPath) ? asset('storage/' . $thumbPath) : null;
                } catch (\Exception $e) {
                    \Log::warning('Thumbnail generation failed for product image', ['path' => $path, 'error' => $e->getMessage()]);
                    return asset('storage/' . $path);
                }
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

        // Get similar designers (same sector or skills)
        $similarDesigners = Designer::where('id', '!=', $designer->id)
            ->where('sector', $designer->sector ?? '')
            ->take(5)
            ->get();

        // Get optimized image paths for avatar and cover with fallback
        $avatarThumb = '';
        $coverThumb = '';

        try {
            if (!empty($designer->avatar)) {
                $avatarThumb = ImageUploadController::getThumbnailPath($designer->avatar);
                if (empty($avatarThumb)) {
                    $avatarThumb = $designer->avatar;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Avatar thumbnail generation failed', ['designer_id' => $designer->id, 'error' => $e->getMessage()]);
            $avatarThumb = $designer->avatar ?? '';
        }

        try {
            if (!empty($designer->cover_image)) {
                $coverThumb = ImageUploadController::getThumbnailPath($designer->cover_image);
                if (empty($coverThumb)) {
                    $coverThumb = $designer->cover_image;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Cover thumbnail generation failed', ['designer_id' => $designer->id, 'error' => $e->getMessage()]);
            $coverThumb = $designer->cover_image ?? '';
        }

        return view('designer-portfolio-new', compact(
            'designer',
            'projectsData',
            'productsData',
            'servicesData',
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

        $designer->load([
            'skills',
            'projects.images',
            'products.images',
            'services'
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

        // Format certifications data for Alpine.js
        $certificationsData = [];
        if (!empty($designer->certifications) && is_array($designer->certifications)) {
            foreach ($designer->certifications as $index => $path) {
                $certificationsData[] = [
                    'id' => $index,
                    'name' => basename($path),
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                ];
            }
        }

        return view('profile-edit', compact('designer', 'projectsData', 'productsData', 'servicesData', 'certificationsData'));
    }

    public function updateCertifications(Request $request)
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
                'certifications' => 'nullable|array|max:3',
                'certifications.*' => 'nullable|string|max:500',
                'new_certification' => 'nullable|file|mimes:pdf|max:10240',
                'remove_index' => 'nullable|integer|min:0|max:2',
            ]);

            $currentCerts = $designer->certifications ?? [];

            // Handle removal
            if ($request->has('remove_index') && isset($currentCerts[$request->remove_index])) {
                $removedPath = $currentCerts[$request->remove_index];
                // Delete the file from storage
                if (\Storage::disk('public')->exists($removedPath)) {
                    \Storage::disk('public')->delete($removedPath);
                }
                array_splice($currentCerts, $request->remove_index, 1);
                $designer->update(['certifications' => array_values($currentCerts)]);

                return response()->json([
                    'success' => true,
                    'message' => 'Certification removed successfully',
                    'certifications' => array_values($currentCerts),
                ]);
            }

            // Handle new upload
            if ($request->hasFile('new_certification')) {
                if (count($currentCerts) >= 3) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Maximum 3 certifications allowed'
                    ], 422);
                }

                $file = $request->file('new_certification');
                $filename = 'cert_' . $designer->id . '_' . (count($currentCerts) + 1) . '_' . time() . '.pdf';
                $path = $file->storeAs('certifications', $filename, 'public');

                $currentCerts[] = $path;
                $designer->update(['certifications' => array_values($currentCerts)]);

                return response()->json([
                    'success' => true,
                    'message' => 'Certification uploaded successfully',
                    'certifications' => array_values($currentCerts),
                    'new_cert' => [
                        'id' => count($currentCerts) - 1,
                        'name' => $filename,
                        'path' => $path,
                        'url' => asset('storage/' . $path),
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No action specified'
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Certifications update failed', [
                'designer_id' => auth('designer')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating certifications'
            ], 500);
        }
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

            $validated = $request->validate([
                'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\'-]+$/',
                'title' => 'nullable|string|max:255',
                'bio' => 'nullable|string|max:1000',
                'phone' => 'nullable|string|max:20|regex:/^[0-9\s\+\-\(\)]+$/',
                'city' => 'nullable|string|max:100',
                'address' => 'nullable|string|max:255',
                'website' => 'nullable|url|max:255',
                'skills' => 'nullable|string|max:500',
                'avatar' => 'nullable|string|max:500|regex:/^[a-zA-Z0-9\/_\-\.]+$/',
                'cover_image' => 'nullable|string|max:500|regex:/^[a-zA-Z0-9\/_\-\.]+$/',
            ]);

            // Update basic info (email is not updatable)
            $updateData = [
                'name' => $validated['name'],
                'title' => strip_tags($validated['title'] ?? '') ?: null,
                'bio' => strip_tags($validated['bio'] ?? '') ?: null,
                'phone_number' => $validated['phone'] ?? null,
                'city' => strip_tags($validated['city'] ?? '') ?: null,
                'address' => strip_tags($validated['address'] ?? '') ?: null,
                'website' => $validated['website'] ?? null,
            ];

            // Add avatar if provided - with security checks
            if (isset($validated['avatar']) && !empty($validated['avatar'])) {
                // Prevent path traversal attacks
                $avatarPath = str_replace(['../', '..\\', './'], '', $validated['avatar']);
                // Ensure path doesn't start with /
                $avatarPath = ltrim($avatarPath, '/\\');
                // Verify file exists in storage
                if (\Storage::disk('public')->exists($avatarPath)) {
                    $updateData['avatar'] = $avatarPath;
                } else {
                    \Log::warning('Avatar file does not exist', ['path' => $avatarPath, 'designer_id' => $designer->id]);
                }
            }

            // Add cover_image if provided - with security checks
            if (isset($validated['cover_image']) && !empty($validated['cover_image'])) {
                // Prevent path traversal attacks
                $coverPath = str_replace(['../', '..\\', './'], '', $validated['cover_image']);
                // Ensure path doesn't start with /
                $coverPath = ltrim($coverPath, '/\\');
                // Verify file exists in storage
                if (\Storage::disk('public')->exists($coverPath)) {
                    $updateData['cover_image'] = $coverPath;
                } else {
                    \Log::warning('Cover image file does not exist', ['path' => $coverPath, 'designer_id' => $designer->id]);
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
}
