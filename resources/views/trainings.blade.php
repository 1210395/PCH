@extends('layout.main')

@section('head')
<title>{{ __('Training & Workshops') }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ __('Enhance your skills with professional training courses designed for creative professionals, entrepreneurs, and innovators.') }}">
<meta name="keywords" content="training, workshops, courses, design, development, marketing, Palestine">
@endsection

@section('content')
{{-- Hero Section with Carousel --}}
@php
    $heroImages = \App\Models\SiteSetting::getHeroImages('trainings');
@endphp
<section class="relative py-16 md:py-20 overflow-hidden {{ empty($heroImages) ? 'bg-gradient-to-r from-blue-600 to-green-500' : '' }}"
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
                <img :src="image" alt="Training & Workshops" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000" :class="currentIndex === index ? 'opacity-100' : 'opacity-0'">
            </template>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/80 to-green-500/80"></div>
        </div>
    @endif
    <div class="relative z-10 max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="max-w-3xl text-white">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-snug mb-3 sm:mb-4">{{ \App\Models\SiteSetting::getHeroTitle('trainings', 'Training & Workshops') }}</h1>
            <p class="text-sm sm:text-lg md:text-xl text-white/90 mb-6 sm:mb-8">
                {{ \App\Models\SiteSetting::getHeroSubtitle('trainings', 'Enhance your skills with professional training courses from academic institutions.') }}
            </p>
            <div class="flex flex-wrap gap-6 md:gap-8 text-white/90">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>{{ $trainings->total() }} {{ __('Available') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>{{ __('From Academic Institutions') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Filter Section --}}
<section class="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm" x-data="{ filtersOpen: false }">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4 sm:py-6">
        <form method="GET" action="{{ route('trainings.index', ['locale' => app()->getLocale()]) }}">
            {{-- Search + Filter Toggle (always visible) --}}
            <div class="flex gap-2">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 rtl:right-3 rtl:left-auto top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search...') }}"
                        class="w-full px-4 py-2.5 pl-10 rtl:pr-10 rtl:pl-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen" class="md:hidden px-3 py-2.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-1.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span class="text-sm font-medium">{{ __('Filters') }}</span>
                </button>
            </div>

            {{-- Collapsible Filters --}}
            <div x-show="filtersOpen" x-collapse class="md:!block mt-4 md:mt-0" :class="{ 'hidden': !filtersOpen }">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
                    {{-- Content Type Filter --}}
                    <div>
                        <select
                            name="type"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()"
                        >
                            <option value="all">{{ __('All Types') }}</option>
                            <option value="training" {{ request('type') == 'training' ? 'selected' : '' }}>{{ __('Trainings') }}</option>
                            <option value="workshop" {{ request('type') == 'workshop' ? 'selected' : '' }}>{{ __('Workshops') }}</option>
                            <option value="announcement" {{ request('type') == 'announcement' ? 'selected' : '' }}>{{ __('Announcements') }}</option>
                        </select>
                    </div>

                    {{-- Category Filter --}}
                    <div>
                        <select
                            name="category"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()"
                        >
                            <option value="all">{{ __('All Categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Level Filter --}}
                    <div>
                        <select
                            name="level"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()"
                        >
                            <option value="all">{{ __('All Levels') }}</option>
                            <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>{{ __('Beginner') }}</option>
                            <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>{{ __('Intermediate') }}</option>
                            <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>{{ __('Advanced') }}</option>
                        </select>
                    </div>

                    {{-- Filter Button --}}
                    <div>
                        <button type="submit" class="w-full px-6 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-medium rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            {{ __('Filter') }}
                        </button>
                    </div>

                    {{-- Notification Subscription Button --}}
                    <div>
                        <x-category-subscribe-button contentType="training" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

{{-- Content Grid --}}
<section class="py-12 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        {{-- Results Count --}}
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900">
                {{ $trainings->total() }} {{ $trainings->total() === 1 ? __('Item') : __('Items') }} {{ __('Available') }}
            </h2>
        </div>

        @if($trainings->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                @foreach($trainings as $item)
                    <a href="{{ route('trainings.show', ['locale' => app()->getLocale(), 'id' => $item->id, 'type' => $item->content_type]) }}"
                       class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group block">
                        {{-- Content --}}
                        <div class="p-5">
                            {{-- Badges Row --}}
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                @php
                                    $typeClasses = match($item->content_type) {
                                        'training' => 'bg-blue-100 text-blue-700',
                                        'workshop' => 'bg-purple-100 text-purple-700',
                                        'announcement' => 'bg-orange-100 text-orange-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    };
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $typeClasses }}">
                                    {{ __(ucfirst($item->content_type)) }}
                                </span>
                                @if($item->content_type === 'training' && $item->level)
                                    @php
                                        $levelClasses = match($item->level) {
                                            'beginner' => 'bg-green-100 text-green-700',
                                            'intermediate' => 'bg-yellow-100 text-yellow-700',
                                            'advanced' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $levelClasses }}">
                                        {{ __(ucfirst($item->level)) }}
                                    </span>
                                @endif
                                @if($item->location_type)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                        {{ __(ucfirst($item->location_type)) }}
                                    </span>
                                @endif
                                @if($item->content_type === 'workshop' && $item->is_expired)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-600">
                                        {{ __('Expired') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Title --}}
                            <h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                {{ $item->title }}
                            </h3>

                            {{-- Institution --}}
                            <p class="text-sm text-gray-500 mb-3">{{ __('by') }} {{ $item->institution ?? __('Institution') }}</p>

                            {{-- Short Description --}}
                            @if($item->short_description || $item->description || $item->content)
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ $item->short_description ?? Str::limit($item->description ?? $item->content ?? '', 100) }}
                            </p>
                            @endif

                            {{-- Date Info --}}
                            <div class="flex flex-wrap gap-3 text-xs text-gray-500 mb-4">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    @if($item->content_type === 'training')
                                        {{ $item->start_date ? $item->start_date->format('M d, Y') : 'TBD' }}
                                    @elseif($item->content_type === 'workshop')
                                        {{ $item->workshop_date ? $item->workshop_date->format('M d, Y') : 'TBD' }}
                                    @else
                                        {{ $item->publish_date ? $item->publish_date->format('M d, Y') : $item->created_at->format('M d, Y') }}
                                    @endif
                                </span>
                                @if($item->content_type === 'training' && $item->end_date)
                                <span class="flex items-center gap-1">
                                    {{ __('to') }} {{ $item->end_date->format('M d, Y') }}
                                </span>
                                @endif
                            </div>

                            {{-- Footer: Certificate & Price --}}
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                @if(($item->content_type === 'training' || $item->content_type === 'workshop') && $item->has_certificate)
                                    <span class="inline-flex items-center gap-1 text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                        </svg>
                                        {{ __('Certificate') }}
                                    </span>
                                @else
                                    <span></span>
                                @endif
                                @if($item->content_type !== 'announcement')
                                    <span class="font-semibold text-green-600">{{ $item->price && $item->price !== 'Free' ? $item->price : __('Free') }}</span>
                                @else
                                    <span class="text-sm text-gray-500">{{ $item->category ?? __('News') }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $trainings->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-20">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ __('No Trainings Found') }}</h3>
                <p class="text-gray-600 mb-6">{{ __('Try adjusting your search or filters') }}</p>
                <a href="{{ route('trainings.index', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                    {{ __('Clear Filters') }}
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
