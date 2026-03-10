<?php

namespace App\Http\Controllers;

use App\Models\Designer;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\MarketplacePost;
use App\Models\FabLab;
use App\Models\Training;
use App\Models\Tender;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = Cache::remember('sitemap_xml', 3600, function () {
            $urls = collect();

            // Static pages
            foreach (['en', 'ar'] as $locale) {
                $urls->push(['url' => url("$locale"), 'priority' => '1.0', 'changefreq' => 'daily']);
                $urls->push(['url' => url("$locale/designers"), 'priority' => '0.9', 'changefreq' => 'daily']);
                $urls->push(['url' => url("$locale/products"), 'priority' => '0.9', 'changefreq' => 'daily']);
                $urls->push(['url' => url("$locale/projects"), 'priority' => '0.9', 'changefreq' => 'daily']);
                $urls->push(['url' => url("$locale/services"), 'priority' => '0.8', 'changefreq' => 'daily']);
                $urls->push(['url' => url("$locale/marketplace"), 'priority' => '0.8', 'changefreq' => 'daily']);
                $urls->push(['url' => url("$locale/fab-labs"), 'priority' => '0.7', 'changefreq' => 'weekly']);
                $urls->push(['url' => url("$locale/trainings"), 'priority' => '0.7', 'changefreq' => 'weekly']);
                $urls->push(['url' => url("$locale/tenders"), 'priority' => '0.7', 'changefreq' => 'daily']);
            }

            // Designers (active, non-admin)
            Designer::where('is_active', true)->where('is_admin', false)
                ->select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($designers) use ($urls) {
                    foreach ($designers as $designer) {
                        foreach (['en', 'ar'] as $locale) {
                            $urls->push([
                                'url' => url("$locale/designer/{$designer->id}"),
                                'lastmod' => $designer->updated_at->toW3cString(),
                                'priority' => '0.8',
                                'changefreq' => 'weekly',
                            ]);
                        }
                    }
                });

            // Products (approved)
            Product::where('approval_status', 'approved')
                ->select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($items) use ($urls) {
                    foreach ($items as $item) {
                        foreach (['en', 'ar'] as $locale) {
                            $urls->push([
                                'url' => url("$locale/products/{$item->id}"),
                                'lastmod' => $item->updated_at->toW3cString(),
                                'priority' => '0.7',
                                'changefreq' => 'weekly',
                            ]);
                        }
                    }
                });

            // Projects (approved)
            Project::where('approval_status', 'approved')
                ->select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($items) use ($urls) {
                    foreach ($items as $item) {
                        foreach (['en', 'ar'] as $locale) {
                            $urls->push([
                                'url' => url("$locale/projects/{$item->id}"),
                                'lastmod' => $item->updated_at->toW3cString(),
                                'priority' => '0.7',
                                'changefreq' => 'weekly',
                            ]);
                        }
                    }
                });

            // Services (approved)
            Service::where('approval_status', 'approved')
                ->select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($items) use ($urls) {
                    foreach ($items as $item) {
                        foreach (['en', 'ar'] as $locale) {
                            $urls->push([
                                'url' => url("$locale/services/{$item->id}"),
                                'lastmod' => $item->updated_at->toW3cString(),
                                'priority' => '0.6',
                                'changefreq' => 'weekly',
                            ]);
                        }
                    }
                });

            // Marketplace posts (approved)
            MarketplacePost::where('approval_status', 'approved')
                ->select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($items) use ($urls) {
                    foreach ($items as $item) {
                        foreach (['en', 'ar'] as $locale) {
                            $urls->push([
                                'url' => url("$locale/marketplace/{$item->id}"),
                                'lastmod' => $item->updated_at->toW3cString(),
                                'priority' => '0.6',
                                'changefreq' => 'weekly',
                            ]);
                        }
                    }
                });

            // FabLabs
            FabLab::select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($items) use ($urls) {
                    foreach ($items as $item) {
                        foreach (['en', 'ar'] as $locale) {
                            $urls->push([
                                'url' => url("$locale/fab-labs/{$item->id}"),
                                'lastmod' => $item->updated_at->toW3cString(),
                                'priority' => '0.5',
                                'changefreq' => 'monthly',
                            ]);
                        }
                    }
                });

            // Trainings
            Training::select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($items) use ($urls) {
                    foreach ($items as $item) {
                        foreach (['en', 'ar'] as $locale) {
                            $urls->push([
                                'url' => url("$locale/trainings/{$item->id}"),
                                'lastmod' => $item->updated_at->toW3cString(),
                                'priority' => '0.5',
                                'changefreq' => 'monthly',
                            ]);
                        }
                    }
                });

            // Tenders
            Tender::select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($items) use ($urls) {
                    foreach ($items as $item) {
                        foreach (['en', 'ar'] as $locale) {
                            $urls->push([
                                'url' => url("$locale/tenders/{$item->id}"),
                                'lastmod' => $item->updated_at->toW3cString(),
                                'priority' => '0.5',
                                'changefreq' => 'weekly',
                            ]);
                        }
                    }
                });

            return $urls;
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemap as $entry) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($entry['url']) . "</loc>\n";
            if (!empty($entry['lastmod'])) {
                $xml .= "    <lastmod>{$entry['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$entry['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$entry['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
