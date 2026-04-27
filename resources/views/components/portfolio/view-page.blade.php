@props([
    'designer',
    'projectsData',
    'productsData',
    'servicesData',
    'marketplaceData' => [],
    'assetPaths',
    'isOwner',
    'showWelcomePopup' => false,
])

<x-portfolio.layout
    :designer="$designer"
    :projects-data="$projectsData"
    :products-data="$productsData"
    :services-data="$servicesData"
    :marketplace-data="$marketplaceData"
>
    @if($isOwner && $designer->sector === 'guest')
    <div class="max-w-5xl mx-auto px-4 mt-4">
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-amber-900">{{ __('Guest Account') }}</p>
                    <p class="text-sm text-amber-700">{{ __('Your profile is not visible in search or discover. Upgrade to unlock all features and showcase your work!') }}</p>
                </div>
            </div>
            <a href="{{ route('account.upgrade', ['locale' => app()->getLocale()]) }}" class="flex-shrink-0 px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-lg font-semibold text-sm hover:shadow-lg transition-all">
                {{ __('Upgrade Now') }}
            </a>
        </div>
    </div>
    @endif

    <x-portfolio.header
        :designer="$designer"
        :asset-paths="$assetPaths"
        :is-owner="$isOwner"
    />

    <x-portfolio.tabs
        :designer="$designer"
        :projects-data="$projectsData"
        :products-data="$productsData"
        :services-data="$servicesData"
        :marketplace-data="$marketplaceData"
        :asset-paths="$assetPaths"
        :is-owner="$isOwner"
    />

    @if($designer->sector !== 'guest')
    <x-portfolio.ratings-section
        :designer="$designer"
    />
    @endif

    <x-portfolio.modals
        :designer="$designer"
    />

    @if($isOwner && $showWelcomePopup)
        <x-portfolio.modal.welcome-popup />
    @endif
</x-portfolio.layout>
