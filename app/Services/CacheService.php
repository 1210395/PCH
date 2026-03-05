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
            return [
                'totals' => [
                    'designers' => Designer::count(),
                    'products' => Product::count(),
                    'projects' => Project::count(),
                    'services' => Service::count(),
                    'marketplacePosts' => MarketplacePost::count(),
                    'fablabs' => FabLab::count(),
                    'trainings' => Training::count(),
                    'tenders' => Tender::count(),
                ],
                'pending' => [
                    'products' => Product::where('approval_status', 'pending')->count(),
                    'projects' => Project::where('approval_status', 'pending')->count(),
                    'services' => Service::where('approval_status', 'pending')->count(),
                    'marketplace' => MarketplacePost::where('approval_status', 'pending')->count(),
                ],
                'status' => [
                    'products' => [
                        'pending' => Product::where('approval_status', 'pending')->count(),
                        'approved' => Product::where('approval_status', 'approved')->count(),
                        'rejected' => Product::where('approval_status', 'rejected')->count(),
                    ],
                    'projects' => [
                        'pending' => Project::where('approval_status', 'pending')->count(),
                        'approved' => Project::where('approval_status', 'approved')->count(),
                        'rejected' => Project::where('approval_status', 'rejected')->count(),
                    ],
                    'services' => [
                        'pending' => Service::where('approval_status', 'pending')->count(),
                        'approved' => Service::where('approval_status', 'approved')->count(),
                        'rejected' => Service::where('approval_status', 'rejected')->count(),
                    ],
                    'marketplace' => [
                        'pending' => MarketplacePost::where('approval_status', 'pending')->count(),
                        'approved' => MarketplacePost::where('approval_status', 'approved')->count(),
                        'rejected' => MarketplacePost::where('approval_status', 'rejected')->count(),
                    ],
                ],
                'designers' => [
                    'active' => Designer::where('is_active', true)->count(),
                    'inactive' => Designer::where('is_active', false)->count(),
                    'trusted' => Designer::where('is_trusted', true)->count(),
                    'admin' => Designer::where('is_admin', true)->count(),
                ],
                'growth' => [
                    'designers_today' => Designer::whereDate('created_at', now()->toDateString())->count(),
                    'designers_this_week' => Designer::where('created_at', '>=', now()->startOfWeek())->count(),
                    'designers_last_week' => Designer::where('created_at', '>=', now()->subWeek()->startOfWeek())
                        ->where('created_at', '<', now()->startOfWeek())->count(),
                    'designers_this_month' => Designer::where('created_at', '>=', now()->startOfMonth())->count(),
                    'designers_last_month' => Designer::where('created_at', '>=', now()->subMonth()->startOfMonth())
                        ->where('created_at', '<', now()->startOfMonth())->count(),
                    'content_this_week' => Product::where('created_at', '>=', now()->startOfWeek())->count()
                        + Project::where('created_at', '>=', now()->startOfWeek())->count()
                        + Service::where('created_at', '>=', now()->startOfWeek())->count()
                        + MarketplacePost::where('created_at', '>=', now()->startOfWeek())->count(),
                    'content_last_week' => Product::where('created_at', '>=', now()->subWeek()->startOfWeek())->where('created_at', '<', now()->startOfWeek())->count()
                        + Project::where('created_at', '>=', now()->subWeek()->startOfWeek())->where('created_at', '<', now()->startOfWeek())->count()
                        + Service::where('created_at', '>=', now()->subWeek()->startOfWeek())->where('created_at', '<', now()->startOfWeek())->count()
                        + MarketplacePost::where('created_at', '>=', now()->subWeek()->startOfWeek())->where('created_at', '<', now()->startOfWeek())->count(),
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
            // Per-sector counts for dynamic counter filtering
            $sectorCounts = Designer::where('is_admin', false)
                ->where('is_active', true)
                ->whereNotNull('sector')
                ->where('sector', '!=', '')
                ->selectRaw('sector, COUNT(*) as count')
                ->groupBy('sector')
                ->pluck('count', 'sector')
                ->toArray();

            // Vendors = anyone with "supplier" in their sector OR sub_sector (case-insensitive)
            $vendorCount = Designer::where('is_admin', false)->where('is_active', true)
                ->where(function($q) {
                    $q->where('sector', 'LIKE', '%supplier%')
                      ->orWhere('sub_sector', 'LIKE', '%supplier%');
                })->count();

            return [
                'designers' => Designer::where('is_admin', false)->where('is_active', true)->count(),
                'designers_only' => Designer::where('is_admin', false)->where('is_active', true)
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
                'companies' => Designer::where('is_admin', false)->where('is_active', true)
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
                'topDesigners' => Designer::select('designers.*')
                    ->selectRaw('(SELECT COUNT(*) FROM projects WHERE projects.designer_id = designers.id AND projects.approval_status = "approved") as projects_count')
                    ->where('is_admin', false)
                    ->where('is_active', true)
                    ->whereNotIn('sector', ['manufacturer', 'showroom'])
                    ->where('sector', 'NOT LIKE', '%supplier%')
                    ->where('sub_sector', 'NOT LIKE', '%supplier%')
                    ->with('skills:id,name')
                    ->orderByDesc('projects_count')
                    ->limit(8)
                    ->get(),

                'topManufacturers' => Designer::select('designers.*')
                    ->selectRaw('(SELECT COUNT(*) FROM products WHERE products.designer_id = designers.id AND products.approval_status = "approved") as products_count')
                    ->where('is_admin', false)
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->whereIn('sector', ['manufacturer', 'showroom'])
                          ->orWhere('sector', 'LIKE', '%supplier%')
                          ->orWhere('sub_sector', 'LIKE', '%supplier%');
                    })
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
                ->inRandomOrder()
                ->limit(20)
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
            return [
                'products' => [
                    'total' => Product::where('designer_id', $designerId)->count(),
                    'pending' => Product::where('designer_id', $designerId)->where('approval_status', 'pending')->count(),
                    'approved' => Product::where('designer_id', $designerId)->where('approval_status', 'approved')->count(),
                    'rejected' => Product::where('designer_id', $designerId)->where('approval_status', 'rejected')->count(),
                ],
                'projects' => [
                    'total' => Project::where('designer_id', $designerId)->count(),
                    'pending' => Project::where('designer_id', $designerId)->where('approval_status', 'pending')->count(),
                    'approved' => Project::where('designer_id', $designerId)->where('approval_status', 'approved')->count(),
                    'rejected' => Project::where('designer_id', $designerId)->where('approval_status', 'rejected')->count(),
                ],
                'services' => [
                    'total' => Service::where('designer_id', $designerId)->count(),
                    'pending' => Service::where('designer_id', $designerId)->where('approval_status', 'pending')->count(),
                    'approved' => Service::where('designer_id', $designerId)->where('approval_status', 'approved')->count(),
                    'rejected' => Service::where('designer_id', $designerId)->where('approval_status', 'rejected')->count(),
                ],
                'marketplace' => [
                    'total' => MarketplacePost::where('designer_id', $designerId)->count(),
                    'pending' => MarketplacePost::where('designer_id', $designerId)->where('approval_status', 'pending')->count(),
                    'approved' => MarketplacePost::where('designer_id', $designerId)->where('approval_status', 'approved')->count(),
                    'rejected' => MarketplacePost::where('designer_id', $designerId)->where('approval_status', 'rejected')->count(),
                ],
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
