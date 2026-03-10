@extends('layout.main')

@section('head')
<title>{{ __('Fab Labs') }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ __('Discover fabrication laboratories across Palestine. Access cutting-edge equipment, learn new skills, and bring your ideas to life.') }}">
<meta name="keywords" content="fab labs, fabrication, maker space, Palestine, 3D printing, laser cutting, innovation">
@endsection

@section('content')
{{-- Hero Section with Carousel --}}
@php
    $heroImages = \App\Models\SiteSetting::getHeroImages('fab_labs');
@endphp
<section class="relative py-12 md:py-16 lg:py-20 overflow-hidden {{ empty($heroImages) ? 'bg-gradient-to-r from-blue-600 to-green-500' : '' }}"
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
                <img :src="image" alt="Fab Labs" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000" :class="currentIndex === index ? 'opacity-100' : 'opacity-0'">
            </template>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/80 to-green-500/80"></div>
        </div>
    @endif
    <div class="relative z-10 max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="text-center md:text-left text-white">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-snug mb-3 sm:mb-4">{{ \App\Models\SiteSetting::getHeroTitle('fab_labs', 'Fab Labs & Incubators') }}</h1>
            <p class="text-sm sm:text-lg md:text-xl text-white/90 max-w-2xl">
                {{ \App\Models\SiteSetting::getHeroSubtitle('fab_labs', 'Discover fabrication laboratories across Palestine. Access cutting-edge equipment, learn new skills, and bring your ideas to life.') }}
            </p>

            {{-- Stats --}}
            <div class="flex flex-wrap gap-4 mt-8 justify-center md:justify-start">
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <span>{{ $fabLabs->total() }} {{ __('Fab Labs') }}</span>
                </div>
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ $cities->count() }} {{ __('Cities') }}</span>
                </div>
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>{{ number_format($fabLabs->sum('members')) }}+ {{ __('Members') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Filter and Search Section --}}
<section class="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm" x-data="{ filtersOpen: false }">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4">
        <form method="GET" action="{{ route('fab-labs', ['locale' => app()->getLocale()]) }}" class="flex flex-col gap-4">
            {{-- Search + Filter Toggle (always visible) --}}
            <div class="flex gap-2">
                <div class="flex-1 relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search fab labs by name, location, or services...') }}"
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
                    {{-- City Filter --}}
                    <div class="md:w-48">
                        <select
                            name="city"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()"
                        >
                            <option value="All Cities">{{ __('All Cities') }}</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                    {{ $city }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Type Filter --}}
                    <div class="md:w-48">
                        <select
                            name="type"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()"
                        >
                            <option value="All Types">{{ __('All Types') }}</option>
                            <option value="university" {{ request('type') == 'university' ? 'selected' : '' }}>{{ __('University') }}</option>
                            <option value="community" {{ request('type') == 'community' ? 'selected' : '' }}>{{ __('Community') }}</option>
                            <option value="private" {{ request('type') == 'private' ? 'selected' : '' }}>{{ __('Private') }}</option>
                            <option value="government" {{ request('type') == 'government' ? 'selected' : '' }}>{{ __('Government') }}</option>
                        </select>
                    </div>

                    {{-- Sort --}}
                    <div class="md:w-48">
                        <select
                            name="sort"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()"
                        >
                            <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>{{ __('Highest Rated') }}</option>
                            <option value="members" {{ request('sort') == 'members' ? 'selected' : '' }}>{{ __('Most Members') }}</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>{{ __('Name (A-Z)') }}</option>
                        </select>
                    </div>

                    {{-- Search Button (mobile) --}}
                    <button type="submit" class="md:hidden px-6 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                        {{ __('Search') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

{{-- Fab Labs Grid --}}
<section class="py-12 md:py-16 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        @if($fabLabs->count() > 0)
            <div class="mb-6">
                <p class="text-gray-600">
                    {{ $fabLabs->total() }} {{ $fabLabs->total() === 1 ? __('lab') : __('labs') }} {{ __('found') }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12 items-stretch">
                @foreach($fabLabs as $fabLab)
                    <x-home.fab-lab-card :fabLab="$fabLab" />
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $fabLabs->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-20">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ __('No Fab Labs Found') }}</h3>
                <p class="text-gray-600 mb-6">{{ __('Try adjusting your search or filters') }}</p>
                <a href="{{ route('fab-labs', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                    {{ __('Clear Filters') }}
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
