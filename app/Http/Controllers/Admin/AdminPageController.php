<?php

namespace App\Http\Controllers\Admin;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin editor for CMS static pages (About, Terms, Privacy, etc.).
 *
 * Each page is stored as a set of key/value entries in SiteSetting.
 * Supports editing text sections, uploading/removing images, and
 * resetting a page to its default content.
 */
class AdminPageController extends AdminBaseController
{
    /**
     * Page configurations with their structured sections
     */
    private function getPageConfigs()
    {
        return [
            'about' => [
                'label' => __('About Us'),
                'icon' => 'fas fa-info-circle',
                'color' => 'blue',
                'sections' => ['hero', 'mission', 'vision', 'what_we_do', 'team'],
            ],
            'support' => [
                'label' => __('Support'),
                'icon' => 'fas fa-life-ring',
                'color' => 'green',
                'sections' => ['hero', 'content', 'faq'],
            ],
            'community_guidelines' => [
                'label' => __('Community Guidelines'),
                'icon' => 'fas fa-users',
                'color' => 'purple',
                'sections' => ['hero', 'content'],
            ],
            'terms' => [
                'label' => __('Terms of Service'),
                'icon' => 'fas fa-file-contract',
                'color' => 'indigo',
                'sections' => ['hero', 'content'],
            ],
            'privacy' => [
                'label' => __('Privacy Policy'),
                'icon' => 'fas fa-shield-alt',
                'color' => 'teal',
                'sections' => ['hero', 'content'],
            ],
            'accessibility' => [
                'label' => __('Accessibility'),
                'icon' => 'fas fa-universal-access',
                'color' => 'orange',
                'sections' => ['hero', 'content'],
            ],
            'sitemap' => [
                'label' => __('Sitemap'),
                'icon' => 'fas fa-sitemap',
                'color' => 'gray',
                'sections' => ['hero', 'content'],
            ],
        ];
    }

    /**
     * Default content for each page
     */
    private function getDefaultContent($slug)
    {
        $defaults = [
            'about' => [
                'title' => 'About Us',
                'title_ar' => 'من نحن',
                'subtitle' => 'Learn more about Palestine Creative Hub',
                'subtitle_ar' => 'تعرف على مركز فلسطين الإبداعي',
                'hero_image' => null,
                'sections' => [
                    [
                        'key' => 'mission',
                        'title' => 'Our Mission',
                        'title_ar' => 'مهمتنا',
                        'content' => 'Palestine Creative Hub is a digital platform supporting designers, MSMEs, and creative industries in Palestine by connecting talent with opportunities.',
                        'content_ar' => 'مركز فلسطين الإبداعي هو منصة رقمية تدعم المصممين والمنشآت الصغيرة والمتوسطة والصناعات الإبداعية في فلسطين من خلال ربط المواهب بالفرص.',
                        'image' => null,
                    ],
                    [
                        'key' => 'vision',
                        'title' => 'Our Vision',
                        'title_ar' => 'رؤيتنا',
                        'content' => 'To be the leading digital hub for creative industries in Palestine, empowering designers and entrepreneurs to showcase their work and grow their businesses.',
                        'content_ar' => 'أن نكون المركز الرقمي الرائد للصناعات الإبداعية في فلسطين، وتمكين المصممين ورواد الأعمال من عرض أعمالهم وتنمية أعمالهم.',
                        'image' => null,
                    ],
                    [
                        'key' => 'what_we_do',
                        'title' => 'What We Do',
                        'title_ar' => 'ماذا نفعل',
                        'content' => 'We provide a marketplace for creative professionals, connect designers with clients, host training programs, and support fabrication laboratories across Palestine.',
                        'content_ar' => 'نوفر سوقاً للمحترفين المبدعين، ونربط المصممين بالعملاء، ونستضيف برامج تدريبية، وندعم مختبرات التصنيع في جميع أنحاء فلسطين.',
                        'image' => null,
                    ],
                ],
                'team_title' => 'Our Team',
                'team_title_ar' => 'فريقنا',
                'team_members' => [],
            ],
            'support' => [
                'title' => 'Support',
                'title_ar' => 'الدعم',
                'subtitle' => 'How can we help you?',
                'subtitle_ar' => 'كيف يمكننا مساعدتك؟',
                'hero_image' => null,
                'content' => 'If you need help with your account, products, or any other issue, please don\'t hesitate to reach out to us.',
                'content_ar' => 'إذا كنت بحاجة إلى مساعدة بشأن حسابك أو منتجاتك أو أي مشكلة أخرى، فلا تتردد في التواصل معنا.',
                'contact_email' => 'creativehub@technopark.ps',
                'contact_phone' => '+970593440216',
                'faq_items' => [
                    [
                        'question' => 'How do I create an account?',
                        'question_ar' => 'كيف أنشئ حساباً؟',
                        'answer' => 'Click on the "Sign Up" button and follow the registration wizard to create your account.',
                        'answer_ar' => 'انقر على زر "إنشاء حساب" واتبع معالج التسجيل لإنشاء حسابك.',
                    ],
                    [
                        'question' => 'How do I list my products?',
                        'question_ar' => 'كيف أعرض منتجاتي؟',
                        'answer' => 'After logging in, go to your portfolio and click "Add Product" to list your products.',
                        'answer_ar' => 'بعد تسجيل الدخول، انتقل إلى معرض أعمالك وانقر على "إضافة منتج" لعرض منتجاتك.',
                    ],
                ],
            ],
            'community_guidelines' => [
                'title' => 'Community Guidelines',
                'title_ar' => 'إرشادات المجتمع',
                'subtitle' => 'Our community standards and expectations',
                'subtitle_ar' => 'معايير مجتمعنا وتوقعاتنا',
                'hero_image' => null,
                'last_updated' => date('Y-m-d'),
                'sections' => [
                    [
                        'title' => 'Respect and Professionalism',
                        'title_ar' => 'الاحترام والمهنية',
                        'content' => 'Treat all community members with respect. Harassment, discrimination, and hate speech will not be tolerated.',
                        'content_ar' => 'عامل جميع أعضاء المجتمع باحترام. لن يتم التسامح مع التحرش والتمييز وخطاب الكراهية.',
                    ],
                    [
                        'title' => 'Original Content',
                        'title_ar' => 'المحتوى الأصلي',
                        'content' => 'Only upload content that you own or have permission to use. Respect intellectual property rights.',
                        'content_ar' => 'قم فقط بتحميل المحتوى الذي تملكه أو لديك إذن لاستخدامه. احترم حقوق الملكية الفكرية.',
                    ],
                    [
                        'title' => 'Quality Standards',
                        'title_ar' => 'معايير الجودة',
                        'content' => 'Maintain high quality in your listings. Provide accurate descriptions, clear images, and honest pricing.',
                        'content_ar' => 'حافظ على جودة عالية في عروضك. قدم أوصافاً دقيقة وصوراً واضحة وأسعاراً صادقة.',
                    ],
                ],
            ],
            'terms' => [
                'title' => 'Terms of Service',
                'title_ar' => 'شروط الخدمة',
                'subtitle' => 'Please read these terms carefully before using our platform',
                'subtitle_ar' => 'يرجى قراءة هذه الشروط بعناية قبل استخدام منصتنا',
                'hero_image' => null,
                'last_updated' => date('Y-m-d'),
                'sections' => [
                    [
                        'title' => 'Acceptance of Terms',
                        'title_ar' => 'قبول الشروط',
                        'content' => 'By accessing and using Palestine Creative Hub, you agree to be bound by these Terms of Service and all applicable laws and regulations.',
                        'content_ar' => 'من خلال الوصول إلى مركز فلسطين الإبداعي واستخدامه، فإنك توافق على الالتزام بشروط الخدمة هذه وجميع القوانين واللوائح المعمول بها.',
                    ],
                    [
                        'title' => 'User Accounts',
                        'title_ar' => 'حسابات المستخدمين',
                        'content' => 'You are responsible for maintaining the confidentiality of your account and password. You agree to accept responsibility for all activities that occur under your account.',
                        'content_ar' => 'أنت مسؤول عن الحفاظ على سرية حسابك وكلمة المرور الخاصة بك. توافق على تحمل المسؤولية عن جميع الأنشطة التي تحدث تحت حسابك.',
                    ],
                    [
                        'title' => 'Intellectual Property',
                        'title_ar' => 'الملكية الفكرية',
                        'content' => 'Users retain ownership of content they upload. By uploading, you grant Palestine Creative Hub a non-exclusive license to display your content on the platform.',
                        'content_ar' => 'يحتفظ المستخدمون بملكية المحتوى الذي يقومون بتحميله. من خلال التحميل، فإنك تمنح مركز فلسطين الإبداعي ترخيصاً غير حصري لعرض المحتوى الخاص بك على المنصة.',
                    ],
                ],
            ],
            'privacy' => [
                'title' => 'Privacy Policy',
                'title_ar' => 'سياسة الخصوصية',
                'subtitle' => 'How we collect, use, and protect your information',
                'subtitle_ar' => 'كيف نجمع معلوماتك ونستخدمها ونحميها',
                'hero_image' => null,
                'last_updated' => date('Y-m-d'),
                'sections' => [
                    [
                        'title' => 'Information We Collect',
                        'title_ar' => 'المعلومات التي نجمعها',
                        'content' => 'We collect information you provide directly to us, including your name, email address, phone number, and professional information when you create an account.',
                        'content_ar' => 'نجمع المعلومات التي تقدمها لنا مباشرة، بما في ذلك اسمك وعنوان بريدك الإلكتروني ورقم هاتفك والمعلومات المهنية عند إنشاء حساب.',
                    ],
                    [
                        'title' => 'How We Use Your Information',
                        'title_ar' => 'كيف نستخدم معلوماتك',
                        'content' => 'We use the information we collect to provide, maintain, and improve our services, to communicate with you, and to personalize your experience.',
                        'content_ar' => 'نستخدم المعلومات التي نجمعها لتقديم خدماتنا وصيانتها وتحسينها، والتواصل معك، وتخصيص تجربتك.',
                    ],
                    [
                        'title' => 'Data Protection',
                        'title_ar' => 'حماية البيانات',
                        'content' => 'We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.',
                        'content_ar' => 'نطبق تدابير أمنية مناسبة لحماية معلوماتك الشخصية من الوصول غير المصرح به أو التغيير أو الإفصاح أو التدمير.',
                    ],
                ],
            ],
            'accessibility' => [
                'title' => 'Accessibility',
                'title_ar' => 'إمكانية الوصول',
                'subtitle' => 'Our commitment to making our platform accessible to everyone',
                'subtitle_ar' => 'التزامنا بجعل منصتنا متاحة للجميع',
                'hero_image' => null,
                'sections' => [
                    [
                        'title' => 'Our Commitment',
                        'title_ar' => 'التزامنا',
                        'content' => 'Palestine Creative Hub is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone.',
                        'content_ar' => 'يلتزم مركز فلسطين الإبداعي بضمان إمكانية الوصول الرقمي للأشخاص ذوي الإعاقة. نعمل باستمرار على تحسين تجربة المستخدم للجميع.',
                    ],
                    [
                        'title' => 'Accessibility Features',
                        'title_ar' => 'ميزات إمكانية الوصول',
                        'content' => 'Our platform supports RTL (right-to-left) text direction for Arabic, responsive design for all devices, and keyboard navigation.',
                        'content_ar' => 'تدعم منصتنا اتجاه النص من اليمين إلى اليسار للعربية، والتصميم المتجاوب لجميع الأجهزة، والتنقل بلوحة المفاتيح.',
                    ],
                ],
            ],
            'sitemap' => [
                'title' => 'Sitemap',
                'title_ar' => 'خريطة الموقع',
                'subtitle' => 'Navigate our platform easily',
                'subtitle_ar' => 'تنقل في منصتنا بسهولة',
                'hero_image' => null,
                'auto_generate' => true,
                'additional_content' => '',
                'additional_content_ar' => '',
            ],
        ];

        return $defaults[$slug] ?? [];
    }

    /**
     * Display list of all manageable pages
     */
    public function index(Request $request, $locale)
    {
        $pages = $this->getPageConfigs();

        // Check which pages have custom content
        $customized = [];
        foreach (array_keys($pages) as $slug) {
            $customized[$slug] = SiteSetting::get("page_{$slug}") !== null;
        }

        return view('admin.pages.index', compact('pages', 'customized'));
    }

    /**
     * Edit a specific page
     */
    public function edit(Request $request, $locale, $slug)
    {
        $configs = $this->getPageConfigs();
        if (!isset($configs[$slug])) {
            abort(404, 'Page not found');
        }

        $config = $configs[$slug];
        $content = SiteSetting::get("page_{$slug}") ?? $this->getDefaultContent($slug);

        return view('admin.pages.edit', compact('slug', 'config', 'content'));
    }

    /**
     * Update page content
     */
    public function update(Request $request, $locale, $slug)
    {
        $configs = $this->getPageConfigs();
        if (!isset($configs[$slug])) {
            return $this->errorResponse(__('Page not found'), 404);
        }

        $content = $request->input('content', []);

        // Save to site_settings
        SiteSetting::set(
            "page_{$slug}",
            $content,
            'json',
            'pages',
            $configs[$slug]['label'],
            "Content for the {$slug} page"
        );

        return $this->successResponse(__('Page updated successfully'));
    }

    /**
     * Upload an image for a page section
     */
    public function uploadImage(Request $request, $locale, $slug)
    {
        $configs = $this->getPageConfigs();
        if (!isset($configs[$slug])) {
            return $this->errorResponse(__('Page not found'), 404);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'section' => 'nullable|string|max:50',
        ]);

        $file = $request->file('image');
        $filename = 'page_' . $slug . '_' . ($request->input('section', 'hero')) . '_' . time() . '.' . ($file->guessExtension() ?? $file->getClientOriginalExtension());
        $path = $file->storeAs('pages', $filename, 'public');

        return $this->successResponse(__('Image uploaded successfully'), [
            'path' => $path,
            'url' => url('media/' . $path),
        ]);
    }

    /**
     * Remove an image
     */
    public function removeImage(Request $request, $locale, $slug)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        // Security: ensure path is within pages directory using realpath validation
        $basePath = realpath(storage_path('app/public/pages'));
        $realPath = realpath(storage_path('app/public/' . $path));

        if (!$realPath || !$basePath || !str_starts_with($realPath, $basePath . DIRECTORY_SEPARATOR)) {
            return $this->errorResponse(__('Invalid image path'));
        }

        Storage::disk('public')->delete($path);

        return $this->successResponse(__('Image removed successfully'));
    }

    /**
     * Reset page to defaults
     */
    public function reset(Request $request, $locale, $slug)
    {
        $configs = $this->getPageConfigs();
        if (!isset($configs[$slug])) {
            return $this->errorResponse(__('Page not found'), 404);
        }

        $defaults = $this->getDefaultContent($slug);

        SiteSetting::set(
            "page_{$slug}",
            $defaults,
            'json',
            'pages',
            $configs[$slug]['label'],
            "Content for the {$slug} page"
        );

        return $this->successResponse(__('Page reset to defaults'), $defaults);
    }

    /**
     * Add a section to a page
     */
    public function addSection(Request $request, $locale, $slug)
    {
        $configs = $this->getPageConfigs();
        if (!isset($configs[$slug])) {
            return $this->errorResponse(__('Page not found'), 404);
        }

        $content = SiteSetting::get("page_{$slug}") ?? $this->getDefaultContent($slug);

        $sectionKey = $request->input('section_key', 'sections');
        $newSection = [
            'title' => '',
            'title_ar' => '',
            'content' => '',
            'content_ar' => '',
            'image' => null,
        ];

        if (!isset($content[$sectionKey])) {
            $content[$sectionKey] = [];
        }
        $content[$sectionKey][] = $newSection;

        SiteSetting::set("page_{$slug}", $content, 'json', 'pages');

        return $this->successResponse(__('Section added'), $content);
    }

    /**
     * Add an FAQ item (for support page)
     */
    public function addFaqItem(Request $request, $locale, $slug)
    {
        $content = SiteSetting::get("page_{$slug}") ?? $this->getDefaultContent($slug);

        $newFaq = [
            'question' => '',
            'question_ar' => '',
            'answer' => '',
            'answer_ar' => '',
        ];

        if (!isset($content['faq_items'])) {
            $content['faq_items'] = [];
        }
        $content['faq_items'][] = $newFaq;

        SiteSetting::set("page_{$slug}", $content, 'json', 'pages');

        return $this->successResponse(__('FAQ item added'), $content);
    }

    /**
     * Add a team member (for about page)
     */
    public function addTeamMember(Request $request, $locale, $slug)
    {
        $content = SiteSetting::get("page_{$slug}") ?? $this->getDefaultContent($slug);

        $newMember = [
            'name' => '',
            'name_ar' => '',
            'role' => '',
            'role_ar' => '',
            'image' => null,
        ];

        if (!isset($content['team_members'])) {
            $content['team_members'] = [];
        }
        $content['team_members'][] = $newMember;

        SiteSetting::set("page_{$slug}", $content, 'json', 'pages');

        return $this->successResponse(__('Team member added'), $content);
    }
}
