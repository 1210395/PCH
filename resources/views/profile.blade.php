@extends('layout.main')

@section('head')
<title>{{ $designer->name }} - {{ __('Profile') }} | {{ config('app.name') }}</title>
<meta name="description" content="{{ __('View') }} {{ $designer->name }} {{ __('profile on') }} {{ config('app.name') }}">
@endsection

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Cover Image -->
    <div class="relative h-48 sm:h-64 md:h-80 bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden">
        @if($designer->cover_image)
            <img
                src="{{ asset('storage/app/public/' . $designer->cover_image) }}"
                alt="Cover"
                class="w-full h-full object-cover opacity-30"
                loading="lazy"
            />
        @else
            <div class="w-full h-full bg-gradient-to-br from-blue-600 to-green-500"></div>
        @endif
    </div>

    <!-- Profile Section -->
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
        <div class="relative -mt-16 sm:-mt-20 md:-mt-24 mb-4 sm:mb-6 md:mb-8">
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8">
                <div class="flex flex-col md:flex-row gap-4 sm:gap-6">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 sm:w-28 sm:h-28 md:w-32 md:h-32 border-4 border-white shadow-xl rounded-full overflow-hidden">
                            @if($designer->avatar)
                                <img
                                    src="{{ asset('storage/app/public/' . $designer->avatar) }}"
                                    alt="{{ $designer->name }}"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                />
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white text-xl sm:text-2xl md:text-3xl font-bold">
                                    {{ strtoupper(substr($designer->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Profile Info -->
                    <div class="flex-1">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
                            <div>
                                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold mb-2">{{ $designer->name }}</h1>
                                <p class="text-sm sm:text-base md:text-lg text-gray-600 mb-3">
                                    {{ $designer->title ?? $designer->position }}
                                </p>
                                <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs sm:text-sm text-gray-600">
                                    @if($designer->location)
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $designer->location }}
                                    </div>
                                    @endif
                                    @if($designer->company_name)
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $designer->company_name }}
                                    </div>
                                    @endif
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ __('Member since') }} {{ $designer->created_at->format('Y') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 sm:px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors text-sm sm:text-base">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        {{ __('Logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="flex flex-wrap gap-4 sm:gap-6 md:gap-8 py-3 sm:py-4 border-t border-gray-200">
                            <div>
                                <div class="text-lg sm:text-xl md:text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                                    {{ number_format($designer->followers_count ?? 0) }}
                                </div>
                                <div class="text-xs sm:text-sm text-gray-600">{{ __('Followers') }}</div>
                            </div>
                            <div>
                                <div class="text-lg sm:text-xl md:text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                                    {{ number_format($projects->count()) }}
                                </div>
                                <div class="text-xs sm:text-sm text-gray-600">{{ __('Projects') }}</div>
                            </div>
                            <div>
                                <div class="text-lg sm:text-xl md:text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                                    {{ number_format($products->count()) }}
                                </div>
                                <div class="text-xs sm:text-sm text-gray-600">{{ __('Products') }}</div>
                            </div>
                            <div>
                                <div class="text-lg sm:text-xl md:text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                                    {{ number_format($services->count()) }}
                                </div>
                                <div class="text-xs sm:text-sm text-gray-600">{{ __('Services') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Tabs -->
        <div x-data="{ activeTab: 'work' }" class="mb-12">
            <!-- Tab Navigation -->
            <div class="bg-white rounded-lg shadow-sm mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex gap-4 sm:gap-8 px-4 sm:px-6" aria-label="Tabs">
                        <button
                            @click="activeTab = 'work'"
                            :class="activeTab === 'work' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            {{ __('Work') }}
                        </button>
                        <button
                            @click="activeTab = 'about'"
                            :class="activeTab === 'about' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            {{ __('About') }}
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Work Tab -->
            <div x-show="activeTab === 'work'" x-transition>
                <!-- Projects Section -->
                @if($projects->count() > 0)
                <div class="mb-8">
                    <h3 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">{{ __('Projects') }} ({{ $projects->count() }})</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        @foreach($projects as $project)
                        <div class="group bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-shadow cursor-pointer">
                            <!-- Project Image -->
                            <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                                @if($project->image)
                                    <img
                                        src="{{ asset('storage/app/public/' . $project->image) }}"
                                        alt="{{ $project->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        loading="lazy"
                                    />
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-blue-100 to-cyan-100 flex items-center justify-center text-gray-400">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                    <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between text-white">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm">{{ $project->likes ?? 0 }}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <span class="text-sm">{{ $project->views ?? 0 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Project Info -->
                            <div class="p-4">
                                @if($project->category)
                                <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full mb-2">
                                    {{ $project->category }}
                                </span>
                                @endif
                                <h3 class="text-lg font-semibold mb-2">{{ $project->title }}</h3>
                                @if($project->description)
                                <p class="text-sm text-gray-600 line-clamp-2">{{ $project->description }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Products Section -->
                @if($products->count() > 0)
                <div class="mb-8">
                    <h3 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">{{ __('Products') }} ({{ $products->count() }})</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        @foreach($products as $product)
                        <div class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-shadow">
                            <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                                @if($product->image)
                                    <img
                                        src="{{ asset('storage/app/public/' . $product->image) }}"
                                        alt="{{ $product->title }}"
                                        class="w-full h-full object-cover"
                                        loading="lazy"
                                    />
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-blue-100 to-cyan-100 flex items-center justify-center text-gray-400">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                @if($product->category)
                                <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full mb-2">
                                    {{ $product->category }}
                                </span>
                                @endif
                                <h3 class="text-lg font-semibold mb-2">{{ $product->title }}</h3>
                                @if($product->description)
                                <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $product->description }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Services Section -->
                @if($services->count() > 0)
                <div class="mb-8">
                    <h3 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">{{ __('Services') }} ({{ $services->count() }})</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        @foreach($services as $service)
                        <div class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-shadow">
                            <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                                @if($service->image)
                                    <img
                                        src="{{ asset('storage/app/public/' . $service->image) }}"
                                        alt="{{ $service->name }}"
                                        class="w-full h-full object-cover"
                                        loading="lazy"
                                    />
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-blue-100 to-cyan-100 flex items-center justify-center text-gray-400">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                @if($service->category)
                                <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full mb-2">
                                    {{ $service->category }}
                                </span>
                                @endif
                                <h3 class="text-lg font-semibold mb-2">{{ $service->name }}</h3>
                                @if($service->description)
                                <p class="text-sm text-gray-600 line-clamp-2">{{ $service->description }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($projects->count() === 0 && $products->count() === 0 && $services->count() === 0)
                <div class="bg-white rounded-lg p-6 sm:p-8 md:p-12 text-center shadow-sm">
                    <svg class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">{{ __('No work yet') }}</h3>
                    <p class="text-gray-500">{{ __('You haven\'t added any projects, products, or services yet.') }}</p>
                </div>
                @endif
            </div>

            <!-- About Tab -->
            <div x-show="activeTab === 'about'" x-transition>
                <div class="grid md:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
                    <div class="md:col-span-2 space-y-6">
                        <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm">
                            <h2 class="text-xl sm:text-2xl font-bold mb-4">{{ __('About Me') }}</h2>
                            @if($designer->bio)
                                <p class="text-gray-600 leading-relaxed whitespace-pre-wrap">{{ $designer->bio }}</p>
                            @else
                                <p class="text-gray-400 italic">{{ __('No bio added yet.') }}</p>
                            @endif
                        </div>

                        @if($designer->skills && $designer->skills->count() > 0)
                        <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm">
                            <h3 class="text-xl font-bold mb-4">{{ __('Skills & Expertise') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($designer->skills as $skill)
                                    <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                                        {{ $skill->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm">
                            <h3 class="text-xl font-bold mb-4">{{ __('Profile Info') }}</h3>
                            <div class="space-y-3">
                                @if($designer->sector)
                                <div>
                                    <div class="text-sm text-gray-500 mb-1">{{ __('Sector') }}</div>
                                    <div class="text-gray-900">{{ __(ucfirst($designer->sector)) }}</div>
                                </div>
                                @endif
                                @if($designer->sub_sector)
                                <div>
                                    <div class="text-sm text-gray-500 mb-1">{{ __('Specialization') }}</div>
                                    <div class="text-gray-900">{{ $designer->sub_sector }}</div>
                                </div>
                                @endif
                                @if($designer->years_of_experience)
                                <div>
                                    <div class="text-sm text-gray-500 mb-1">{{ __('Experience') }}</div>
                                    <div class="text-gray-900">{{ $designer->years_of_experience }}</div>
                                </div>
                                @endif
                                @if($designer->email)
                                <div>
                                    <div class="text-sm text-gray-500 mb-1">{{ __('Email') }}</div>
                                    <a href="mailto:{{ $designer->email }}" class="text-blue-600 hover:underline">
                                        {{ $designer->email }}
                                    </a>
                                </div>
                                @endif
                                @if($designer->website)
                                <div>
                                    <div class="text-sm text-gray-500 mb-1">{{ __('Website') }}</div>
                                    <a href="{{ $designer->website }}" target="_blank" class="text-blue-600 hover:underline">
                                        {{ $designer->website }}
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
