<?php
    $currentRoute = request()->route()->getName() ?? '';
    $locale = app()->getLocale();
?>

<aside class="fixed top-0 h-full bg-gray-800 text-white transition-all duration-300 z-50 shadow-xl flex flex-col <?php echo e(app()->getLocale() === 'ar' ? 'right-0' : 'left-0'); ?>"
       :class="{ 'w-64': sidebarOpen, 'w-20': !sidebarOpen }">

    <!-- Logo / Brand -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-700 flex-shrink-0">
        <a href="<?php echo e(route('admin.dashboard', ['locale' => $locale])); ?>" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center">
                <i class="fas fa-cube text-white text-lg"></i>
            </div>
            <span x-show="sidebarOpen" x-transition class="font-bold text-lg"><?php echo e(__('PCH Admin')); ?></span>
        </a>
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-700 transition-colors lg:block hidden">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="mt-6 px-3 flex-1 overflow-y-auto pb-24" x-data="{
        pendingCounts: { products: 0, projects: 0, services: 0, marketplace_posts: 0, academic_trainings: 0, academic_workshops: 0, academic_announcements: 0 },
        async loadPendingCounts() {
            try {
                const response = await fetch('<?php echo e(route('admin.pending-counts', ['locale' => $locale])); ?>');
                this.pendingCounts = await response.json();
            } catch (e) {
                console.error('Failed to load pending counts');
            }
        }
    }" x-init="loadPendingCounts()">

        <!-- Dashboard -->
        <a href="<?php echo e(route('admin.dashboard', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.dashboard') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-home w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition><?php echo e(__('Dashboard')); ?></span>
        </a>

        <!-- Profiles -->
        <a href="<?php echo e(route('admin.designers.index', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.designers') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-users w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Profiles')); ?></span>
        </a>

        <!-- Academic Accounts -->
        <a href="<?php echo e(route('admin.academic-accounts.index', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.academic-accounts') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-university w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Academic Accounts')); ?></span>
        </a>

        <!-- Products -->
        <a href="<?php echo e(route('admin.products.index', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.products') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-shopping-bag w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Products')); ?></span>
            <span x-show="sidebarOpen && pendingCounts.products > 0"
                  x-text="pendingCounts.products"
                  class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
            </span>
        </a>

        <!-- Projects -->
        <a href="<?php echo e(route('admin.projects.index', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.projects') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-folder w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Projects')); ?></span>
            <span x-show="sidebarOpen && pendingCounts.projects > 0"
                  x-text="pendingCounts.projects"
                  class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
            </span>
        </a>

        <!-- Services -->
        <a href="<?php echo e(route('admin.services.index', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.services') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-briefcase w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Services')); ?></span>
            <span x-show="sidebarOpen && pendingCounts.services > 0"
                  x-text="pendingCounts.services"
                  class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
            </span>
        </a>

        <!-- Marketplace -->
        <a href="<?php echo e(route('admin.marketplace.index', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.marketplace') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-store w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Marketplace')); ?></span>
            <span x-show="sidebarOpen && pendingCounts.marketplace_posts > 0"
                  x-text="pendingCounts.marketplace_posts"
                  class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
            </span>
        </a>

        <!-- FabLabs -->
        <a href="<?php echo e(route('admin.fablabs.index', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.fablabs') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-building w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('FabLabs')); ?></span>
        </a>

        <!-- Tenders -->
        <a href="<?php echo e(route('admin.tenders.index', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.tenders') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-file-contract w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Tenders')); ?></span>
        </a>

        <!-- Divider -->
        <div class="my-4 border-t border-gray-700"></div>

        <!-- Academic Content Section Header -->
        <div x-show="sidebarOpen" class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            <?php echo e(__('Academic Content')); ?>

        </div>

        <!-- Academic Trainings -->
        <a href="<?php echo e(route('admin.academic-content.trainings', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.academic-content.trainings') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-chalkboard-teacher w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Academic Trainings')); ?></span>
            <span x-show="sidebarOpen && pendingCounts.academic_trainings > 0"
                  x-text="pendingCounts.academic_trainings"
                  class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
            </span>
        </a>

        <!-- Academic Workshops -->
        <a href="<?php echo e(route('admin.academic-content.workshops', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.academic-content.workshops') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-tools w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Academic Workshops')); ?></span>
            <span x-show="sidebarOpen && pendingCounts.academic_workshops > 0"
                  x-text="pendingCounts.academic_workshops"
                  class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
            </span>
        </a>

        <!-- Academic Announcements -->
        <a href="<?php echo e(route('admin.academic-content.announcements', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.academic-content.announcements') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-bullhorn w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Academic Announcements')); ?></span>
            <span x-show="sidebarOpen && pendingCounts.academic_announcements > 0"
                  x-text="pendingCounts.academic_announcements"
                  class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
            </span>
        </a>

        <!-- Divider -->
        <div class="my-4 border-t border-gray-700"></div>

        <!-- Moderation Section Header -->
        <div x-show="sidebarOpen" class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            <?php echo e(__('Moderation')); ?>

        </div>

        <!-- Profile Ratings (expandable group) -->
        <div x-data="{ ratingsOpen: <?php echo e(str_starts_with($currentRoute, 'admin.ratings') ? 'true' : 'false'); ?> }">
            <button @click="ratingsOpen = !ratingsOpen"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-lg mb-1 transition-all duration-200
                           <?php echo e(str_starts_with($currentRoute, 'admin.ratings') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
                <i class="fas fa-star w-5 text-center"></i>
                <span x-show="sidebarOpen" x-transition class="flex-1 text-left"><?php echo e(__('Profile Ratings')); ?></span>
                <span x-show="sidebarOpen && pendingCounts.profile_ratings > 0"
                      x-text="pendingCounts.profile_ratings"
                      class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
                </span>
                <i x-show="sidebarOpen" :class="ratingsOpen ? 'fa-chevron-down' : 'fa-chevron-right'" class="fas text-xs opacity-60"></i>
            </button>

            <!-- Nested links -->
            <div x-show="ratingsOpen && sidebarOpen" x-transition class="ml-4 space-y-1 mb-2">
                <a href="<?php echo e(route('admin.ratings.index', ['locale' => $locale])); ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200
                          <?php echo e($currentRoute === 'admin.ratings.index' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'); ?>">
                    <i class="fas fa-list w-4 text-center text-xs"></i>
                    <span><?php echo e(__('All Ratings')); ?></span>
                </a>
                <a href="<?php echo e(route('admin.ratings.analytics', ['locale' => $locale])); ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200
                          <?php echo e($currentRoute === 'admin.ratings.analytics' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'); ?>">
                    <i class="fas fa-chart-bar w-4 text-center text-xs"></i>
                    <span><?php echo e(__('Analytics')); ?></span>
                </a>
                <a href="<?php echo e(route('admin.ratings.criteria.index', ['locale' => $locale])); ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200
                          <?php echo e(str_starts_with($currentRoute, 'admin.ratings.criteria') ? 'bg-gray-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'); ?>">
                    <i class="fas fa-check-square w-4 text-center text-xs"></i>
                    <span><?php echo e(__('Criteria')); ?></span>
                </a>
            </div>
        </div>

        <!-- Divider -->
        <div class="my-4 border-t border-gray-700"></div>

        <!-- Analytics (expandable group) -->
        <div x-data="{ analyticsOpen: <?php echo e(str_starts_with($currentRoute, 'admin.analytics') ? 'true' : 'false'); ?> }">
            <button @click="analyticsOpen = !analyticsOpen"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-lg mb-1 transition-all duration-200
                           <?php echo e(str_starts_with($currentRoute, 'admin.analytics') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
                <i class="fas fa-chart-area w-5 text-center"></i>
                <span x-show="sidebarOpen" x-transition class="flex-1 text-left"><?php echo e(__('Analytics')); ?></span>
                <i x-show="sidebarOpen" :class="analyticsOpen ? 'fa-chevron-down' : 'fa-chevron-right'" class="fas text-xs opacity-60"></i>
            </button>

            <!-- Nested links -->
            <div x-show="analyticsOpen && sidebarOpen" x-transition class="ml-4 space-y-1 mb-2">
                <a href="<?php echo e(route('admin.analytics.overview', ['locale' => $locale])); ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200
                          <?php echo e($currentRoute === 'admin.analytics.overview' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'); ?>">
                    <i class="fas fa-home w-4 text-center text-xs"></i>
                    <span><?php echo e(__('Overview')); ?></span>
                </a>
                <a href="<?php echo e(route('admin.analytics.engagement', ['locale' => $locale])); ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200
                          <?php echo e($currentRoute === 'admin.analytics.engagement' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'); ?>">
                    <i class="fas fa-heart w-4 text-center text-xs"></i>
                    <span><?php echo e(__('Engagement')); ?></span>
                </a>
                <a href="<?php echo e(route('admin.analytics.traffic', ['locale' => $locale])); ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200
                          <?php echo e($currentRoute === 'admin.analytics.traffic' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'); ?>">
                    <i class="fas fa-chart-bar w-4 text-center text-xs"></i>
                    <span><?php echo e(__('Traffic')); ?></span>
                </a>
                <a href="<?php echo e(route('admin.analytics.geographic', ['locale' => $locale])); ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200
                          <?php echo e($currentRoute === 'admin.analytics.geographic' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'); ?>">
                    <i class="fas fa-map-marker-alt w-4 text-center text-xs"></i>
                    <span><?php echo e(__('Geographic')); ?></span>
                </a>
                <a href="<?php echo e(route('admin.analytics.workflow', ['locale' => $locale])); ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200
                          <?php echo e($currentRoute === 'admin.analytics.workflow' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'); ?>">
                    <i class="fas fa-tasks w-4 text-center text-xs"></i>
                    <span><?php echo e(__('Workflow')); ?></span>
                </a>
                <a href="<?php echo e(route('admin.analytics.improvement', ['locale' => $locale])); ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200
                          <?php echo e($currentRoute === 'admin.analytics.improvement' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'); ?>">
                    <i class="fas fa-exclamation-triangle w-4 text-center text-xs"></i>
                    <span><?php echo e(__('Improvement')); ?></span>
                </a>
                <a href="<?php echo e(route('admin.analytics.search', ['locale' => $locale])); ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200
                          <?php echo e($currentRoute === 'admin.analytics.search' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'); ?>">
                    <i class="fas fa-search w-4 text-center text-xs"></i>
                    <span><?php echo e(__('Search')); ?></span>
                </a>
                <a href="<?php echo e(route('admin.analytics.insights', ['locale' => $locale])); ?>"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200
                          <?php echo e($currentRoute === 'admin.analytics.insights' ? 'bg-amber-500 text-white' : 'text-amber-400 hover:bg-gray-700 hover:text-white'); ?>">
                    <i class="fas fa-lightbulb w-4 text-center text-xs"></i>
                    <span><?php echo e(__('Insights')); ?></span>
                </a>
            </div>
        </div>

        <!-- Settings -->
        <a href="<?php echo e(route('admin.settings.index', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e($currentRoute === 'admin.settings.index' ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-cog w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Settings')); ?></span>
        </a>

        <!-- Pages -->
        <a href="<?php echo e(route('admin.pages.index', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.pages') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-file-alt w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Pages')); ?></span>
        </a>

        <!-- Lookups -->
        <a href="<?php echo e(route('admin.dropdowns.index', ['locale' => $locale])); ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg mb-2 transition-all duration-200
                  <?php echo e(str_starts_with($currentRoute, 'admin.dropdowns') ? 'sidebar-item active text-white' : 'text-gray-300 hover:bg-gray-700'); ?>">
            <i class="fas fa-list-alt w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="flex-1"><?php echo e(__('Lookups')); ?></span>
        </a>

    </nav>

    <!-- User Section at Bottom -->
    <div class="flex-shrink-0 p-4 border-t border-gray-700 bg-gray-800">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold">
                <?php echo e(substr(auth('designer')->user()->name ?? 'A', 0, 1)); ?>

            </div>
            <div x-show="sidebarOpen" x-transition class="flex-1 min-w-0">
                <p class="font-medium text-sm truncate"><?php echo e(auth('designer')->user()->name ?? 'Admin'); ?></p>
                <p class="text-xs text-gray-400 truncate"><?php echo e(auth('designer')->user()->email ?? ''); ?></p>
            </div>
            <form action="<?php echo e(route('logout', ['locale' => $locale])); ?>" method="POST" x-show="sidebarOpen">
                <?php echo csrf_field(); ?>
                <button type="submit" class="p-2 text-gray-400 hover:text-white transition-colors" title="<?php echo e(__('Logout')); ?>">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/admin/partials/sidebar.blade.php ENDPATH**/ ?>