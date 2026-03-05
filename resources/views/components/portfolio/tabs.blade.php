@props(['designer', 'projectsData', 'productsData', 'servicesData', 'marketplaceData' => [], 'assetPaths', 'isOwner'])

@php
    // Calculate visible counts for each tab
    // Owner sees: approved + pending (excludes rejected)
    // Non-owner sees: only approved
    $projectsCount = $designer->projects ? ($isOwner
        ? $designer->projects->whereIn('approval_status', ['approved', 'pending'])->count()
        : $designer->projects->where('approval_status', 'approved')->count()
    ) : 0;

    $productsCount = $designer->products ? ($isOwner
        ? $designer->products->whereIn('approval_status', ['approved', 'pending'])->count()
        : $designer->products->where('approval_status', 'approved')->count()
    ) : 0;

    $servicesCount = $designer->services ? ($isOwner
        ? $designer->services->whereIn('approval_status', ['approved', 'pending'])->count()
        : $designer->services->where('approval_status', 'approved')->count()
    ) : 0;

    $marketplaceCount = $designer->marketplacePosts ? ($isOwner
        ? $designer->marketplacePosts->whereIn('approval_status', ['approved', 'pending'])->count()
        : $designer->marketplacePosts->where('approval_status', 'approved')->count()
    ) : 0;

    // Determine default active tab based on visible content
    $defaultTab = $projectsCount > 0 ? 'work' : ($productsCount > 0 ? 'products' : ($servicesCount > 0 ? 'services' : ($marketplaceCount > 0 ? 'marketplace' : 'about')));
@endphp

<div class="max-w-[1200px] mx-auto px-3 sm:px-4 md:px-6" x-data="{ activeTab: '{{ $defaultTab }}' }">
    <div class="mb-8 sm:mb-12">
        <!-- Tab Navigation - Enhanced Design -->
        <div class="mb-6 sm:mb-8 overflow-x-auto -mx-3 px-3 sm:mx-0 sm:px-0">
            <div class="inline-flex h-11 items-center justify-center rounded-xl bg-gray-100 p-1 text-gray-600 shadow-sm min-w-full sm:min-w-0">
                @if($projectsCount > 0 || $isOwner)
                <button @click="activeTab = 'work'"
                        :class="activeTab === 'work'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'hover:bg-white/50 hover:text-gray-900'"
                        class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium transition-all duration-200 whitespace-nowrap flex-1 sm:flex-initial">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <span>{{ __('Projects') }}</span>
                    @if($projectsCount > 0)
                    <span class="ml-0.5 inline-flex items-center justify-center min-w-[18px] h-4 sm:h-5 px-1 sm:px-1.5 text-[10px] sm:text-xs font-semibold bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-full">
                        {{ $projectsCount }}
                    </span>
                    @endif
                </button>
                @endif

                @if($productsCount > 0 || $isOwner)
                <button @click="activeTab = 'products'"
                        :class="activeTab === 'products'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'hover:bg-white/50 hover:text-gray-900'"
                        class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium transition-all duration-200 whitespace-nowrap flex-1 sm:flex-initial">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>{{ __('Products') }}</span>
                    @if($productsCount > 0)
                    <span class="ml-0.5 inline-flex items-center justify-center min-w-[18px] h-4 sm:h-5 px-1 sm:px-1.5 text-[10px] sm:text-xs font-semibold bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-full">
                        {{ $productsCount }}
                    </span>
                    @endif
                </button>
                @endif

                @if($servicesCount > 0 || $isOwner)
                <button @click="activeTab = 'services'"
                        :class="activeTab === 'services'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'hover:bg-white/50 hover:text-gray-900'"
                        class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium transition-all duration-200 whitespace-nowrap flex-1 sm:flex-initial">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ __('Services') }}</span>
                    @if($servicesCount > 0)
                    <span class="ml-0.5 inline-flex items-center justify-center min-w-[18px] h-4 sm:h-5 px-1 sm:px-1.5 text-[10px] sm:text-xs font-semibold bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-full">
                        {{ $servicesCount }}
                    </span>
                    @endif
                </button>
                @endif

                @if($marketplaceCount > 0 || $isOwner)
                <button @click="activeTab = 'marketplace'"
                        :class="activeTab === 'marketplace'
                            ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white shadow-sm'
                            : 'hover:bg-purple-50 hover:text-purple-700'"
                        class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium transition-all duration-200 whitespace-nowrap flex-1 sm:flex-initial">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>{{ __('Marketplace') }}</span>
                    @if($marketplaceCount > 0)
                    <span class="ml-0.5 inline-flex items-center justify-center min-w-[18px] h-4 sm:h-5 px-1 sm:px-1.5 text-[10px] sm:text-xs font-semibold bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-full">
                        {{ $marketplaceCount }}
                    </span>
                    @endif
                </button>
                @endif

                <button @click="activeTab = 'about'"
                        :class="activeTab === 'about'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'hover:bg-white/50 hover:text-gray-900'"
                        class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium transition-all duration-200 whitespace-nowrap flex-1 sm:flex-initial">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ __('About') }}</span>
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div x-show="activeTab === 'work'">
            <x-portfolio.tabs.projects
                :designer="$designer"
                :projects="$designer->projects"
                :is-owner="$isOwner"
            />
        </div>

        <div x-show="activeTab === 'products'">
            <x-portfolio.tabs.products
                :designer="$designer"
                :products="$designer->products"
                :is-owner="$isOwner"
            />
        </div>

        <div x-show="activeTab === 'services'">
            <x-portfolio.tabs.services
                :designer="$designer"
                :services="$designer->services"
                :is-owner="$isOwner"
            />
        </div>

        <div x-show="activeTab === 'marketplace'">
            <x-portfolio.tabs.marketplace
                :designer="$designer"
                :marketplace-posts="$marketplaceData"
                :is-owner="$isOwner"
            />
        </div>

        <div x-show="activeTab === 'about'">
            <x-portfolio.tabs.about
                :designer="$designer"
                :is-owner="$isOwner"
            />
        </div>
    </div>
</div>
