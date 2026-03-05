@extends('layout.main')

@section('title', __('Edit Profile'))

{{--
    Profile Edit Page - Refactored Component Architecture:
    - Uses Blade Components instead of @include directives
    - Alpine.js state centralized in layout component
    - All CRUD methods integrated
    - Modals extracted to separate components

    Component Structure:
    - x-profile.edit-page (Main component)
      - x-profile.layout (Alpine.js state container)
        - x-profile.header (Sticky header with navigation)
        - x-profile.tabs (Tab content wrapper)
          - x-profile.tabs.profile (Profile tab)
          - x-profile.tabs.portfolio (Projects/Products/Services tabs)
        - x-profile.modals (Modal container)
          - x-modal.project
          - x-modal.product
          - x-modal.service
--}}

@section('content')
<x-profile.edit-page
    :designer="$designer"
    :projects-data="$projectsData"
    :products-data="$productsData"
    :services-data="$servicesData"
    :marketplace-data="$marketplaceData ?? []"
    :certifications-data="$certificationsData ?? []"
/>

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
@endsection
