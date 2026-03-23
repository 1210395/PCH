@extends('layout.main')

@section('head')
<title>{{ $institution->name }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ Str::limit($institution->description, 160) }}">
@endsection

@section('content')
{{-- Hero Section --}}
<section class="relative py-12 md:py-16 bg-gradient-to-r from-purple-600 to-blue-500 overflow-hidden">
    {{-- Banner Background --}}
    @if($institution->banner)
    <div class="absolute inset-0">
        <img src="{{ $institution->banner_url }}" alt="{{ $institution->name }} banner" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-900/80 to-blue-900/80"></div>
    </div>
    @endif
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 relative z-10">
        <div class="flex flex-col md:flex-row items-center gap-8">
            {{-- Logo --}}
            <div class="flex-shrink-0">
                @if($institution->logo)
                    <img src="{{ $institution->logo_url }}" alt="{{ $institution->name }}" class="w-32 h-32 rounded-2xl object-cover border-4 border-white shadow-xl">
                @else
                    <div class="w-32 h-32 rounded-2xl bg-white flex items-center justify-center shadow-xl">
                        <span class="text-5xl font-bold text-purple-600">{{ strtoupper(substr($institution->name, 0, 1)) }}</span>
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="text-center md:text-left text-white flex-1">
                <div class="flex flex-wrap items-center gap-3 justify-center md:justify-start mb-3">
                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                        @if($institution->institution_type === 'university') bg-blue-400/30 text-blue-100
                        @elseif($institution->institution_type === 'tvet') bg-purple-400/30 text-purple-100
                        @elseif($institution->institution_type === 'college') bg-green-400/30 text-green-100
                        @else bg-gray-400/30 text-gray-100
                        @endif
                    ">
                        {{ $institution->institution_type_label }}
                    </span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold mb-3">{{ $institution->name }}</h1>

                @if($institution->city || $institution->address)
                <div class="flex items-center gap-2 justify-center md:justify-start text-white/90 mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ $institution->city }}{{ $institution->address ? ', ' . $institution->address : '' }}</span>
                </div>
                @endif

                {{-- Contact Actions --}}
                <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                    @if($institution->website)
                    <a href="{{ $institution->website }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-purple-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        {{ __('Visit Website') }}
                    </a>
                    @endif
                    @if($institution->phone)
                    <a href="tel:{{ $institution->phone }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ $institution->phone }}
                    </a>
                    @endif
                    @if($institution->email)
                    <a href="mailto:{{ $institution->email }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ __('Email Us') }}
                    </a>
                    @endif
                    {{-- Share Button --}}
                    <x-share-button
                        :url="route('academic-institution.show', ['locale' => app()->getLocale(), 'id' => $institution->id])"
                        :title="$institution->name"
                        :description="Str::limit($institution->description ?? ($institution->institution_type_label . ' in ' . ($institution->city ?? 'Palestine')), 150)"
                        variant="icon-only"
                        size="md"
                        class="bg-white/20 hover:bg-white/30"
                    />
                    {{-- Subscribe Button --}}
                    <x-profile-subscribe-button
                        profileType="academic"
                        :profileId="$institution->id"
                        :profileName="$institution->name"
                    />
                </div>
            </div>

            {{-- Stats --}}
            <div class="flex gap-4 md:gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-white">{{ $trainings->count() }}</div>
                    <div class="text-white/80 text-sm">{{ __('Trainings') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-white">{{ $workshops->count() }}</div>
                    <div class="text-white/80 text-sm">{{ __('Workshops') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-white">{{ $announcements->count() }}</div>
                    <div class="text-white/80 text-sm">{{ __('News') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- About Section --}}
@if($institution->description)
<section class="py-12 bg-white border-b border-gray-100">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('About') }}</h2>
        <div class="prose prose-lg max-w-none text-gray-600">
            {!! nl2br(e($institution->description)) !!}
        </div>
    </div>
</section>
@endif

{{-- Trainings Section --}}
@if($trainings->count() > 0)
<section class="py-12 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('Trainings') }}</h2>
            <span class="text-sm text-gray-500">({{ $trainings->count() }})</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($trainings as $training)
                <a href="{{ route('trainings.show', ['locale' => app()->getLocale(), 'id' => $training->id]) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group block">
                    {{-- Content --}}
                    <div class="p-4">
                        {{-- Level Badge --}}
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            @if($training->level)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-700">
                                {{ $training->level_label ?? __(ucfirst($training->level)) }}
                            </span>
                            @endif
                            @if($training->location_type)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                {{ $training->location_type_label ?? __(ucfirst($training->location_type)) }}
                            </span>
                            @endif
                        </div>

                        <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-purple-600 transition-colors line-clamp-2">
                            {{ $training->title }}
                        </h3>

                        @if($training->short_description)
                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                            {{ $training->short_description }}
                        </p>
                        @endif

                        <div class="flex flex-wrap gap-2 text-xs text-gray-500 mb-3">
                            @if($training->start_date)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $training->start_date->format('M d, Y') }}
                            </span>
                            @endif
                            @if($training->end_date)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                to {{ $training->end_date->format('M d, Y') }}
                            </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            @if($training->has_certificate)
                            <span class="inline-flex items-center gap-1 text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                                {{ __('Certificate') }}
                            </span>
                            @else
                            <span></span>
                            @endif
                            @if($training->price && $training->price !== 'Free')
                            <span class="text-sm font-semibold text-green-600">{{ $training->price }}</span>
                            @else
                            <span class="text-sm font-semibold text-green-600">{{ __('Free') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Workshops Section --}}
@if($workshops->count() > 0)
<section class="py-12 bg-white">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('Workshops') }}</h2>
            <span class="text-sm text-gray-500">({{ $workshops->count() }})</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($workshops as $workshop)
                <a href="{{ route('trainings.show', ['locale' => app()->getLocale(), 'id' => $workshop->id, 'type' => 'workshop']) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group block">
                    {{-- Content --}}
                    <div class="p-4">
                        {{-- Location Type Badge --}}
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            @if($workshop->location_type)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                {{ $workshop->location_type_label ?? __(ucfirst($workshop->location_type)) }}
                            </span>
                            @endif
                            @if($workshop->is_expired)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                {{ __('Expired') }}
                            </span>
                            @endif
                        </div>

                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                            {{ $workshop->title }}
                        </h3>

                        @if($workshop->short_description)
                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                            {{ $workshop->short_description }}
                        </p>
                        @endif

                        <div class="flex flex-wrap gap-2 text-xs text-gray-500 mb-3">
                            @if($workshop->workshop_date)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $workshop->workshop_date->format('M d, Y') }}
                            </span>
                            @endif
                            @if($workshop->start_time)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $workshop->start_time }}{{ $workshop->end_time ? ' - ' . $workshop->end_time : '' }}
                            </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            @if($workshop->has_certificate)
                            <span class="inline-flex items-center gap-1 text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                                {{ __('Certificate') }}
                            </span>
                            @else
                            <span></span>
                            @endif
                            @if($workshop->max_participants)
                            <span class="text-xs text-gray-500">{{ $workshop->max_participants }} {{ __('spots') }}</span>
                            @endif
                        </div>

                        @if($workshop->registration_link)
                        <span onclick="event.stopPropagation(); window.open({{ Js::from($workshop->registration_link) }}, '_blank');" class="mt-3 inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium cursor-pointer">
                            {{ __('Register') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Announcements Section --}}
@if($announcements->count() > 0)
<section class="py-12 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('News & Announcements') }}</h2>
            <span class="text-sm text-gray-500">({{ $announcements->count() }})</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($announcements as $announcement)
                @php
                    $isExternalLink = !empty($announcement->external_link);
                    $announcementLink = $isExternalLink
                        ? $announcement->external_link
                        : route('trainings.show', ['locale' => app()->getLocale(), 'id' => $announcement->id, 'type' => 'announcement']);
                @endphp
                <a href="{{ $announcementLink }}" @if($isExternalLink) target="_blank" rel="noopener" @endif class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group block">
                    {{-- Content --}}
                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-700">
                                {{ $announcement->category_label ?? __(ucfirst($announcement->category ?? __('General'))) }}
                            </span>
                            @if($announcement->priority && $announcement->priority !== 'normal')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                {{ $announcement->priority_label ?? __(ucfirst($announcement->priority)) }}
                            </span>
                            @endif
                        </div>

                        <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-orange-600 transition-colors line-clamp-2">
                            {{ $announcement->title }}
                        </h3>

                        <p class="text-sm text-gray-600 line-clamp-3 mb-3">
                            {{ Str::limit(strip_tags($announcement->content), 150) }}
                        </p>

                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $announcement->publish_date->format('M d, Y') }}</span>
                            @if($announcement->external_link)
                            <span class="text-orange-600 group-hover:underline flex items-center gap-1">
                                {{ __('Read More') }}
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Empty State (if no content) --}}
@if($trainings->count() === 0 && $workshops->count() === 0 && $announcements->count() === 0)
<section class="py-20 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 text-center">
        <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ __('No Content Yet') }}</h3>
        <p class="text-gray-600">{{ __("This institution hasn't published any trainings, workshops, or announcements yet.") }}</p>
    </div>
</section>
@endif

{{-- Related Institutions --}}
@if($relatedInstitutions->count() > 0)
<section class="py-12 bg-white border-t border-gray-100">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('More Institutions in') }} {{ $institution->city }}</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedInstitutions as $related)
                <a href="{{ route('academic-institution.show', ['locale' => app()->getLocale(), 'id' => $related->id]) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all group block">
                    <div class="h-24 bg-gradient-to-br from-purple-500 to-blue-500 relative flex items-center justify-center overflow-hidden">
                        @if($related->banner)
                            <img src="{{ $related->banner_url }}" alt="{{ $related->name }}" class="absolute inset-0 w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-br from-purple-900/60 to-blue-900/60"></div>
                        @endif
                        <div class="relative z-10">
                            @if($related->logo)
                                <img src="{{ $related->logo_url }}" alt="{{ $related->name }}" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-lg">
                            @else
                                <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center shadow-lg">
                                    <span class="text-2xl font-bold text-purple-600">{{ strtoupper(substr($related->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="p-4 text-center">
                        <h3 class="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors line-clamp-1">
                            {{ $related->name }}
                        </h3>
                        <span class="text-xs text-gray-500">{{ $related->institution_type_label }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Back Button --}}
<section class="py-8 bg-gray-50 border-t border-gray-100">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 text-center">
        <a href="{{ route('academic-tevets', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-800 text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('Back to Academic & Workplace Learning Centers') }}
        </a>
    </div>
</section>
@endsection
