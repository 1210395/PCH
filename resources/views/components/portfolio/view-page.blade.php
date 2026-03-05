@props([
    'designer',
    'projectsData',
    'productsData',
    'servicesData',
    'marketplaceData' => [],
    'assetPaths',
    'isOwner'
])

<x-portfolio.layout
    :designer="$designer"
    :projects-data="$projectsData"
    :products-data="$productsData"
    :services-data="$servicesData"
    :marketplace-data="$marketplaceData"
>
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

    <x-portfolio.ratings-section
        :designer="$designer"
    />

    <x-portfolio.modals
        :designer="$designer"
    />
</x-portfolio.layout>
