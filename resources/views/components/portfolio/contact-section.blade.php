@props(['designer'])

<!-- Contact Info - NEW DESIGN -->
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 shadow-sm">
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">{{ __('Contact Info') }}</h3>
        <div class="space-y-3">
            @if($designer->email && $designer->show_email)
            <div class="flex items-center gap-3 text-gray-700 text-sm sm:text-base">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="break-all">{{ $designer->email }}</span>
            </div>
            @endif
            @if($designer->website)
            <div class="flex items-center gap-3 text-gray-700 text-sm sm:text-base">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <a href="{{ $designer->website }}" target="_blank" class="text-blue-600 hover:underline break-all">
                    {{ $designer->website }}
                </a>
            </div>
            @endif
            @if($designer->city && $designer->show_location)
            <div class="flex items-center gap-3 text-gray-700 text-sm sm:text-base">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span>{{ $designer->city }}</span>
            </div>
            @endif
            @if($designer->phone_number && $designer->show_phone)
            <div class="flex items-center gap-3 text-gray-700 text-sm sm:text-base">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <span>{{ \App\Helpers\DropdownHelper::formatPhoneWithCountry($designer->phone_number, $designer->phone_country) }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
