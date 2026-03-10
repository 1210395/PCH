@extends('layout.main')

@section('head')
<title>{{ __('Services') }} - {{ config('app.name') }}</title>
<meta name="description" content="Browse and discover professional services from talented designers and creative professionals in Palestine.">
<meta name="keywords" content="services, design services, creative services, freelance, Palestine">
@endsection

@section('content')
{{-- Hero Section with Carousel --}}
@php
    $heroImages = \App\Models\SiteSetting::getHeroImages('services');
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
                <img :src="image" alt="Services" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000" :class="currentIndex === index ? 'opacity-100' : 'opacity-0'">
            </template>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/80 to-green-500/80"></div>
        </div>
    @endif
    <div class="relative z-10 max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="text-center text-white">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-snug mb-3 sm:mb-4">{{ \App\Models\SiteSetting::getHeroTitle('services', 'Professional Services') }}</h1>
            <p class="text-sm sm:text-lg md:text-xl text-white/90 max-w-2xl mx-auto">{{ \App\Models\SiteSetting::getHeroSubtitle('services', 'Connect with skilled professionals offering design, consulting, and creative services') }}</p>
        </div>
    </div>
</section>

{{-- Filter and Search Section --}}
<section class="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm" x-data="{ filtersOpen: false }">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4">
        <form method="GET" action="{{ route('services', ['locale' => app()->getLocale()]) }}" class="flex flex-col gap-4">
            {{-- Search + Filter Toggle (always visible) --}}
            <div class="flex gap-2">
                <div class="flex-1 relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search services...') }}"
                        class="w-full px-4 py-2.5 pl-10 rtl:pr-10 rtl:pl-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <svg class="absolute left-3 rtl:right-3 rtl:left-auto top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div x-show="filtersOpen" x-collapse class="md:!block" :class="{ 'hidden': !filtersOpen }">
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
                            <option value="most_requested" {{ request('sort') == 'most_requested' ? 'selected' : '' }}>{{ __('Most Requested') }}</option>
                        </select>
                    </div>

                    {{-- Search Button (mobile) --}}
                    <button type="submit" class="md:hidden px-6 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                        {{ __('Search') }}
                    </button>

                    {{-- Notification Subscription Button --}}
                    <x-category-subscribe-button contentType="service" />
                </div>
            </div>
        </form>
    </div>
</section>

{{-- Services Grid --}}
<section class="py-12 md:py-16 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        @if($services->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12">
                @foreach($services as $service)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300 group">
                        {{-- Service Icon/Header --}}
                        <div class="h-32 bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Service Content --}}
                        <div class="p-5">
                            {{-- Category Badge --}}
                            @if($service->category)
                                <span class="inline-block px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full mb-3">
                                    {{ $service->category }}
                                </span>
                            @endif

                            {{-- Service Name --}}
                            <h3 class="font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                <a href="{{ route('services.show', ['locale' => app()->getLocale(), 'id' => $service->id]) }}">
                                    {{ $service->name }}
                                </a>
                            </h3>

                            {{-- Description --}}
                            <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                {{ Str::limit($service->description, 100) }}
                            </p>

                            {{-- Designer Info --}}
                            @if($service->designer)
                                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                                    <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                                        @if($service->designer->profile_image)
                                            <img src="{{ asset('storage/' . $service->designer->profile_image) }}" alt="{{ $service->designer->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white text-xs font-bold">
                                                {{ strtoupper(substr($service->designer->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $service->designer->name }}</p>
                                        @if($service->designer->sub_sector)
                                            <p class="text-xs text-gray-500 truncate">{{ $service->designer->sub_sector }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- View Button --}}
                            <a href="{{ route('services.show', ['locale' => app()->getLocale(), 'id' => $service->id]) }}"
                               class="mt-4 block w-full text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gradient-to-r hover:from-blue-600 hover:to-green-500 hover:text-white transition-all text-sm font-medium">
                                {{ __('View Details') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $services->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-20">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ __('No Services Found') }}</h3>
                <p class="text-gray-600 mb-6">{{ __('Try adjusting your search or filters') }}</p>
                <a href="{{ route('services', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                    {{ __('Clear Filters') }}
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
