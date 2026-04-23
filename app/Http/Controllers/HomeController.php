<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Services\CacheService;
use Illuminate\Http\Request;

/**
 * Renders the homepage and handles the global search (full-page and AJAX autocomplete).
 * Relies on CacheService for homepage stats and featured content to avoid per-request queries.
 */
class HomeController extends Controller
{
    /**
     * Display the homepage with featured content, stats, and configurable counters.
     *
     * @return \Illuminate\View\View
     */
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

        // Build badge counter data using cached stats (locale-aware labels)
        $locale = app()->getLocale();
        $badgeType = $counterSettings['badge_counter']['type'] ?? 'designers';
        $badgeLabel = ($locale === 'ar' && !empty($counterSettings['badge_counter']['label_ar']))
            ? $counterSettings['badge_counter']['label_ar']
            : __($counterSettings['badge_counter']['label'] ?? 'creative professionals');
        $badgeCounter = [
            'count' => $this->getStatFromCache($badgeType, $counterSettings['badge_counter']['sectors'] ?? [], $homepageStats),
            'label' => $badgeLabel,
        ];

        // Build stats counters data using cached stats (locale-aware labels)
        $statsCounters = [];
        foreach ($counterSettings['stats_counters'] ?? [] as $counter) {
            $counterType = $counter['type'] ?? 'products';
            $counterSectors = $counter['sectors'] ?? [];
            $counterLabel = ($locale === 'ar' && !empty($counter['label_ar']))
                ? $counter['label_ar']
                : __($counter['label']);
            $statsCounters[] = [
                'count' => $this->getStatFromCache($counterType, $counterSectors, $homepageStats),
                'label' => $counterLabel,
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

    /**
     * Execute a full-page search across designers, projects, and products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $query = strip_tags(trim($query));

        if (empty($query)) {
            return redirect()->route('home', ['locale' => app()->getLocale()]);
        }

        // Build bilingual search terms (Arabic ↔ English)
        $searchTerms = $this->buildBilingualSearch($query);

        // Search designers (FULLTEXT with LIKE fallback)
        try {
            $designers = Designer::where('is_admin', false)
                ->where('is_active', true)
                ->where('sector', '!=', 'guest')
                ->where(function ($q) use ($query, $searchTerms) {
                    try {
                        $q->whereRaw('MATCH(name, bio, sector, sub_sector, city) AGAINST(? IN BOOLEAN MODE)', [$searchTerms]);
                    } catch (\Throwable $e) {
                        // FULLTEXT index may not exist
                    }
                    $q->orWhere('name', 'LIKE', "%{$query}%");
                    // company_name may not exist on all deployments
                    if (\Schema::hasColumn('designers', 'company_name')) {
                        $q->orWhere('company_name', 'LIKE', "%{$query}%");
                    }
                })
                ->select('id', 'name', 'avatar', 'sector', 'sub_sector', 'city', 'bio', 'followers_count')
                ->with('skills:id,name')
                ->limit(20)
                ->get();
        } catch (\Throwable $e) {
            // Fallback: simple LIKE search if FULLTEXT fails
            $designers = Designer::where('is_admin', false)
                ->where('is_active', true)
                ->where('sector', '!=', 'guest')
                ->where('name', 'LIKE', "%{$query}%")
                ->select('id', 'name', 'avatar', 'sector', 'sub_sector', 'city', 'bio', 'followers_count')
                ->with('skills:id,name')
                ->limit(20)
                ->get();
        }

        // Search projects (FULLTEXT with LIKE fallback)
        try {
            $projects = Project::where('approval_status', 'approved')
                ->whereHas('designer', function($q) {
                    $q->where('is_admin', false)->where('is_active', true);
                })
                ->where(function ($q) use ($searchTerms, $query) {
                    $q->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerms])
                      ->orWhere('title', 'LIKE', "%{$query}%")
                      ->orWhere('category', 'LIKE', "%{$query}%");
                })
                ->with(['designer:id,name,avatar', 'images'])
                ->limit(20)
                ->get();
        } catch (\Throwable $e) {
            $projects = Project::where('approval_status', 'approved')
                ->where('title', 'LIKE', "%{$query}%")
                ->with(['designer:id,name,avatar', 'images'])
                ->limit(20)
                ->get();
        }

        // Search products (FULLTEXT with LIKE fallback)
        try {
            $products = \App\Models\Product::where('approval_status', 'approved')
                ->whereHas('designer', function($q) {
                    $q->where('is_admin', false)->where('is_active', true);
                })
                ->where(function ($q) use ($searchTerms, $query) {
                    $q->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerms])
                      ->orWhere('title', 'LIKE', "%{$query}%")
                      ->orWhere('category', 'LIKE', "%{$query}%");
                })
                ->with(['designer:id,name,avatar', 'images'])
                ->limit(20)
                ->get();
        } catch (\Throwable $e) {
            $products = \App\Models\Product::where('approval_status', 'approved')
                ->where('title', 'LIKE', "%{$query}%")
                ->with(['designer:id,name,avatar', 'images'])
                ->limit(20)
                ->get();
        }

        // Search services (LIKE only, no FULLTEXT)
        try {
            $services = \App\Models\Service::where('approval_status', 'approved')
                ->whereHas('designer', function($q) {
                    $q->where('is_admin', false)->where('is_active', true);
                })
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%")
                      ->orWhere('category', 'LIKE', "%{$query}%");
                })
                ->with('designer:id,name,avatar')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            $services = collect();
        }

        // Search marketplace posts (FULLTEXT with LIKE fallback)
        try {
            $marketplace = \App\Models\MarketplacePost::where('approval_status', 'approved')
                ->whereHas('designer', function($q) {
                    $q->where('is_admin', false)->where('is_active', true);
                })
                ->where(function ($q) use ($searchTerms, $query) {
                    $q->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerms])
                      ->orWhere('title', 'LIKE', "%{$query}%");
                })
                ->with('designer:id,name,avatar')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            $marketplace = collect();
        }

        // Total results count
        $totalResults = $designers->count() + $projects->count() + $products->count() + $services->count() + $marketplace->count();

        // Log the search query for analytics (never let this break the page)
        try {
            \App\Models\SearchLog::create([
                'query'         => mb_strtolower(trim($query)),
                'results_count' => $totalResults,
                'ip_address'    => $request->ip(),
                'designer_id'   => auth('designer')->id(),
            ]);
        } catch (\Throwable $e) {}

        return view('search', compact('query', 'designers', 'projects', 'products', 'services', 'marketplace', 'totalResults'));
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
                'products' => [],
                'services' => [],
                'marketplace' => [],
                'trainings' => [],
            ]);
        }

        // Build bilingual search terms (Arabic ↔ English)
        $searchTerms = $this->buildBilingualSearch($query);

        // Search designers using FULLTEXT (limit to 4, excluding admin and inactive accounts)
        try {
            $designers = Designer::where('is_admin', false)
                ->where('is_active', true)
                ->where('sector', '!=', 'guest')
                ->where(function ($q) use ($searchTerms, $query) {
                    try {
                        $q->whereRaw('MATCH(name, bio, sector, sub_sector, city) AGAINST(? IN BOOLEAN MODE)', [$searchTerms]);
                    } catch (\Throwable $e) {}
                    $q->orWhere('name', 'LIKE', "%{$query}%");
                    if (\Schema::hasColumn('designers', 'company_name')) {
                        $q->orWhere('company_name', 'LIKE', "%{$query}%");
                    }
                })
                ->select('id', 'name', 'sector', 'sub_sector', 'avatar')
                ->limit(4)
                ->get();
        } catch (\Throwable $e) {
            $designers = Designer::where('is_admin', false)
                ->where('is_active', true)
                ->where('name', 'LIKE', "%{$query}%")
                ->select('id', 'name', 'sector', 'sub_sector', 'avatar')
                ->limit(4)
                ->get();
        }

        // Search projects (limit to 4, only approved)
        $projects = Project::where('approval_status', 'approved')
            ->whereHas('designer', function($q) {
                $q->where('is_admin', false)->where('is_active', true);
            })
            ->where(function ($q) use ($searchTerms, $query) {
                $q->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerms])
                  ->orWhere('title', 'LIKE', "%{$query}%")
                  ->orWhere('category', 'LIKE', "%{$query}%");
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
                    'category' => $project->localized_category,
                    'designer_name' => $project->designer->name ?? null,
                    'image' => $project->images->first()->image_path ?? null
                ];
            });

        // Search products using FULLTEXT (limit to 4, only approved)
        $products = \App\Models\Product::where('approval_status', 'approved')
            ->whereHas('designer', function($q) {
                $q->where('is_admin', false)->where('is_active', true);
            })
            ->where(function ($q) use ($searchTerms, $query) {
                $q->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerms])
                  ->orWhere('title', 'LIKE', "%{$query}%")
                  ->orWhere('category', 'LIKE', "%{$query}%");
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
                    'category' => $product->localized_category,
                    'designer_name' => $product->designer->name ?? null,
                    'image' => $product->images->first()->image_path ?? null
                ];
            });

        // Search services (limit to 3, only approved)
        $services = \App\Models\Service::where('approval_status', 'approved')
            ->whereHas('designer', function($q) {
                $q->where('is_admin', false)->where('is_active', true);
            })
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('category', 'LIKE', "%{$query}%");
            })
            ->with('designer:id,name')
            ->select('id', 'name as title', 'designer_id', 'category', 'approval_status')
            ->limit(3)
            ->get()
            ->map(function($service) {
                return [
                    'id' => $service->id,
                    'title' => $service->title,
                    'category' => $service->localized_category,
                    'designer_name' => $service->designer->name ?? null,
                ];
            });

        // Search marketplace posts (limit to 3, only approved)
        $marketplace = \App\Models\MarketplacePost::where('approval_status', 'approved')
            ->whereHas('designer', function($q) {
                $q->where('is_admin', false)->where('is_active', true);
            })
            ->where(function ($q) use ($searchTerms, $query) {
                $q->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerms])
                  ->orWhere('title', 'LIKE', "%{$query}%");
            })
            ->with('designer:id,name')
            ->select('id', 'title', 'designer_id', 'category', 'type', 'image', 'approval_status')
            ->limit(3)
            ->get()
            ->map(function($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'category' => $post->localized_category,
                    'type' => $post->type,
                    'designer_name' => $post->designer->name ?? null,
                    'image' => $post->image,
                ];
            });

        // Search trainings (limit to 3, only approved or from admin)
        $trainings = \App\Models\AcademicTraining::where(function ($q) {
                $q->where('approval_status', 'approved')
                  ->orWhereNull('approval_status');
            })
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('category', 'LIKE', "%{$query}%");
            })
            ->select('id', 'title', 'category', 'start_date', 'location_type')
            ->limit(3)
            ->get()
            ->map(function($training) {
                return [
                    'id' => $training->id,
                    'title' => $training->title,
                    'category' => $training->category,
                    'start_date' => $training->start_date?->format('M d, Y'),
                    'location_type' => $training->location_type,
                ];
            });

        return response()->json([
            'success' => true,
            'designers' => $designers,
            'projects' => $projects,
            'products' => $products,
            'services' => $services,
            'marketplace' => $marketplace,
            'trainings' => $trainings,
        ]);
    }

    /**
     * Build bilingual search terms — expands Arabic query with English equivalent and vice versa.
     */
    private function buildBilingualSearch(string $query): string
    {
        $arEnMap = [
            'مصمم' => 'designer', 'مصنع' => 'manufacturer', 'صالة عرض' => 'showroom',
            'مورد' => 'vendor', 'مهندس' => 'architect',
            'رام الله' => 'Ramallah', 'القدس' => 'Jerusalem', 'نابلس' => 'Nablus',
            'الخليل' => 'Hebron', 'بيت لحم' => 'Bethlehem', 'غزة' => 'Gaza',
            'جنين' => 'Jenin', 'طولكرم' => 'Tulkarm', 'قلقيلية' => 'Qalqilya',
            'أريحا' => 'Jericho', 'سلفيت' => 'Salfit', 'طوباس' => 'Tubas',
            'تصميم' => 'design', 'فن' => 'art', 'حرف' => 'craft',
            'أزياء' => 'fashion', 'تصوير' => 'photography', 'عمارة' => 'architecture',
            'خدمة' => 'service', 'مشروع' => 'project', 'منتج' => 'product',
            'أثاث' => 'furniture', 'ديكور' => 'decoration', 'إضاءة' => 'lighting',
            'خشب' => 'wood', 'زجاج' => 'glass', 'نجارة' => 'carpentry',
            'صيانة' => 'maintenance', 'تركيب' => 'installation', 'استشارات' => 'consultation',
            'تدريب' => 'training', 'ورشة' => 'workshop', 'إعلان' => 'announcement',
            'جامعة' => 'university', 'كلية' => 'college',
        ];

        $searchTerms = $query . '*';
        $lowerQuery = mb_strtolower($query);

        foreach ($arEnMap as $ar => $en) {
            if (mb_strpos($lowerQuery, $ar) !== false) {
                $searchTerms = $query . '* ' . $en . '*';
                break;
            }
            if (stripos($lowerQuery, $en) !== false) {
                $searchTerms = $query . '* ' . $ar . '*';
                break;
            }
        }

        return $searchTerms;
    }
}
