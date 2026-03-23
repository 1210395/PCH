@extends('layout.main')

@section('head')
<title>{{ __('Search Results') }}: {{ $query }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ __('Search results for') }} {{ $query }}">
@endsection

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        {{-- Search Header --}}
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ __('Search Results') }}</h1>
            <p class="text-gray-600">{{ __('Found') }} <span class="font-semibold">{{ $totalResults }}</span> {{ __('results for') }} "<span class="font-semibold">{{ $query }}</span>"</p>
        </div>

        {{-- Tabs Navigation --}}
        <div class="mb-8" x-data="{ activeTab: 'all' }">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-4 sm:space-x-8 overflow-x-auto scrollbar-hide" aria-label="Tabs">
                    <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('All') }} ({{ $totalResults }})
                    </button>
                    <button @click="activeTab = 'designers'" :class="activeTab === 'designers' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Designers') }} ({{ $designers->count() }})
                    </button>
                    <button @click="activeTab = 'projects'" :class="activeTab === 'projects' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Projects') }} ({{ $projects->count() }})
                    </button>
                    <button @click="activeTab = 'products'" :class="activeTab === 'products' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Products') }} ({{ $products->count() }})
                    </button>
                </nav>
            </div>

            {{-- All Results Tab --}}
            <div x-show="activeTab === 'all'" class="mt-8 space-y-12">
                {{-- Designers Section --}}
                @if($designers->count() > 0)
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('Designers') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 auto-rows-fr">
                        @foreach($designers as $designer)
                            <x-home.designer-card :designer="$designer" />
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Projects Section --}}
                @if($projects->count() > 0)
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('Projects') }}</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6 auto-rows-fr">
                        @foreach($projects as $project)
                            <x-home.project-card :project="$project" :designer="$project->designer" />
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Products Section --}}
                @if($products->count() > 0)
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('Products') }}</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6 auto-rows-fr">
                        @foreach($products as $product)
                            <x-home.product-card :product="$product" :designer="$product->designer" />
                        @endforeach
                    </div>
                </div>
                @endif

                @if($totalResults === 0)
                <div class="text-center py-16">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No results found') }}</h3>
                    <p class="mt-2 text-gray-500">{{ __('Try different keywords') }}</p>
                </div>
                @endif
            </div>

            {{-- Designers Only Tab --}}
            <div x-show="activeTab === 'designers'" class="mt-8">
                @if($designers->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 auto-rows-fr">
                    @foreach($designers as $designer)
                        <x-home.designer-card :designer="$designer" />
                    @endforeach
                </div>
                @else
                <div class="text-center py-16">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No designers found') }}</h3>
                    <p class="mt-2 text-gray-500">{{ __('Try searching with different keywords') }}</p>
                </div>
                @endif
            </div>

            {{-- Projects Only Tab --}}
            <div x-show="activeTab === 'projects'" class="mt-8">
                @if($projects->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                    @foreach($projects as $project)
                        <x-home.project-card :project="$project" :designer="$project->designer" />
                    @endforeach
                </div>
                @else
                <div class="text-center py-16">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No projects found') }}</h3>
                    <p class="mt-2 text-gray-500">{{ __('Try searching with different keywords') }}</p>
                </div>
                @endif
            </div>

            {{-- Products Only Tab --}}
            <div x-show="activeTab === 'products'" class="mt-8">
                @if($products->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                    @foreach($products as $product)
                        <x-home.product-card :product="$product" :designer="$product->designer" />
                    @endforeach
                </div>
                @else
                <div class="text-center py-16">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No products found') }}</h3>
                    <p class="mt-2 text-gray-500">{{ __('Try searching with different keywords') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
