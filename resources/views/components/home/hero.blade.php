@props(['stats', 'badgeCounter' => null, 'statsCounters' => null])

@php
    $heroImages = \App\Models\SiteSetting::getHeroImages('home');
    if (empty($heroImages)) {
        $heroImages = [url('images/hero-bg.jpg')];
    }
@endphp

<section class="relative overflow-hidden" x-data="{
    images: @js($heroImages),
    currentIndex: 0,
    interval: null,
    init() {
        if (this.images.length > 1) {
            this.startCarousel();
        }
    },
    startCarousel() {
        this.interval = setInterval(() => {
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
        }, 5000);
    },
    destroy() {
        if (this.interval) clearInterval(this.interval);
    }
}" x-init="init()" @destroy.window="destroy()">
    {{-- Background Image Carousel with Gradient Overlay --}}
    <div class="absolute inset-0">
        <template x-for="(image, index) in images" :key="index">
            <img
                :src="image"
                alt="{{ __('Palestine Creative Hub') }}"
                class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000"
                :class="currentIndex === index ? 'opacity-100' : 'opacity-0'"
                onerror="this.style.display='none'"
            />
        </template>
        {{-- Gradient overlay for better text readability --}}
        <div class="absolute inset-0 bg-gradient-to-r from-white/80 via-white/70 to-white/60"></div>
    </div>

    <div class="relative max-w-[1440px] mx-auto px-4 sm:px-6 py-12 sm:py-16 md:py-28">
        <div class="max-w-3xl mx-auto text-center">
            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-sm mb-6 animate-fadeIn">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                <span class="text-sm text-gray-700 font-medium">
                    {{ __('Join') }} {{ number_format($badgeCounter['count'] ?? $stats['designers'] ?? 0) }}+ {{ $badgeCounter['label'] ?? __('creative professionals') }}
                </span>
            </div>

            {{-- Headline --}}
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-7xl mb-4 sm:mb-6 bg-gradient-to-r from-gray-900 via-blue-900 to-teal-900 bg-clip-text text-transparent animate-slideUp">
                {{ \App\Models\SiteSetting::getHeroTitle('home', 'Showcase Your Creative Excellence') }}
            </h1>

            {{-- Subheadline --}}
            <p class="text-base sm:text-lg md:text-xl text-gray-600 mb-6 sm:mb-8 max-w-2xl mx-auto leading-relaxed animate-slideUp" style="animation-delay: 0.1s;">
                {{ \App\Models\SiteSetting::getHeroSubtitle('home', 'The ultimate platform for designers, MSMEs, and creative industries to connect, collaborate, and grow their business') }}
            </p>

            {{-- CTA Buttons --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 mb-10 sm:mb-16 animate-slideUp" style="animation-delay: 0.2s;">
                <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                    {{ __('Get Started Free') }}
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="#featured-projects" class="inline-flex items-center px-8 py-4 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:border-blue-500 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    {{ __('Browse Work') }}
                </a>
            </div>

            {{-- Stats --}}
            @if($statsCounters && count($statsCounters) > 0)
                {{-- Dynamic Stats Counters from CMS --}}
                @php
                    $colCount = min(count($statsCounters), 6);
                    $gridClass = match($colCount) {
                        1 => 'grid-cols-1',
                        2 => 'grid-cols-2',
                        3 => 'grid-cols-3',
                        4 => 'grid-cols-2 md:grid-cols-4',
                        5 => 'grid-cols-2 md:grid-cols-3 lg:grid-cols-5',
                        6 => 'grid-cols-2 md:grid-cols-3 lg:grid-cols-6',
                        default => 'grid-cols-3',
                    };
                @endphp
                <div class="grid {{ $gridClass }} gap-4 sm:gap-8 max-w-4xl mx-auto animate-fadeIn" style="animation-delay: 0.3s;">
                    @foreach($statsCounters as $counter)
                        <div>
                            <div class="text-2xl sm:text-3xl md:text-4xl mb-1 bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent font-bold">
                                {{ number_format($counter['count'] ?? 0) }}+
                            </div>
                            <div class="text-sm text-gray-600">{{ $counter['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Fallback to legacy stats --}}
                <div class="grid grid-cols-3 gap-4 sm:gap-8 max-w-2xl mx-auto animate-fadeIn" style="animation-delay: 0.3s;">
                    <div>
                        <div class="text-3xl md:text-4xl mb-1 bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent font-bold">
                            {{ number_format($stats['products'] ?? 0) }}+
                        </div>
                        <div class="text-sm text-gray-600">{{ __('Products') }}</div>
                    </div>
                    <div>
                        <div class="text-3xl md:text-4xl mb-1 bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent font-bold">
                            {{ number_format($stats['projects'] ?? 0) }}+
                        </div>
                        <div class="text-sm text-gray-600">{{ __('Projects') }}</div>
                    </div>
                    <div>
                        <div class="text-3xl md:text-4xl mb-1 bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent font-bold">
                            {{ number_format($stats['companies'] ?? 0) }}+
                        </div>
                        <div class="text-sm text-gray-600">{{ __('Manufacturers and Showrooms') }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.8s ease-out forwards;
}

.animate-slideUp {
    animation: slideUp 0.8s ease-out forwards;
}
</style>
