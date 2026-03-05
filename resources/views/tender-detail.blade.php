@extends('layout.main')

@php
    // UTF-8 sanitization for head section
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];
    $tenderTitle = $sanitize($tender->title ?? '');
    $tenderDescription = $sanitize($tender->short_description ?: Str::limit($tender->description ?? '', 150));
    $tenderPublisher = $sanitize($tender->publisher_type ?? '');

    // Helper function to detect if text contains Arabic characters
    $hasArabic = function($text) {
        return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
    };

    // Determine text direction based on content
    $titleDir = $hasArabic($tender->title ?? '') ? 'rtl' : 'ltr';
    $descDir = $hasArabic($tender->description ?? '') ? 'rtl' : 'ltr';
@endphp
@section('head')
<title>{{ $tenderTitle }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ $tenderDescription }}">
<meta name="keywords" content="tender, opportunity, {{ $tenderPublisher }}, Palestine">
@endsection

@section('content')
{{-- Header Section --}}
<section class="bg-white border-b border-gray-200">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6">
        {{-- Back Button --}}
        <a href="{{ route('tenders.index', ['locale' => app()->getLocale()]) }}"
           class="inline-flex items-center mb-4 text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('Back to Tenders') }}
        </a>

        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="flex-1">
                {{-- Title --}}
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3" dir="{{ $titleDir }}" style="text-align: {{ $titleDir == 'rtl' ? 'right' : 'left' }};">{{ $tender->title }}</h1>

                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2">
                    @php
                        $statusClasses = match($tender->status) {
                            'open' => 'bg-green-100 text-green-800 border-green-200',
                            'closing_soon' => 'bg-orange-100 text-orange-800 border-orange-200',
                            'closed' => 'bg-gray-100 text-gray-800 border-gray-200',
                            default => 'bg-gray-100 text-gray-800 border-gray-200'
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusClasses }}">
                        {{ $tender->status_label }}
                    </span>
                </div>
            </div>

            {{-- Reference Number --}}
            @if($tender->reference_number)
                <div class="lg:text-right">
                    <p class="text-gray-600 text-sm mb-1">{{ __('Reference Number') }}</p>
                    <p class="font-semibold text-gray-900">{{ $tender->reference_number }}</p>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- Main Content --}}
<section class="py-8 bg-gray-50">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
            {{-- Main Content Column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Overview --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Overview') }}</h2>
                    <div class="prose prose-sm max-w-none text-gray-600" dir="{{ $descDir }}" style="text-align: {{ $descDir == 'rtl' ? 'right' : 'left' }};">
                        @if(strip_tags($tender->description) !== $tender->description)
                            {{-- Contains HTML formatting - allow only safe tags --}}
                            {!! strip_tags($tender->description, '<p><br><ul><ol><li><strong><em><b><i><h1><h2><h3><h4><h5><h6><a><table><thead><tbody><tr><th><td><span><div>') !!}
                        @else
                            {{-- Plain text, preserve whitespace --}}
                            <p class="whitespace-pre-line">{{ $tender->description }}</p>
                        @endif
                    </div>
                </div>

                {{-- Scope of Work --}}
                @if($tender->scope && count($tender->scope) > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Scope of Work') }}</h2>
                        <ul class="space-y-2">
                            @foreach($tender->scope as $item)
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-gray-700">{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Deliverables --}}
                @if($tender->deliverables && count($tender->deliverables) > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Deliverables') }}</h2>
                        <ul class="space-y-2">
                            @foreach($tender->deliverables as $item)
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-gray-700">{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Requirements --}}
                @if($tender->requirements && count($tender->requirements) > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Requirements') }}</h2>
                        <ul class="space-y-2">
                            @foreach($tender->requirements as $item)
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-gray-700">{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Timeline --}}
                @if($tender->timeline && count($tender->timeline) > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Project Timeline') }}</h2>
                        <div class="space-y-4">
                            @foreach($tender->timeline as $index => $phase)
                                @if($index > 0)
                                    <hr class="border-gray-200">
                                @endif
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center text-white flex-shrink-0 font-bold">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 mb-1">{{ $phase['phase'] ?? $phase }}</h3>
                                        @if(isset($phase['duration']))
                                            <p class="text-sm text-gray-600 mb-2">{{ __('Duration:') }} {{ $phase['duration'] }}</p>
                                        @endif
                                        @if(isset($phase['description']))
                                            <p class="text-gray-700">{{ $phase['description'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Application Process --}}
                @if($tender->application_process && count($tender->application_process) > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Application Process') }}</h2>
                        <ol class="space-y-2 list-decimal list-inside">
                            @foreach($tender->application_process as $step)
                                <li class="text-gray-700">{{ $step }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Key Information') }}</h3>

                    <div class="space-y-4">
                        {{-- Publisher --}}
                        <div>
                            <div class="flex items-center gap-2 text-gray-600 mb-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span class="text-sm">{{ __('Publisher') }}</span>
                            </div>
                            <p class="font-semibold text-gray-900" dir="{{ $hasArabic($tender->publisher ?? '') ? 'rtl' : 'ltr' }}">{{ $tender->publisher }}</p>
                            @if($tender->publisher_type)
                                <p class="text-sm text-gray-600">{{ ucfirst($tender->publisher_type) }}</p>
                            @endif
                        </div>

                        <hr class="border-gray-200">

                        {{-- Published Date --}}
                        @if($tender->published_date)
                            <div>
                                <div class="flex items-center gap-2 text-gray-600 mb-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm">{{ __('Published') }}</span>
                                </div>
                                <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($tender->published_date)->format('F d, Y') }}</p>
                            </div>

                            <hr class="border-gray-200">
                        @endif

                        {{-- Deadline --}}
                        @if($tender->deadline)
                            @php
                                $daysLeft = $tender->days_until_deadline;
                            @endphp
                            <div>
                                <div class="flex items-center gap-2 text-gray-600 mb-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm">{{ __('Deadline') }}</span>
                                </div>
                                <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($tender->deadline)->format('F d, Y') }}</p>
                                @if($daysLeft !== null && $daysLeft > 0)
                                    <p class="text-sm mt-1 {{ $daysLeft <= 14 ? 'text-orange-600' : 'text-green-600' }}">
                                        {{ $daysLeft }} {{ $daysLeft !== 1 ? __('days') : __('day') }} {{ __('remaining') }}
                                    </p>
                                @endif
                            </div>

                            <hr class="border-gray-200">
                        @endif

                        {{-- Budget --}}
                        @if($tender->budget)
                            <div>
                                <div class="flex items-center gap-2 text-gray-600 mb-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm">{{ __('Budget') }}</span>
                                </div>
                                <p class="font-semibold text-gray-900">{{ $tender->budget }}</p>
                            </div>

                            <hr class="border-gray-200">
                        @endif

                        {{-- Location --}}
                        @if($tender->location)
                            <div>
                                <div class="flex items-center gap-2 text-gray-600 mb-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-sm">{{ __('Location') }}</span>
                                </div>
                                <p class="font-semibold text-gray-900" dir="{{ $hasArabic($tender->location ?? '') ? 'rtl' : 'ltr' }}">{{ $tender->location }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Contact Information --}}
                    @if($tender->contact_person || $tender->contact_email || $tender->contact_phone)
                        <hr class="my-6 border-gray-200">

                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-900 mb-3">{{ __('Contact Information') }}</h4>
                            <div class="space-y-2 text-sm">
                                @if($tender->contact_person)
                                    <p class="text-gray-700">
                                        <span class="font-semibold">{{ __('Contact Person:') }}</span><br>
                                        {{ $tender->contact_person }}
                                    </p>
                                @endif
                                @if($tender->contact_email)
                                    <p class="text-gray-700">
                                        <span class="font-semibold">{{ __('Email:') }}</span><br>
                                        <a href="mailto:{{ $tender->contact_email }}" class="text-blue-600 hover:text-blue-700">
                                            {{ $tender->contact_email }}
                                        </a>
                                    </p>
                                @endif
                                @if($tender->contact_phone)
                                    <p class="text-gray-700">
                                        <span class="font-semibold">{{ __('Phone:') }}</span><br>
                                        <a href="tel:{{ $tender->contact_phone }}" class="text-blue-600 hover:text-blue-700">
                                            {{ $tender->contact_phone }}
                                        </a>
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Action Button --}}
                    @if($tender->source_url)
                        <a href="{{ $tender->source_url }}" target="_blank" rel="noopener noreferrer"
                           class="w-full inline-flex items-center justify-center py-3 bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-700 hover:to-green-600 text-white font-semibold rounded-lg transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            {{ __('View Original Tender') }}
                        </a>
                        <p class="text-xs text-gray-500 text-center mt-3">
                            {{ __('This tender was collected from external sources') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
