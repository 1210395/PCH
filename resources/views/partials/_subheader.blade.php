{{-- Subheader Navigation - Visible on Discover, Services, Products, Projects, and Designers pages --}}
@php
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
@endphp

@if($showSubheader)
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
            canScrollLeft: false,
            canScrollRight: false,
            navEl: null,
            checkScroll() {
                if (!this.navEl) return;
                this.canScrollLeft = this.navEl.scrollLeft > 2;
                this.canScrollRight = this.navEl.scrollLeft < (this.navEl.scrollWidth - this.navEl.clientWidth - 2);
            },
            scrollNav(dir) {
                if (!this.navEl) return;
                this.navEl.scrollBy({ left: dir * 150, behavior: 'smooth' });
            },
            init() {
                this.navEl = this.$refs.nav;
                this.$nextTick(() => this.checkScroll());
                this.navEl.addEventListener('scroll', () => this.checkScroll(), { passive: true });
                new ResizeObserver(() => this.checkScroll()).observe(this.navEl);
            }
         }">
        <div class="relative">
            {{-- Left fade + arrow --}}
            <div x-show="canScrollLeft" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="absolute left-0 top-0 bottom-0 z-10 flex items-center sm:hidden">
                <div class="h-full w-10 bg-gradient-to-r from-slate-50 via-slate-50/90 to-transparent flex items-center pl-0.5">
                    <button @click="scrollNav(-1)" type="button" class="w-6 h-6 rounded-full bg-white/90 shadow border border-gray-200/60 flex items-center justify-center text-gray-500 hover:text-gray-700 active:scale-90 transition-all">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Right fade + arrow --}}
            <div x-show="canScrollRight" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="absolute right-0 top-0 bottom-0 z-10 flex items-center sm:hidden">
                <div class="h-full w-10 bg-gradient-to-l from-gray-100 via-gray-100/90 to-transparent flex items-center justify-end pr-0.5">
                    <button @click="scrollNav(1)" type="button" class="w-6 h-6 rounded-full bg-white/90 shadow border border-gray-200/60 flex items-center justify-center text-gray-500 hover:text-gray-700 active:scale-90 transition-all">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <nav x-ref="nav" class="flex items-center justify-start sm:justify-center gap-1.5 sm:gap-2 py-2 sm:py-2.5 overflow-x-auto scrollbar-hide scroll-smooth px-1">
                @foreach($navLinks as $link)
                    @php
                        $isActive = request()->is(trim($link['url'], '/')) || request()->is(app()->getLocale() . $link['url']) || request()->is(app()->getLocale() . $link['url'] . '/*');
                        $isHighlighted = $link['highlight'] ?? false;
                    @endphp
                    <a href="{{ url(app()->getLocale() . $link['url']) }}"
                       class="relative text-xs sm:text-sm font-medium whitespace-nowrap transition-all duration-200 px-3 sm:px-4 py-2 sm:py-1.5 rounded-full flex-shrink-0
                              @if($isActive)
                                  bg-blue-600 text-white shadow-md
                              @elseif($isHighlighted)
                                  bg-gradient-to-r from-blue-500 to-green-500 text-white shadow-md hover:shadow-lg
                              @else
                                  text-gray-600 hover:text-blue-600 hover:bg-white/80 hover:shadow-sm
                              @endif">
                        {{ (app()->getLocale() === 'ar' && !empty($link['title_ar'])) ? $link['title_ar'] : $link['title'] }}
                    </a>
                @endforeach
            </nav>
        </div>
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
@endif
