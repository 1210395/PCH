{{-- Featured Projects Section - Based on Figma FeaturedProjects.tsx --}}
<section class="py-10 sm:py-12 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div class="animate-on-load animate-slideInLeft">
                <h2 class="text-2xl sm:text-3xl md:text-4xl mb-2 text-gray-900">{{ __('Featured Projects') }}</h2>
                <p class="text-gray-600">{{ __('Discover outstanding work from creative professionals') }}</p>
            </div>
            <a href="{{ url(app()->getLocale() . '/projects') }}" class="text-blue-600 hover:text-blue-700 transition-all hover-scale animate-on-load animate-slideInRight">
                {{ __('View All Projects') }} →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($featuredProjects as $project)
            {{-- Project Card --}}
            <div class="group relative bg-white rounded-xl overflow-hidden shadow-sm cursor-pointer hover-lift">
                <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                    <img src="{{ $project->image }}" alt="{{ $project->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                    {{-- Hover overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    {{-- Featured Badge --}}
                    @if($project->featured)
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1 text-xs font-medium text-white bg-gradient-to-r from-blue-600 to-green-500 rounded-md">{{ __('Featured') }}</span>
                    </div>
                    @endif

                    {{-- Category Badge --}}
                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1 text-xs font-medium bg-white/90 backdrop-blur-sm text-gray-700 rounded-md">{{ $project->category->name ?? __('Uncategorized') }}</span>
                    </div>
                </div>

                <div class="p-4">
                    <h3 class="text-lg mb-3 text-gray-900 line-clamp-1 group-hover:text-blue-600 transition-colors">{{ $project->title }}</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden">
                            @if($project->designer && $project->designer->avatar)
                                <img src="{{ $project->designer->avatar }}" alt="{{ $project->designer->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-600 text-sm font-medium">
                                    {{ $project->designer ? strtoupper(substr($project->designer->name, 0, 1)) : '?' }}
                                </div>
                            @endif
                        </div>
                        <span class="text-sm text-gray-600">{{ $project->designer->name ?? __('Unknown') }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
