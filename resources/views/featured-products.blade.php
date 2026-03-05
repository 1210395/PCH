@props(['products'])

<section id="featured-products" class="py-10 sm:py-12 bg-white">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl mb-2 text-gray-900">Featured Products</h2>
                <p class="text-gray-600">Explore amazing products from manufacturers and showrooms</p>
            </div>
            <a href="{{ route('products', ['locale' => app()->getLocale()]) }}" class="hidden md:inline-flex items-center text-blue-600 hover:text-blue-700 font-medium transition-colors">
                View All Products
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>

        @if($products && $products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 auto-rows-fr">
                @foreach($products as $product)
                    <x-home.product-card :product="$product" />
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ __('No Products Yet') }}</h3>
                <p class="text-gray-600 mb-6">{{ __('Showcase your products to the community!') }}</p>
                <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-200">
                    {{ __('Start Showcasing') }}
                </a>
            </div>
        @endif

        {{-- Mobile "View All" button --}}
        @if($products && $products->count() > 0)
            <div class="mt-8 text-center md:hidden">
                <a href="{{ route('products', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                    View All Products
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</section>
