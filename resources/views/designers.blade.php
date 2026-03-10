@extends('layout.main')

@section('head')
<title>{{ !empty($sector) && $sector === 'showroom' ? __('Showrooms') : (!empty($sector) && $sector === 'manufacturer' ? __('Manufacturers') : (!empty($sector) && $sector === 'vendor' ? __('Vendors') : ($type === 'manufacturers' ? __('Manufacturers, Showrooms & Vendors') : ($type === 'designers' ? __('Designers') : __('All Members'))))) }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ __('Browse talented designers, manufacturers, showrooms, and vendors. Connect with creative professionals and discover amazing work.') }}">
@endsection

@section('content')
{{-- Hero Section with Carousel --}}
@php
    $heroImages = \App\Models\SiteSetting::getHeroImages('designers');
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
                <img :src="image" alt="Designers" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000" :class="currentIndex === index ? 'opacity-100' : 'opacity-0'">
            </template>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/80 to-green-500/80"></div>
        </div>
    @endif
    <div class="relative z-10 max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="text-center text-white">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-snug mb-3 sm:mb-4">
                @if(!empty($sector) && $sector === 'showroom')
                    {{ __('Showrooms') }}
                @elseif(!empty($sector) && $sector === 'manufacturer')
                    {{ __('Manufacturers') }}
                @elseif(!empty($sector) && $sector === 'vendor')
                    {{ __('Vendors') }}
                @elseif($type === 'manufacturers')
                    {{ __('Manufacturers, Showrooms & Vendors') }}
                @elseif($type === 'designers')
                    {{ __('Designers') }}
                @else
                    {{ \App\Models\SiteSetting::getHeroTitle('designers', 'Discover Creative Talent') }}
                @endif
            </h1>
            <p class="text-sm sm:text-lg md:text-xl text-white/90 max-w-2xl mx-auto">
                @if(!empty($sector) && $sector === 'showroom')
                    {{ __('Discover quality products from trusted showrooms') }}
                @elseif(!empty($sector) && $sector === 'manufacturer')
                    {{ __('Discover quality products from trusted manufacturers') }}
                @elseif(!empty($sector) && $sector === 'vendor')
                    {{ __('Discover quality products from trusted vendors') }}
                @elseif($type === 'manufacturers')
                    {{ __('Discover quality products from trusted manufacturers, showrooms, and vendors') }}
                @elseif($type === 'designers')
                    {{ __('Connect with talented creatives and artists') }}
                @else
                    {{ \App\Models\SiteSetting::getHeroSubtitle('designers', 'Browse talented designers, manufacturers, showrooms, and vendors. Connect with creative professionals and discover amazing work.') }}
                @endif
            </p>
        </div>
    </div>
</section>

<div class="min-h-screen bg-gray-50">
    <!-- Filter Section -->
    <div class="bg-white border-b border-gray-200" x-data="{ filtersOpen: false }">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4 sm:py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Search Form -->
                <form action="{{ route('designers', ['locale' => app()->getLocale()]) }}" method="GET" class="flex gap-2">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <div class="relative flex-1">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search...') }}"
                               class="pl-10 pr-4 py-2.5 w-full sm:w-64 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="button" @click="filtersOpen = !filtersOpen" class="md:hidden px-3 py-2.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span class="text-sm font-medium">{{ __('Filters') }}</span>
                    </button>
                    <button type="submit" class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-medium rounded-lg hover:from-blue-700 hover:to-green-600 transition-all">
                        {{ __('Search') }}
                    </button>
                </form>
            </div>

            <!-- Collapsible Filter Tabs & Sort -->
            <div x-show="filtersOpen" x-collapse class="md:!block" :class="{ 'hidden': !filtersOpen }">
                <!-- Filter Tabs -->
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ route('designers', ['locale' => app()->getLocale(), 'type' => 'all', 'sort' => $sort, 'search' => request('search')]) }}"
                       class="px-4 py-2 rounded-full font-medium transition-all {{ $type === 'all' ? 'bg-gradient-to-r from-blue-600 to-green-500 text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-gray-400 hover:bg-gray-50' }}">
                        {{ __('All') }}
                        <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs {{ $type === 'all' ? 'bg-white/20' : 'bg-gray-100' }}">{{ $allCount }}</span>
                    </a>
                    <a href="{{ route('designers', ['locale' => app()->getLocale(), 'type' => 'designers', 'sort' => $sort, 'search' => request('search')]) }}"
                       class="px-4 py-2 rounded-full font-medium transition-all {{ $type === 'designers' ? 'bg-gradient-to-r from-blue-600 to-green-500 text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-gray-400 hover:bg-gray-50' }}">
                        {{ __('Designers') }}
                        <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs {{ $type === 'designers' ? 'bg-white/20' : 'bg-gray-100' }}">{{ $designersCount }}</span>
                    </a>
                    <a href="{{ route('designers', ['locale' => app()->getLocale(), 'type' => 'manufacturers', 'sort' => $sort, 'search' => request('search')]) }}"
                       class="px-4 py-2 rounded-full font-medium transition-all {{ $type === 'manufacturers' && empty($sector) ? 'bg-gradient-to-r from-blue-600 to-green-500 text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-gray-400 hover:bg-gray-50' }}">
                        {{ __('Manufacturers, Showrooms & Vendors') }}
                        <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs {{ $type === 'manufacturers' && empty($sector) ? 'bg-white/20' : 'bg-gray-100' }}">{{ $manufacturersCount }}</span>
                    </a>
                    <a href="{{ route('designers', ['locale' => app()->getLocale(), 'sector' => 'manufacturer', 'sort' => $sort, 'search' => request('search')]) }}"
                       class="px-4 py-2 rounded-full font-medium transition-all {{ !empty($sector) && $sector === 'manufacturer' ? 'bg-gradient-to-r from-blue-600 to-green-500 text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-gray-400 hover:bg-gray-50' }}">
                        {{ __('Manufacturers') }}
                    </a>
                    <a href="{{ route('designers', ['locale' => app()->getLocale(), 'sector' => 'showroom', 'sort' => $sort, 'search' => request('search')]) }}"
                       class="px-4 py-2 rounded-full font-medium transition-all {{ !empty($sector) && $sector === 'showroom' ? 'bg-gradient-to-r from-blue-600 to-green-500 text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-gray-400 hover:bg-gray-50' }}">
                        {{ __('Showrooms') }}
                    </a>
                    <a href="{{ route('designers', ['locale' => app()->getLocale(), 'sector' => 'vendor', 'sort' => $sort, 'search' => request('search')]) }}"
                       class="px-4 py-2 rounded-full font-medium transition-all {{ !empty($sector) && $sector === 'vendor' ? 'bg-gradient-to-r from-blue-600 to-green-500 text-white shadow-lg' : 'bg-white text-gray-700 border border-gray-300 hover:border-gray-400 hover:bg-gray-50' }}">
                        {{ __('Vendors') }}
                    </a>
                </div>

                <!-- Sort Options -->
                <div class="mt-4 flex items-center gap-2">
                    <span class="text-sm text-gray-500">{{ __('Sort by:') }}</span>
                    <a href="{{ route('designers', ['locale' => app()->getLocale(), 'type' => $type, 'sort' => 'popular', 'search' => request('search')]) }}"
                       class="px-3 py-1.5 text-sm rounded-lg transition-all {{ $sort === 'popular' ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        {{ __('Most Popular') }}
                    </a>
                    <a href="{{ route('designers', ['locale' => app()->getLocale(), 'type' => $type, 'sort' => 'newest', 'search' => request('search')]) }}"
                       class="px-3 py-1.5 text-sm rounded-lg transition-all {{ $sort === 'newest' ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        {{ __('Newest') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-6 sm:py-8">
        @if($designers->count() > 0)
            <!-- Results Count -->
            <p class="text-gray-600 mb-6">
                {{ __('Showing') }} {{ $designers->firstItem() }}-{{ $designers->lastItem() }} {{ __('of') }} {{ $designers->total() }} {{ __('results') }}
                @if(request('search'))
                    {{ __('for') }} "<strong>{{ request('search') }}</strong>"
                @endif
            </p>

            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($designers as $designer)
                    <x-home.designer-card :designer="$designer" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $designers->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    @if(request('search'))
                        {{ __('No results found') }}
                    @else
                        {{ __('No') }} {{ !empty($sector) && $sector === 'showroom' ? __('showrooms') : (!empty($sector) && $sector === 'manufacturer' ? __('manufacturers') : (!empty($sector) && $sector === 'vendor' ? __('vendors') : ($type === 'manufacturers' ? __('manufacturers, showrooms, or vendors') : ($type === 'designers' ? __('designers') : __('members'))))) }} {{ __('yet') }}
                    @endif
                </h3>
                <p class="text-gray-600 mb-6">
                    @if(request('search'))
                        {{ __('Try adjusting your search or filter criteria') }}
                    @else
                        {{ __('Be the first to join our creative community!') }}
                    @endif
                </p>
                @if(request('search'))
                    <a href="{{ route('designers', ['locale' => app()->getLocale(), 'type' => $type]) }}"
                       class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all">
                        {{ __('Clear Search') }}
                    </a>
                @else
                    <a href="{{ route('register', ['locale' => app()->getLocale()]) }}"
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all">
                        {{ __('Join Now') }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
