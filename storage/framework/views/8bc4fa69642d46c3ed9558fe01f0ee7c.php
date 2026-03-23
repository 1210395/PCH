
<?php
    $subheaderSettings = \App\Models\SiteSetting::get('subheader_settings');
    if (!$subheaderSettings) {
        $subheaderSettings = [
            'enabled' => true,
            'nav_links' => [
                ['title' => 'My Projects', 'url' => '/my-projects', 'highlight' => false, 'order' => 1],
                ['title' => 'My Products', 'url' => '/my-products', 'highlight' => false, 'order' => 2],
                ['title' => 'My Services', 'url' => '/my-services', 'highlight' => false, 'order' => 3],
                ['title' => 'Add Project', 'url' => '/projects/create', 'highlight' => true, 'order' => 4],
                ['title' => 'Add Product', 'url' => '/products/create', 'highlight' => true, 'order' => 5],
            ],
        ];
    }

    $subheaderEnabled = $subheaderSettings['enabled'] ?? true;
    $navLinks = $subheaderSettings['nav_links'] ?? [];

    // Sort links by order
    usort($navLinks, function($a, $b) {
        return ($a['order'] ?? 999) - ($b['order'] ?? 999);
    });

    // Check if we're on allowed pages (discover, services, products, projects, designers and their subpages)
    $allowedPages = [
        'home',                    // Discover page
        'services*',               // Services and subpages
        'products*',               // Products and subpages
        'projects*',               // Projects and subpages
        'designers*',              // Designers and subpages
        'designer.profile*',       // Designer profile pages
    ];

    $isAllowedPage = false;
    foreach ($allowedPages as $pattern) {
        if (request()->routeIs($pattern) || request()->is(app()->getLocale() . '/' . str_replace('*', '', $pattern) . '*') || request()->is(str_replace('*', '', $pattern) . '*')) {
            $isAllowedPage = true;
            break;
        }
    }

    // Show subheader on allowed pages
    $showSubheader = $subheaderEnabled && $isAllowedPage && count($navLinks) > 0;
?>

<?php if($showSubheader): ?>
<div id="subheader"
     class="bg-gradient-to-r from-slate-50 to-gray-100 border-b border-gray-200/80 shadow-sm transition-all duration-300 ease-in-out"
     x-data="{
        hidden: false,
        lastScrollY: 0,
        init() {
            this.lastScrollY = window.scrollY;
            window.addEventListener('scroll', () => {
                const currentScrollY = window.scrollY;
                if (currentScrollY > this.lastScrollY && currentScrollY > 100) {
                    this.hidden = true;
                } else {
                    this.hidden = false;
                }
                this.lastScrollY = currentScrollY;
            }, { passive: true });
        }
     }"
     :class="{ '-translate-y-full opacity-0': hidden, 'translate-y-0 opacity-100': !hidden }">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6"
         x-data="{
            isDragging: false,
            startX: 0,
            scrollStart: 0,
            navEl: null,
            init() {
                this.navEl = this.$refs.nav;

                // Touch swipe support (passive, handled by CSS scroll)
                // Mouse drag support for desktop
                this.navEl.addEventListener('mousedown', (e) => {
                    this.isDragging = true;
                    this.startX = e.pageX - this.navEl.offsetLeft;
                    this.scrollStart = this.navEl.scrollLeft;
                    this.navEl.style.cursor = 'grabbing';
                    this.navEl.style.userSelect = 'none';
                });
                document.addEventListener('mousemove', (e) => {
                    if (!this.isDragging) return;
                    e.preventDefault();
                    const x = e.pageX - this.navEl.offsetLeft;
                    const walk = (x - this.startX) * 1.5;
                    this.navEl.scrollLeft = this.scrollStart - walk;
                });
                document.addEventListener('mouseup', () => {
                    if (this.isDragging) {
                        this.isDragging = false;
                        this.navEl.style.cursor = '';
                        this.navEl.style.userSelect = '';
                    }
                });
            }
         }">
        <nav x-ref="nav"
             class="flex items-center justify-start sm:justify-center gap-1 sm:gap-2 py-2 sm:py-2.5 overflow-x-auto scrollbar-hide scroll-smooth snap-x snap-mandatory sm:snap-none touch-pan-x">
            <?php $__currentLoopData = $navLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isActive = request()->is(trim($link['url'], '/')) || request()->is(app()->getLocale() . $link['url']) || request()->is(app()->getLocale() . $link['url'] . '/*');
                    $isHighlighted = $link['highlight'] ?? false;
                ?>
                <a href="<?php echo e(url(app()->getLocale() . $link['url'])); ?>"
                   class="relative text-xs sm:text-sm font-medium whitespace-nowrap transition-all duration-200 px-3 sm:px-4 py-2 sm:py-1.5 rounded-full flex-shrink-0 snap-start
                          <?php if($isActive): ?>
                              bg-blue-600 text-white shadow-md
                          <?php elseif($isHighlighted): ?>
                              bg-gradient-to-r from-blue-500 to-green-500 text-white shadow-md hover:shadow-lg
                          <?php else: ?>
                              text-gray-600 hover:text-blue-600 hover:bg-white/80 hover:shadow-sm
                          <?php endif; ?>">
                    <?php echo e((app()->getLocale() === 'ar' && !empty($link['title_ar'])) ? $link['title_ar'] : $link['title']); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
    </div>
</div>

<style>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
<?php endif; ?>
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/partials/_subheader.blade.php ENDPATH**/ ?>