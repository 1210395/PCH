<?php

namespace App\Http\Controllers\Admin;

use App\Models\Designer;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\MarketplacePost;
use App\Models\FabLab;
use App\Models\Training;
use App\Models\Tender;
use App\Models\AcademicTraining;
use App\Models\AcademicWorkshop;
use App\Models\AcademicAnnouncement;
use App\Models\ProfileRating;
use App\Services\CacheService;
use Illuminate\Http\Request;

/**
 * Renders the admin dashboard and serves the AJAX pending-counts endpoint.
 *
 * Aggregates platform KPIs, pending approval queues, designer statistics,
 * sector/city breakdowns, and top contributors. Most data is fetched from
 * CacheService to avoid expensive queries on every page load.
 */
class AdminDashboardController extends AdminBaseController
{
    /**
     * Display the admin dashboard.
     *
     * Returns a Blade view with all statistics, or a JSON response when the
     * request expects JSON (used by AJAX dashboard refresh calls).
     *
     * @param  Request  $request
     * @param  string   $locale
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $locale)
    {
        // Get cached dashboard statistics
        $stats = CacheService::getDashboardStats();

        // Get total counts from cache
        $counts = [
            'designers' => $stats['totals']['designers'],
            'products' => $stats['totals']['products'],
            'projects' => $stats['totals']['projects'],
            'services' => $stats['totals']['services'],
            'marketplace_posts' => $stats['totals']['marketplacePosts'],
            'fablabs' => $stats['totals']['fablabs'],
            'trainings' => $stats['totals']['trainings'],
            'tenders' => $stats['totals']['tenders'],
        ];

        // Get pending approval counts from cache (only for user-submitted content)
        $pendingCounts = [
            'products' => $stats['pending']['products'],
            'projects' => $stats['pending']['projects'],
            'services' => $stats['pending']['services'],
            'marketplace_posts' => $stats['pending']['marketplace'],
            'total' => 0,
        ];
        $pendingCounts['total'] = $pendingCounts['products'] + $pendingCounts['projects']
            + $pendingCounts['services'] + $pendingCounts['marketplace_posts'];

        // Get recent activity (not cached - needs fresh data)
        $recentActivity = [
            'designers' => Designer::latest()->take(5)->get(),
            'products' => Product::with('designer')->latest()->take(5)->get(),
            'projects' => Project::with('designer')->latest()->take(5)->get(),
        ];

        // Get counts by status from cache
        $statusCounts = [
            'products' => [
                'pending' => $stats['status']['products']['pending'],
                'approved' => $stats['status']['products']['approved'],
                'rejected' => $stats['status']['products']['rejected'],
            ],
            'projects' => [
                'pending' => $stats['status']['projects']['pending'],
                'approved' => $stats['status']['projects']['approved'],
                'rejected' => $stats['status']['projects']['rejected'],
            ],
            'services' => [
                'pending' => $stats['status']['services']['pending'],
                'approved' => $stats['status']['services']['approved'],
                'rejected' => $stats['status']['services']['rejected'],
            ],
            'marketplace' => [
                'pending' => $stats['status']['marketplace']['pending'],
                'approved' => $stats['status']['marketplace']['approved'],
                'rejected' => $stats['status']['marketplace']['rejected'],
            ],
        ];

        // Get designer statistics from cache
        $designerStats = [
            'active' => $stats['designers']['active'],
            'inactive' => $stats['designers']['inactive'],
            'trusted' => $stats['designers']['trusted'],
            'admins' => $stats['designers']['admin'],
        ];

        // Growth & trend data
        $growth = $stats['growth'] ?? [];
        $sectorData = $stats['sectors'] ?? [];
        $cityData = $stats['cities'] ?? [];

        // Build daily registrations for last 7 days
        $registrationsDaily = [];
        $rawDaily = $stats['registrations_daily'] ?? [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $registrationsDaily[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('D'),
                'count' => $rawDaily[$date] ?? 0,
            ];
        }

        // Top contributors (designers with most approved content) - cached for 5 min
        $topContributors = cache()->remember('admin_top_contributors', 300, function () {
            return Designer::where('is_admin', false)
                ->where('is_active', true)
                ->withCount([
                    'projects as approved_projects_count' => fn($q) => $q->where('approval_status', 'approved'),
                    'products as approved_products_count' => fn($q) => $q->where('approval_status', 'approved'),
                    'services as approved_services_count' => fn($q) => $q->where('approval_status', 'approved'),
                ])
                ->having('approved_projects_count', '>', 0)
                ->orHaving('approved_products_count', '>', 0)
                ->orHaving('approved_services_count', '>', 0)
                ->orderByRaw('approved_projects_count + approved_products_count + approved_services_count DESC')
                ->limit(5)
                ->get()
                ->map(function ($d) {
                    $d->total_content = $d->approved_projects_count + $d->approved_products_count + $d->approved_services_count;
                    return $d;
                });
        });

        // Approval rate
        $totalContent = $counts['products'] + $counts['projects'] + $counts['services'] + $counts['marketplace_posts'];
        $totalApproved = ($statusCounts['products']['approved'] ?? 0) + ($statusCounts['projects']['approved'] ?? 0)
            + ($statusCounts['services']['approved'] ?? 0) + ($statusCounts['marketplace']['approved'] ?? 0);
        $approvalRate = $totalContent > 0 ? round(($totalApproved / $totalContent) * 100) : 0;

        // Average content per active designer
        $activeDesigners = max($designerStats['active'], 1);
        $avgContentPerUser = round($totalContent / $activeDesigners, 1);

        if ($request->expectsJson()) {
            return $this->jsonResponse([
                'counts' => $counts,
                'pending_counts' => $pendingCounts,
                'recent_activity' => $recentActivity,
                'status_counts' => $statusCounts,
                'designer_stats' => $designerStats,
            ]);
        }

        return view('admin.dashboard', compact(
            'counts',
            'pendingCounts',
            'recentActivity',
            'statusCounts',
            'designerStats',
            'growth',
            'sectorData',
            'cityData',
            'registrationsDaily',
            'topContributors',
            'approvalRate',
            'avgContentPerUser'
        ));
    }

    /**
     * Return pending approval counts for all content types as JSON.
     *
     * Used by the admin navbar to update badge counts without a full page reload.
     * Note: admin-managed Trainings and Tenders have no approval workflow.
     *
     * @param  Request  $request
     * @param  string   $locale
     * @return \Illuminate\Http\JsonResponse
     */
    public function pendingCounts(Request $request, $locale)
    {
        $stats = CacheService::getDashboardStats();

        return $this->jsonResponse([
            'products' => $stats['pending']['products'],
            'projects' => $stats['pending']['projects'],
            'services' => $stats['pending']['services'],
            'marketplace_posts' => $stats['pending']['marketplace'],
            'academic_trainings' => AcademicTraining::pending()->count(),
            'academic_workshops' => AcademicWorkshop::pending()->count(),
            'academic_announcements' => AcademicAnnouncement::pending()->count(),
            'profile_ratings' => ProfileRating::pending()->count(),
        ]);
    }
}
