@extends('layout.main')

@section('title', __('Marketplace') . ' | ' . config('app.name'))

@section('content')
@php
    $heroImages = \App\Models\SiteSetting::getHeroImages('marketplace');
@endphp
<div class="min-h-screen bg-gray-50">
    {{-- Hero Section with Carousel --}}
    <section class="relative h-64 overflow-hidden {{ empty($heroImages) ? 'bg-gradient-to-r from-blue-600 to-purple-600' : '' }}"
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
                    <img :src="image" alt="Marketplace" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000" :class="currentIndex === index ? 'opacity-100' : 'opacity-0'">
                </template>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600/80 to-purple-600/80"></div>
            </div>
        @else
            <div class="absolute inset-0 bg-black/30"></div>
        @endif
        <div class="relative z-10 container mx-auto px-4 h-full flex items-center justify-center text-center">
            <div>
                <h1 class="text-2xl sm:text-4xl md:text-5xl font-bold text-white mb-3 sm:mb-4">{{ \App\Models\SiteSetting::getHeroTitle('marketplace', 'Marketplace') }}</h1>
                <p class="text-sm sm:text-lg text-white/90 max-w-2xl mx-auto">
                    {{ \App\Models\SiteSetting::getHeroSubtitle('marketplace', 'Discover services, collaborations, showcases and opportunities from the design community') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Filter Bar --}}
    <div class="sticky top-0 z-40 bg-white border-b shadow-sm" x-data="{ filtersOpen: false }">
        <div class="container mx-auto px-4">
            <form method="GET" action="{{ route('marketplace.index', ['locale' => app()->getLocale()]) }}" class="py-4">
                {{-- Search + Filter Toggle (always visible) --}}
                <div class="flex gap-2">
                    <div class="flex-1 relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ __('Search marketplace...') }}"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="button" @click="filtersOpen = !filtersOpen" class="lg:hidden px-3 py-2.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span class="text-sm font-medium">{{ __('Filters') }}</span>
                    </button>
                </div>

                {{-- Collapsible Filters --}}
                <div x-show="filtersOpen" x-collapse class="lg:!block mt-4 lg:mt-0" :class="{ 'hidden': !filtersOpen }">
                <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center">
                    {{-- Category Filter --}}
                    <div class="w-full lg:w-auto">
                        <select
                            name="category"
                            class="w-full lg:w-48 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Type Filter --}}
                    <div class="w-full lg:w-auto">
                        <select
                            name="type"
                            class="w-full lg:w-48 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="">{{ __('All Types') }}</option>
                            @foreach(\App\Helpers\DropdownHelper::marketplaceTypes() as $mtype)
                                <option value="{{ $mtype['value'] }}" {{ request('type') === $mtype['value'] ? 'selected' : '' }}>{{ __($mtype['label']) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sort Filter --}}
                    <div class="w-full lg:w-auto">
                        <select
                            name="sort"
                            class="w-full lg:w-48 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="recent" {{ request('sort') === 'recent' || !request('sort') ? 'selected' : '' }}>{{ __('Most Recent') }}</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>{{ __('Most Popular') }}</option>
                            <option value="views" {{ request('sort') === 'views' ? 'selected' : '' }}>{{ __('Most Viewed') }}</option>
                            <option value="comments" {{ request('sort') === 'comments' ? 'selected' : '' }}>{{ __('Most Discussed') }}</option>
                        </select>
                    </div>

                    {{-- View Mode Toggle --}}
                    <div class="flex items-center gap-2 bg-gray-100 rounded-lg p-1">
                        <button
                            type="button"
                            onclick="setViewMode('grid')"
                            id="gridViewBtn"
                            class="p-2 rounded transition-colors view-mode-btn active"
                            title="{{ __('Grid View') }}"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M10 3H3v7h7V3zm11 0h-7v7h7V3zM10 14H3v7h7v-7zm11 0h-7v7h7v-7z"/>
                            </svg>
                        </button>
                        <button
                            type="button"
                            onclick="setViewMode('list')"
                            id="listViewBtn"
                            class="p-2 rounded transition-colors view-mode-btn"
                            title="{{ __('List View') }}"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Notification Subscription Button --}}
                    <x-category-subscribe-button contentType="marketplace" />
                </div>
                </div>

                {{-- Active Tags Display --}}
                @if(request('tags'))
                    @php
                        $activeTags = array_filter(explode(',', request('tags')));
                    @endphp
                    @if(!empty($activeTags))
                        <div class="flex flex-wrap gap-2 mt-4">
                            <span class="text-sm text-gray-600">{{ __('Active filters:') }}</span>
                            @foreach($activeTags as $tag)
                                <a
                                    href="{{ route('marketplace.index', array_merge(['locale' => app()->getLocale()], request()->except('tags'), ['tags' => implode(',', array_diff($activeTags, [$tag]))])) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm hover:bg-blue-200 transition-colors"
                                >
                                    {{ $tag }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endforeach
                            <a
                                href="{{ route('marketplace.index', array_merge(['locale' => app()->getLocale()], request()->except('tags'))) }}"
                                class="text-sm text-gray-500 hover:text-gray-700 underline"
                            >
                                {{ __('Clear all') }}
                            </a>
                        </div>
                    @endif
                @endif

                {{-- Tag Filter Dropdown --}}
                @if($allTags->isNotEmpty())
                    <div class="mt-4">
                        <details class="group">
                            <summary class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">{{ __('Filter by tags') }}</span>
                                <svg class="w-4 h-4 text-gray-500 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </summary>
                            <div class="mt-3 p-4 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                    @foreach($allTags as $tag)
                                        @php
                                            $currentTags = request('tags') ? explode(',', request('tags')) : [];
                                            $isActive = in_array($tag, $currentTags);
                                            $newTags = $isActive
                                                ? implode(',', array_diff($currentTags, [$tag]))
                                                : implode(',', array_merge($currentTags, [$tag]));
                                        @endphp
                                        <a
                                            href="{{ route('marketplace.index', array_merge(['locale' => app()->getLocale()], request()->except('tags'), $newTags ? ['tags' => $newTags] : [])) }}"
                                            class="inline-flex items-center justify-between px-3 py-2 rounded-md text-sm transition-colors {{ $isActive ? 'bg-blue-100 text-blue-700 font-medium' : 'bg-gray-50 text-gray-700 hover:bg-gray-100' }}"
                                        >
                                            <span>{{ $tag }}</span>
                                            @if($isActive)
                                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                                </svg>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </details>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Posts Grid/List --}}
    <div class="container mx-auto px-4 py-8">
        @if($posts->count() > 0)
            {{-- Grid View --}}
            <div id="gridView" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 auto-rows-fr">
                @foreach($posts as $index => $post)
                    <x-home.marketplace-card
                        :post="$post"
                        :eager="$index < 8"
                        view-mode="grid"
                    />
                @endforeach
            </div>

            {{-- List View --}}
            <div id="listView" class="hidden space-y-6">
                @foreach($posts as $index => $post)
                    <x-home.marketplace-card
                        :post="$post"
                        :eager="$index < 6"
                        view-mode="list"
                    />
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-16">
                <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-4 text-xl font-semibold text-gray-900">{{ __('No posts found') }}</h3>
                <p class="mt-2 text-gray-600">{{ __('Try adjusting your filters or search query.') }}</p>
                <a
                    href="{{ route('marketplace.index', ['locale' => app()->getLocale()]) }}"
                    class="mt-6 inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    {{ __('Clear all filters') }}
                </a>
            </div>
        @endif
    </div>
</div>

@section('footer_js')
<script>
    // View mode toggle
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');

    // Load saved view mode
    const savedViewMode = localStorage.getItem('marketplaceViewMode') || 'grid';
    setViewMode(savedViewMode);

    function setViewMode(mode) {
        if (mode === 'grid') {
            gridView.classList.remove('hidden');
            listView.classList.add('hidden');
            gridBtn.classList.add('active', 'bg-white', 'text-blue-600', 'shadow-sm');
            gridBtn.classList.remove('text-gray-600');
            listBtn.classList.remove('active', 'bg-white', 'text-blue-600', 'shadow-sm');
            listBtn.classList.add('text-gray-600');
        } else {
            gridView.classList.add('hidden');
            listView.classList.remove('hidden');
            listBtn.classList.add('active', 'bg-white', 'text-blue-600', 'shadow-sm');
            listBtn.classList.remove('text-gray-600');
            gridBtn.classList.remove('active', 'bg-white', 'text-blue-600', 'shadow-sm');
            gridBtn.classList.add('text-gray-600');
        }

        // Save to localStorage
        localStorage.setItem('marketplaceViewMode', mode);
    }
</script>
@endsection
@endsection
