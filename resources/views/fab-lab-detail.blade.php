@extends('layout.main')

@php
    // UTF-8 sanitization for head section
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];
    $fabLabName = $sanitize($fabLab->name ?? '');
    $fabLabDescription = $sanitize($fabLab->description ?? '');
    $fabLabType = $sanitize($fabLab->type ?? '');
    $fabLabCity = $sanitize($fabLab->city ?? '');
@endphp
@section('head')
<title>{{ $fabLabName }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ Str::limit($fabLabDescription, 160) }}">
<meta name="keywords" content="fab lab, {{ $fabLabType }}, {{ $fabLabCity }}, fabrication">
@endsection

@section('content')
{{-- Back Navigation --}}
<section class="bg-white border-b border-gray-200">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4">
        <a href="{{ route('fab-labs', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center text-gray-600 hover:text-blue-600 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('Back to Fab Labs') }}
        </a>
    </div>
</section>

{{-- Cover Image & Header --}}
<section class="relative h-64 sm:h-96 bg-gray-200">
    @if($fabLab->cover_image)
        <img src="{{ url('media/' . $fabLab->cover_image) }}" alt="{{ $fabLab->name }}" class="w-full h-full object-cover">
    @else
        <div class="w-full h-full bg-gradient-to-br from-blue-500 to-green-400"></div>
    @endif

    {{-- Gradient Overlay --}}
    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

    {{-- Header Content --}}
    <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-8">
        <div class="max-w-[1440px] mx-auto">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="@php
                            echo match($fabLab->type) {
                                'university' => 'bg-blue-600',
                                'community' => 'bg-green-600',
                                'private' => 'bg-purple-600',
                                'government' => 'bg-indigo-600',
                                default => 'bg-gray-600',
                            };
                        @endphp text-white text-sm font-medium px-3 py-1 rounded-full">
                            {{ __(ucfirst($fabLab->type)) }}
                        </span>
                        @if($fabLab->verified)
                            <span class="bg-green-600 text-white text-sm font-medium px-3 py-1 rounded-full flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                {{ __('Verified') }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-2">{{ $fabLab->name }}</h1>
                    <div class="flex items-center gap-2 text-white/90">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-lg">{{ $fabLab->location }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Main Content --}}
<section class="bg-gray-50 py-6 sm:py-8">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="grid lg:grid-cols-3 gap-6 sm:gap-8">
            {{-- Main Content Column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Rating Card --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex flex-col md:flex-row md:items-center gap-6">
                        <div class="text-center md:text-left">
                            <div class="text-4xl font-bold text-gray-900 mb-1">{{ number_format($fabLab->rating, 1) }}</div>
                            <div class="flex items-center justify-center md:justify-start mb-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= floor($fabLab->rating) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <div class="text-sm text-gray-600">{{ $fabLab->reviews_count }} {{ __('reviews') }}</div>
                        </div>

                        <div class="hidden md:block w-px h-16 bg-gray-200"></div>

                        <div class="flex-1 grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                                    {{ $fabLab->members }}
                                </div>
                                <div class="text-sm text-gray-600">{{ __('Active Members') }}</div>
                            </div>
                            <div>
                                @php
                                    $equipment = is_array($fabLab->equipment) ? $fabLab->equipment : [];
                                    $totalEquipment = count($equipment);
                                @endphp
                                <div class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                                    {{ $totalEquipment }}
                                </div>
                                <div class="text-sm text-gray-600">{{ __('Equipment Items') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- About Section --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('About') }}</h2>
                    <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $fabLab->description }}</p>
                </div>

                {{-- Equipment Section --}}
                @if(!empty($equipment))
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        {{ __('Equipment') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($equipment as $item)
                            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                                <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm">{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Services Section --}}
                @if(!empty($fabLab->services) && is_array($fabLab->services))
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Services') }}</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($fabLab->services as $service)
                            <div class="p-5 border border-gray-200 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">{{ $service }}</h4>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Features & Amenities --}}
                @if(!empty($fabLab->features) && is_array($fabLab->features))
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Features & Amenities') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($fabLab->features as $feature)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm">{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Contact Information --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('Contact Information') }}</h3>
                    <div class="space-y-3">
                        @if($fabLab->phone)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <a href="tel:{{ $fabLab->phone }}" class="text-gray-700 hover:text-blue-600">{{ $fabLab->phone }}</a>
                            </div>
                        @endif

                        @if($fabLab->email)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <a href="mailto:{{ $fabLab->email }}" class="text-gray-700 hover:text-blue-600">{{ $fabLab->email }}</a>
                            </div>
                        @endif

                        @if($fabLab->website)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                <a href="https://{{ $fabLab->website }}" target="_blank" rel="noopener noreferrer" class="text-gray-700 hover:text-blue-600">{{ $fabLab->website }}</a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Opening Hours --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('Opening Hours') }}</h3>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-gray-700">{{ $fabLab->localized_opening_hours }}</p>
                    </div>
                </div>

                {{-- Location --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('Location') }}</h3>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <div>
                            <p class="text-gray-700 font-semibold">{{ $fabLab->city }}</p>
                            <p class="text-gray-600 text-sm">{{ $fabLab->location }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Related Fab Labs --}}
@if(isset($relatedFabLabs) && $relatedFabLabs->count() > 0)
<section class="py-10 sm:py-16 bg-white">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900">{{ __('More Fab Labs in') }} {{ $fabLab->city }}</h2>
            <a href="{{ route('fab-labs', ['locale' => app()->getLocale(), 'city' => $fabLab->city]) }}" class="hidden md:inline-flex items-center px-4 py-2 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                {{ __('View All') }}
                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($relatedFabLabs as $relatedFabLab)
                <x-home.fab-lab-card :fabLab="$relatedFabLab" />
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
