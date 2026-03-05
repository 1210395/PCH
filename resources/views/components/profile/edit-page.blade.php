@props([
    'designer',
    'projectsData',
    'productsData',
    'servicesData',
    'marketplaceData' => [],
    'certificationsData' => [],
    'assetPaths' => []
])

<x-profile.layout
    :designer="$designer"
    :projects-data="$projectsData"
    :products-data="$productsData"
    :services-data="$servicesData"
    :marketplace-data="$marketplaceData"
    :certifications-data="$certificationsData"
>
    <x-profile.header
        :back-url="route('profile', ['locale' => app()->getLocale()])"
    />

    <x-profile.tabs
        :designer="$designer"
        :projects-data="$projectsData"
        :products-data="$productsData"
        :services-data="$servicesData"
        :marketplace-data="$marketplaceData"
        :asset-paths="$assetPaths"
    />

    <x-profile.modals
        :designer="$designer"
    />
</x-profile.layout>

<style>
[x-cloak] {
    display: none !important;
}

@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
