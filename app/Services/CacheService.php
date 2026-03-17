<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Designer;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\MarketplacePost;
use App\Models\FabLab;
use App\Models\Training;
use App\Models\Tender;
use App\Models\Notification;

/**
 * Centralised cache management for the Palestine Creative Hub.
 *
 * All methods are static so they can be called from model event hooks,
 * controllers, and middleware without dependency injection. Each method wraps
 * an expensive query set in a Cache::remember() call using one of the TTL
 * constants defined below.
 */
class CacheService
{
    // Cache TTL constants (in seconds)
    const TTL_SHORT = 60;        // 1 minute
    const TTL_MEDIUM = 300;      // 5 minutes
    const TTL_LONG = 900;        // 15 minutes
    const TTL_HOUR = 3600;       // 1 hour

    /**
     * Get dashboard statistics with caching
     */
    public static function getDashboardStats(): array
    {
        return Cache::remember('admin_dashboard_stats', self::TTL_MEDIUM, function() {
            // Consolidate approval status counts into single queries per model (saves ~20 queries)
            $productStats = Product::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected
            ")->first();

            $projectStats = Project::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected
            ")->first();

            $serviceStats = Service::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected
            ")->first();

            $marketplaceStats = MarketplacePost::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected
            ")->first();

            // Consolidate designer stats into single query
            $designerStats = Designer::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN is_trusted = 1 THEN 1 ELSE 0 END) as trusted,
                SUM(CASE WHEN is_admin = 1 THEN 1 ELSE 0 END) as admin
            ")->first();

            // Consolidate growth metrics
            $weekStart = now()->startOfWeek();
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $monthStart = now()->startOfMonth();
            $lastMonthStart = now()->subMonth()->startOfMonth();

            $designerGrowth = Designer::selectRaw("
                SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as this_week,
                SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as last_week,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as this_month,
                SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as last_month
            ", [now()->toDateString(), $weekStart, $lastWeekStart, $weekStart, $monthStart, $lastMonthStart, $monthStart])->first();

            // Content growth - single query per model for this week/last week
            $contentThisWeek = Product::where('created_at', '>=', $weekStart)->count()
                + Project::where('created_at', '>=', $weekStart)->count()
                + Service::where('created_at', '>=', $weekStart)->count()
                + MarketplacePost::where('created_at', '>=', $weekStart)->count();

            $contentLastWeek = Product::where('created_at', '>=', $lastWeekStart)->where('created_at', '<', $weekStart)->count()
                + Project::where('created_at', '>=', $lastWeekStart)->where('created_at', '<', $weekStart)->count()
                + Service::where('created_at', '>=', $lastWeekStart)->where('created_at', '<', $weekStart)->count()
                + MarketplacePost::where('created_at', '>=', $lastWeekStart)->where('created_at', '<', $weekStart)->count();

            return [
                'totals' => [
                    'designers' => $designerStats->total,
                    'products' => $productStats->total,
                    'projects' => $projectStats->total,
                    'services' => $serviceStats->total,
                    'marketplacePosts' => $marketplaceStats->total,
                    'fablabs' => FabLab::count(),
                    'trainings' => Training::count(),
                    'tenders' => Tender::count(),
                ],
                'pending' => [
                    'products' => $productStats->pending,
                    'projects' => $projectStats->pending,
                    'services' => $serviceStats->pending,
                    'marketplace' => $marketplaceStats->pending,
                ],
                'status' => [
                    'products' => ['pending' => $productStats->pending, 'approved' => $productStats->approved, 'rejected' => $productStats->rejected],
                    'projects' => ['pending' => $projectStats->pending, 'approved' => $projectStats->approved, 'rejected' => $projectStats->rejected],
                    'services' => ['pending' => $serviceStats->pending, 'approved' => $serviceStats->approved, 'rejected' => $serviceStats->rejected],
                    'marketplace' => ['pending' => $marketplaceStats->pending, 'approved' => $marketplaceStats->approved, 'rejected' => $marketplaceStats->rejected],
                ],
                'designers' => [
                    'active' => $designerStats->active,
                    'inactive' => $designerStats->inactive,
                    'trusted' => $designerStats->trusted,
                    'admin' => $designerStats->admin,
                ],
                'growth' => [
                    'designers_today' => $designerGrowth->today,
                    'designers_this_week' => $designerGrowth->this_week,
                    'designers_last_week' => $designerGrowth->last_week,
                    'designers_this_month' => $designerGrowth->this_month,
                    'designers_last_month' => $designerGrowth->last_month,
                    'content_this_week' => $contentThisWeek,
                    'content_last_week' => $contentLastWeek,
                ],
                'sectors' => Designer::where('is_admin', false)
                    ->whereNotNull('sector')
                    ->where('sector', '!=', '')
                    ->selectRaw('sector, COUNT(*) as count')
                    ->groupBy('sector')
                    ->orderByDesc('count')
                    ->pluck('count', 'sector')
                    ->toArray(),
                'cities' => Designer::where('is_admin', false)
                    ->whereNotNull('city')
                    ->where('city', '!=', '')
                    ->selectRaw('city, COUNT(*) as count')
                    ->groupBy('city')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->pluck('count', 'city')
                    ->toArray(),
                'registrations_daily' => Designer::where('created_at', '>=', now()->subDays(6)->startOfDay())
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('count', 'date')
                    ->toArray(),
            ];
        });
    }

    /**
     * Get homepage statistics with caching
     */
    public static function getHomepageStats(): array
    {
        return Cache::remember('homepage_stats', self::TTL_LONG, function() {
            // Per-sector counts for dynamic counter filtering (exclude guests)
            $sectorCounts = Designer::where('is_admin', false)
                ->where('is_active', true)
                ->where('sector', '!=', 'guest')
                ->whereNotNull('sector')
                ->where('sector', '!=', '')
                ->selectRaw('sector, COUNT(*) as count')
                ->groupBy('sector')
                ->pluck('count', 'sector')
                ->toArray();

            // Vendors = anyone with "supplier" in their sector OR sub_sector (case-insensitive)
            $vendorCount = Designer::where('is_admin', false)->where('is_active', true)->where('sector', '!=', 'guest')
                ->where(function($q) {
                    $q->where('sector', 'LIKE', '%supplier%')
                      ->orWhere('sub_sector', 'LIKE', '%supplier%');
                })->count();

            return [
                'designers' => Designer::where('is_admin', false)->where('is_active', true)->where('sector', '!=', 'guest')->count(),
                'designers_only' => Designer::where('is_admin', false)->where('is_active', true)->where('sector', '!=', 'guest')
                    ->whereNotIn('sector', ['manufacturer', 'showroom'])
                    ->where('sector', 'NOT LIKE', '%supplier%')
                    ->where('sub_sector', 'NOT LIKE', '%supplier%')->count(),
                'products' => Product::where('approval_status', 'approved')->count(),
                'projects' => Project::where('approval_status', 'approved')->count(),
                'services' => Service::where('approval_status', 'approved')->count(),
                'fablabs' => FabLab::count(),
                'trainings' => Training::count(),
                'tenders' => Tender::count(),
                'marketplace' => MarketplacePost::where('approval_status', 'approved')->count(),
                'companies' => Designer::where('is_admin', false)->where('is_active', true)->where('sector', '!=', 'guest')
                    ->where(function($q) {
                        $q->whereIn('sector', ['manufacturer', 'showroom'])
                          ->orWhere('sector', 'LIKE', '%supplier%')
                          ->orWhere('sub_sector', 'LIKE', '%supplier%');
                    })->count(),
                'vendors' => $vendorCount,
                'sector_counts' => $sectorCounts,
            ];
        });
    }

    /**
     * Get featured content for homepage with caching
     */
    public static function getFeaturedContent(): array
    {
        return Cache::remember('homepage_featured', self::TTL_LONG, function() {
            return [
                'topDesigners' => Designer::select('id', 'name', 'avatar', 'sector', 'sub_sector', 'city', 'bio', 'followers_count')
                    ->where('is_admin', false)
                    ->where('is_active', true)
                    ->where('sector', '!=', 'guest')
                    ->whereNotIn('sector', ['manufacturer', 'showroom'])
                    ->where('sector', 'NOT LIKE', '%supplier%')
                    ->where('sub_sector', 'NOT LIKE', '%supplier%')
                    ->withCount(['projects' => fn($q) => $q->where('approval_status', 'approved')])
                    ->with('skills:id,name')
                    ->orderByDesc('projects_count')
                    ->limit(8)
                    ->get(),

                'topManufacturers' => Designer::select('id', 'name', 'avatar', 'sector', 'sub_sector', 'city', 'bio', 'followers_count')
                    ->where('is_admin', false)
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->whereIn('sector', ['manufacturer', 'showroom'])
                          ->orWhere('sector', 'LIKE', '%supplier%')
                          ->orWhere('sub_sector', 'LIKE', '%supplier%');
                    })
                    ->withCount(['products' => fn($q) => $q->where('approval_status', 'approved')])
                    ->with('skills:id,name')
                    ->orderByDesc('products_count')
                    ->limit(8)
                    ->get(),

                'featuredProjects' => Project::select('id', 'title', 'description', 'designer_id', 'category', 'created_at')
                    ->with(['designer:id,name,avatar', 'images:id,project_id,image_path'])
                    ->where('approval_status', 'approved')
                    ->latest()
                    ->limit(8)
                    ->get(),

                'featuredProducts' => Product::select('id', 'title', 'description', 'designer_id', 'category', 'created_at')
                    ->with(['designer:id,name,avatar', 'images:id,product_id,image_path'])
                    ->where('approval_status', 'approved')
                    ->latest()
                    ->limit(8)
                    ->get(),
            ];
        });
    }

    /**
     * Get notification unread count for a designer
     */
    public static function getUnreadNotificationCount(int $designerId): int
    {
        return Cache::remember("designer_{$designerId}_unread_notifications", self::TTL_SHORT, function() use ($designerId) {
            return Notification::where('designer_id', $designerId)
                ->where('read', false)
                ->count();
        });
    }

    /**
     * Get marketplace categories with caching
     */
    public static function getMarketplaceCategories(): \Illuminate\Support\Collection
    {
        return Cache::remember('marketplace_categories', self::TTL_HOUR, function() {
            return MarketplacePost::where('approval_status', 'approved')
                ->whereHas('designer', function($d) {
                    $d->where('is_active', true)->where('is_admin', false);
                })
                ->distinct()
                ->pluck('category')
                ->filter()
                ->sort()
                ->values();
        });
    }

    /**
     * Get marketplace tags with caching (from CMS dropdown options)
     */
    public static function getMarketplaceTags(): \Illuminate\Support\Collection
    {
        return Cache::remember('marketplace_tags', self::TTL_HOUR, function() {
            return collect(\App\Helpers\DropdownHelper::marketplaceTags())->values();
        });
    }

    /**
     * Get similar designers by sector with caching
     */
    public static function getSimilarDesigners(int $excludeId, ?string $sector, int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "similar_designers_" . ($sector ?? 'default');

        $designers = Cache::remember($cacheKey, self::TTL_LONG, function() use ($sector) {
            return Designer::where('is_admin', false)
                ->where('is_active', true)
                ->where('sector', $sector ?? '')
                ->select('id', 'name', 'avatar', 'sector', 'sub_sector', 'city', 'followers_count')
                ->orderByDesc('followers_count')
                ->limit(10)
                ->get();
        });

        return $designers->reject(fn($d) => $d->id === $excludeId)->take($limit);
    }

    /**
     * Get designer content stats with caching
     */
    public static function getDesignerContentStats(int $designerId): array
    {
        return Cache::remember("designer_{$designerId}_content_stats", self::TTL_MEDIUM, function() use ($designerId) {
            // Consolidate 16 queries into 4 using conditional aggregation
            $statusQuery = "
                COUNT(*) as total,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected
            ";

            $products = Product::where('designer_id', $designerId)->selectRaw($statusQuery)->first();
            $projects = Project::where('designer_id', $designerId)->selectRaw($statusQuery)->first();
            $services = Service::where('designer_id', $designerId)->selectRaw($statusQuery)->first();
            $marketplace = MarketplacePost::where('designer_id', $designerId)->selectRaw($statusQuery)->first();

            return [
                'products' => ['total' => $products->total, 'pending' => $products->pending, 'approved' => $products->approved, 'rejected' => $products->rejected],
                'projects' => ['total' => $projects->total, 'pending' => $projects->pending, 'approved' => $projects->approved, 'rejected' => $projects->rejected],
                'services' => ['total' => $services->total, 'pending' => $services->pending, 'approved' => $services->approved, 'rejected' => $services->rejected],
                'marketplace' => ['total' => $marketplace->total, 'pending' => $marketplace->pending, 'approved' => $marketplace->approved, 'rejected' => $marketplace->rejected],
            ];
        });
    }

    /**
     * Clear all dashboard-related caches
     */
    public static function clearDashboardCache(): void
    {
        Cache::forget('admin_dashboard_stats');
        Cache::forget('homepage_stats');
        Cache::forget('homepage_featured');
    }

    /**
     * Clear designer-specific caches
     */
    public static function clearDesignerCache(int $designerId): void
    {
        Cache::forget("designer_{$designerId}_unread_notifications");
        Cache::forget("designer_{$designerId}_content_stats");
    }

    /**
     * Clear marketplace caches
     */
    public static function clearMarketplaceCache(): void
    {
        Cache::forget('marketplace_categories');
        Cache::forget('marketplace_tags');
    }

    /**
     * Clear all caches (for admin use)
     */
    public static function clearAllCaches(): void
    {
        self::clearDashboardCache();
        self::clearMarketplaceCache();
        // Note: Designer-specific caches are cleared individually
    }
}
