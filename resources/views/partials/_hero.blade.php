{{-- Hero Section - Based on Figma Design --}}
@php
    $heroImage = \App\Models\SiteSetting::getHeroImage('home') ?? url('images/hero-bg.jpg');
@endphp
<section class="relative overflow-hidden min-h-[420px] sm:min-h-[500px] md:min-h-[600px]">
    {{-- Background Media --}}
    <div class="absolute inset-0 z-0">
        @if(preg_match('/\.(mp4|webm|mov)$/i', $heroImage))
            <video src="{{ $heroImage }}" class="w-full h-full object-cover animate-scaleIn" autoplay muted loop playsinline></video>
        @else
            <img src="{{ $heroImage }}" alt="{{ __('Palestine Creative Hub') }}" class="w-full h-full object-cover animate-scaleIn">
        @endif
        {{-- Overlay for better text readability --}}
        <div class="absolute inset-0 bg-gradient-to-r from-white/80 via-white/70 to-white/60"></div>
    </div>

    <div class="relative z-10 max-w-[1440px] mx-auto px-4 sm:px-6 py-12 sm:py-16 md:py-28">
        <div class="max-w-3xl mx-auto text-center">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-sm mb-6 animate-on-load animate-fadeIn delay-100">
                <svg class="w-4 h-4 text-blue-600 animate-pulse-subtle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                <span class="text-sm text-gray-700">
                    {{ __('Join') }} 50,000+ {{ __('creative professionals') }}
                </span>
            </div>

            {{-- Headline --}}
            <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-7xl leading-relaxed mb-4 sm:mb-6 pb-1 bg-gradient-to-r from-gray-900 via-blue-900 to-teal-900 bg-clip-text text-transparent animate-on-load animate-fadeInUp delay-200">
                {{ __('Showcase Your Creative Excellence') }}
            </h2>

            {{-- Subheadline --}}
            <p class="text-base sm:text-lg md:text-xl text-gray-600 mb-6 sm:mb-8 max-w-2xl mx-auto animate-on-load animate-fadeInUp delay-300">
                {{ __('The ultimate platform for designers, MSMEs, and creative industries to connect, collaborate, and grow their business') }}
            </p>

            {{-- CTA Buttons --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 animate-on-load animate-fadeInUp delay-400">
                <a href="{{ url(app()->getLocale() . '/register') }}" class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-700 hover:to-green-600 rounded-lg transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1 hover-glow">
                    {{ __('Get Started Free') }}
                    <svg class="ml-2 w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                <a href="{{ url(app()->getLocale() . '/projects') }}" class="inline-flex items-center px-6 py-3 text-base font-medium text-gray-700 bg-white hover:bg-gray-50 border-2 border-gray-200 rounded-lg transition-all hover-scale">
                    {{ __('Browse Work') }}
                </a>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-4 sm:gap-8 mt-10 sm:mt-16 max-w-2xl mx-auto">
                <div class="animate-on-load animate-fadeInUp delay-500">
                    <div class="text-2xl sm:text-3xl md:text-4xl mb-1 bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                        50K+
                    </div>
                    <div class="text-sm text-gray-600">{{ __('Creatives') }}</div>
                </div>
                <div class="animate-on-load animate-fadeInUp delay-600">
                    <div class="text-2xl sm:text-3xl md:text-4xl mb-1 bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                        200K+
                    </div>
                    <div class="text-sm text-gray-600">{{ __('Projects') }}</div>
                </div>
                <div class="animate-on-load animate-fadeInUp delay-700">
                    <div class="text-2xl sm:text-3xl md:text-4xl mb-1 bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                        15K+
                    </div>
                    <div class="text-sm text-gray-600">{{ __('Companies') }}</div>
                </div>
            </div>

        </div>
    </div>
</section>
