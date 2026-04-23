@props(['designer', 'assetPaths', 'isOwner'])

@php
    $averageRating = $designer->average_rating;
    $ratingCount = $designer->rating_count;
    $hasRated = auth('designer')->check() ? \App\Models\ProfileRating::hasRated($designer->id, auth('designer')->id()) : false;
    $ratingCriteria = \App\Models\RatingCriteria::active()->ordered()->get();
@endphp

<!-- Cover Image Section -->
<div class="relative h-40 sm:h-56 md:h-72 lg:h-80 bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden rounded-b-2xl sm:rounded-b-3xl">
    @if($assetPaths['cover'])
        <img
            src="{{ $assetPaths['cover'] }}"
            alt="{{ $designer->name }} Cover"
            class="w-full h-full object-cover"
            loading="lazy"
        />
        <!-- Subtle gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-black/10"></div>
    @else
        <div class="w-full h-full bg-gradient-to-br from-blue-600 to-green-500"></div>
    @endif
</div>

<!-- Profile Section -->
<div class="max-w-[1200px] mx-auto px-3 sm:px-4 md:px-6">
    <div class="relative -mt-12 sm:-mt-16 md:-mt-24 mb-4 sm:mb-6 md:mb-8">
        <!-- Profile Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 md:p-8">
            <div class="flex flex-col md:flex-row gap-4 sm:gap-6">
                <!-- Avatar -->
                <div class="flex-shrink-0 mx-auto md:mx-0">
                    <div class="w-20 h-20 sm:w-28 sm:h-28 md:w-32 md:h-32 border-3 sm:border-4 border-white shadow-lg rounded-full overflow-hidden ring-2 sm:ring-4 ring-gray-100">
                        @if($assetPaths['avatar'])
                            <img
                                src="{{ $assetPaths['avatar'] }}"
                                alt="{{ $designer->name }}"
                                class="w-full h-full object-cover"
                                loading="lazy"
                            />
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white text-xl sm:text-2xl md:text-3xl font-bold">
                                {{ strtoupper(substr($designer->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3 sm:gap-4 mb-3 sm:mb-4">
                        <div>
                            <div class="flex items-center justify-center md:justify-start gap-2 mb-1 sm:mb-2">
                                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ $designer->name }}</h1>
                                @if($designer->is_trusted)
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full text-xs sm:text-sm font-semibold bg-blue-100 text-blue-700 border border-blue-200" title="{{ __('Trusted member — verified by admin') }}">
                                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    {{ __('Trusted') }}
                                </span>
                                @endif
                                @if($designer->is_tevet && in_array($designer->sector, ['manufacturer', 'showroom']))
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full text-xs sm:text-sm font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ __('Workplace Learning Center') }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm sm:text-base md:text-lg text-gray-600 mb-2 sm:mb-3">
                                @if($designer->sub_sector)
                                    {{ $designer->sub_sector }}
                                @elseif($designer->sector)
                                    {{ ucfirst($designer->sector) }}
                                @else
                                    {{ __('Designer') }}
                                @endif
                            </p>
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 sm:gap-3 md:gap-4 text-xs sm:text-sm text-gray-600">
                                @if($designer->location)
                                <div class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="truncate max-w-[150px] sm:max-w-none">{{ $designer->location }}</span>
                                </div>
                                @endif
                                <div class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>{{ __('Since') }} {{ $designer->created_at->format('Y') }}</span>
                                </div>
                            </div>

                            {{-- Social Media Links --}}
                            @if($designer->linkedin || $designer->instagram || $designer->facebook || $designer->behance)
                            <div class="flex items-center justify-center md:justify-start gap-3 mt-3">
                                @if($designer->linkedin)
                                <a href="{{ $designer->linkedin }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gray-100 hover:bg-[#0077b5] flex items-center justify-center text-gray-600 hover:text-white transition-all duration-200 group" title="LinkedIn">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                                @endif
                                @if($designer->instagram)
                                <a href="{{ $designer->instagram }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gray-100 hover:bg-gradient-to-br hover:from-[#f09433] hover:via-[#e6683c] hover:to-[#bc1888] flex items-center justify-center text-gray-600 hover:text-white transition-all duration-200 group" title="Instagram">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                                @endif
                                @if($designer->facebook)
                                <a href="{{ $designer->facebook }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gray-100 hover:bg-[#1877f2] flex items-center justify-center text-gray-600 hover:text-white transition-all duration-200 group" title="Facebook">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                                @endif
                                @if($designer->behance)
                                <a href="{{ $designer->behance }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gray-100 hover:bg-[#1769ff] flex items-center justify-center text-gray-600 hover:text-white transition-all duration-200 group" title="Behance">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M22 7h-7v-2h7v2zm1.726 10c-.442 1.297-2.029 3-5.101 3-3.074 0-5.564-1.729-5.564-5.675 0-3.91 2.325-5.92 5.466-5.92 3.082 0 4.964 1.782 5.375 4.426.078.506.109 1.188.095 2.14h-8.027c.13 3.211 3.483 3.312 4.588 2.029h3.168zm-7.686-4h4.965c-.105-1.547-1.136-2.219-2.477-2.219-1.466 0-2.277.768-2.488 2.219zm-9.574 6.988h-6.466v-14.967h6.953c5.476.081 5.58 5.444 2.72 6.906 3.461 1.26 3.577 8.061-3.207 8.061zm-3.466-8.988h3.584c2.508 0 2.906-3-.312-3h-3.272v3zm3.391 3h-3.391v3.016h3.341c3.055 0 2.868-3.016.05-3.016z"/>
                                    </svg>
                                </a>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        @if($isOwner)
                        <div class="flex flex-wrap gap-2 justify-center md:justify-start flex-shrink-0">
                            @if($designer->sector === 'guest')
                            <a href="{{ route('account.upgrade', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-3 sm:px-4 md:px-6 py-2 sm:py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-orange-500/30 transition-all text-xs sm:text-sm md:text-base animate-pulse">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                                {{ __('Upgrade to Full Account') }}
                            </a>
                            @else
                            <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-3 sm:px-4 md:px-6 py-2 sm:py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg font-semibold hover:shadow-lg transition-all text-xs sm:text-sm md:text-base">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                {{ __('Edit Profile') }}
                            </a>
                            @endif
                            <x-share-button
                                :url="route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id])"
                                :title="$designer->name . ' - ' . __('Portfolio')"
                                :description="Str::limit($designer->bio ?? ($designer->sub_sector ?? __('Creative Professional')), 150)"
                                variant="icon-only"
                                size="md"
                            />
                        </div>
                        @else
                        <div class="flex flex-wrap gap-2 justify-center md:justify-start flex-shrink-0">
                            {{-- Send Message Button --}}
                            @if($designer->allow_messages)
                                @if(auth('designer')->check())
                                <button
                                    id="messageRequestBtn"
                                    onclick="sendMessageRequest({{ $designer->id }})"
                                    class="inline-flex items-center px-3 sm:px-4 md:px-6 py-2 sm:py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg font-semibold hover:shadow-lg transition-all text-xs sm:text-sm md:text-base"
                                >
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <span class="hidden sm:inline">{{ __('Send Message') }}</span>
                                    <span class="sm:hidden">{{ __('Message') }}</span>
                                </button>
                                @else
                                {{-- Show button for logged out users that redirects to login --}}
                                <a
                                    href="{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}"
                                    class="inline-flex items-center px-3 sm:px-4 md:px-6 py-2 sm:py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg font-semibold hover:shadow-lg transition-all text-xs sm:text-sm md:text-base"
                                >
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <span class="hidden sm:inline">{{ __('Send Message') }}</span>
                                    <span class="sm:hidden">{{ __('Message') }}</span>
                                </a>
                                @endif
                            @endif
                            {{-- Send Email Button --}}
                            @if(auth('designer')->check() && $designer->email)
                                @if($designer->show_email)
                                <a href="{{ route('email.compose', ['locale' => app()->getLocale(), 'designerId' => $designer->id]) }}"
                                   class="inline-flex items-center px-3 sm:px-4 md:px-6 py-2 sm:py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 hover:shadow-lg transition-all text-xs sm:text-sm md:text-base"
                                >
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="hidden sm:inline">{{ __('Send Email') }}</span>
                                    <span class="sm:hidden">{{ __('Email') }}</span>
                                </a>
                                @else
                                <button
                                   disabled
                                   class="inline-flex items-center px-3 sm:px-4 md:px-6 py-2 sm:py-2.5 bg-gray-100 border border-gray-200 text-gray-400 rounded-lg font-semibold cursor-not-allowed text-xs sm:text-sm md:text-base"
                                   title="{{ __('This designer has disabled email contact') }}"
                                >
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="hidden sm:inline">{{ __('Email Disabled') }}</span>
                                    <span class="sm:hidden">{{ __('Email') }}</span>
                                </button>
                                @endif
                            @endif
                            {{-- Share Profile Button --}}
                            <x-share-button
                                :url="route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id])"
                                :title="$designer->name . ' - ' . __('Portfolio')"
                                :description="Str::limit($designer->bio ?? ($designer->sub_sector ?? __('Creative Professional')), 150)"
                                variant="icon-only"
                                size="md"
                            />
                            {{-- Subscribe Button --}}
                            <x-profile-subscribe-button
                                profileType="designer"
                                :profileId="$designer->id"
                                :profileName="$designer->name"
                            />
                        </div>
                        @endif
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-3 sm:gap-4 md:gap-6 lg:gap-8 py-3 sm:py-4 border-t border-gray-200 max-w-md">
                        <div class="text-center sm:text-left">
                            <div class="text-lg sm:text-xl md:text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                                {{ $designer->projects->count() ?? 0 }}
                            </div>
                            <div class="text-[10px] sm:text-xs md:text-sm text-gray-600">{{ __('Projects') }}</div>
                        </div>
                        <div class="text-center sm:text-left">
                            <div class="text-lg sm:text-xl md:text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                                {{ $designer->products->count() ?? 0 }}
                            </div>
                            <div class="text-[10px] sm:text-xs md:text-sm text-gray-600">{{ __('Products') }}</div>
                        </div>
                        <!-- Rating Display -->
                        <div class="text-center sm:text-left cursor-pointer group" onclick="scrollToRatings()">
                            <div class="flex items-center justify-center sm:justify-start gap-1">
                                <span class="text-lg sm:text-xl md:text-2xl font-bold bg-gradient-to-r from-yellow-500 to-orange-500 bg-clip-text text-transparent">
                                    {{ number_format($averageRating, 1) }}
                                </span>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <div class="text-[10px] sm:text-xs md:text-sm text-gray-600 group-hover:text-blue-600 transition-colors">
                                {{ $ratingCount }} {{ $ratingCount == 1 ? __('Rating') : __('Ratings') }}
                            </div>
                        </div>
                    </div>

                    <!-- Rate Profile Button (for non-owners who are logged in, not for guest profiles) -->
                    @if(!$isOwner && auth('designer')->check() && $designer->sector !== 'guest')
                    <div class="mt-3 sm:mt-4">
                        @if($hasRated)
                        <button
                            onclick="showRatingModal(true)"
                            class="inline-flex items-center px-3 sm:px-4 py-2 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg text-xs sm:text-sm font-medium hover:bg-yellow-100 transition-all"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            {{ __('Update Your Rating') }}
                        </button>
                        @else
                        <button
                            onclick="showRatingModal(false)"
                            class="inline-flex items-center px-3 sm:px-4 py-2 bg-gradient-to-r from-yellow-400 to-orange-500 text-white rounded-lg text-xs sm:text-sm font-semibold hover:shadow-lg hover:shadow-yellow-500/30 transition-all"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            {{ __('Rate this Profile') }}
                        </button>
                        @endif
                    </div>
                    @elseif(!$isOwner && !auth('designer')->check())
                    <div class="mt-3 sm:mt-4">
                        <a
                            href="{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}"
                            class="inline-flex items-center px-3 sm:px-4 py-2 bg-gradient-to-r from-yellow-400 to-orange-500 text-white rounded-lg text-xs sm:text-sm font-semibold hover:shadow-lg hover:shadow-yellow-500/30 transition-all"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            {{ __('Rate this Profile') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// DOMContentLoaded handler for message request checking
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's a pending message request
    @if(auth('designer')->check() && $designer->allow_messages)
    checkPendingMessageRequest({{ $designer->id }});
    @endif
});

// Check for pending message request
async function checkPendingMessageRequest(designerId) {
    try {
        const response = await fetch('{{ url(app()->getLocale()) }}/messages/check-pending-request/' + designerId, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success && data.has_conversation) {
            updateMessageButtonToOpenChat(data.conversation_url);
        } else if (data.success && data.has_pending_request) {
            updateMessageButtonToPending();
        }
    } catch (error) {
        console.error('Error checking pending request:', error);
    }
}

// Update button to show "Open Chat" when conversation exists
function updateMessageButtonToOpenChat(chatUrl) {
    const btn = document.getElementById('messageRequestBtn');
    if (!btn) return;

    const link = document.createElement('a');
    link.href = chatUrl;
    link.className = 'inline-flex items-center px-3 sm:px-4 md:px-6 py-2 sm:py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg font-semibold hover:shadow-lg transition-all text-xs sm:text-sm md:text-base';
    link.innerHTML = `
        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <span class="hidden sm:inline">{{ __("Open Chat") }}</span>
        <span class="sm:hidden">{{ __("Chat") }}</span>
    `;
    btn.replaceWith(link);
}

// Update button to show pending state
function updateMessageButtonToPending() {
    const btn = document.getElementById('messageRequestBtn');
    if (!btn) return;

    btn.disabled = true;
    btn.onclick = null;
    btn.className = 'inline-flex items-center px-3 sm:px-4 md:px-6 py-2 sm:py-2.5 bg-gray-400 text-white rounded-lg font-semibold cursor-not-allowed transition-all text-xs sm:text-sm md:text-base';

    const longText = btn.querySelector('.hidden.sm\\:inline');
    const shortText = btn.querySelector('.sm\\:hidden');

    if (longText) longText.textContent = '{{ __("Request Sent") }}';
    if (shortText) shortText.textContent = '{{ __("Sent") }}';
}

// Custom Modal Functions
function showModal(title, message, type = 'success') {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4';
    modal.style.animation = 'fadeIn 0.2s ease-out';

    const iconColors = {
        success: 'from-green-500 to-blue-500',
        error: 'from-red-500 to-pink-500',
        warning: 'from-yellow-500 to-orange-500',
        info: 'from-blue-600 to-green-500'
    };

    const icons = {
        success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
        error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
        warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
    };

    modal.innerHTML = `
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm" onclick="this.parentElement.remove()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform" style="animation: scaleIn 0.2s ease-out;">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-r ${iconColors[type]} flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${icons[type]}
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">${title}</h3>
                </div>
                <p class="text-gray-600 mb-6 ml-16">${message}</p>
                <div class="flex justify-end">
                    <button onclick="this.closest('.fixed').remove()"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                        {{ __('OK') }}
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Add CSS animations if not already present
    if (!document.getElementById('modal-animations')) {
        const style = document.createElement('style');
        style.id = 'modal-animations';
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes scaleIn {
                from { transform: scale(0.9); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
}

function showMessageRequestModal(designerName, onConfirm) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4';
    modal.style.animation = 'fadeIn 0.2s ease-out';

    const defaultMessage = `{{ __("Hi") }} ${designerName}, {{ __("I'd like to connect with you!") }}`;

    modal.innerHTML = `
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform" style="animation: scaleIn 0.2s ease-out;">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">{{ __('Send Message Request') }}</h3>
                </div>
                <p class="text-gray-600 mb-4">{{ __('Include a message with your request to') }} ${designerName}:</p>
                <textarea
                    id="messageRequestText"
                    rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none text-gray-700"
                    placeholder="{{ __('Write your message...') }}"
                >${defaultMessage}</textarea>
                <p class="text-xs text-gray-400 mt-2 mb-4">{{ __('They will be able to accept or decline your request.') }}</p>
                <div class="flex gap-3 justify-end">
                    <button class="cancel-btn px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all">
                        {{ __('Cancel') }}
                    </button>
                    <button class="confirm-btn px-6 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                        {{ __('Send Request') }}
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Focus on textarea
    setTimeout(() => {
        const textarea = modal.querySelector('#messageRequestText');
        if (textarea) {
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        }
    }, 100);

    // Add event listeners
    const cancelBtn = modal.querySelector('.cancel-btn');
    const confirmBtn = modal.querySelector('.confirm-btn');
    const backdrop = modal.querySelector('.absolute');

    cancelBtn.addEventListener('click', () => modal.remove());
    backdrop.addEventListener('click', () => modal.remove());
    confirmBtn.addEventListener('click', () => {
        const messageText = modal.querySelector('#messageRequestText').value.trim();
        modal.remove();
        onConfirm(messageText);
    });
}

// Send Message Request functionality
async function sendMessageRequest(designerId) {
    const designerName = '{{ $designer->name ?? __("this designer") }}';

    showMessageRequestModal(designerName, async function(customMessage) {
        try {
            const response = await fetch('{{ url(app()->getLocale()) }}/messages/send-request/' + designerId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: customMessage
                })
            });

            const data = await response.json();

            if (data.success) {
                showModal('{{ __("Success!") }}', data.message || '{{ __("Message request sent successfully!") }}', 'success');
                // Update button to show pending state
                updateMessageButtonToPending();
            } else {
                showModal('{{ __("Error") }}', data.message || '{{ __("Failed to send message request.") }}', 'error');
            }
        } catch (error) {
            console.error('Error sending message request:', error);
            showModal('{{ __("Error") }}', '{{ __("An error occurred. Please try again.") }}', 'error');
        }
    });
}

// ==========================================
// Profile Rating Functions
// ==========================================

// Criteria loaded server-side (avoids extra AJAX round-trip)
const ratingCriteriaList = @json($ratingCriteria->map(fn($c) => [
    'id'    => $c->id,
    'label' => app()->getLocale() === 'ar' ? $c->ar_label : $c->en_label,
]));

function scrollToRatings() {
    const ratingsSection = document.getElementById('ratings-section');
    if (ratingsSection) {
        ratingsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function buildCriteriaHTML() {
    if (!ratingCriteriaList || ratingCriteriaList.length === 0) return '';
    return `
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('How was your experience?') }} <span class="text-xs text-gray-400">{{ __('(optional)') }}</span></label>
            <div class="space-y-2" id="criteriaCheckboxes">
                ${ratingCriteriaList.map(c => `
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:border-yellow-400 hover:bg-yellow-50 cursor-pointer transition-all group">
                        <input type="checkbox" value="${c.id}" name="criteria_ids[]"
                               class="criteria-checkbox w-4 h-4 rounded border-gray-300 text-yellow-500 focus:ring-yellow-400 cursor-pointer">
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">${c.label}</span>
                    </label>
                `).join('')}
            </div>
        </div>
    `;
}

function showRatingModal(isUpdate = false) {
    const modal = document.createElement('div');
    modal.id = 'ratingModal';
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4';
    modal.style.animation = 'fadeIn 0.2s ease-out';

    const designerName = '{{ $designer->name ?? __("this designer") }}';
    const designerId = {{ $designer->id }};

    modal.innerHTML = `
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform max-h-[90vh] overflow-y-auto" style="animation: scaleIn 0.2s ease-out;">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">${isUpdate ? '{{ __("Update Your Rating") }}' : '{{ __("Rate") }} ' + designerName}</h3>
                </div>

                <!-- Star Rating -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Your Rating') }}</label>
                    <div class="flex gap-2" id="starRating">
                        ${[1,2,3,4,5].map(i => `
                            <button type="button" onclick="setRating(${i})" class="star-btn p-1 transition-transform hover:scale-110 focus:outline-none" data-rating="${i}">
                                <svg class="w-10 h-10 text-gray-300 hover:text-yellow-400 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        `).join('')}
                    </div>
                    <input type="hidden" id="selectedRating" value="0">
                </div>

                <!-- Criteria Checkboxes -->
                ${buildCriteriaHTML()}

                <!-- Comment -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Your Review') }} <span class="text-red-500">*</span></label>
                    <textarea
                        id="ratingComment"
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 resize-none text-gray-700"
                        placeholder="{{ __('Share your experience with this designer... (minimum 10 characters)') }}"
                        minlength="10"
                        maxlength="500"
                    ></textarea>
                    <p class="text-xs text-gray-400 mt-1"><span id="charCount">0</span>/500 {{ __('characters') }}</p>
                </div>

                <div class="flex gap-3 justify-end">
                    <button onclick="closeRatingModal()" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all">
                        {{ __('Cancel') }}
                    </button>
                    <button onclick="submitRating(${designerId}, ${isUpdate})" id="submitRatingBtn" class="px-6 py-2.5 bg-gradient-to-r from-yellow-400 to-orange-500 text-white rounded-lg font-semibold hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        ${isUpdate ? '{{ __("Update Rating") }}' : '{{ __("Submit Rating") }}'}
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Add event listeners
    const backdrop = modal.querySelector('.absolute');
    backdrop.addEventListener('click', closeRatingModal);

    // Character count
    const textarea = document.getElementById('ratingComment');
    const charCount = document.getElementById('charCount');
    textarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
        validateRatingForm();
    });

    // If updating, fetch existing rating (includes pre-checked criteria)
    if (isUpdate) {
        fetchExistingRating(designerId);
    }
}

function closeRatingModal() {
    const modal = document.getElementById('ratingModal');
    if (modal) modal.remove();
}

function setRating(rating) {
    document.getElementById('selectedRating').value = rating;

    // Update star visuals
    const stars = document.querySelectorAll('#starRating .star-btn svg');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });

    validateRatingForm();
}

function validateRatingForm() {
    const rating = parseInt(document.getElementById('selectedRating').value);
    const comment = document.getElementById('ratingComment').value.trim();
    const submitBtn = document.getElementById('submitRatingBtn');

    if (rating >= 1 && rating <= 5 && comment.length >= 10) {
        submitBtn.disabled = false;
    } else {
        submitBtn.disabled = true;
    }
}

async function fetchExistingRating(designerId) {
    try {
        const response = await fetch(`{{ url(app()->getLocale()) }}/designer/${designerId}/my-rating`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success && data.rating) {
            setRating(data.rating.rating);
            document.getElementById('ratingComment').value = data.rating.comment || '';
            document.getElementById('charCount').textContent = (data.rating.comment || '').length;

            // Pre-check previously selected criteria
            if (data.rating.criteria_ids && data.rating.criteria_ids.length > 0) {
                document.querySelectorAll('.criteria-checkbox').forEach(checkbox => {
                    if (data.rating.criteria_ids.includes(parseInt(checkbox.value))) {
                        checkbox.checked = true;
                        checkbox.closest('label').classList.add('border-yellow-400', 'bg-yellow-50');
                    }
                });
            }

            validateRatingForm();
        }
    } catch (error) {
        console.error('Error fetching existing rating:', error);
    }
}

async function submitRating(designerId, isUpdate) {
    const rating = parseInt(document.getElementById('selectedRating').value);
    const comment = document.getElementById('ratingComment').value.trim();

    if (rating < 1 || rating > 5) {
        showModal('{{ __("Error") }}', '{{ __("Please select a rating (1-5 stars)") }}', 'error');
        return;
    }

    if (comment.length < 10) {
        showModal('{{ __("Error") }}', '{{ __("Please write a review with at least 10 characters") }}', 'error');
        return;
    }

    const submitBtn = document.getElementById('submitRatingBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>{{ __("Submitting...") }}';

    // Collect checked criteria IDs
    const criteriaIds = Array.from(
        document.querySelectorAll('.criteria-checkbox:checked')
    ).map(cb => parseInt(cb.value));

    try {
        const url = `{{ url(app()->getLocale()) }}/designer/${designerId}/rate`;
        const method = isUpdate ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ rating, comment, criteria_ids: criteriaIds })
        });

        const data = await response.json();

        closeRatingModal();

        if (data.success) {
            showModal('{{ __("Success!") }}', data.message || '{{ __("Your rating has been submitted!") }}', 'success');
            // Reload page to show updated rating
            setTimeout(() => location.reload(), 1500);
        } else {
            showModal('{{ __("Error") }}', data.message || '{{ __("Failed to submit rating.") }}', 'error');
        }
    } catch (error) {
        console.error('Error submitting rating:', error);
        closeRatingModal();
        showModal('{{ __("Error") }}', '{{ __("An error occurred. Please try again.") }}', 'error');
    }
}
</script>
