@props(['designer', 'services', 'isOwner'])

<!-- Services Tab - REDESIGNED -->
<div id="services-tab" class="tab-content">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
        @if($designer->services->count() > 0)
        @foreach($designer->services as $service)
        <!-- Service Card - NEW DESIGN -->
        <div class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 relative p-4 sm:p-6">
            <!-- Service Icon -->
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center mb-3 sm:mb-4">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>

            <!-- Service Info -->
            <div>
                @if($service->category)
                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 mb-3">
                    {{ $service->category }}
                </span>
                @endif
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3">{{ $service->name }}</h3>
                <p class="text-sm sm:text-base text-gray-600 leading-relaxed">{{ $service->description }}</p>
            </div>
        </div>
        @endforeach
        @endif

        <!-- Add New Service Card (Owner Only) -->
        @if($isOwner)
        <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}#services" class="group bg-white rounded-xl border-2 border-dashed border-gray-300 hover:border-blue-500 shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer flex items-center justify-center min-h-[200px]">
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 group-hover:text-blue-600 transition-colors">{{ __('Add New Service') }}</h3>
                <p class="text-sm text-gray-500 mt-2">{{ __('Click to create a new service') }}</p>
            </div>
        </a>
        @endif
    </div>
</div>
