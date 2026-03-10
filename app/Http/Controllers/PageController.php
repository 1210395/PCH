<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * URL slug to settings key mapping
     */
    private function getPageMap()
    {
        return [
            'about' => 'about',
            'support' => 'support',
            'community-guidelines' => 'community_guidelines',
            'terms' => 'terms',
            'privacy' => 'privacy',
            'accessibility' => 'accessibility',
            'sitemap' => 'sitemap',
        ];
    }

    /**
     * Show a CMS page
     */
    public function show($locale, $slug)
    {
        $pageMap = $this->getPageMap();

        if (!isset($pageMap[$slug])) {
            abort(404);
        }

        $settingsKey = $pageMap[$slug];
        $content = SiteSetting::get("page_{$settingsKey}");

        // If no content in DB, use defaults from controller
        if (!$content) {
            $content = $this->getDefaultContent($settingsKey);
        }

        // For sitemap, gather site links
        $siteLinks = null;
        if ($settingsKey === 'sitemap') {
            $siteLinks = $this->getSitemapLinks();
        }

        return view("pages.{$settingsKey}", compact('content', 'siteLinks'));
    }

    /**
     * Get fallback content if nothing in DB
     */
    private function getDefaultContent($slug)
    {
        return [
            'title' => ucwords(str_replace('_', ' ', $slug)),
            'title_ar' => '',
            'subtitle' => '',
            'subtitle_ar' => '',
            'hero_image' => null,
            'sections' => [],
            'faq_items' => [],
            'team_members' => [],
        ];
    }

    /**
     * Gather sitemap links from the site
     */
    private function getSitemapLinks()
    {
        $locale = app()->getLocale();

        return [
            __('Main Pages') => [
                ['title' => __('Home'), 'url' => route('home', $locale)],
                ['title' => __('Products'), 'url' => route('products.index', $locale)],
                ['title' => __('Projects'), 'url' => route('projects.index', $locale)],
                ['title' => __('Services'), 'url' => route('services.index', $locale)],
                ['title' => __('Designers'), 'url' => route('designers.index', $locale)],
                ['title' => __('Marketplace'), 'url' => route('marketplace.index', $locale)],
                ['title' => __('Fab Labs'), 'url' => route('fablabs.index', $locale)],
                ['title' => __('Trainings'), 'url' => route('trainings.index', $locale)],
                ['title' => __('Tenders'), 'url' => route('tenders.index', $locale)],
            ],
            __('Account') => [
                ['title' => __('Log In'), 'url' => route('login', $locale)],
                ['title' => __('Sign Up'), 'url' => route('register', $locale)],
            ],
            __('Information') => [
                ['title' => __('About Us'), 'url' => url("{$locale}/about")],
                ['title' => __('Support'), 'url' => url("{$locale}/support")],
                ['title' => __('Community Guidelines'), 'url' => url("{$locale}/community-guidelines")],
                ['title' => __('Terms of Service'), 'url' => url("{$locale}/terms")],
                ['title' => __('Privacy Policy'), 'url' => url("{$locale}/privacy")],
                ['title' => __('Accessibility'), 'url' => url("{$locale}/accessibility")],
            ],
        ];
    }
}
