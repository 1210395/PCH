@props([
    'designer',
    'projectsData',
    'productsData',
    'servicesData',
    'marketplaceData' => [],
    'assetPaths' => []
])

{{-- Main Content --}}
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    {{-- Tab Navigation - Enhanced Design --}}
    <div class="mb-8 overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0">
        <div class="inline-flex h-11 items-center justify-center rounded-xl bg-gray-100 p-1 text-gray-600 shadow-sm min-w-full sm:min-w-0">
            <button @click="activeTab = 'profile'"
                    :class="activeTab === 'profile'
                        ? 'bg-white text-gray-900 shadow-sm'
                        : 'hover:bg-white/50 hover:text-gray-900'"
                    class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 whitespace-nowrap flex-1 sm:flex-initial">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="hidden xs:inline sm:inline">{{ __('Profile') }}</span>
            </button>
            <button @click="activeTab = 'projects'"
                    :class="activeTab === 'projects'
                        ? 'bg-white text-gray-900 shadow-sm'
                        : 'hover:bg-white/50 hover:text-gray-900'"
                    class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 relative whitespace-nowrap flex-1 sm:flex-initial">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <span class="hidden xs:inline sm:inline">{{ __('Projects') }}</span>
                <span x-show="projects.length > 0"
                      x-text="projects.length"
                      class="ml-0.5 sm:ml-1 inline-flex items-center justify-center min-w-[18px] sm:min-w-[20px] h-4 sm:h-5 px-1 sm:px-1.5 text-[10px] sm:text-xs font-semibold bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-full">
                </span>
            </button>
            <button @click="activeTab = 'products'"
                    :class="activeTab === 'products'
                        ? 'bg-white text-gray-900 shadow-sm'
                        : 'hover:bg-white/50 hover:text-gray-900'"
                    class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 whitespace-nowrap flex-1 sm:flex-initial">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="hidden xs:inline sm:inline">{{ __('Products') }}</span>
                <span x-show="products.length > 0"
                      x-text="products.length"
                      class="ml-0.5 sm:ml-1 inline-flex items-center justify-center min-w-[18px] sm:min-w-[20px] h-4 sm:h-5 px-1 sm:px-1.5 text-[10px] sm:text-xs font-semibold bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-full">
                </span>
            </button>
            <button @click="activeTab = 'services'"
                    :class="activeTab === 'services'
                        ? 'bg-white text-gray-900 shadow-sm'
                        : 'hover:bg-white/50 hover:text-gray-900'"
                    class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 whitespace-nowrap flex-1 sm:flex-initial">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span class="hidden xs:inline sm:inline">{{ __('Services') }}</span>
                <span x-show="services.length > 0"
                      x-text="services.length"
                      class="ml-0.5 sm:ml-1 inline-flex items-center justify-center min-w-[18px] sm:min-w-[20px] h-4 sm:h-5 px-1 sm:px-1.5 text-[10px] sm:text-xs font-semibold bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-full">
                </span>
            </button>
            <button @click="activeTab = 'marketplace'"
                    :class="activeTab === 'marketplace'
                        ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white shadow-sm'
                        : 'hover:bg-purple-50 hover:text-purple-700'"
                    class="inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-lg px-2 sm:px-4 py-2 text-xs sm:text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 whitespace-nowrap flex-1 sm:flex-initial">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span class="hidden xs:inline sm:inline">{{ __('Marketplace') }}</span>
                <span x-show="marketplacePosts.length > 0"
                      x-text="marketplacePosts.length"
                      class="ml-0.5 sm:ml-1 inline-flex items-center justify-center min-w-[18px] sm:min-w-[20px] h-4 sm:h-5 px-1 sm:px-1.5 text-[10px] sm:text-xs font-semibold bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-full">
                </span>
            </button>
        </div>
    </div>

    {{-- Profile Tab Content --}}
    <x-profile.tabs.profile
        :designer="$designer"
        :asset-paths="$assetPaths"
    />

    {{-- Projects Tab Content --}}
    <x-profile.tabs.portfolio
        type="project"
        :items="$projectsData"
        :title="__('Projects')"
        icon-path="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
    />

    {{-- Products Tab Content --}}
    <x-profile.tabs.portfolio
        type="product"
        :items="$productsData"
        :title="__('Products')"
        icon-path="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
    />

    {{-- Services Tab Content --}}
    <x-profile.tabs.portfolio
        type="service"
        :items="$servicesData"
        :title="__('Services')"
        icon-path="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
    />

    {{-- Marketplace Tab Content --}}
    <x-profile.tabs.marketplace-edit
        :items="$marketplaceData"
    />
</div>
