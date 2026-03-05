@props(['projects'])

<section id="featured-projects" class="py-8 sm:py-12 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl mb-1 sm:mb-2 text-gray-900">{{ __('Featured Projects') }}</h2>
                <p class="text-gray-600">{{ __('Discover outstanding work from creative professionals') }}</p>
            </div>
            <a href="{{ route('projects', ['locale' => app()->getLocale()]) }}" class="hidden md:inline-flex items-center text-blue-600 hover:text-blue-700 font-medium transition-colors">
                {{ __('View All Projects') }}
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>

        @if($projects && $projects->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 auto-rows-fr">
                @foreach($projects as $project)
                    <x-home.project-card :project="$project" />
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ __('No Projects Yet') }}</h3>
                <p class="text-gray-600 mb-6">{{ __('Share your creative work with the community!') }}</p>
                <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-200">
                    {{ __('Start Showcasing') }}
                </a>
            </div>
        @endif

        {{-- Mobile "View All" button --}}
        @if($projects && $projects->count() > 0)
            <div class="mt-8 text-center md:hidden">
                <a href="{{ route('projects', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                    {{ __('View All Projects') }}
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</section>
