@extends('layout.main')

@php
    // UTF-8 sanitization for head section
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];
    $headTitle = $sanitize($service->name ?? '');
    $headDescription = $sanitize($service->description ?? '');
    $headCategory = $sanitize($service->category ?? '');
    $headDesignerName = $sanitize($service->designer->name ?? '');
@endphp
@php
    $ogImage = $service->images->first() ? url('media/' . $service->images->first()->image_path) : url('media/images/logo.png');
@endphp

@section('head')
<title>{{ $headTitle }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ Str::limit($headDescription, 160) }}">
<meta name="keywords" content="service, {{ $headCategory }}, {{ $headDesignerName }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta property="og:title" content="{{ $headTitle }} - {{ config('app.name') }}">
<meta property="og:description" content="{{ Str::limit($headDescription, 160) }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:type" content="article">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $headTitle }}">
<meta name="twitter:description" content="{{ Str::limit($headDescription, 160) }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@section('content')
@php
    // UTF-8 sanitization helper
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];

    $designer = $service->designer;
    $designerAvatar = $designer && $designer->profile_image ? url('media/' . $designer->profile_image) : null;

    // Sanitize text fields
    $serviceName = $sanitize($service->name ?? '');
    $serviceDescription = $sanitize($service->description ?? '');
    $serviceCategory = $sanitize($service->category ?? '');
    $designerName = $sanitize($designer->name ?? '');
    $designerTitle = $sanitize($designer->title ?? 'Creative Professional');
    $designerCity = $sanitize($designer->city ?? '');

@endphp

<div class="min-h-screen bg-gray-50" x-data="{ showContactModal: false }">
    {{-- Breadcrumb --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="flex items-center gap-2 text-sm">
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Home') }}</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('services', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Services') }}</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium truncate max-w-[200px]">{{ $service->name }}</span>
            </nav>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

            {{-- Left Column - Service Info --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Service Header Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    {{-- Service Banner --}}
                    <div class="h-40 bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center">
                        <div class="w-20 h-20 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Service Content --}}
                    <div class="p-6">
                        {{-- Category Badge --}}
                        @if($service->category)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 mb-4">
                                {{ $service->category }}
                            </span>
                        @endif

                        {{-- Service Title --}}
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">{{ $service->name }}</h1>

                        {{-- Meta Info --}}
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Posted {{ $service->created_at->diffForHumans() }}</span>
                            </div>
                            @if($designer && $designer->city)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $designer->city }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Description --}}
                        <div class="prose prose-gray max-w-none">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('About This Service') }}</h3>
                            <p class="text-gray-600 leading-relaxed whitespace-pre-wrap">{{ $service->description }}</p>
                        </div>
                    </div>
                </div>

                {{-- Related Services --}}
                @if(isset($relatedServices) && $relatedServices->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('Similar Services') }}</h2>
                        <a href="{{ route('services', ['locale' => app()->getLocale(), 'category' => $service->category]) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                            {{ __('View All') }}
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($relatedServices as $relatedService)
                        <a href="{{ route('services.show', ['locale' => app()->getLocale(), 'id' => $relatedService->id]) }}"
                           class="block p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors group">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors truncate">
                                        {{ $relatedService->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">
                                        {{ Str::limit($relatedService->description, 80) }}
                                    </p>
                                    @if($relatedService->designer)
                                    <p class="text-xs text-gray-400 mt-2">
                                        by {{ $relatedService->designer->name }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Column - Sidebar --}}
            <div class="space-y-6">
                {{-- Provider Card --}}
                @if($designer)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">{{ __('Service Provider') }}</h3>

                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="flex items-center gap-4 group mb-4">
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex items-center justify-center text-white font-bold text-lg shadow-md flex-shrink-0">
                            @if($designerAvatar)
                                <img src="{{ $designerAvatar }}" alt="{{ $designer->name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($designer->name, 0, 2)) }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $designer->name }}</span>
                                @if($designer->email_verified_at)
                                    <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 truncate">{{ $designer->sub_sector ?? $designer->sector ?? __('Creative Professional') }}</p>
                        </div>
                    </a>

                    {{-- Designer Stats --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        @if($designer->years_of_experience)
                        <div class="p-3 bg-gray-50 rounded-lg text-center">
                            <p class="text-lg font-bold text-gray-900">{{ $designer->years_of_experience }}</p>
                            <p class="text-xs text-gray-500">{{ __('Experience') }}</p>
                        </div>
                        @endif
                        <div class="p-3 bg-gray-50 rounded-lg text-center">
                            <p class="text-lg font-bold text-gray-900">{{ $designer->services()->where('approval_status', 'approved')->count() }}</p>
                            <p class="text-xs text-gray-500">{{ __('Services') }}</p>
                        </div>
                    </div>

                    {{-- Contact Button --}}
                    @auth('designer')
                        @if(auth('designer')->id() !== $designer->id)
                        <button @click="showContactModal = true" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            {{ __('Contact') }}
                        </button>
                        @else
                        <div class="text-center text-sm text-gray-500 py-2">
                            {{ __('This is your service') }}
                        </div>
                        @endif
                    @else
                        <a href="{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            {{ __('Contact') }}
                        </a>
                    @endauth

                    {{-- View Profile Link --}}
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}"
                       class="mt-3 w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors text-sm">
                        {{ __('View Full Profile') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
                @endif

                {{-- Service Details Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">{{ __('Service Details') }}</h3>

                    <div class="space-y-4">
                        @if($service->category)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('Category') }}</span>
                            <span class="font-medium text-gray-900">{{ $service->category }}</span>
                        </div>
                        @endif

                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('Posted') }}</span>
                            <span class="font-medium text-gray-900">{{ $service->created_at->format('M d, Y') }}</span>
                        </div>

                        @if($designer && $designer->city)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('Location') }}</span>
                            <span class="font-medium text-gray-900">{{ $designer->city }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Share Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">{{ __('Share Service') }}</h3>
                    <x-share-button
                        :url="route('services.show', ['locale' => app()->getLocale(), 'id' => $service->id])"
                        :title="$service->name"
                        :description="Str::limit($service->description, 150)"
                        variant="default"
                        size="md"
                    />
                </div>
            </div>
        </div>
    </div>

    {{-- Contact Provider Modal --}}
    @if($designer)
    <div x-show="showContactModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showContactModal = false"></div>

        {{-- Modal --}}
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-4 sm:p-6 transform transition-all"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- Close Button --}}
            <button @click="showContactModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Modal Content --}}
            <div class="text-center">
                {{-- Designer Avatar --}}
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex items-center justify-center text-white text-xl font-bold shadow-lg">
                    @if($designerAvatar)
                        <img src="{{ $designerAvatar }}" alt="{{ $designer->name }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($designer->name, 0, 2)) }}
                    @endif
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Contact') }} {{ $designer->name }}?</h3>
                <p class="text-gray-600 text-sm mb-6">{{ __("You're about to start a conversation about this service.") }}</p>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <button @click="showContactModal = false" class="flex-1 px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <a href="{{ route('messages.compose', ['locale' => app()->getLocale(), 'designerId' => $designer->id]) }}" class="flex-1 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all text-center">
                        {{ __('Send Message') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
