<?php

namespace App\Http\Controllers\Admin;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

/**
 * Admin settings for public-facing layout text sections.
 *
 * Manages footer, header, and subheader text (bilingual) stored in SiteSetting.
 * Provides update and reset-to-defaults endpoints for each section.
 */
class AdminLayoutSettingsController extends AdminBaseController
{
    /**
     * Default footer settings
     */
    public static function getDefaultFooterSettings()
    {
        return [
            'description' => 'A digital hub and marketplace supporting designers, MSMEs, and creative industries in Palestine. Connecting talent with opportunities.',
            'description_ar' => 'منصة رقمية وسوق إلكتروني لدعم المصممين والمشاريع الصغيرة والمتوسطة والصناعات الإبداعية في فلسطين. ربط المواهب بالفرص.',
            'quick_links' => [
                ['title' => 'Discover', 'title_ar' => 'اكتشف', 'url' => '/'],
                ['title' => 'Projects', 'title_ar' => 'المشاريع', 'url' => '/projects'],
                ['title' => 'Products', 'title_ar' => 'المنتجات', 'url' => '/products'],
                ['title' => 'Fab Labs', 'title_ar' => 'مختبرات التصنيع', 'url' => '/fab-labs'],
                ['title' => 'Marketplace', 'title_ar' => 'السوق', 'url' => '/marketplace'],
            ],
            'resource_links' => [
                ['title' => 'About Us', 'title_ar' => 'من نحن', 'url' => '#'],
                ['title' => 'Support', 'title_ar' => 'الدعم', 'url' => '#'],
                ['title' => 'Community Guidelines', 'title_ar' => 'إرشادات المجتمع', 'url' => '#'],
                ['title' => 'Terms of Service', 'title_ar' => 'شروط الخدمة', 'url' => '#'],
                ['title' => 'Privacy Policy', 'title_ar' => 'سياسة الخصوصية', 'url' => '#'],
            ],
            'contact' => [
                'address' => "Palestine Creative Hub\nRamallah, Palestine",
                'email' => 'info@palestinecreativehub.ps',
                'phone' => '+970 123 456 789',
            ],
            'social_links' => [
                ['platform' => 'facebook', 'url' => '#'],
                ['platform' => 'twitter', 'url' => '#'],
                ['platform' => 'instagram', 'url' => '#'],
                ['platform' => 'linkedin', 'url' => '#'],
            ],
            'copyright' => '© ' . date('Y') . ' Palestine Creative Hub. All rights reserved.',
            'copyright_ar' => '© ' . date('Y') . ' مركز فلسطين الإبداعي. جميع الحقوق محفوظة.',
            'bottom_links' => [
                ['title' => 'Accessibility', 'title_ar' => 'إمكانية الوصول', 'url' => '#'],
                ['title' => 'Sitemap', 'title_ar' => 'خريطة الموقع', 'url' => '#'],
            ],
            'supporter_text' => 'Platform supported by Global Communities and through the Swedish Government.',
            'supporter_text_ar' => 'المنصة مدعومة من قبل مؤسسة المجتمعات العالمية وبتمويل من الحكومة السويدية.',
        ];
    }

    /**
     * Default header settings
     */
    public static function getDefaultHeaderSettings()
    {
        return [
            'nav_links' => [
                ['title' => 'Discover', 'title_ar' => 'اكتشف', 'url' => '/', 'route' => 'home', 'highlight' => false],
                ['title' => 'Products', 'title_ar' => 'المنتجات', 'url' => '/products', 'route' => 'products', 'highlight' => false],
                ['title' => 'Projects', 'title_ar' => 'المشاريع', 'url' => '/projects', 'route' => 'projects', 'highlight' => false],
                ['title' => 'Services', 'title_ar' => 'الخدمات', 'url' => '/services', 'route' => 'services', 'highlight' => false],
                ['title' => 'Fab Labs', 'title_ar' => 'مختبرات التصنيع', 'url' => '/fab-labs', 'route' => 'fab-labs', 'highlight' => false],
                ['title' => 'Academic & Workplace Learning Centers', 'title_ar' => 'المراكز الأكاديمية ومراكز التعلم المهني', 'url' => '/academic-tevets', 'route' => 'academic-tevets', 'highlight' => false],
                ['title' => 'MarketPlace', 'title_ar' => 'السوق', 'url' => '/marketplace', 'route' => 'marketplace.index', 'highlight' => true],
            ],
        ];
    }

    /**
     * Default subheader settings
     */
    public static function getDefaultSubheaderSettings()
    {
        return [
            'enabled' => true,
            'nav_links' => [
                ['title' => 'My Projects', 'title_ar' => 'مشاريعي', 'url' => '/my-projects', 'highlight' => false, 'order' => 1],
                ['title' => 'My Products', 'title_ar' => 'منتجاتي', 'url' => '/my-products', 'highlight' => false, 'order' => 2],
                ['title' => 'My Services', 'title_ar' => 'خدماتي', 'url' => '/my-services', 'highlight' => false, 'order' => 3],
                ['title' => 'Add Project', 'title_ar' => 'إضافة مشروع', 'url' => '/projects/create', 'highlight' => true, 'order' => 4],
                ['title' => 'Add Product', 'title_ar' => 'إضافة منتج', 'url' => '/products/create', 'highlight' => true, 'order' => 5],
            ],
        ];
    }

    /**
     * Update footer settings
     */
    public function updateFooter(Request $request, $locale)
    {
        $request->validate([
            'description' => 'nullable|string|max:500',
            'description_ar' => 'nullable|string|max:500',
            'quick_links' => 'nullable|array',
            'quick_links.*.title' => 'required|string|max:100',
            'quick_links.*.title_ar' => 'nullable|string|max:100',
            'quick_links.*.url' => 'required|string|max:255',
            'resource_links' => 'nullable|array',
            'resource_links.*.title' => 'required|string|max:100',
            'resource_links.*.title_ar' => 'nullable|string|max:100',
            'resource_links.*.url' => 'required|string|max:255',
            'contact.address' => 'nullable|string|max:255',
            'contact.email' => 'nullable|email|max:255',
            'contact.phone' => 'nullable|string|max:50',
            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'required|string|in:facebook,twitter,instagram,linkedin,youtube,tiktok',
            'social_links.*.url' => 'required|string|max:255',
            'copyright' => 'nullable|string|max:255',
            'copyright_ar' => 'nullable|string|max:255',
            'bottom_links' => 'nullable|array',
            'bottom_links.*.title' => 'required|string|max:100',
            'bottom_links.*.title_ar' => 'nullable|string|max:100',
            'bottom_links.*.url' => 'required|string|max:255',
            'supporter_text' => 'nullable|string|max:500',
            'supporter_text_ar' => 'nullable|string|max:500',
        ]);

        $footerSettings = [
            'description' => $request->input('description', ''),
            'description_ar' => $request->input('description_ar', ''),
            'quick_links' => $request->input('quick_links', []),
            'resource_links' => $request->input('resource_links', []),
            'contact' => $request->input('contact', []),
            'social_links' => $request->input('social_links', []),
            'copyright' => $request->input('copyright', ''),
            'copyright_ar' => $request->input('copyright_ar', ''),
            'bottom_links' => $request->input('bottom_links', []),
            'supporter_text' => $request->input('supporter_text', ''),
            'supporter_text_ar' => $request->input('supporter_text_ar', ''),
        ];

        SiteSetting::set('footer_settings', $footerSettings, 'json', 'layout', 'Footer Settings', 'Footer content and links configuration');

        return $this->successResponse('Footer settings updated successfully');
    }

    /**
     * Update header settings
     */
    public function updateHeader(Request $request, $locale)
    {
        $request->validate([
            'nav_links' => 'nullable|array',
            'nav_links.*.title' => 'required|string|max:100',
            'nav_links.*.title_ar' => 'nullable|string|max:100',
            'nav_links.*.url' => 'nullable|string|max:255',
            'nav_links.*.route' => 'nullable|string|max:100',
            'nav_links.*.highlight' => 'nullable',
            'nav_links.*.type' => 'nullable|string|max:50',
            'nav_links.*.order' => 'nullable|integer',
            'nav_links.*.children' => 'nullable|array',
            'nav_links.*.children.*.title' => 'nullable|string|max:100',
            'nav_links.*.children.*.title_ar' => 'nullable|string|max:100',
            'nav_links.*.children.*.url' => 'nullable|string|max:255',
            'nav_links.*.children.*.order' => 'nullable|integer',
        ]);

        $headerSettings = [
            'nav_links' => $request->input('nav_links', []),
        ];

        SiteSetting::set('header_settings', $headerSettings, 'json', 'layout', 'Header Settings', 'Header navigation configuration');

        return $this->successResponse('Header settings updated successfully');
    }

    /**
     * Update subheader settings
     */
    public function updateSubheader(Request $request, $locale)
    {
        $request->validate([
            'enabled' => 'nullable|boolean',
            'nav_links' => 'nullable|array',
            'nav_links.*.title' => 'required|string|max:100',
            'nav_links.*.title_ar' => 'nullable|string|max:100',
            'nav_links.*.url' => 'required|string|max:255',
            'nav_links.*.highlight' => 'nullable|boolean',
            'nav_links.*.order' => 'nullable|integer',
        ]);

        $subheaderSettings = [
            'enabled' => $request->boolean('enabled', true),
            'nav_links' => $request->input('nav_links', []),
        ];

        SiteSetting::set('subheader_settings', $subheaderSettings, 'json', 'layout', 'Subheader Settings', 'Subheader navigation configuration for logged-in users');

        return $this->successResponse('Subheader settings updated successfully');
    }

    /**
     * Reset footer to defaults
     */
    public function resetFooter(Request $request, $locale)
    {
        SiteSetting::set('footer_settings', static::getDefaultFooterSettings(), 'json', 'layout', 'Footer Settings', 'Footer content and links configuration');

        return $this->successResponse('Footer settings reset to defaults');
    }

    /**
     * Reset header to defaults
     */
    public function resetHeader(Request $request, $locale)
    {
        SiteSetting::set('header_settings', static::getDefaultHeaderSettings(), 'json', 'layout', 'Header Settings', 'Header navigation configuration');

        return $this->successResponse('Header settings reset to defaults');
    }

    /**
     * Reset subheader to defaults
     */
    public function resetSubheader(Request $request, $locale)
    {
        SiteSetting::set('subheader_settings', static::getDefaultSubheaderSettings(), 'json', 'layout', 'Subheader Settings', 'Subheader navigation configuration for logged-in users');

        return $this->successResponse('Subheader settings reset to defaults');
    }
}
