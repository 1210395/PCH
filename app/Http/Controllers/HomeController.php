<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Services\CacheService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get cached homepage stats (single query for all counts)
        $homepageStats = CacheService::getHomepageStats();

        // Get cached featured content (top designers, manufacturers, projects, products)
        $featuredContent = CacheService::getFeaturedContent();

        $topDesigners = $featuredContent['topDesigners'];
        $manufacturersShowrooms = $featuredContent['topManufacturers'];
        $featuredProjects = $featuredContent['featuredProjects'];
        $featuredProducts = $featuredContent['featuredProducts'];

        // Get counter settings from database or use defaults
        $counterSettings = SiteSetting::get('counter_settings');
        if (!$counterSettings) {
            $counterSettings = [
                'badge_counter' => [
                    'type' => 'designers',
                    'label' => 'creative professionals',
                    'sectors' => [],
                ],
                'stats_counters' => [
                    ['type' => 'products', 'label' => 'Products', 'sectors' => []],
                    ['type' => 'projects', 'label' => 'Projects', 'sectors' => []],
                    ['type' => 'designers_by_sector', 'label' => 'Manufacturers & Showrooms', 'sectors' => ['manufacturer', 'showroom']],
                ],
            ];
        }

        // Build badge counter data using cached stats
        $badgeType = $counterSettings['badge_counter']['type'] ?? 'designers';
        $badgeCounter = [
            'count' => $this->getStatFromCache($badgeType, $counterSettings['badge_counter']['sectors'] ?? [], $homepageStats),
            'label' => $counterSettings['badge_counter']['label'] ?? 'creative professionals',
        ];

        // Build stats counters data using cached stats
        $statsCounters = [];
        foreach ($counterSettings['stats_counters'] ?? [] as $counter) {
            $counterType = $counter['type'] ?? 'products';
            $counterSectors = $counter['sectors'] ?? [];
            $statsCounters[] = [
                'count' => $this->getStatFromCache($counterType, $counterSectors, $homepageStats),
                'label' => $counter['label'],
            ];
        }

        // Legacy stats array for backward compatibility (using cached values)
        $stats = [
            'designers' => $homepageStats['designers'],
            'products' => $homepageStats['products'],
            'projects' => $homepageStats['projects'],
            'companies' => $homepageStats['companies'],
        ];

        return view('home', compact('topDesigners', 'manufacturersShowrooms', 'featuredProjects', 'featuredProducts', 'stats', 'badgeCounter', 'statsCounters'));
    }

    /**
     * Get stat value from cached homepage stats
     */
    private function getStatFromCache(string $type, array $sectors, array $stats): int
    {
        // Handle designers filtered by specific sectors
        if ($type === 'designers_by_sector') {
            if (empty($sectors)) {
                return $stats['designers'] ?? 0;
            }
            // Sum up counts for each selected sector from cached per-sector data
            $sectorCounts = $stats['sector_counts'] ?? [];
            $total = 0;
            foreach ($sectors as $sector) {
                $total += $sectorCounts[$sector] ?? 0;
            }
            return $total;
        }

        return match($type) {
            'all_members' => $stats['designers'] ?? 0,
            'designers' => $stats['designers'] ?? 0,
            'products' => $stats['products'] ?? 0,
            'projects' => $stats['projects'] ?? 0,
            'services' => $stats['services'] ?? 0,
            'fablabs' => $stats['fablabs'] ?? 0,
            'trainings' => $stats['trainings'] ?? 0,
            'tenders' => $stats['tenders'] ?? 0,
            'marketplace_posts' => $stats['marketplace'] ?? 0,
            default => 0,
        };
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $query = strip_tags(trim($query));

        if (empty($query)) {
            return redirect()->route('home', ['locale' => app()->getLocale()]);
        }

        // Search designers (excluding admin and inactive accounts)
        $designers = Designer::where('is_admin', false)
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('bio', 'like', '%' . $query . '%')
                  ->orWhere('sector', 'like', '%' . $query . '%')
                  ->orWhere('sub_sector', 'like', '%' . $query . '%')
                  ->orWhere('city', 'like', '%' . $query . '%');
            })
            ->with('skills')
            ->limit(20)
            ->get();

        // Search projects (only approved)
        $projects = Project::where('approval_status', 'approved')
            ->whereHas('designer', function($q) {
                $q->where('is_admin', false)->where('is_active', true);
            })
            ->where(function($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->orWhere('category', 'like', '%' . $query . '%')
                  ->orWhere('role', 'like', '%' . $query . '%');
            })
            ->with(['designer', 'images'])
            ->limit(20)
            ->get();

        // Search products (only approved, uses 'title' column, not 'name')
        $products = \App\Models\Product::where('approval_status', 'approved')
            ->whereHas('designer', function($q) {
                $q->where('is_admin', false)->where('is_active', true);
            })
            ->where(function($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->orWhere('category', 'like', '%' . $query . '%');
            })
            ->with(['designer', 'images'])
            ->limit(20)
            ->get();

        // Total results count
        $totalResults = $designers->count() + $projects->count() + $products->count();

        return view('search', compact('query', 'designers', 'projects', 'products', 'totalResults'));
    }

    /**
     * Instant search API endpoint for navbar autocomplete
     */
    public function instantSearch(Request $request)
    {
        $query = $request->input('q', '');
        $query = strip_tags(trim($query));

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'designers' => [],
                'projects' => [],
                'products' => []
            ]);
        }

        // Search designers (limit to 4, excluding admin and inactive accounts)
        $designers = Designer::where('is_admin', false)
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('sector', 'like', '%' . $query . '%')
                  ->orWhere('sub_sector', 'like', '%' . $query . '%');
            })
            ->select('id', 'name', 'sector', 'sub_sector', 'avatar')
            ->limit(4)
            ->get();

        // Search projects (limit to 4, only approved)
        $projects = Project::where('approval_status', 'approved')
            ->whereHas('designer', function($q) {
                $q->where('is_admin', false)->where('is_active', true);
            })
            ->where(function($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('category', 'like', '%' . $query . '%');
            })
            ->with(['designer:id,name', 'images' => function($q) {
                $q->select('project_id', 'image_path')->limit(1);
            }])
            ->select('id', 'title', 'designer_id', 'category', 'approval_status')
            ->limit(4)
            ->get()
            ->map(function($project) {
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'category' => $project->category,
                    'designer_name' => $project->designer->name ?? null,
                    'image' => $project->images->first()->image_path ?? null
                ];
            });

        // Search products (limit to 4, only approved)
        $products = \App\Models\Product::where('approval_status', 'approved')
            ->whereHas('designer', function($q) {
                $q->where('is_admin', false)->where('is_active', true);
            })
            ->where(function($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('category', 'like', '%' . $query . '%');
            })
            ->with(['designer:id,name', 'images' => function($q) {
                $q->select('product_id', 'image_path')->limit(1);
            }])
            ->select('id', 'title', 'designer_id', 'category', 'approval_status')
            ->limit(4)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->title,
                    'category' => $product->category,
                    'designer_name' => $product->designer->name ?? null,
                    'image' => $product->images->first()->image_path ?? null
                ];
            });

        return response()->json([
            'success' => true,
            'designers' => $designers,
            'projects' => $projects,
            'products' => $products
        ]);
    }
}
