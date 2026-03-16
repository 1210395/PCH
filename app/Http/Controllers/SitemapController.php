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

            foreach (['en', 'ar'] as $locale) {
                // Static listing pages
                $urls->push(['url' => route('home',              ['locale' => $locale]), 'priority' => '1.0', 'changefreq' => 'daily']);
                $urls->push(['url' => route('designers',         ['locale' => $locale]), 'priority' => '0.9', 'changefreq' => 'daily']);
                $urls->push(['url' => route('products',          ['locale' => $locale]), 'priority' => '0.9', 'changefreq' => 'daily']);
                $urls->push(['url' => route('projects',          ['locale' => $locale]), 'priority' => '0.9', 'changefreq' => 'daily']);
                $urls->push(['url' => route('services',          ['locale' => $locale]), 'priority' => '0.8', 'changefreq' => 'daily']);
                $urls->push(['url' => route('marketplace.index', ['locale' => $locale]), 'priority' => '0.8', 'changefreq' => 'daily']);
                $urls->push(['url' => route('fab-labs',          ['locale' => $locale]), 'priority' => '0.7', 'changefreq' => 'weekly']);
                $urls->push(['url' => route('trainings.index',   ['locale' => $locale]), 'priority' => '0.7', 'changefreq' => 'weekly']);
                $urls->push(['url' => route('tenders.index',     ['locale' => $locale]), 'priority' => '0.7', 'changefreq' => 'daily']);
                $urls->push(['url' => route('academic-tevets',   ['locale' => $locale]), 'priority' => '0.6', 'changefreq' => 'weekly']);
            }

            // Designers (active, non-admin)
            Designer::where('is_active', true)->where('is_admin', false)
                ->select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(500, function ($designers) use ($urls) {
                    foreach ($designers as $designer) {
                        foreach (['en', 'ar'] as $locale) {
                            $urls->push([
                                'url'        => route('designer.portfolio', ['locale' => $locale, 'id' => $designer->id]),
                                'lastmod'    => $designer->updated_at->toW3cString(),
                                'priority'   => '0.8',
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
                                'url'        => route('product.detail', ['locale' => $locale, 'id' => $item->id]),
                                'lastmod'    => $item->updated_at->toW3cString(),
                                'priority'   => '0.7',
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
                                'url'        => route('project.detail', ['locale' => $locale, 'id' => $item->id]),
                                'lastmod'    => $item->updated_at->toW3cString(),
                                'priority'   => '0.7',
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
                                'url'        => route('services.show', ['locale' => $locale, 'id' => $item->id]),
                                'lastmod'    => $item->updated_at->toW3cString(),
                                'priority'   => '0.6',
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
                                'url'        => route('marketplace.show', ['locale' => $locale, 'id' => $item->id]),
                                'lastmod'    => $item->updated_at->toW3cString(),
                                'priority'   => '0.6',
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
                                'url'        => route('fab-lab.detail', ['locale' => $locale, 'id' => $item->id]),
                                'lastmod'    => $item->updated_at->toW3cString(),
                                'priority'   => '0.5',
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
                                'url'        => route('trainings.show', ['locale' => $locale, 'id' => $item->id]),
                                'lastmod'    => $item->updated_at->toW3cString(),
                                'priority'   => '0.5',
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
                                'url'        => route('tenders.show', ['locale' => $locale, 'id' => $item->id]),
                                'lastmod'    => $item->updated_at->toW3cString(),
                                'priority'   => '0.5',
                                'changefreq' => 'weekly',
                            ]);
                        }
                    }
                });

            return $urls;
        });

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
        $xml .= '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($sitemap as $entry) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($entry['url'], ENT_XML1) . "</loc>\n";
            if (!empty($entry['lastmod'])) {
                $xml .= "    <lastmod>{$entry['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$entry['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$entry['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
