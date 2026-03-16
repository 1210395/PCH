<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="flex items-center justify-between h-16 px-6">
        <!-- Left: Mobile menu toggle & Breadcrumb -->
        <div class="flex items-center gap-4">
            <!-- Mobile menu toggle -->
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-100 transition-colors lg:hidden">
                <i class="fas fa-bars text-gray-600"></i>
            </button>

            <!-- Breadcrumb -->
            <nav class="hidden sm:flex items-center text-sm text-gray-500">
                <a href="{{ route('admin.dashboard', ['locale' => app()->getLocale()]) }}" class="hover:text-gray-700">
                    {{ __('Admin') }}
                </a>
                @hasSection('breadcrumb')
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    @yield('breadcrumb')
                @endif
            </nav>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-4">
            <!-- View Site -->
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
               target="_blank"
               class="hidden sm:flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800 transition-colors">
                <i class="fas fa-external-link-alt"></i>
                {{ __('View Site') }}
            </a>

            <!-- Language Switch -->
            @php
                $currentLocale = app()->getLocale();
                $otherLocale   = $currentLocale === 'en' ? 'ar' : 'en';
                $currentPath   = request()->getPathInfo();
                $switchedPath  = preg_replace('/^\/' . preg_quote($currentLocale, '/') . '\//', '/' . $otherLocale . '/', $currentPath);
                $langSwitchUrl = url($switchedPath) . (request()->getQueryString() ? '?' . request()->getQueryString() : '');
            @endphp
            <a href="{{ $langSwitchUrl }}"
               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors text-sm font-medium text-gray-700"
               title="{{ $currentLocale === 'en' ? 'Switch to Arabic' : 'التبديل إلى الإنجليزية' }}">
                @if($currentLocale === 'en')
                    <span class="text-base leading-none">🇵🇸</span>
                    <span>العربية</span>
                @else
                    <span class="text-base leading-none">🇬🇧</span>
                    <span>English</span>
                @endif
            </a>

            <!-- Pending Approvals Quick Access -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bell text-gray-600"></i>
                    @php
                        $totalPending = \App\Models\Product::pending()->count()
                            + \App\Models\Project::pending()->count()
                            + \App\Models\Service::pending()->count()
                            + \App\Models\MarketplacePost::pending()->count();
                    @endphp
                    @if($totalPending > 0)
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                            {{ $totalPending > 99 ? '99+' : $totalPending }}
                        </span>
                    @endif
                </button>

                <!-- Dropdown -->
                <div x-show="open"
                     x-transition
                     @click.away="open = false"
                     class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">{{ __('Pending Approvals') }}</h3>
                    </div>
                    <div class="py-2">
                        @php
                            $pendingProducts = \App\Models\Product::pending()->count();
                            $pendingProjects = \App\Models\Project::pending()->count();
                            $pendingServices = \App\Models\Service::pending()->count();
                            $pendingMarketplace = \App\Models\MarketplacePost::pending()->count();
                        @endphp

                        <a href="{{ route('admin.products.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
                           class="flex items-center justify-between px-4 py-2 hover:bg-gray-50">
                            <span class="text-gray-700">{{ __('Products') }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $pendingProducts > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $pendingProducts }}
                            </span>
                        </a>
                        <a href="{{ route('admin.projects.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
                           class="flex items-center justify-between px-4 py-2 hover:bg-gray-50">
                            <span class="text-gray-700">{{ __('Projects') }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $pendingProjects > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $pendingProjects }}
                            </span>
                        </a>
                        <a href="{{ route('admin.services.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
                           class="flex items-center justify-between px-4 py-2 hover:bg-gray-50">
                            <span class="text-gray-700">{{ __('Services') }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $pendingServices > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $pendingServices }}
                            </span>
                        </a>
                        <a href="{{ route('admin.marketplace.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
                           class="flex items-center justify-between px-4 py-2 hover:bg-gray-50">
                            <span class="text-gray-700">{{ __('Marketplace') }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $pendingMarketplace > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $pendingMarketplace }}
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold text-sm">
                        {{ substr(auth('designer')->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                </button>

                <!-- Dropdown -->
                <div x-show="open"
                     x-transition
                     @click.away="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="font-medium text-gray-800">{{ auth('designer')->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-gray-500">{{ auth('designer')->user()->email ?? '' }}</p>
                    </div>
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => auth('designer')->id() ]) }}"
                       class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-user w-4"></i>
                        {{ __('My Profile') }}
                    </a>
                    <form action="{{ route('logout', ['locale' => app()->getLocale()]) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt w-4"></i>
                            {{ __('Logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
