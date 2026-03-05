@extends('layout.main')

@php
    // UTF-8 sanitization for head section
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];
    $trainingTitle = $sanitize($training->title ?? '');
    $trainingDescription = $sanitize($training->short_description ?? '');
    $trainingCategory = $sanitize($training->category ?? '');
    $trainingLevel = $sanitize($training->level ?? '');
@endphp
@section('head')
<title>{{ $trainingTitle }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ $trainingDescription }}">
<meta name="keywords" content="{{ $trainingCategory }}, training, workshop, {{ $trainingLevel }}, Palestine">
@endsection

@section('content')
{{-- Header Section --}}
<section class="bg-white border-b border-gray-200">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-6 sm:py-8">
        {{-- Back Button --}}
        <a href="{{ route('trainings.index', ['locale' => app()->getLocale()]) }}"
           class="inline-flex items-center mb-4 text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('Back to Trainings') }}
        </a>

        {{-- Badges --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            @php
                $locationTypeClasses = match($training->location_type) {
                    'online' => 'bg-blue-100 text-blue-700',
                    'in-person' => 'bg-green-100 text-green-700',
                    'hybrid' => 'bg-purple-100 text-purple-700',
                    default => 'bg-gray-100 text-gray-700'
                };
                $levelClasses = match($training->level) {
                    'beginner' => 'bg-green-100 text-green-700',
                    'intermediate' => 'bg-yellow-100 text-yellow-700',
                    'advanced' => 'bg-red-100 text-red-700',
                    default => 'bg-gray-100 text-gray-700'
                };
            @endphp
            @if($training->location_type)
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $locationTypeClasses }}">
                {{ ucfirst($training->location_type) }}
            </span>
            @endif
            @if($training->level)
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $levelClasses }}">
                {{ ucfirst($training->level) }}
            </span>
            @endif
            @if($training->category)
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                {{ $training->category }}
            </span>
            @endif
            @if($training->has_certificate)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-600 to-green-500 text-white">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                {{ __('Certificate') }}
            </span>
            @endif
        </div>

        {{-- Title with Share Button --}}
        <div class="flex flex-wrap items-start justify-between gap-4 mb-3">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $training->title }}</h1>
            <x-share-button
                :url="route('trainings.show', ['locale' => app()->getLocale(), 'id' => $training->id])"
                :title="$training->title"
                :description="Str::limit($training->short_description ?? $training->description, 150)"
                variant="default"
                size="sm"
            />
        </div>

        @if($training->short_description)
        <p class="text-gray-600 text-lg mb-4">{{ $training->short_description }}</p>
        @endif

        {{-- Stats --}}
        <div class="flex flex-wrap items-center gap-6 text-gray-600">
            @if($training->rating)
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 fill-yellow-400 text-yellow-400" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="font-semibold">{{ number_format($training->rating, 1) }}</span>
                <span class="text-sm">({{ $training->reviews_count ?? 0 }} {{ __('reviews') }})</span>
            </div>
            @endif
            @if($training->enrolled_students)
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>{{ $training->enrolled_students }} {{ __('students enrolled') }}</span>
            </div>
            @endif
            @if($training->duration)
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $training->duration }}</span>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- Main Content --}}
<section class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
            {{-- Main Content Column --}}
            <div class="lg:col-span-2 space-y-6 sm:space-y-8">
                {{-- About This Course --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('About This Course') }}</h2>
                    <div class="prose prose-sm max-w-none text-gray-700">
                        {!! nl2br(e($training->description)) !!}
                    </div>
                </div>

                {{-- What You'll Learn --}}
                @if($training->learning_outcomes && count($training->learning_outcomes) > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __("What You'll Learn") }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($training->learning_outcomes as $outcome)
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-gray-700">{{ $outcome }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Syllabus --}}
                @if($training->syllabus && count($training->syllabus) > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Course Syllabus') }}</h2>
                        <div class="space-y-4">
                            @foreach($training->syllabus as $index => $module)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center text-white flex-shrink-0 font-bold">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="flex-1">
                                            @if(isset($module['week']))
                                                <p class="text-sm text-gray-500 mb-1">{{ $module['week'] }}</p>
                                            @endif
                                            <h3 class="font-semibold text-gray-900 mb-2">{{ $module['title'] ?? $module }}</h3>
                                            @if(isset($module['topics']) && is_array($module['topics']))
                                                <ul class="space-y-1">
                                                    @foreach($module['topics'] as $topic)
                                                        <li class="text-sm text-gray-600 flex items-start gap-2">
                                                            <span class="text-blue-600 mt-1">-</span>
                                                            <span>{{ $topic }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Requirements --}}
                @if($training->requirements && count($training->requirements) > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Requirements') }}</h2>
                        <ul class="space-y-2">
                            @foreach($training->requirements as $requirement)
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-gray-700">{{ $requirement }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Tools --}}
                @if($training->tools && count($training->tools) > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Tools & Software') }}</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($training->tools as $tool)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $tool }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Instructor --}}
                @if($training->instructor_name)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Your Instructor') }}</h2>
                        <div class="flex items-start gap-4">
                            @if($training->instructor_image)
                                <img src="{{ asset('storage/' . $training->instructor_image) }}"
                                     alt="{{ $training->instructor_name }}"
                                     class="w-20 h-20 rounded-full object-cover">
                            @else
                                <div class="w-20 h-20 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center text-white text-2xl font-bold">
                                    {{ substr($training->instructor_name, 0, 1) }}
                                </div>
                            @endif
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-lg mb-1">{{ $training->instructor_name }}</h3>
                                @if($training->instructor_title)
                                    <p class="text-gray-600 mb-3">{{ $training->instructor_title }}</p>
                                @endif
                                @if($training->instructor_bio)
                                    <p class="text-gray-700">{{ $training->instructor_bio }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
                    {{-- Training Image --}}
                    <div class="aspect-video mb-4 rounded-lg overflow-hidden bg-gray-200">
                        @if($training->image)
                            <img src="{{ asset('storage/' . $training->image) }}"
                                 alt="{{ $training->title }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-100 to-green-100 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Price --}}
                    <div class="text-center mb-6">
                        <div class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent mb-2">
                            {{ $training->price ?: __('Free for members') }}
                        </div>
                        <p class="text-sm text-gray-600">{{ __('Available for registered members') }}</p>
                    </div>

                    {{-- Enroll Button --}}
                    @if($training->registration_link)
                    <a href="{{ $training->registration_link }}" target="_blank" rel="noopener" class="w-full py-3 bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-700 hover:to-green-600 text-white font-semibold rounded-lg transition-all mb-4 flex items-center justify-center gap-2">
                        <span>{{ __('Enroll Now') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @else
                    <button onclick="showEnrollModal()" class="w-full py-3 bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-700 hover:to-green-600 text-white font-semibold rounded-lg transition-all mb-4">
                        {{ __('Enroll Now') }}
                    </button>
                    @endif

                    <hr class="my-6 border-gray-200">

                    {{-- Course Details --}}
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ __('Course Details') }}</h3>

                    <div class="space-y-4">
                        @if($training->start_date)
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Start Date') }}</p>
                                    <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($training->start_date)->format('F d, Y') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($training->end_date)
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('End Date') }}</p>
                                    <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($training->end_date)->format('F d, Y') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($training->duration)
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Duration') }}</p>
                                    <p class="font-semibold text-gray-900">{{ $training->duration }}</p>
                                </div>
                            </div>
                        @endif

                        @if($training->schedule)
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Schedule') }}</p>
                                    <p class="font-semibold text-gray-900">{{ $training->schedule }}</p>
                                </div>
                            </div>
                        @endif

                        @if($training->location)
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Location') }}</p>
                                    <p class="font-semibold text-gray-900">{{ $training->location }}</p>
                                </div>
                            </div>
                        @endif

                        @if($training->languages && count($training->languages) > 0)
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Language') }}</p>
                                    <p class="font-semibold text-gray-900">{{ implode(', ', $training->languages) }}</p>
                                </div>
                            </div>
                        @endif

                        @if($training->max_participants)
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Max Participants') }}</p>
                                    <p class="font-semibold text-gray-900">{{ $training->max_participants }} {{ __('spots') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Provider/Institution --}}
                    @if($training->academicAccount)
                        <hr class="my-6 border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Provided by') }}</h4>
                        <a href="{{ route('academic-institution.show', ['locale' => app()->getLocale(), 'id' => $training->academicAccount->id]) }}" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            @if($training->academicAccount->logo)
                                <img src="{{ $training->academicAccount->logo_url }}" alt="{{ $training->academicAccount->name }}" class="w-12 h-12 rounded-lg object-cover">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <span class="text-lg font-bold text-purple-600">{{ strtoupper(substr($training->academicAccount->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-gray-900">{{ $training->academicAccount->name }}</p>
                                <p class="text-xs text-gray-500">{{ $training->academicAccount->institution_type_label }}</p>
                            </div>
                        </a>
                    @endif

                    {{-- Features --}}
                    @if($training->features && count($training->features) > 0)
                        <hr class="my-6 border-gray-200">

                        <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('This course includes:') }}</h4>
                        <ul class="space-y-2">
                            @foreach($training->features as $feature)
                                <li class="flex items-center gap-2 text-sm text-gray-700">
                                    <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Enroll Modal --}}
<div id="enrollModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="hideEnrollModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <button onclick="hideEnrollModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-r from-blue-100 to-green-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('Interested in this training?') }}</h3>
            <p class="text-gray-600 mb-6">
                @if($training->academicAccount)
                    {{ __('Contact') }} <strong>{{ $training->academicAccount->name }}</strong> {{ __('directly to enroll in this training program.') }}
                @else
                    {{ __('Contact the training provider directly to enroll in this program.') }}
                @endif
            </p>

            @if($training->academicAccount)
                <div class="space-y-3">
                    @if($training->academicAccount->email)
                    <a href="mailto:{{ $training->academicAccount->email }}?subject=Enrollment Inquiry: {{ $training->title }}" class="flex items-center justify-center gap-2 w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ __('Send Email') }}
                    </a>
                    @endif
                    @if($training->academicAccount->phone)
                    <a href="tel:{{ $training->academicAccount->phone }}" class="flex items-center justify-center gap-2 w-full py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ __('Call') }} {{ $training->academicAccount->phone }}
                    </a>
                    @endif
                    @if($training->academicAccount->website)
                    <a href="{{ $training->academicAccount->website }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 w-full py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        {{ __('Visit Website') }}
                    </a>
                    @endif
                </div>
            @else
                <p class="text-sm text-gray-500">{{ __('No contact information available at this time.') }}</p>
            @endif
        </div>
    </div>
</div>

<script>
function showEnrollModal() {
    document.getElementById('enrollModal').classList.remove('hidden');
    document.getElementById('enrollModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function hideEnrollModal() {
    document.getElementById('enrollModal').classList.add('hidden');
    document.getElementById('enrollModal').classList.remove('flex');
    document.body.style.overflow = '';
}

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideEnrollModal();
    }
});
</script>
@endsection
