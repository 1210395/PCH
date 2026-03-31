@extends('layout.main')

@section('head')
<title>{{ __('Projects') }} - {{ config('app.name') }}</title>
<meta name="description" content="Browse creative projects from talented designers in Palestine. Discover architectural designs, interior projects, and creative works.">
<meta name="keywords" content="projects, creative works, architecture, interior design, Palestine">
@endsection

@section('content')
{{-- Hero Section with Carousel --}}
@php
    $heroImages = \App\Models\SiteSetting::getHeroImages('projects');
@endphp
<section class="relative py-12 md:py-16 lg:py-20 overflow-hidden {{ empty($heroImages) ? 'bg-gradient-to-br from-blue-600 to-green-500' : '' }}"
    @if(!empty($heroImages))
    x-data="{
        images: @js($heroImages),
        currentIndex: 0,
        interval: null,
        init() {
            if (this.images.length > 1) {
                this.interval = setInterval(() => {
                    this.currentIndex = (this.currentIndex + 1) % this.images.length;
                }, 5000);
            }
        }
    }"
    x-init="init()"
    @endif
>
    @if(!empty($heroImages))
        <div class="absolute inset-0 z-0">
            <template x-for="(image, index) in images" :key="index">
                <img :src="image" alt="Projects" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000" :class="currentIndex === index ? 'opacity-100' : 'opacity-0'">
            </template>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/80 to-green-500/80"></div>
        </div>
    @endif
    <div class="relative z-10 max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="text-center text-white">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-snug mb-3 sm:mb-4">{{ \App\Models\SiteSetting::getHeroTitle('projects', 'Explore Creative Projects') }}</h1>
            <p class="text-sm sm:text-lg md:text-xl text-white/90 max-w-2xl mx-auto">{{ \App\Models\SiteSetting::getHeroSubtitle('projects', 'Discover inspiring work from talented designers and creative professionals') }}</p>
        </div>
    </div>
</section>

{{-- Filter and Search Section --}}
<section class="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm" x-data="{ filtersOpen: false }">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4">
        <form method="GET" action="{{ route('projects', ['locale' => app()->getLocale()]) }}" class="flex flex-col gap-4">
            {{-- Search + Filter Toggle (always visible) --}}
            <div class="flex gap-2">
                <div class="flex-1 relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search projects...') }}"
                        class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <svg class="search-icon-fixed absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="md:hidden px-3 py-2.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span class="text-sm font-medium">{{ __('Filters') }}</span>
                </button>
                <button type="submit" class="hidden md:block px-6 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                    {{ __('Search') }}
                </button>
            </div>

            {{-- Collapsible Filters --}}
            <div class="filter-panel hidden md:block" :class="{ '!block': filtersOpen }">
                <div class="flex flex-col md:flex-row gap-4">
                    {{-- Category Filter --}}
                    <div class="md:w-48">
                        <select
                            name="category"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()"
                        >
                            <option value="all">{{ __('All Categories') }}</option>
                            @if(isset($categories) && count($categories) > 0)
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Sort --}}
                    <div class="md:w-48">
                        <select
                            name="sort"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()"
                        >
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('Latest') }}</option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>{{ __('Most Popular') }}</option>
                            <option value="most_liked" {{ request('sort') == 'most_liked' ? 'selected' : '' }}>{{ __('Most Liked') }}</option>
                        </select>
                    </div>

                    {{-- Search Button (mobile) --}}
                    <button type="submit" class="md:hidden px-6 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                        {{ __('Search') }}
                    </button>

                    {{-- Notification Subscription Button --}}
                    <x-category-subscribe-button contentType="project" />
                </div>
            </div>
        </form>
    </div>
</section>

{{-- Projects Grid --}}
<section class="py-12 md:py-16 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        @if($projects->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12 auto-rows-fr">
                @foreach($projects as $project)
                    <x-home.project-card :project="$project" />
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $projects->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-20">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ __('No Projects Found') }}</h3>
                <p class="text-gray-600 mb-6">{{ __('Try adjusting your search or filters') }}</p>
                <a href="{{ route('projects', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                    {{ __('Clear Filters') }}
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
