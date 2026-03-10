@extends('layout.main')

@php
    // Helper function to detect if text contains Arabic characters
    $hasArabic = function($text) {
        return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text ?? '');
    };
@endphp

@section('head')
<title>{{ __('Tenders & Opportunities') }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ __('Discover the latest tender opportunities for designers, developers, and creative professionals across Palestine.') }}">
<meta name="keywords" content="tenders, opportunities, RFP, contracts, design, development, Palestine">
@endsection

@section('content')
{{-- Hero Section with Carousel --}}
@php
    $heroImages = \App\Models\SiteSetting::getHeroImages('tenders');
@endphp
<section class="relative py-16 overflow-hidden {{ empty($heroImages) ? 'bg-gradient-to-r from-blue-600 to-green-500' : '' }}"
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
                <img :src="image" alt="Tenders & Opportunities" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000" :class="currentIndex === index ? 'opacity-100' : 'opacity-0'">
            </template>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/80 to-green-500/80"></div>
        </div>
    @endif
    <div class="relative z-10 max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="max-w-2xl text-white">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-snug mb-3 sm:mb-4">{{ \App\Models\SiteSetting::getHeroTitle('tenders', 'Tenders & Opportunities') }}</h1>
            <p class="text-sm sm:text-lg text-white/90">
                {{ \App\Models\SiteSetting::getHeroSubtitle('tenders', 'Discover the latest tender opportunities for designers, developers, and creative professionals across Palestine.') }}
            </p>
        </div>
    </div>
</section>

{{-- Filter Section --}}
<section class="bg-white border-b border-gray-200">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4 sm:py-6">
        <form method="GET" action="{{ route('tenders.index', ['locale' => app()->getLocale()]) }}">
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Search --}}
                    <div class="md:col-span-2">
                        <div class="relative">
                            <svg class="absolute left-3 rtl:right-3 rtl:left-auto top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="{{ __('Search tenders...') }}"
                                class="w-full px-4 py-2.5 pl-10 rtl:pr-10 rtl:pl-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                        </div>
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <select
                            name="status"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()"
                        >
                            <option value="all">{{ __('All Status') }}</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                            <option value="closing_soon" {{ request('status') == 'closing_soon' ? 'selected' : '' }}>{{ __('Closing Soon') }}</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

{{-- Tenders List --}}
<section class="py-8 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        {{-- Results Count --}}
        <div class="mb-6">
            <p class="text-gray-600">
                {{ __('Showing') }} <span class="font-semibold">{{ $tenders->total() }}</span> {{ $tenders->total() !== 1 ? __('tenders') : __('tender') }}
            </p>
        </div>

        @if($tenders->count() > 0)
            <div class="grid grid-cols-1 gap-6 mb-8">
                @foreach($tenders as $tender)
                    @php
                        $daysLeft = $tender->days_until_deadline;
                        $statusClasses = match($tender->status) {
                            'open' => 'bg-green-100 text-green-800 border-green-200',
                            'closing_soon' => 'bg-orange-100 text-orange-800 border-orange-200',
                            'closed' => 'bg-gray-100 text-gray-800 border-gray-200',
                            default => 'bg-gray-100 text-gray-800 border-gray-200'
                        };
                        $titleDir = $hasArabic($tender->title) ? 'rtl' : 'ltr';
                        $descDir = $hasArabic($tender->description) ? 'rtl' : 'ltr';
                    @endphp
                    <a href="{{ route('tenders.show', ['locale' => app()->getLocale(), 'id' => $tender->id]) }}"
                       class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow block">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="flex-1">
                                {{-- Header --}}
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2 hover:text-blue-600 transition-colors" dir="{{ $titleDir }}" style="text-align: {{ $titleDir == 'rtl' ? 'right' : 'left' }};">
                                            {{ $tender->title }}
                                        </h3>
                                        <p class="text-gray-600 text-sm line-clamp-2 mb-3" dir="{{ $descDir }}" style="text-align: {{ $descDir == 'rtl' ? 'right' : 'left' }};">
                                            {{ $tender->short_description ?: Str::limit($tender->description, 150) }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Meta Information --}}
                                <div class="flex flex-wrap items-center gap-4 mb-4">
                                    <div class="flex items-center gap-2 text-gray-600 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <span dir="{{ $hasArabic($tender->publisher) ? 'rtl' : 'ltr' }}">{{ $tender->publisher }}</span>
                                    </div>
                                    @if($tender->published_date)
                                        <div class="flex items-center gap-2 text-gray-600 text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>{{ __('Published:') }} {{ \Carbon\Carbon::parse($tender->published_date)->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Budget --}}
                                @if($tender->budget)
                                    <div class="text-sm">
                                        <span class="text-gray-600">{{ __('Budget:') }} </span>
                                        <span class="font-semibold text-gray-900">{{ $tender->budget }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Right Side - Status and Deadline --}}
                            <div class="lg:text-right flex lg:flex-col gap-3 lg:gap-2 items-start lg:items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusClasses }}">
                                    {{ $tender->status_label }}
                                </span>
                                @if($tender->deadline)
                                    <div class="text-sm">
                                        <p class="text-gray-600 mb-1">{{ __('Deadline:') }}</p>
                                        <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($tender->deadline)->format('M d, Y') }}</p>
                                        @if($daysLeft !== null && $daysLeft > 0 && $daysLeft <= 14)
                                            <p class="text-orange-600 text-xs mt-1">
                                                {{ $daysLeft }} {{ $daysLeft !== 1 ? __('days') : __('day') }} {{ __('left') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $tenders->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-20">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ __('No Tenders Found') }}</h3>
                <p class="text-gray-600 mb-6">{{ __('Try adjusting your search or filters') }}</p>
                <a href="{{ route('tenders.index', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                    {{ __('Clear Filters') }}
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
