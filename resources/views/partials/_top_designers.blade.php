{{-- Top Designers Section - Based on Figma TopDesigners.tsx --}}
<section class="py-10 sm:py-16 bg-white">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div class="animate-on-load animate-slideInLeft">
                <h2 class="text-2xl sm:text-3xl md:text-4xl mb-2 text-gray-900">{{ __('Top Designers') }}</h2>
                <p class="text-gray-600">{{ __('Connect with talented creatives and MSMEs') }}</p>
            </div>
            <a href="{{ url(app()->getLocale() . '/designers') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-all hover-scale animate-on-load animate-slideInRight">
                {{ __('View All') }}
                <svg class="ml-2 w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($topDesigners as $index => $designer)
            {{-- Designer Card {{ $index + 1 }} --}}
            <div class="bg-white rounded-xl overflow-hidden shadow-sm cursor-pointer hover-lift animate-on-load animate-fadeInUp delay-{{ ($index + 1) * 100 }}">
                {{-- Cover Image --}}
                <div class="relative h-32 bg-gradient-to-br from-blue-500 to-green-400">
                </div>

                {{-- Content --}}
                <div class="p-6 pt-0">
                    {{-- Avatar --}}
                    <div class="flex justify-center -mt-12 mb-4">
                        <div class="w-24 h-24 border-4 border-white shadow-lg rounded-full overflow-hidden bg-gray-200">
                            @if($designer->avatar)
                                <img src="{{ $designer->avatar }}" alt="{{ $designer->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-600 text-2xl font-medium">
                                    {{ strtoupper(substr($designer->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Name & Bio --}}
                    <div class="text-center mb-4">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <h3 class="text-xl text-gray-900">{{ $designer->name }}</h3>
                        </div>
                        @if($designer->bio)
                        <p class="text-sm text-gray-600 line-clamp-2">{{ $designer->bio }}</p>
                        @endif
                        @if($designer->location)
                        <p class="text-xs text-gray-500 mt-1">{{ $designer->location }}</p>
                        @endif
                    </div>

                    {{-- Stats --}}
                    <div class="flex items-center justify-center gap-6 mb-4 pb-4 border-b border-gray-200">
                        <div class="text-center">
                            <div class="text-lg text-gray-900">{{ $designer->projects_count }}</div>
                            <div class="text-xs text-gray-600">{{ __('Projects') }}</div>
                        </div>
                    </div>

                    {{-- Skills --}}
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($designer->skills->take(3) as $skill)
                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-md">{{ $skill->name }}</span>
                        @endforeach
                    </div>

                    {{-- View Profile Button --}}
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="w-full bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-700 hover:to-green-600 text-white px-4 py-2 rounded-md transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ __('View Profile') }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
