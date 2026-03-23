<!-- START: Laravel Integration Point -->
<!-- Expected data: Dynamic footer settings from SiteSetting model -->
<!-- START: Footer Component - Matching Figma Footer Design EXACTLY -->
<?php
    $footerSettings = \App\Models\SiteSetting::get('footer_settings');
    if (!$footerSettings) {
        $footerSettings = [
            'description' => 'A digital hub and marketplace supporting designers, MSMEs, and creative industries in Palestine. Connecting talent with opportunities.',
            'quick_links' => [
                ['title' => 'Discover', 'url' => '/'],
                ['title' => 'Projects', 'url' => '/projects'],
                ['title' => 'Products', 'url' => '/products'],
                ['title' => 'Fab Labs', 'url' => '/fab-labs'],
                ['title' => 'Marketplace', 'url' => '/marketplace'],
            ],
            'resource_links' => [
                ['title' => 'About Us', 'url' => '#'],
                ['title' => 'Support', 'url' => '#'],
                ['title' => 'Community Guidelines', 'url' => '#'],
                ['title' => 'Terms of Service', 'url' => '#'],
                ['title' => 'Privacy Policy', 'url' => '#'],
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
            'bottom_links' => [
                ['title' => 'Accessibility', 'url' => '#'],
                ['title' => 'Sitemap', 'url' => '#'],
            ],
            'supporter_text' => 'Platform supported by Global Communities and through the Swedish Government.',
        ];
    }
?>
<footer class="bg-gradient-to-r from-blue-600 to-green-500 text-white">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-8 sm:py-12 animate-on-load animate-fadeInUp delay-200">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8 mb-6 sm:mb-8">

            <!-- About Section -->
            <div>
                <div class="mb-4">
                    <img src="<?php echo e(asset('images/logo-white.webp')); ?>" alt="Palestine Creative Hub" class="h-[3.6rem]">
                </div>
                <p class="text-white/90 text-sm leading-relaxed">
                    <?php if(app()->getLocale() === 'ar' && !empty($footerSettings['description_ar'])): ?>
                        <?php echo e($footerSettings['description_ar']); ?>

                    <?php else: ?>
                        <?php echo e($footerSettings['description'] ?? __('A digital hub and marketplace supporting designers, MSMEs, and creative industries in Palestine. Connecting talent with opportunities.')); ?>

                    <?php endif; ?>
                </p>
                <?php
                    $supporterText = (app()->getLocale() === 'ar' && !empty($footerSettings['supporter_text_ar'])) ? $footerSettings['supporter_text_ar'] : ($footerSettings['supporter_text'] ?? '');
                ?>
                <?php if(!empty($supporterText)): ?>
                <p class="text-white/70 text-xs mt-3 leading-relaxed">
                    <?php echo e($supporterText); ?>

                </p>
                <?php endif; ?>
				<div class="mt-3 flex items-center gap-4 sm:gap-5">
					<img src="<?php echo e(asset('images/sweden-english.png')); ?>" alt="Sweden" class="w-24 sm:w-[130px] max-w-[50%]" />
					<img src="<?php echo e(asset('images/global-w.png')); ?>" alt="Global Communites" class="w-24 sm:w-[130px] max-w-[50%]" />
				</div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="mb-4"><?php echo e(__('Quick Links')); ?></h3>
                <ul class="space-y-3">
                    <?php $__currentLoopData = $footerSettings['quick_links'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $linkUrl = $link['url'] ?? '#';
                        $isExternal = str_starts_with($linkUrl, 'http://') || str_starts_with($linkUrl, 'https://');
                        $href = $linkUrl === '#' ? '#' : ($isExternal ? $linkUrl : url(app()->getLocale() . $linkUrl));
                        $linkTitle = (app()->getLocale() === 'ar' && !empty($link['title_ar'])) ? $link['title_ar'] : $link['title'];
                    ?>
                    <li>
                        <a href="<?php echo e($href); ?>" class="text-white/90 hover:text-white transition-colors text-sm" <?php if($isExternal): ?> target="_blank" rel="noopener noreferrer" <?php endif; ?>>
                            <?php echo e($linkTitle); ?>

                        </a>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h3 class="mb-4"><?php echo e(__('Resources')); ?></h3>
                <ul class="space-y-3">
                    <?php $__currentLoopData = $footerSettings['resource_links'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $linkUrl = $link['url'] ?? '#';
                        $isExternal = str_starts_with($linkUrl, 'http://') || str_starts_with($linkUrl, 'https://');
                        $href = $linkUrl === '#' ? '#' : ($isExternal ? $linkUrl : url(app()->getLocale() . $linkUrl));
                        $linkTitle = (app()->getLocale() === 'ar' && !empty($link['title_ar'])) ? $link['title_ar'] : $link['title'];
                    ?>
                    <li>
                        <a href="<?php echo e($href); ?>" class="text-white/90 hover:text-white transition-colors text-sm" <?php if($isExternal): ?> target="_blank" rel="noopener noreferrer" <?php endif; ?>>
                            <?php echo e($linkTitle); ?>

                        </a>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h3 class="mb-4"><?php echo e(__('Contact Us')); ?></h3>
                <ul class="space-y-3">
                    <?php if(!empty($footerSettings['contact']['address'])): ?>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-white/90 text-sm">
                            <?php echo nl2br(e($footerSettings['contact']['address'])); ?>

                        </span>
                    </li>
                    <?php endif; ?>
                    <?php if(!empty($footerSettings['contact']['email'])): ?>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <a href="mailto:<?php echo e($footerSettings['contact']['email']); ?>" class="text-white/90 hover:text-white transition-colors text-sm">
                            <?php echo e($footerSettings['contact']['email']); ?>

                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if(!empty($footerSettings['contact']['phone'])): ?>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <a href="tel:<?php echo e(preg_replace('/\s+/', '', $footerSettings['contact']['phone'])); ?>" class="text-white/90 hover:text-white transition-colors text-sm">
                            <?php echo e($footerSettings['contact']['phone']); ?>

                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- Social Media -->
                <?php if(!empty($footerSettings['social_links'])): ?>
                <div class="flex items-center gap-3 mt-6">
                    <?php $__currentLoopData = $footerSettings['social_links']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $social): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $socialUrl = $social['url'] ?? '#';
                        // Remove any leading # that might have been accidentally added
                        if (str_starts_with($socialUrl, '#http')) {
                            $socialUrl = substr($socialUrl, 1);
                        }
                        $isValidUrl = str_starts_with($socialUrl, 'http://') || str_starts_with($socialUrl, 'https://');
                        $socialHref = $isValidUrl ? $socialUrl : '#';
                    ?>
                    <a
                        href="<?php echo e($socialHref); ?>"
                        class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors"
                        aria-label="<?php echo e(ucfirst($social['platform'])); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?php switch($social['platform']):
                            case ('facebook'): ?>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                <?php break; ?>
                            <?php case ('twitter'): ?>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                                <?php break; ?>
                            <?php case ('instagram'): ?>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                                <?php break; ?>
                            <?php case ('linkedin'): ?>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                                <?php break; ?>
                            <?php case ('youtube'): ?>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                                <?php break; ?>
                            <?php case ('tiktok'): ?>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                </svg>
                                <?php break; ?>
                        <?php endswitch; ?>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="pt-8 border-t border-white/20">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-white/90 text-sm">
                    <?php if(app()->getLocale() === 'ar' && !empty($footerSettings['copyright_ar'])): ?>
                        <?php echo e($footerSettings['copyright_ar']); ?>

                    <?php else: ?>
                        <?php echo e($footerSettings['copyright'] ?? '© ' . date('Y') . ' Palestine Creative Hub. All rights reserved.'); ?>

                    <?php endif; ?>
                </p>
                <?php if(!empty($footerSettings['bottom_links'])): ?>
                <div class="flex items-center gap-6">
                    <?php $__currentLoopData = $footerSettings['bottom_links']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $bottomLinkUrl = $link['url'] ?? '#';
                        $isExternal = str_starts_with($bottomLinkUrl, 'http://') || str_starts_with($bottomLinkUrl, 'https://');
                        $bottomHref = $bottomLinkUrl === '#' ? '#' : ($isExternal ? $bottomLinkUrl : url(app()->getLocale() . $bottomLinkUrl));
                    ?>
                    <?php
                        $bottomLinkTitle = (app()->getLocale() === 'ar' && !empty($link['title_ar'])) ? $link['title_ar'] : $link['title'];
                    ?>
                    <a href="<?php echo e($bottomHref); ?>" class="text-white/90 hover:text-white transition-colors text-sm" <?php if($isExternal): ?> target="_blank" rel="noopener noreferrer" <?php endif; ?>>
                        <?php echo e($bottomLinkTitle); ?>

                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>
<!-- END: Laravel Integration Point -->
<!-- END: Footer Component -->
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/partials/_footer.blade.php ENDPATH**/ ?>