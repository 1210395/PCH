<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\NotificationController;
use App\Services\CacheService;

/**
 * DesignerController
 *
 * Public-facing designer routes:
 * - index() - designers listing page
 * - show() - public portfolio view
 * - trackView() - track profile views
 *
 * Profile management moved to DesignerProfileController.
 * Social interactions moved to DesignerFollowController.
 */
class DesignerController extends Controller
{
    /**
     * Display a designer's public portfolio page.
     *
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\View\View
     */
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
                'image' => $m->image ? url('media/' . $m->image) : null,
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

    /**
     * Display the public designers listing with filtering and sorting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $locale)
    {
        $query = Designer::query()
            ->select('id', 'name', 'email', 'avatar', 'cover_image', 'sector', 'sub_sector', 'city', 'bio', 'title', 'company_name', 'followers_count', 'views_count', 'created_at', 'is_active', 'is_admin')
            ->with('skills:id,name');

        // Exclude admin, inactive, and guest accounts from public listings
        $query->where('is_admin', false)->where('is_active', true)->where('sector', '!=', 'guest');

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

        // Search using FULLTEXT index for better performance
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strip_tags($request->search);
            $query->whereRaw('MATCH(name, bio, sector, sub_sector, city) AGAINST(? IN BOOLEAN MODE)', [$searchTerm . '*']);
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

        $designers = $query->simplePaginate(12)->appends($request->query());

        // Get counts for each type (excluding admin and inactive accounts)
        $stats = CacheService::getHomepageStats();
        $allCount = $stats['designers'];
        $designersCount = $stats['designers_only'];
        $manufacturersCount = $stats['companies'];
        $vendorsCount = $stats['vendors'] ?? 0;

        return view('designers', compact('designers', 'type', 'sector', 'sort', 'allCount', 'designersCount', 'manufacturersCount', 'vendorsCount'));
    }

    /**
     * Increment the view count for a designer profile via AJAX (skips self-views).
     *
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\JsonResponse
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
}
