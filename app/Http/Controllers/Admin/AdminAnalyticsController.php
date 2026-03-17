<?php

namespace App\Http\Controllers\Admin;

use App\Models\Designer;
use App\Models\Like;
use App\Models\PageVisit;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectView;
use App\Models\Service;
use App\Models\MarketplacePost;
use App\Models\ProfileRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalyticsExport;

/**
 * Provides advanced platform analytics for admin users.
 *
 * Computes designer growth, content trends, approval workflow stats,
 * geographic distribution, ratings trends, and top contributors.
 * Results are cached under a versioned key and can be exported to Excel.
 */
class AdminAnalyticsController extends AdminBaseController
{
    /**
     * Display the analytics dashboard with optional date, sector, and city filters.
     *
     * Preset shortcuts (7d, 30d, 90d, 1y, all) are converted to concrete date
     * ranges. Results are cached for 5 minutes under a version-stamped key so
     * that refreshing the cache invalidates all filter combinations at once.
     *
     * @param  Request  $request
     * @param  string   $locale  Active locale (en|ar)
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $locale)
    {
        $preset   = $request->get('preset', '30d');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $sector   = $request->get('sector');
        $city     = $request->get('city');

        if ($preset !== 'custom') {
            [$dateFrom, $dateTo] = $this->presetToDates($preset);
        }

        $filters = compact('preset', 'dateFrom', 'dateTo', 'sector', 'city');

        // Version-based cache key – incrementing the version invalidates all analytics caches at once
        $version  = Cache::get('admin:analytics:version', 1);
        $cacheKey = "admin:analytics:{$version}:" . md5(json_encode($filters));

        $cached = Cache::remember($cacheKey, 300, function () use ($filters) {
            return [
                'data'      => $this->computeAnalytics($filters),
                'cached_at' => now()->toISOString(),
            ];
        });

        $data     = $cached['data'];
        $cachedAt = Carbon::parse($cached['cached_at']);

        // Dropdown options for filter UI
        $sectors = Designer::where('is_admin', false)
            ->whereNotNull('sector')->where('sector', '!=', '')->where('sector', '!=', 'guest')
            ->distinct()->orderBy('sector')->pluck('sector');

        $cities = Designer::where('is_admin', false)
            ->whereNotNull('city')->where('city', '!=', '')
            ->distinct()->orderBy('city')->pluck('city');

        return view('admin.analytics.index', compact(
            'data', 'cachedAt', 'filters', 'sectors', 'cities'
        ));
    }

    /**
     * Export the current analytics dataset as a multi-sheet Excel file.
     *
     * Applies the same filter logic as `index()` but skips the cache and
     * streams the file directly using `AnalyticsExport`.
     *
     * @param  Request  $request
     * @param  string   $locale
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request, $locale)
    {
        $preset   = $request->get('preset', '30d');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $sector   = $request->get('sector');
        $city     = $request->get('city');

        if ($preset !== 'custom') {
            [$dateFrom, $dateTo] = $this->presetToDates($preset);
        }

        $filters  = compact('preset', 'dateFrom', 'dateTo', 'sector', 'city');
        $data     = $this->computeAnalytics($filters);
        $filename = 'analytics-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new AnalyticsExport($data, $filters), $filename);
    }

    /**
     * Invalidate all analytics caches by incrementing the global version counter.
     *
     * Redirects back to the analytics index with the current filters preserved.
     *
     * @param  Request  $request
     * @param  string   $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refresh(Request $request, $locale)
    {
        Cache::increment('admin:analytics:version');

        return redirect()
            ->route('admin.analytics.index', array_merge(['locale' => $locale], $request->except(['_token', '_method'])))
            ->with('success', 'Analytics cache refreshed.');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Run all analytics queries for the given filter set and return a data array.
     *
     * Covers: KPIs, designer growth, content trends, approval workflow,
     * average time-to-approve, geographic distribution, sector breakdown,
     * ratings trend, and top 15 designers by content count.
     *
     * @param  array{preset: string, dateFrom: ?string, dateTo: ?string, sector: ?string, city: ?string}  $filters
     * @return array<string, mixed>
     */
    private function computeAnalytics(array $filters): array
    {
        $dateFrom = $filters['dateFrom'] ?? null;
        $dateTo   = $filters['dateTo'] ?? null;
        $sector   = $filters['sector'] ?? null;
        $city     = $filters['city'] ?? null;

        $endOfDay = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;

        // ---- KPIs (always global – no date/sector/city filter) ---------------
        $totalDesigners  = Designer::where('is_admin', false)->where('sector', '!=', 'guest')->count();
        $activeDesigners = Designer::where('is_admin', false)->where('sector', '!=', 'guest')->where('is_active', true)->count();

        $pendingTotal = Product::pending()->count()
            + Project::pending()->count()
            + Service::pending()->count()
            + MarketplacePost::pending()->count()
            + ProfileRating::where('status', 'pending')->count();

        $totalApprovedContent = Product::approved()->count()
            + Project::approved()->count()
            + Service::approved()->count()
            + MarketplacePost::approved()->count();

        $totalRatings  = ProfileRating::where('status', 'approved')->count();
        $averageRating = round(ProfileRating::where('status', 'approved')->avg('rating') ?? 0, 2);

        // ---- Designer Growth (monthly, date/sector/city filtered) ------------
        $designerGrowthRaw = Designer::where('is_admin', false)
            ->where('sector', '!=', 'guest')
            ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($endOfDay, fn($q) => $q->where('created_at', '<=', $endOfDay))
            ->when($sector,   fn($q) => $q->where('sector', $sector))
            ->when($city,     fn($q) => $q->where('city', $city))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // ---- Content Trends (monthly, date filtered) -------------------------
        $applyDates = function ($query) use ($dateFrom, $endOfDay) {
            if ($dateFrom) $query->where('created_at', '>=', $dateFrom);
            if ($endOfDay) $query->where('created_at', '<=', $endOfDay);
        };

        $productsByMonth = Product::when($dateFrom || $endOfDay, $applyDates)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')->pluck('count', 'month');

        $projectsByMonth = Project::when($dateFrom || $endOfDay, $applyDates)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')->pluck('count', 'month');

        $servicesByMonth = Service::when($dateFrom || $endOfDay, $applyDates)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')->pluck('count', 'month');

        $marketByMonth = MarketplacePost::when($dateFrom || $endOfDay, $applyDates)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')->pluck('count', 'month');

        $contentMonths = $productsByMonth->keys()
            ->merge($projectsByMonth->keys())
            ->merge($servicesByMonth->keys())
            ->merge($marketByMonth->keys())
            ->unique()->sort()->values();

        $contentTrends = $contentMonths->map(fn($m) => [
            'month'       => $m,
            'products'    => $productsByMonth->get($m, 0),
            'projects'    => $projectsByMonth->get($m, 0),
            'services'    => $servicesByMonth->get($m, 0),
            'marketplace' => $marketByMonth->get($m, 0),
        ])->values();

        // Merge designer growth months with content months for a unified timeline
        $allMonths = $designerGrowthRaw->keys()
            ->merge($contentMonths)
            ->unique()->sort()->values();

        $designerGrowth = $allMonths->map(fn($m) => [
            'month' => $m,
            'count' => $designerGrowthRaw->get($m, 0),
        ])->values();

        // ---- Approval Workflow (global) ---------------------------------------
        $approvalWorkflow = [];
        foreach ([
            'Products'    => Product::class,
            'Projects'    => Project::class,
            'Services'    => Service::class,
            'Marketplace' => MarketplacePost::class,
        ] as $label => $model) {
            $counts = $model::selectRaw('approval_status, COUNT(*) as cnt')
                ->groupBy('approval_status')
                ->pluck('cnt', 'approval_status');

            $approvalWorkflow[] = [
                'type'     => $label,
                'pending'  => $counts->get('pending', 0),
                'approved' => $counts->get('approved', 0),
                'rejected' => $counts->get('rejected', 0),
            ];
        }

        // ---- Avg Time to Approve in hours (global) ---------------------------
        $avgApprovalTime = [];
        foreach ([
            'Products'    => Product::class,
            'Projects'    => Project::class,
            'Services'    => Service::class,
            'Marketplace' => MarketplacePost::class,
        ] as $label => $model) {
            $avg = $model::where('approval_status', 'approved')
                ->whereNotNull('approved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, approved_at)) as avg_hours')
                ->value('avg_hours');

            $avgApprovalTime[] = [
                'type'      => $label,
                'avg_hours' => $avg !== null ? round($avg, 1) : 0,
            ];
        }

        // ---- Geographic Distribution (sector/city filtered) ------------------
        $byCity = Designer::where('is_admin', false)
            ->where('sector', '!=', 'guest')
            ->whereNotNull('city')->where('city', '!=', '')
            ->when($sector, fn($q) => $q->where('sector', $sector))
            ->selectRaw('city, COUNT(*) as count')
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(15)
            ->get();

        // ---- Sector Breakdown (city filtered) --------------------------------
        $bySector = Designer::where('is_admin', false)
            ->where('sector', '!=', 'guest')
            ->whereNotNull('sector')->where('sector', '!=', '')
            ->when($city, fn($q) => $q->where('city', $city))
            ->selectRaw('sector, COUNT(*) as count')
            ->groupBy('sector')
            ->orderByDesc('count')
            ->get();

        // ---- Ratings Trend (monthly, date filtered) --------------------------
        $ratingsTrend = ProfileRating::where('status', 'approved')
            ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($endOfDay, fn($q) => $q->where('created_at', '<=', $endOfDay))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, ROUND(AVG(rating), 2) as avg_rating, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month'      => $r->month,
                'avg_rating' => (float) $r->avg_rating,
                'count'      => (int) $r->count,
            ])
            ->values();

        // ---- Top Designers by content count (sector/city filtered) -----------
        $topDesigners = Designer::select('designers.id', 'designers.name', 'designers.city', 'designers.sector')
            ->where('designers.is_admin', false)
            ->where('designers.sector', '!=', 'guest')
            ->when($sector, fn($q) => $q->where('designers.sector', $sector))
            ->when($city,   fn($q) => $q->where('designers.city', $city))
            ->withCount([
                'products as products_count',
                'projects as projects_count',
                'services as services_count',
                'marketplacePosts as marketplace_count',
            ])
            ->get()
            ->map(fn($d) => [
                'id'          => $d->id,
                'name'        => $d->name,
                'city'        => $d->city,
                'sector'      => $d->sector,
                'products'    => $d->products_count,
                'projects'    => $d->projects_count,
                'services'    => $d->services_count,
                'marketplace' => $d->marketplace_count,
                'total'       => $d->products_count + $d->projects_count + $d->services_count + $d->marketplace_count,
            ])
            ->sortByDesc('total')
            ->values()
            ->take(15);

        // ---- Top viewed content (across all approved types) ------------------
        $topViewedContent = collect()
            ->merge(Product::approved()->select('id', 'title', 'views_count', 'likes_count')->orderByDesc('views_count')->limit(20)->get()->map(fn($i) => ['type' => 'Product',      'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count, 'likes' => (int)$i->likes_count]))
            ->merge(Project::approved()->select('id', 'title', 'views_count', 'likes_count')->orderByDesc('views_count')->limit(20)->get()->map(fn($i) => ['type' => 'Project',      'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count, 'likes' => (int)$i->likes_count]))
            ->merge(Service::approved()->select('id', 'name as title', 'views_count')->orderByDesc('views_count')->limit(20)->get()->map(fn($i) => ['type' => 'Service',      'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count, 'likes' => 0]))
            ->merge(MarketplacePost::approved()->select('id', 'title', 'views_count', 'likes_count')->orderByDesc('views_count')->limit(20)->get()->map(fn($i) => ['type' => 'Marketplace', 'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count, 'likes' => (int)$i->likes_count]))
            ->sortByDesc('views')
            ->values()
            ->take(15);

        // ---- Top liked content (across all approved types) -------------------
        $topLikedContent = collect()
            ->merge(Product::approved()->select('id', 'title', 'views_count', 'likes_count')->orderByDesc('likes_count')->limit(20)->get()->map(fn($i) => ['type' => 'Product',      'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count, 'likes' => (int)$i->likes_count]))
            ->merge(Project::approved()->select('id', 'title', 'views_count', 'likes_count')->orderByDesc('likes_count')->limit(20)->get()->map(fn($i) => ['type' => 'Project',      'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count, 'likes' => (int)$i->likes_count]))
            ->merge(MarketplacePost::approved()->select('id', 'title', 'views_count', 'likes_count')->orderByDesc('likes_count')->limit(20)->get()->map(fn($i) => ['type' => 'Marketplace', 'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count, 'likes' => (int)$i->likes_count]))
            ->sortByDesc('likes')
            ->values()
            ->take(15);

        // ---- Top followed designers ------------------------------------------
        $topFollowedDesigners = Designer::where('is_admin', false)
            ->where('sector', '!=', 'guest')
            ->when($sector, fn($q) => $q->where('sector', $sector))
            ->when($city,   fn($q) => $q->where('city', $city))
            ->select('id', 'name', 'city', 'sector', 'followers_count', 'views_count', 'likes_count')
            ->orderByDesc('followers_count')
            ->limit(15)
            ->get();

        // ---- Engagement trends (monthly views + likes using event tables) ----
        // Views: use project_views table (only table with per-event timestamps)
        $viewsByMonth = ProjectView::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($endOfDay, fn($q) => $q->where('created_at', '<=', $endOfDay))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Likes: polymorphic likes table has created_at per event
        $likesByMonth = Like::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($endOfDay, fn($q) => $q->where('created_at', '<=', $endOfDay))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        $engagementMonths = $viewsByMonth->keys()
            ->merge($likesByMonth->keys())
            ->unique()->sort()->values();

        $engagementTrend = $engagementMonths->map(fn($m) => [
            'month' => $m,
            'views' => $viewsByMonth->get($m, 0),
            'likes' => $likesByMonth->get($m, 0),
        ])->values();

        // ---- Page traffic (top pages + monthly breakdown) --------------------
        $pageTrafficTotals = PageVisit::selectRaw('page_key, COUNT(*) as count')
            ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($endOfDay, fn($q) => $q->where('created_at', '<=', $endOfDay))
            ->groupBy('page_key')
            ->orderByDesc('count')
            ->get()
            ->map(fn($r) => ['page' => $r->page_key, 'count' => (int) $r->count]);

        $pageTrafficTrend = PageVisit::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, page_key, COUNT(*) as count")
            ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($endOfDay, fn($q) => $q->where('created_at', '<=', $endOfDay))
            ->groupBy('month', 'page_key')
            ->orderBy('month')
            ->get()
            ->groupBy('month')
            ->map(fn($rows) => $rows->pluck('count', 'page_key'))
            ->values();

        // ---- Improvement signals --------------------------------------------
        // Content with zero views (approved only)
        $zeroViewsContent = collect()
            ->merge(Product::approved()->where('views_count', 0)->select('id', 'title')->get()->map(fn($i) => ['type' => 'Product',      'id' => $i->id, 'title' => $i->title]))
            ->merge(Project::approved()->where('views_count', 0)->select('id', 'title')->get()->map(fn($i) => ['type' => 'Project',      'id' => $i->id, 'title' => $i->title]))
            ->merge(Service::approved()->where('views_count', 0)->select('id', 'name as title')->get()->map(fn($i) => ['type' => 'Service',      'id' => $i->id, 'title' => $i->title]))
            ->merge(MarketplacePost::approved()->where('views_count', 0)->select('id', 'title')->get()->map(fn($i) => ['type' => 'Marketplace', 'id' => $i->id, 'title' => $i->title]))
            ->values();

        // Content with zero likes — services excluded (no likes_count column)
        $zeroLikesContent = collect()
            ->merge(Product::approved()->where('likes_count', 0)->select('id', 'title', 'views_count')->orderByDesc('views_count')->limit(30)->get()->map(fn($i) => ['type' => 'Product',      'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count]))
            ->merge(Project::approved()->where('likes_count', 0)->select('id', 'title', 'views_count')->orderByDesc('views_count')->limit(30)->get()->map(fn($i) => ['type' => 'Project',      'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count]))
            ->merge(MarketplacePost::approved()->where('likes_count', 0)->select('id', 'title', 'views_count')->orderByDesc('views_count')->limit(30)->get()->map(fn($i) => ['type' => 'Marketplace', 'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count]))
            ->sortByDesc('views')
            ->values()
            ->take(30);

        // High-view low-engagement: views > 10 but likes = 0 — services excluded (no likes_count)
        $highViewLowLikes = collect()
            ->merge(Product::approved()->where('views_count', '>', 10)->where('likes_count', 0)->select('id', 'title', 'views_count')->orderByDesc('views_count')->limit(20)->get()->map(fn($i) => ['type' => 'Product',      'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count]))
            ->merge(Project::approved()->where('views_count', '>', 10)->where('likes_count', 0)->select('id', 'title', 'views_count')->orderByDesc('views_count')->limit(20)->get()->map(fn($i) => ['type' => 'Project',      'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count]))
            ->merge(MarketplacePost::approved()->where('views_count', '>', 10)->where('likes_count', 0)->select('id', 'title', 'views_count')->orderByDesc('views_count')->limit(20)->get()->map(fn($i) => ['type' => 'Marketplace', 'id' => $i->id, 'title' => $i->title, 'views' => (int)$i->views_count]))
            ->sortByDesc('views')
            ->values()
            ->take(20);

        // Inactive designers: registered but no approved content
        $inactiveDesigners = Designer::where('is_admin', false)
            ->where('sector', '!=', 'guest')
            ->where('is_active', true)
            ->when($sector, fn($q) => $q->where('sector', $sector))
            ->when($city,   fn($q) => $q->where('city', $city))
            ->withCount([
                'products as products_count'   => fn($q) => $q->where('approval_status', 'approved'),
                'projects as projects_count'   => fn($q) => $q->where('approval_status', 'approved'),
                'services as services_count'   => fn($q) => $q->where('approval_status', 'approved'),
                'marketplacePosts as marketplace_count' => fn($q) => $q->where('approval_status', 'approved'),
            ])
            ->having(DB::raw('products_count + projects_count + services_count + marketplace_count'), '=', 0)
            ->select('id', 'name', 'city', 'sector', 'created_at')
            ->orderBy('created_at')
            ->limit(30)
            ->get();

        return compact(
            'totalDesigners', 'activeDesigners', 'pendingTotal',
            'totalApprovedContent', 'totalRatings', 'averageRating',
            'designerGrowth', 'contentTrends',
            'approvalWorkflow', 'avgApprovalTime',
            'byCity', 'bySector',
            'ratingsTrend', 'topDesigners',
            'topViewedContent', 'topLikedContent', 'topFollowedDesigners',
            'engagementTrend',
            'pageTrafficTotals', 'pageTrafficTrend',
            'zeroViewsContent', 'zeroLikesContent', 'highViewLowLikes', 'inactiveDesigners'
        );
    }

    /**
     * Convert a named time preset into a [dateFrom, dateTo] tuple of date strings.
     *
     * Returns [null, null] for the 'all' preset (no date constraint).
     *
     * @param  string  $preset  One of: 7d, 30d, 90d, 1y, all
     * @return array{0: ?string, 1: ?string}
     */
    private function presetToDates(string $preset): array
    {
        return match ($preset) {
            '7d'   => [now()->subDays(7)->toDateString(),  now()->toDateString()],
            '30d'  => [now()->subDays(30)->toDateString(), now()->toDateString()],
            '90d'  => [now()->subDays(90)->toDateString(), now()->toDateString()],
            '1y'   => [now()->subYear()->toDateString(),   now()->toDateString()],
            'all'  => [null, null],
            default => [now()->subDays(30)->toDateString(), now()->toDateString()],
        };
    }
}
