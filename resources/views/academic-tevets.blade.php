@extends('layout.main')

@section('head')
<title>{{ __('Academic & Workplace Learning Centers') }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ __('Discover academic institutions, TVETs, and workplace learning centers across Palestine. Connect with universities, colleges, and workplace learning enterprises.') }}">
<meta name="keywords" content="academic, TVET, workplace learning centers, universities, colleges, Palestine, education">
@endsection

@section('content')
{{-- Hero Section with Carousel --}}
@php
    $heroImages = \App\Models\SiteSetting::getHeroImages('academic_tevets');
@endphp
<section class="relative py-12 md:py-16 lg:py-20 overflow-hidden {{ empty($heroImages) ? 'bg-gradient-to-r from-purple-600 to-blue-500' : '' }}"
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
                <img :src="image" alt="Academic & Workplace Learning Centers" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000" :class="currentIndex === index ? 'opacity-100' : 'opacity-0'">
            </template>
            <div class="absolute inset-0 bg-gradient-to-r from-purple-600/80 to-blue-500/80"></div>
        </div>
    @endif
    <div class="relative z-10 max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="text-center md:text-left text-white">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-snug mb-3 sm:mb-4">{{ \App\Models\SiteSetting::getHeroTitle('academic_tevets', 'Academic & Workplace Learning Centers') }}</h1>
            <p class="text-sm sm:text-lg md:text-xl text-white/90 max-w-2xl">
                {{ \App\Models\SiteSetting::getHeroSubtitle('academic_tevets', 'Discover academic institutions, TVETs, and workplace learning centers across Palestine. Connect with universities, colleges, and workplace learning enterprises.') }}
            </p>

            {{-- Stats --}}
            <div class="flex flex-wrap gap-4 mt-8 justify-center md:justify-start">
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                    </svg>
                    <span>{{ $totalAcademic }} {{ __('Academic Institutions') }}</span>
                </div>
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>{{ $totalTevets }} {{ __('TVETs') }}</span>
                </div>
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>{{ $totalPrivateSectors }} {{ __('Workplace Learning Centers') }}</span>
                </div>
                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ $cities->count() }} {{ __('Cities') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Filter and Search Section --}}
<section class="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4">
        <form method="GET" action="{{ route('academic-tevets', ['locale' => app()->getLocale()]) }}" class="flex flex-col md:flex-row gap-4">
            {{-- Search --}}
            <div class="flex-1">
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search by name, location...') }}"
                        class="w-full px-4 py-2.5 pl-10 rtl:pr-10 rtl:pl-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    >
                    <svg class="absolute left-3 rtl:right-3 rtl:left-auto top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- City Filter --}}
            <div class="md:w-48">
                <select
                    name="city"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
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
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    onchange="this.form.submit()"
                >
                    <option value="all" {{ request('type') == 'all' || !request('type') ? 'selected' : '' }}>{{ __('All Types') }}</option>
                    <option value="academic" {{ request('type') == 'academic' ? 'selected' : '' }}>{{ __('Academic Institutions') }}</option>
                    <option value="tvet" {{ request('type') == 'tvet' ? 'selected' : '' }}>{{ __('TVETs') }}</option>
                    <option value="private_sector" {{ request('type') == 'private_sector' ? 'selected' : '' }}>{{ __('Workplace Learning Centers') }}</option>
                </select>
            </div>

            {{-- Search Button --}}
            <button
                type="submit"
                class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-blue-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all"
            >
                {{ __('Search') }}
            </button>
        </form>
    </div>
</section>

{{-- Content Section --}}
<section class="py-12 md:py-16 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">

        @php
            $hasAcademic = is_countable($academicInstitutions) ? count($academicInstitutions) > 0 : $academicInstitutions->count() > 0;
            $hasTevets = is_countable($tevetInstitutions) ? count($tevetInstitutions) > 0 : $tevetInstitutions->count() > 0;
            $hasPrivateSectors = is_countable($privateSectors) ? count($privateSectors) > 0 : $privateSectors->count() > 0;
        @endphp

        @if($hasAcademic || $hasTevets || $hasPrivateSectors)

            {{-- Academic Institutions Section --}}
            @if($hasAcademic)
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ __('Academic Institutions') }}</h2>
                    <span class="text-sm text-gray-500">({{ is_countable($academicInstitutions) ? count($academicInstitutions) : $academicInstitutions->count() }})</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($academicInstitutions as $institution)
                        <a href="{{ route('academic-institution.show', ['locale' => app()->getLocale(), 'id' => $institution->id]) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group block">
                            {{-- Logo/Header with Banner --}}
                            <div class="h-32 bg-gradient-to-br from-purple-500 to-blue-500 relative flex items-center justify-center overflow-hidden">
                                @if($institution->banner)
                                    <img src="{{ $institution->banner_url }}" alt="{{ $institution->name }}" class="absolute inset-0 w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-br from-purple-900/60 to-blue-900/60"></div>
                                @endif
                                <div class="relative z-10">
                                    @if($institution->logo)
                                        <img src="{{ $institution->logo_url }}" alt="{{ $institution->name }}" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                                    @else
                                        <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center shadow-lg">
                                            <span class="text-3xl font-bold text-purple-600">{{ strtoupper(substr($institution->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                {{-- Institution Type Badge --}}
                                <span class="absolute top-3 right-3 px-2 py-1 text-xs font-semibold rounded-full z-10
                                    @if($institution->institution_type === 'university') bg-blue-100 text-blue-700
                                    @elseif($institution->institution_type === 'college') bg-green-100 text-green-700
                                    @else bg-gray-100 text-gray-700
                                    @endif
                                ">
                                    {{ $institution->institution_type_label }}
                                </span>
                            </div>

                            {{-- Content --}}
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-purple-600 transition-colors">
                                    {{ $institution->name }}
                                </h3>

                                @if($institution->city)
                                <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ $institution->city }}</span>
                                </div>
                                @endif

                                @if($institution->description)
                                <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                    {{ Str::limit($institution->description, 100) }}
                                </p>
                                @endif

                                {{-- View Profile Button --}}
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <span class="block w-full text-center px-3 py-2 text-sm font-medium text-purple-600 hover:bg-purple-50 rounded-lg transition-colors">
                                        {{ __('View Details') }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($academicInstitutions instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-8">
                        {{ $academicInstitutions->links() }}
                    </div>
                @endif
            </div>
            @endif

            {{-- TVETs Section --}}
            @if($hasTevets)
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ __('TVETs') }}</h2>
                    <span class="text-sm text-gray-500">({{ is_countable($tevetInstitutions) ? count($tevetInstitutions) : $tevetInstitutions->count() }})</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($tevetInstitutions as $institution)
                        <a href="{{ route('academic-institution.show', ['locale' => app()->getLocale(), 'id' => $institution->id]) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group block">
                            {{-- Logo/Header with Banner --}}
                            <div class="h-32 bg-gradient-to-br from-orange-500 to-yellow-500 relative flex items-center justify-center overflow-hidden">
                                @if($institution->banner)
                                    <img src="{{ $institution->banner_url }}" alt="{{ $institution->name }}" class="absolute inset-0 w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-br from-orange-900/60 to-yellow-900/60"></div>
                                @endif
                                <div class="relative z-10">
                                    @if($institution->logo)
                                        <img src="{{ $institution->logo_url }}" alt="{{ $institution->name }}" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                                    @else
                                        <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center shadow-lg">
                                            <span class="text-3xl font-bold text-orange-600">{{ strtoupper(substr($institution->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                {{-- TVET Badge --}}
                                <span class="absolute top-3 right-3 px-2 py-1 text-xs font-semibold rounded-full z-10 bg-purple-100 text-purple-700">
                                    TVET
                                </span>
                            </div>

                            {{-- Content --}}
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-orange-600 transition-colors">
                                    {{ $institution->name }}
                                </h3>

                                @if($institution->city)
                                <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ $institution->city }}</span>
                                </div>
                                @endif

                                @if($institution->description)
                                <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                    {{ Str::limit($institution->description, 100) }}
                                </p>
                                @endif

                                {{-- View Details Button --}}
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <span class="block w-full text-center px-3 py-2 text-sm font-medium text-orange-600 hover:bg-orange-50 rounded-lg transition-colors">
                                        {{ __('View Details') }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($tevetInstitutions instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-8">
                        {{ $tevetInstitutions->links() }}
                    </div>
                @endif
            </div>
            @endif

            {{-- Workplace Learning Centers Section --}}
            @if($hasPrivateSectors)
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ __('Workplace Learning Centers') }}</h2>
                    <span class="text-sm text-gray-500">({{ is_countable($privateSectors) ? count($privateSectors) : $privateSectors->count() }})</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($privateSectors as $privateSector)
                        <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $privateSector->id]) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group block">
                            {{-- Logo/Header --}}
                            <div class="h-32 bg-gradient-to-br from-blue-500 to-green-500 relative flex items-center justify-center">
                                @if($privateSector->avatar)
                                    <img src="{{ asset('storage/' . $privateSector->avatar) }}" alt="{{ $privateSector->name }}" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                                @else
                                    <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center shadow-lg">
                                        <span class="text-3xl font-bold text-blue-600">{{ strtoupper(substr($privateSector->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                {{-- Sector Badge --}}
                                <span class="absolute top-3 right-3 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                    {{ __(ucfirst($privateSector->sector)) }}
                                </span>
                            </div>

                            {{-- Content --}}
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 mb-1 group-hover:text-blue-600 transition-colors">
                                    {{ $privateSector->company_name ?: $privateSector->name }}
                                </h3>

                                @if($privateSector->company_name && $privateSector->name !== $privateSector->company_name)
                                <p class="text-sm text-gray-500 mb-2">{{ $privateSector->name }}</p>
                                @endif

                                @if($privateSector->city)
                                <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ $privateSector->city }}</span>
                                </div>
                                @endif

                                @if($privateSector->bio)
                                <p class="text-sm text-gray-600 line-clamp-2">
                                    {{ Str::limit($privateSector->bio, 100) }}
                                </p>
                                @endif

                                {{-- View Profile Button --}}
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <span class="block w-full text-center px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        {{ __('View Profile') }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($privateSectors instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-8">
                        {{ $privateSectors->links() }}
                    </div>
                @endif
            </div>
            @endif

        @else
            {{-- Empty State --}}
            <div class="text-center py-20">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ __('No Results Found') }}</h3>
                <p class="text-gray-600 mb-6">{{ __('Try adjusting your search or filters') }}</p>
                <a href="{{ route('academic-tevets', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                    {{ __('Clear Filters') }}
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
