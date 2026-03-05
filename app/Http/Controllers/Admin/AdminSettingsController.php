<?php

namespace App\Http\Controllers\Admin;

use App\Models\SiteSetting;
use App\Models\AdminSetting;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends AdminBaseController
{
    /**
     * List of pages that have hero images
     */
    private $heroPages = [
        'home' => 'Home Page',
        'projects' => 'Projects Page',
        'products' => 'Products Page',
        'services' => 'Services Page',
        'marketplace' => 'Marketplace Page',
        'designers' => 'Designers Page',
        'fab_labs' => 'Fab Labs Page',
        'trainings' => 'Trainings Page',
        'tenders' => 'Tenders Page',
        'academic_tevets' => 'Academic & Workplace Learning Centers Page',
    ];

    /**
     * Default footer settings
     */
    private function getDefaultFooterSettings()
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
    private function getDefaultHeaderSettings()
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
    private function getDefaultSubheaderSettings()
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
     * Display the settings page
     */
    public function index(Request $request, $locale)
    {
        $heroImages = [];
        foreach ($this->heroPages as $key => $label) {
            // Get multiple images for carousel
            $imagePaths = SiteSetting::getHeroImagePaths($key);
            $images = [];
            foreach ($imagePaths as $path) {
                $images[] = [
                    'url' => url('storage/' . $path),
                    'path' => $path,
                ];
            }
            $heroImages[$key] = [
                'label' => $label,
                'images' => $images,
                'count' => count($images),
            ];
        }

        // Get footer settings
        $footerSettings = SiteSetting::get('footer_settings');
        if (!$footerSettings) {
            $footerSettings = $this->getDefaultFooterSettings();
        }

        // Get header settings
        $headerSettings = SiteSetting::get('header_settings');
        if (!$headerSettings) {
            $headerSettings = $this->getDefaultHeaderSettings();
        }

        // Get subheader settings
        $subheaderSettings = SiteSetting::get('subheader_settings');
        if (!$subheaderSettings) {
            $subheaderSettings = $this->getDefaultSubheaderSettings();
        }

        // Get counter settings
        $counterSettings = SiteSetting::get('counter_settings');
        if (!$counterSettings) {
            $counterSettings = $this->getDefaultCounterSettings();
        }
        $availableCounterTypes = $this->getAvailableCounterTypes();
        $availableSectors = $this->getAvailableSectors();

        // Get registration policies settings
        $registrationPolicies = SiteSetting::get('registration_policies');
        if (!$registrationPolicies) {
            $registrationPolicies = $this->getDefaultRegistrationPolicies();
        }

        return view('admin.settings.index', compact('heroImages', 'footerSettings', 'headerSettings', 'subheaderSettings', 'counterSettings', 'availableCounterTypes', 'availableSectors', 'registrationPolicies'));
    }

    /**
     * Add hero image to a page's carousel (max 5 images)
     */
    public function updateHeroImage(Request $request, $locale)
    {
        $request->validate([
            'page' => 'required|string|in:' . implode(',', array_keys($this->heroPages)),
            'image' => 'required|image|max:10240', // Max 10MB
        ]);

        $page = $request->input('page');

        // Check if already at max images (5)
        $currentImages = SiteSetting::getHeroImagePaths($page);
        if (count($currentImages) >= 5) {
            return $this->errorResponse('Maximum 5 images allowed per page. Please remove an image first.', 422);
        }

        // Store new image using storeAs
        $file = $request->file('image');
        $filename = $page . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $imagePath = $file->storeAs('hero_images', $filename, 'public');

        // Add to carousel
        SiteSetting::addHeroImage($page, $imagePath);

        // Return updated image list
        $images = [];
        foreach (SiteSetting::getHeroImagePaths($page) as $path) {
            $images[] = [
                'url' => url('storage/' . $path),
                'path' => $path,
            ];
        }

        return $this->successResponse(
            'Hero image added successfully',
            ['images' => $images, 'count' => count($images)]
        );
    }

    /**
     * Remove a specific hero image from a page's carousel
     */
    public function removeHeroImage(Request $request, $locale)
    {
        $request->validate([
            'page' => 'required|string|in:' . implode(',', array_keys($this->heroPages)),
            'index' => 'required|integer|min:0|max:4',
        ]);

        $page = $request->input('page');
        $index = $request->input('index');

        // Remove image at index and get the path of removed image
        $removedPath = SiteSetting::removeHeroImage($page, $index);

        if ($removedPath) {
            // Delete from storage
            Storage::disk('public')->delete($removedPath);
        }

        // Return updated image list
        $images = [];
        foreach (SiteSetting::getHeroImagePaths($page) as $path) {
            $images[] = [
                'url' => url('storage/' . $path),
                'path' => $path,
            ];
        }

        return $this->successResponse('Hero image removed successfully', [
            'images' => $images,
            'count' => count($images)
        ]);
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
     * Reset footer to defaults
     */
    public function resetFooter(Request $request, $locale)
    {
        SiteSetting::set('footer_settings', $this->getDefaultFooterSettings(), 'json', 'layout', 'Footer Settings', 'Footer content and links configuration');

        return $this->successResponse('Footer settings reset to defaults');
    }

    /**
     * Reset header to defaults
     */
    public function resetHeader(Request $request, $locale)
    {
        SiteSetting::set('header_settings', $this->getDefaultHeaderSettings(), 'json', 'layout', 'Header Settings', 'Header navigation configuration');

        return $this->successResponse('Header settings reset to defaults');
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
     * Reset subheader to defaults
     */
    public function resetSubheader(Request $request, $locale)
    {
        SiteSetting::set('subheader_settings', $this->getDefaultSubheaderSettings(), 'json', 'layout', 'Subheader Settings', 'Subheader navigation configuration for logged-in users');

        return $this->successResponse('Subheader settings reset to defaults');
    }

    /**
     * Toggle auto-accept setting for a specific type
     */
    public function toggleAutoAccept(Request $request, $locale, $type)
    {
        $validTypes = ['designers', 'products', 'projects', 'services', 'marketplace', 'trainings', 'workshops', 'announcements'];

        if (!in_array($type, $validTypes)) {
            return $this->errorResponse('Invalid type', 400);
        }

        $key = "auto_accept_{$type}";
        $newValue = AdminSetting::toggle($key, auth()->id());

        $status = $newValue ? 'enabled' : 'disabled';
        return $this->successResponse("Auto-accept for {$type} has been {$status}", [
            'enabled' => $newValue,
            'type' => $type
        ]);
    }

    /**
     * Get auto-accept status for all types
     */
    public function getAutoAcceptStatus(Request $request, $locale)
    {
        $types = ['designers', 'products', 'projects', 'services', 'marketplace', 'trainings', 'workshops', 'announcements'];
        $status = [];

        foreach ($types as $type) {
            $status[$type] = AdminSetting::isAutoAcceptEnabled($type);
        }

        return $this->successResponse('Auto-accept status retrieved', $status);
    }

    /**
     * Available counter types for the discover page
     */
    public function getAvailableCounterTypes()
    {
        return [
            'all_members' => ['label' => 'All Members', 'description' => 'Total active registered members', 'category' => 'simple'],
            'designers' => ['label' => 'Designers (All)', 'description' => 'Total active designers (all sectors)', 'category' => 'simple'],
            'designers_by_sector' => ['label' => 'Designers by Sector', 'description' => 'Count designers filtered by selected sectors', 'category' => 'sector_filter'],
            'products' => ['label' => 'Products', 'description' => 'Total approved products', 'category' => 'simple'],
            'projects' => ['label' => 'Projects', 'description' => 'Total approved projects', 'category' => 'simple'],
            'services' => ['label' => 'Services', 'description' => 'Total approved services', 'category' => 'simple'],
            'fablabs' => ['label' => 'Fab Labs', 'description' => 'Total fab labs', 'category' => 'simple'],
            'trainings' => ['label' => 'Trainings', 'description' => 'Total trainings', 'category' => 'simple'],
            'tenders' => ['label' => 'Tenders', 'description' => 'Total tenders', 'category' => 'simple'],
            'marketplace_posts' => ['label' => 'Marketplace Posts', 'description' => 'Total approved marketplace posts', 'category' => 'simple'],
        ];
    }

    /**
     * Available sectors for filtering designers
     * These are loaded from DropdownHelper which reads from the database
     */
    public function getAvailableSectors()
    {
        $sectors = \App\Helpers\DropdownHelper::sectorOptions();
        $result = [];
        foreach ($sectors as $sector) {
            // Exclude 'guest' sector as it's not a real designer type
            if ($sector['value'] !== 'guest') {
                $result[] = [
                    'value' => $sector['value'],
                    'label' => $sector['label']
                ];
            }
        }
        return $result;
    }

    /**
     * Get default counter settings
     */
    private function getDefaultCounterSettings()
    {
        return [
            'badge_counter' => [
                'type' => 'designers',
                'label' => 'creative professionals',
                'label_ar' => 'مبدعين محترفين',
                'sectors' => [],
            ],
            'stats_counters' => [
                ['type' => 'products', 'label' => 'Products', 'label_ar' => 'المنتجات', 'sectors' => []],
                ['type' => 'projects', 'label' => 'Projects', 'label_ar' => 'المشاريع', 'sectors' => []],
                ['type' => 'designers_by_sector', 'label' => 'Manufacturers & Showrooms', 'label_ar' => 'المصنعين وصالات العرض', 'sectors' => ['manufacturer', 'showroom']],
            ],
        ];
    }

    /**
     * Get counter settings for the discover/home page
     */
    public function getCounterSettings(Request $request, $locale)
    {
        $counterSettings = SiteSetting::get('counter_settings');
        if (!$counterSettings) {
            $counterSettings = $this->getDefaultCounterSettings();
        }

        return $this->successResponse('Counter settings retrieved', [
            'settings' => $counterSettings,
            'available_types' => $this->getAvailableCounterTypes(),
        ]);
    }

    /**
     * Update counter settings
     */
    public function updateCounters(Request $request, $locale)
    {
        // Get badge counter and stats counters from request
        $badgeCounter = $request->input('badge_counter');
        $statsCounters = $request->input('stats_counters');

        // Basic validation - ensure required fields exist
        if (!$badgeCounter || !is_array($badgeCounter)) {
            return $this->errorResponse('Badge counter is required', 422);
        }

        if (!isset($badgeCounter['type']) || !isset($badgeCounter['label'])) {
            return $this->errorResponse('Badge counter must have type and label', 422);
        }

        if (!$statsCounters || !is_array($statsCounters) || count($statsCounters) < 1) {
            return $this->errorResponse('At least one stats counter is required', 422);
        }

        // Validate counter types
        $availableTypes = array_keys($this->getAvailableCounterTypes());
        if (!in_array($badgeCounter['type'], $availableTypes)) {
            return $this->errorResponse('Invalid badge counter type: ' . $badgeCounter['type'], 422);
        }

        foreach ($statsCounters as $index => $counter) {
            if (!isset($counter['type']) || !isset($counter['label'])) {
                return $this->errorResponse("Stats counter {$index} must have type and label", 422);
            }
            if (!in_array($counter['type'], $availableTypes)) {
                return $this->errorResponse("Invalid stats counter type: " . $counter['type'], 422);
            }
        }

        // Get available sectors for validation
        $availableSectors = array_map(function($sector) {
            return $sector['value'];
        }, $this->getAvailableSectors());

        // Clean up badge counter sectors and validate
        if ($badgeCounter['type'] !== 'designers_by_sector') {
            $badgeCounter['sectors'] = [];
        } else {
            $badgeCounter['sectors'] = isset($badgeCounter['sectors']) && is_array($badgeCounter['sectors'])
                ? array_values(array_filter($badgeCounter['sectors'], function($sector) use ($availableSectors) {
                    return in_array($sector, $availableSectors);
                }))
                : [];
            if (empty($badgeCounter['sectors'])) {
                return $this->errorResponse('Badge counter with "Designers by Sector" type requires at least one sector selected', 422);
            }
        }

        // Clean up stats counters sectors and validate
        foreach ($statsCounters as $index => &$counter) {
            if ($counter['type'] !== 'designers_by_sector') {
                $counter['sectors'] = [];
            } else {
                $counter['sectors'] = isset($counter['sectors']) && is_array($counter['sectors'])
                    ? array_values(array_filter($counter['sectors'], function($sector) use ($availableSectors) {
                        return in_array($sector, $availableSectors);
                    }))
                    : [];
                if (empty($counter['sectors'])) {
                    return $this->errorResponse('Stats counter "' . ($counter['label'] ?? ($index + 1)) . '" with "Designers by Sector" type requires at least one sector selected', 422);
                }
            }
        }

        $counterSettings = [
            'badge_counter' => $badgeCounter,
            'stats_counters' => $statsCounters,
        ];

        SiteSetting::set('counter_settings', $counterSettings, 'json', 'layout', 'Counter Settings', 'Home page counter configuration');

        // Clear homepage cache so new counter config takes effect immediately
        CacheService::clearDashboardCache();

        return $this->successResponse('Counter settings updated successfully');
    }

    /**
     * Reset counters to defaults
     */
    public function resetCounters(Request $request, $locale)
    {
        SiteSetting::set('counter_settings', $this->getDefaultCounterSettings(), 'json', 'layout', 'Counter Settings', 'Home page counter configuration');

        // Clear homepage cache so defaults take effect immediately
        CacheService::clearDashboardCache();

        return $this->successResponse('Counter settings reset to defaults');
    }

    /**
     * Default registration policies settings
     */
    private function getDefaultRegistrationPolicies()
    {
        return [
            'content' => "Welcome to Palestine Creative Hub!\n\n" .
                "By creating an account and using our platform, you agree to the following terms:\n\n" .
                "1. INTELLECTUAL PROPERTY\n" .
                "You retain ownership of all content you upload. However, you grant us a license to display your work on our platform. You must only upload content that you own or have permission to use.\n\n" .
                "2. USER CONDUCT\n" .
                "You agree to maintain professional conduct, respect other members, and not engage in harassment, spam, or fraudulent activities.\n\n" .
                "3. CONTENT GUIDELINES\n" .
                "All content must be appropriate for a professional creative community. We reserve the right to remove content that violates our guidelines.\n\n" .
                "4. ACCOUNT RESPONSIBILITY\n" .
                "You are responsible for maintaining the security of your account and all activities under your account.\n\n" .
                "5. PRIVACY\n" .
                "We respect your privacy and handle your data in accordance with our privacy policy.\n\n" .
                "6. MODIFICATIONS\n" .
                "We may update these terms from time to time. Continued use of the platform constitutes acceptance of any changes.\n\n" .
                "For questions, please contact our support team.",
        ];
    }

    /**
     * Update registration policies
     */
    public function updateRegistrationPolicies(Request $request, $locale)
    {
        $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        $policiesSettings = [
            'content' => $request->input('content'),
        ];

        SiteSetting::set('registration_policies', $policiesSettings, 'json', 'policies', 'Registration Policies', 'Terms and policies shown during registration');

        return $this->successResponse('Registration policies updated successfully');
    }

    /**
     * Reset registration policies to defaults
     */
    public function resetRegistrationPolicies(Request $request, $locale)
    {
        SiteSetting::set('registration_policies', $this->getDefaultRegistrationPolicies(), 'json', 'policies', 'Registration Policies', 'Terms and policies shown during registration');

        return $this->successResponse('Registration policies reset to defaults');
    }
}
