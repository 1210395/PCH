@props(['designer'])

@php
    $isGuest = $designer->sector === 'guest' || $designer->sub_sector === 'Guest';
@endphp

@if($isGuest)
<div class="bg-gradient-to-r from-blue-50 via-blue-100 to-green-50 border-l-4 border-blue-600 rounded-lg shadow-sm mb-6 overflow-hidden">
    <div class="p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-green-500 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ __('Complete Your Profile') }}</h3>
                <p class="text-sm text-gray-700 mb-3">
                    {{ __('You\'re currently browsing as a') }} <span class="font-semibold">{{ __('Guest') }}</span>. {{ __('Complete your profile to unlock all features:') }}
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ __('Showcase projects & products') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ __('Get discovered by clients') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ __('Connect with designers') }}</span>
                    </div>
                </div>
            </div>

            <div class="w-full sm:w-auto flex-shrink-0">
                <a href="{{ route('register', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    {{ __('Complete Profile') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endif
