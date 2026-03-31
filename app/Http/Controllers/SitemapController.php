<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\MarketplacePost;
use App\Models\FabLab;
use App\Models\Training;
use App\Models\AcademicTraining;
use App\Models\AcademicWorkshop;
use App\Models\AcademicAccount;
use App\Models\Tender;
use Illuminate\Support\Facades\Cache;

/**
 * Generates the XML sitemap with xhtml:link hreflang for bilingual SEO.
 * Includes all public content: designers, products, projects, services,
 * marketplace, fablabs, trainings, workshops, tenders, academic institutions,
 * and static pages. Cached for 1 hour.
 */
class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = Cache::remember('sitemap_xml_v2', 3600, function () {
            $urls = collect();
            $base = config('app.url');

            // ==========================================
            // STATIC PAGES (both locales)
            // ==========================================
            $staticPages = [
                ['route' => 'home',              'priority' => '1.0', 'freq' => 'daily'],
                ['route' => 'designers',         'priority' => '0.9', 'freq' => 'daily'],
                ['route' => 'products',          'priority' => '0.9', 'freq' => 'daily'],
                ['route' => 'projects',          'priority' => '0.9', 'freq' => 'daily'],
                ['route' => 'services',          'priority' => '0.8', 'freq' => 'daily'],
                ['route' => 'marketplace.index', 'priority' => '0.8', 'freq' => 'daily'],
                ['route' => 'fab-labs',          'priority' => '0.7', 'freq' => 'weekly'],
                ['route' => 'trainings.index',   'priority' => '0.7', 'freq' => 'weekly'],
                ['route' => 'tenders.index',     'priority' => '0.7', 'freq' => 'daily'],
                ['route' => 'academic-tevets',   'priority' => '0.6', 'freq' => 'weekly'],
            ];

            foreach ($staticPages as $page) {
                $enUrl = route($page['route'], ['locale' => 'en']);
                $arUrl = route($page['route'], ['locale' => 'ar']);
                $urls->push([
                    'url' => $enUrl,
                    'priority' => $page['priority'],
                    'changefreq' => $page['freq'],
                    'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                ]);
                $urls->push([
                    'url' => $arUrl,
                    'priority' => $page['priority'],
                    'changefreq' => $page['freq'],
                    'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                ]);
            }

            // CMS static pages (about, terms, etc.)
            $cmsPages = ['about', 'support', 'community-guidelines', 'terms', 'privacy', 'accessibility'];
            foreach ($cmsPages as $slug) {
                foreach (['en', 'ar'] as $locale) {
                    $enUrl = route('page.show', ['locale' => 'en', 'slug' => $slug]);
                    $arUrl = route('page.show', ['locale' => 'ar', 'slug' => $slug]);
                    $urls->push([
                        'url' => route('page.show', ['locale' => $locale, 'slug' => $slug]),
                        'priority' => '0.4',
                        'changefreq' => 'monthly',
                        'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                    ]);
                }
            }

            // ==========================================
            // DYNAMIC CONTENT
            // ==========================================

            // Designers (active, non-admin, non-guest)
            Designer::where('is_active', true)->where('is_admin', false)->where('sector', '!=', 'guest')
                ->select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($items) use ($urls) {
                    foreach ($items as $item) {
                        $enUrl = route('designer.portfolio', ['locale' => 'en', 'id' => $item->id]);
                        $arUrl = route('designer.portfolio', ['locale' => 'ar', 'id' => $item->id]);
                        $urls->push([
                            'url' => $enUrl, 'lastmod' => $item->updated_at->toW3cString(),
                            'priority' => '0.8', 'changefreq' => 'weekly',
                            'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                        ]);
                        $urls->push([
                            'url' => $arUrl, 'lastmod' => $item->updated_at->toW3cString(),
                            'priority' => '0.8', 'changefreq' => 'weekly',
                            'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                        ]);
                    }
                });

            // Products (approved)
            $this->addContentUrls($urls, Product::where('approval_status', 'approved'), 'product.detail', '0.7', 'weekly');

            // Projects (approved)
            $this->addContentUrls($urls, Project::where('approval_status', 'approved'), 'project.detail', '0.7', 'weekly');

            // Services (approved)
            $this->addContentUrls($urls, Service::where('approval_status', 'approved'), 'services.show', '0.6', 'weekly');

            // Marketplace posts (approved)
            $this->addContentUrls($urls, MarketplacePost::where('approval_status', 'approved'), 'marketplace.show', '0.6', 'weekly');

            // FabLabs
            $this->addContentUrls($urls, FabLab::query(), 'fab-lab.detail', '0.5', 'monthly');

            // Admin Trainings
            $this->addContentUrls($urls, Training::query(), 'trainings.show', '0.5', 'monthly');

            // Academic Trainings (approved)
            AcademicTraining::where('approval_status', 'approved')
                ->select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($items) use ($urls) {
                    foreach ($items as $item) {
                        $enUrl = route('trainings.show', ['locale' => 'en', 'id' => $item->id]);
                        $arUrl = route('trainings.show', ['locale' => 'ar', 'id' => $item->id]);
                        $urls->push([
                            'url' => $enUrl, 'lastmod' => $item->updated_at->toW3cString(),
                            'priority' => '0.5', 'changefreq' => 'weekly',
                            'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                        ]);
                        $urls->push([
                            'url' => $arUrl, 'lastmod' => $item->updated_at->toW3cString(),
                            'priority' => '0.5', 'changefreq' => 'weekly',
                            'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                        ]);
                    }
                });

            // Tenders (visible)
            Tender::where('is_visible', true)
                ->select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($items) use ($urls) {
                    foreach ($items as $item) {
                        $enUrl = route('tenders.show', ['locale' => 'en', 'id' => $item->id]);
                        $arUrl = route('tenders.show', ['locale' => 'ar', 'id' => $item->id]);
                        $urls->push([
                            'url' => $enUrl, 'lastmod' => $item->updated_at->toW3cString(),
                            'priority' => '0.5', 'changefreq' => 'weekly',
                            'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                        ]);
                        $urls->push([
                            'url' => $arUrl, 'lastmod' => $item->updated_at->toW3cString(),
                            'priority' => '0.5', 'changefreq' => 'weekly',
                            'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                        ]);
                    }
                });

            // Academic Institutions (active)
            AcademicAccount::where('is_active', true)
                ->select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($items) use ($urls) {
                    foreach ($items as $item) {
                        $enUrl = route('academic-institution.show', ['locale' => 'en', 'id' => $item->id]);
                        $arUrl = route('academic-institution.show', ['locale' => 'ar', 'id' => $item->id]);
                        $urls->push([
                            'url' => $enUrl, 'lastmod' => $item->updated_at->toW3cString(),
                            'priority' => '0.5', 'changefreq' => 'monthly',
                            'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                        ]);
                        $urls->push([
                            'url' => $arUrl, 'lastmod' => $item->updated_at->toW3cString(),
                            'priority' => '0.5', 'changefreq' => 'monthly',
                            'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                        ]);
                    }
                });

            return $urls;
        });

        // Build XML
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        foreach ($sitemap as $entry) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($entry['url'], ENT_XML1) . "</loc>\n";
            if (!empty($entry['lastmod'])) {
                $xml .= "    <lastmod>{$entry['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$entry['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$entry['priority']}</priority>\n";

            // Add hreflang alternates for bilingual SEO
            if (!empty($entry['alternates'])) {
                foreach ($entry['alternates'] as $lang => $href) {
                    $xml .= '    <xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . htmlspecialchars($href, ENT_XML1) . '" />' . "\n";
                }
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Helper to add content URLs for both locales with hreflang.
     */
    private function addContentUrls($urls, $query, string $routeName, string $priority, string $freq): void
    {
        $query->select('id', 'updated_at')
            ->orderByDesc('updated_at')
            ->chunk(500, function ($items) use ($urls, $routeName, $priority, $freq) {
                foreach ($items as $item) {
                    $enUrl = route($routeName, ['locale' => 'en', 'id' => $item->id]);
                    $arUrl = route($routeName, ['locale' => 'ar', 'id' => $item->id]);
                    $urls->push([
                        'url' => $enUrl, 'lastmod' => $item->updated_at->toW3cString(),
                        'priority' => $priority, 'changefreq' => $freq,
                        'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                    ]);
                    $urls->push([
                        'url' => $arUrl, 'lastmod' => $item->updated_at->toW3cString(),
                        'priority' => $priority, 'changefreq' => $freq,
                        'alternates' => ['en' => $enUrl, 'ar' => $arUrl],
                    ]);
                }
            });
    }
}
