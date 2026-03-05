@props(['manufacturers'])

<section class="py-10 sm:py-16 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div>
                <h2 class="text-xl sm:text-3xl md:text-4xl mb-2 text-gray-900">Manufacturers & Showrooms</h2>
                <p class="text-gray-600">Discover quality products from trusted manufacturers</p>
            </div>
            <a href="{{ route('designers', ['locale' => app()->getLocale()]) }}" class="hidden md:inline-flex items-center px-4 py-2 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                View All
                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>

        @if($manufacturers && $manufacturers->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 auto-rows-fr">
                @foreach($manufacturers as $manufacturer)
                    <x-home.designer-card :designer="$manufacturer" />
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ __('No Manufacturers Yet') }}</h3>
                <p class="text-gray-600 mb-6">{{ __('Be the first manufacturer to showcase your products!') }}</p>
                <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-200">
                    {{ __('Join Now') }}
                </a>
            </div>
        @endif

        {{-- Mobile "View All" button --}}
        @if($manufacturers && $manufacturers->count() > 0)
            <div class="mt-8 text-center md:hidden">
                <a href="{{ route('designers', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                    View All Manufacturers
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</section>
