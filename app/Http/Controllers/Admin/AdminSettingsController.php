<?php

namespace App\Http\Controllers\Admin;

use App\Models\SiteSetting;
use App\Models\AdminSetting;
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
     * Display the settings page
     */
    public function index(Request $request, $locale)
    {
        $heroImages = [];
        foreach ($this->heroPages as $key => $label) {
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

        $footerSettings = SiteSetting::get('footer_settings') ?: AdminLayoutSettingsController::getDefaultFooterSettings();
        $headerSettings = SiteSetting::get('header_settings') ?: AdminLayoutSettingsController::getDefaultHeaderSettings();
        $subheaderSettings = SiteSetting::get('subheader_settings') ?: AdminLayoutSettingsController::getDefaultSubheaderSettings();
        $counterSettings = SiteSetting::get('counter_settings') ?: AdminCounterSettingsController::getDefaultCounterSettings();
        $availableCounterTypes = AdminCounterSettingsController::getAvailableCounterTypes();
        $availableSectors = AdminCounterSettingsController::getAvailableSectors();
        $registrationPolicies = SiteSetting::get('registration_policies') ?: $this->getDefaultRegistrationPolicies();

        $heroTexts = [];
        foreach ($this->heroPages as $key => $label) {
            $heroTexts[$key] = SiteSetting::getHeroTexts($key) ?: self::getDefaultHeroTexts($key);
        }

        return view('admin.settings.index', compact('heroImages', 'heroTexts', 'footerSettings', 'headerSettings', 'subheaderSettings', 'counterSettings', 'availableCounterTypes', 'availableSectors', 'registrationPolicies'));
    }

    /**
     * Add hero image to a page's carousel (max 5 images)
     */
    public function updateHeroImage(Request $request, $locale)
    {
        $request->validate([
            'page' => 'required|string|in:' . implode(',', array_keys($this->heroPages)),
            'image' => 'required|image|max:10240',
        ]);

        $page = $request->input('page');

        $currentImages = SiteSetting::getHeroImagePaths($page);
        if (count($currentImages) >= 5) {
            return $this->errorResponse('Maximum 5 images allowed per page. Please remove an image first.', 422);
        }

        $file = $request->file('image');
        $filename = $page . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $imagePath = $file->storeAs('hero_images', $filename, 'public');

        SiteSetting::addHeroImage($page, $imagePath);

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

        $removedPath = SiteSetting::removeHeroImage($page, $index);

        if ($removedPath) {
            Storage::disk('public')->delete($removedPath);
        }

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

    /**
     * Update hero texts for a specific page
     */
    public function updateHeroTexts(Request $request, $locale)
    {
        $request->validate([
            'page' => 'required|string|in:' . implode(',', array_keys($this->heroPages)),
            'title' => 'required|string|max:200',
            'title_ar' => 'required|string|max:200',
            'subtitle' => 'required|string|max:500',
            'subtitle_ar' => 'required|string|max:500',
        ]);

        $page = $request->input('page');
        SiteSetting::setHeroTexts($page, [
            'title' => $request->input('title'),
            'title_ar' => $request->input('title_ar'),
            'subtitle' => $request->input('subtitle'),
            'subtitle_ar' => $request->input('subtitle_ar'),
        ]);

        return $this->successResponse('Hero texts updated for ' . $this->heroPages[$page]);
    }

    /**
     * Reset hero texts for a specific page to defaults
     */
    public function resetHeroTexts(Request $request, $locale)
    {
        $request->validate([
            'page' => 'required|string|in:' . implode(',', array_keys($this->heroPages)),
        ]);

        $page = $request->input('page');
        SiteSetting::setHeroTexts($page, self::getDefaultHeroTexts($page));

        return $this->successResponse('Hero texts reset to defaults for ' . $this->heroPages[$page]);
    }

    /**
     * Default hero texts for each page
     */
    public static function getDefaultHeroTexts($page)
    {
        $defaults = [
            'home' => [
                'title' => 'Showcase Your Creative Excellence',
                'title_ar' => 'اعرض تميزك الإبداعي',
                'subtitle' => 'The ultimate platform for designers, MSMEs, and creative industries to connect, collaborate, and grow their business',
                'subtitle_ar' => 'المنصة المثالية للمصممين والمنشآت الصغيرة والمتوسطة والصناعات الإبداعية للتواصل والتعاون وتنمية أعمالهم',
            ],
            'products' => [
                'title' => 'Discover Unique Products',
                'title_ar' => 'اكتشف منتجات فريدة',
                'subtitle' => 'Explore handcrafted and unique products from talented creators and manufacturers',
                'subtitle_ar' => 'استكشف منتجات مصنوعة يدوياً وفريدة من المبدعين والمصنعين الموهوبين',
            ],
            'projects' => [
                'title' => 'Explore Creative Projects',
                'title_ar' => 'استكشف المشاريع الإبداعية',
                'subtitle' => 'Discover inspiring work from talented designers and creative professionals',
                'subtitle_ar' => 'اكتشف أعمالاً ملهمة من المصممين والمحترفين المبدعين الموهوبين',
            ],
            'services' => [
                'title' => 'Professional Services',
                'title_ar' => 'الخدمات المهنية',
                'subtitle' => 'Connect with skilled professionals offering design, consulting, and creative services',
                'subtitle_ar' => 'تواصل مع محترفين مهرة يقدمون خدمات التصميم والاستشارات والخدمات الإبداعية',
            ],
            'marketplace' => [
                'title' => 'Marketplace',
                'title_ar' => 'السوق',
                'subtitle' => 'Discover services, collaborations, showcases and opportunities from the design community',
                'subtitle_ar' => 'اكتشف الخدمات والتعاون والمعارض والفرص من مجتمع التصميم',
            ],
            'designers' => [
                'title' => 'Discover Creative Talent',
                'title_ar' => 'اكتشف المواهب الإبداعية',
                'subtitle' => 'Browse talented designers, manufacturers, showrooms, and vendors. Connect with creative professionals and discover amazing work.',
                'subtitle_ar' => 'تصفح المصممين والمصنعين وصالات العرض والموردين الموهوبين. تواصل مع المحترفين المبدعين واكتشف أعمالاً مذهلة.',
            ],
            'fab_labs' => [
                'title' => 'Fab Labs & Incubators',
                'title_ar' => 'مختبرات التصنيع والحاضنات',
                'subtitle' => 'Discover fabrication laboratories across Palestine. Access cutting-edge equipment, learn new skills, and bring your ideas to life.',
                'subtitle_ar' => 'اكتشف مختبرات التصنيع في فلسطين. احصل على معدات متطورة وتعلم مهارات جديدة وحقق أفكارك.',
            ],
            'trainings' => [
                'title' => 'Training & Workshops',
                'title_ar' => 'التدريب وورش العمل',
                'subtitle' => 'Enhance your skills with professional training courses from academic institutions.',
                'subtitle_ar' => 'طور مهاراتك من خلال دورات تدريبية مهنية من المؤسسات الأكاديمية.',
            ],
            'tenders' => [
                'title' => 'Tenders & Opportunities',
                'title_ar' => 'المناقصات والفرص',
                'subtitle' => 'Discover the latest tender opportunities for designers, developers, and creative professionals across Palestine.',
                'subtitle_ar' => 'اكتشف أحدث فرص المناقصات للمصممين والمطورين والمحترفين المبدعين في فلسطين.',
            ],
            'academic_tevets' => [
                'title' => 'Academic & Workplace Learning Centers',
                'title_ar' => 'المراكز الأكاديمية ومراكز التعلم المهني',
                'subtitle' => 'Discover academic institutions, TVETs, and workplace learning centers across Palestine. Connect with universities, colleges, and workplace learning enterprises.',
                'subtitle_ar' => 'اكتشف المؤسسات الأكاديمية ومراكز التعليم والتدريب المهني والتقني ومراكز التعلم في مكان العمل في فلسطين.',
            ],
        ];

        return $defaults[$page] ?? [
            'title' => '',
            'title_ar' => '',
            'subtitle' => '',
            'subtitle_ar' => '',
        ];
    }
}
