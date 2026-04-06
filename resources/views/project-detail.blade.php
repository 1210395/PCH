@extends('layout.main')

@php
    // UTF-8 sanitization using helper
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];
    $headTitle = $sanitize($project->title ?? '');
    $headDescription = $sanitize($project->description ?? '');
    $headCategory = $sanitize($project->category ?? '');
    $headDesignerName = $sanitize($project->designer?->name ?? '');
@endphp
@php
    $ogImage = $project->images->first() ? url('media/' . $project->images->first()->image_path) : url('media/images/logo.png');
@endphp

@section('head')
<title>{{ $headTitle }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ Str::limit($headDescription, 160) }}">
<meta name="keywords" content="project, {{ $headCategory }}, {{ $headDesignerName }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta property="og:title" content="{{ $headTitle }} - {{ config('app.name') }}">
<meta property="og:description" content="{{ Str::limit($headDescription, 160) }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:type" content="article">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $headTitle }}">
<meta name="twitter:description" content="{{ Str::limit($headDescription, 160) }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@section('content')
@php
    // Use centralized UTF-8 sanitization helper
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];

    $designer = $project->designer;
    $designerAvatar = $designer && $designer->avatar ? url('media/' . $designer->avatar) : null;

    // Handle images
    if (is_object($project->images) && method_exists($project->images, 'count')) {
        $images = $project->images;
    } else {
        $imagesArray = is_string($project->images) ? json_decode($project->images, true) : (is_array($project->images) ? $project->images : []);
        $images = collect($imagesArray);
    }

    // Build images array for Alpine.js
    $imageUrls = [];
    foreach($images as $image) {
        $imagePath = is_object($image) && isset($image->image_path) ? $image->image_path : $image;
        $imageUrls[] = url('media/' . $imagePath);
    }

    $firstImage = count($imageUrls) > 0 ? $imageUrls[0] : null;

    // Handle category
    $categoryName = null;
    if (is_string($project->category) && !empty($project->category)) {
        $categoryName = $sanitize($project->category);
    } elseif ($project->category_id && method_exists($project, 'category')) {
        $categoryRelation = $project->category;
        if ($categoryRelation && isset($categoryRelation->name)) {
            $categoryName = $sanitize($categoryRelation->name);
        }
    }

    // Sanitize project text fields for safe display
    $projectTitle = $sanitize($project->title);
    $projectDescription = $sanitize($project->description);
    $projectRole = $sanitize($project->role);
    $designerName = $sanitize($designer?->name ?? '');
    $designerTitle = $sanitize($designer?->title ?? 'Creative Professional');
    $designerCity = $sanitize($designer?->city ?? '');

    // Check if user has liked this project
    $hasLiked = false;
    if (auth('designer')->check()) {
        $hasLiked = \App\Models\Like::where('designer_id', auth('designer')->id())
            ->where('likeable_type', 'App\Models\Project')
            ->where('likeable_id', $project->id)
            ->exists();
    }
@endphp

<div class="min-h-screen bg-gray-50" x-data="{
    images: {{ json_encode($imageUrls, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) }},
    currentIndex: 0,
    showContactModal: false,
    liked: {{ $hasLiked ? 'true' : 'false' }},
    likesCount: {{ $project->likes_count ?? 0 }},
    get activeImage() { return this.images[this.currentIndex] || ''; },
    get totalImages() { return this.images.length; },
    next() { if (this.totalImages > 1) this.currentIndex = (this.currentIndex + 1) % this.totalImages; },
    prev() { if (this.totalImages > 1) this.currentIndex = (this.currentIndex - 1 + this.totalImages) % this.totalImages; },
    goTo(index) { this.currentIndex = index; },
    async toggleLike() {
        @auth('designer')
        try {
            const response = await fetch('{{ route('project.like', ['locale' => app()->getLocale(), 'id' => $project->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                this.liked = data.liked;
                this.likesCount = data.likes_count;
            }
        } catch (error) {
            console.error('Error toggling like:', error);
        }
        @else
        window.location.href = '{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}';
        @endauth
    }
}">
    {{-- Breadcrumb --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="flex items-center gap-2 text-sm">
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Home') }}</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('projects', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Projects') }}</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium truncate max-w-[200px]">{{ $project->title }}</span>
            </nav>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">

            {{-- Left Column - Image Gallery --}}
            <div class="space-y-4">
                {{-- Main Image with Navigation --}}
                <div class="relative bg-gray-100 rounded-2xl overflow-hidden aspect-[4/3] group/gallery">
                    @if($firstImage)
                    <img
                        :src="activeImage"
                        alt="{{ $project->title }}"
                        class="w-full h-full object-cover transition-opacity duration-300 cursor-pointer"
                        @click="$dispatch('open-lightbox', { images: images, index: currentIndex })"
                        onerror="this.parentElement.classList.add('bg-gradient-to-br', 'from-blue-600', 'to-green-500'); this.style.display='none';"
                    >

                    {{-- Navigation Arrows - Center with Blue-Green Gradient --}}
                    <template x-if="totalImages > 1">
                        <div>
                            {{-- Previous Arrow - Left Center --}}
                            <button
                                @click="prev()"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-gradient-to-r from-blue-600 to-green-500 shadow-lg flex items-center justify-center text-white hover:shadow-xl hover:shadow-blue-500/30 hover:scale-110 transition-all z-10"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>

                            {{-- Next Arrow - Right Center --}}
                            <button
                                @click="next()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-gradient-to-r from-blue-600 to-green-500 shadow-lg flex items-center justify-center text-white hover:shadow-xl hover:shadow-blue-500/30 hover:scale-110 transition-all z-10"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            {{-- Image Counter - Bottom Right --}}
                            <div class="absolute bottom-4 right-4 px-3 py-1.5 bg-black/60 text-white text-sm font-medium rounded-full z-10">
                                <span x-text="(currentIndex + 1) + ' / ' + totalImages"></span>
                            </div>
                        </div>
                    </template>
                    @else
                    {{-- No Image Placeholder --}}
                    <div class="w-full h-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center">
                        <svg class="w-20 h-20 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    @endif
                </div>

                {{-- Thumbnail Strip --}}
                @if(count($imageUrls) > 1)
                <div class="flex justify-center gap-3 overflow-x-auto py-3 px-2 mt-6">
                    @foreach($imageUrls as $index => $url)
                    <button
                        @click="goTo({{ $index }})"
                        :class="currentIndex === {{ $index }} ? 'ring-2 ring-blue-500 ring-offset-2' : 'opacity-70 hover:opacity-100'"
                        class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden transition-all"
                    >
                        <img src="{{ $url }}" alt="Thumbnail {{ $index + 1 }}" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                @endif

                {{-- Dots Indicator (mobile-friendly) --}}
                @if(count($imageUrls) > 1)
                <div class="flex justify-center gap-3 lg:hidden mt-4">
                    @foreach($imageUrls as $index => $url)
                    <button
                        @click="goTo({{ $index }})"
                        :class="currentIndex === {{ $index }} ? 'bg-blue-600 w-6' : 'bg-gray-300 w-2'"
                        class="h-2 rounded-full transition-all duration-300"
                    ></button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Right Column - Project Info --}}
            <div class="space-y-5">
                {{-- Title & Category --}}
                <div>
                    <div class="flex flex-wrap gap-2 mb-3">
                        @if($categoryName)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            {{ $categoryName }}
                        </span>
                        @endif
                        @if($project->role)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                            {{ $project->role }}
                        </span>
                        @endif
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $project->title }}</h1>
                </div>

                {{-- Date --}}
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $project->created_at->format('M Y') }}</span>
                    </div>
                </div>

                {{-- Designer Info --}}
                <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-100">
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="flex items-center gap-3 group">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex items-center justify-center text-white font-bold shadow-md">
                            @if($designerAvatar)
                                <img src="{{ $designerAvatar }}" alt="{{ $designer->name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($designer->name, 0, 2)) }}
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $designer->name }}</span>
                                @if($designer->email_verified_at)
                                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500">{{ $designer->title ?? __('Creative Professional') }}</p>
                        </div>
                    </a>
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                        {{ __('View Profile') }}
                    </a>
                </div>

                {{-- Description --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ __('About This Project') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-wrap">{{ $project->description }}</p>
                </div>

                {{-- Project Details Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @if($categoryName)
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <div class="text-xs text-gray-500 mb-0.5">{{ __('Category') }}</div>
                        <div class="font-medium text-gray-900 text-sm">{{ $categoryName }}</div>
                    </div>
                    @endif
                    @if($project->role)
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <div class="text-xs text-blue-600 mb-0.5">{{ __('Role') }}</div>
                        <div class="font-medium text-blue-900 text-sm">{{ $project->role }}</div>
                    </div>
                    @endif
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <div class="text-xs text-gray-500 mb-0.5">{{ __('Year') }}</div>
                        <div class="font-medium text-gray-900 text-sm">{{ $project->created_at->format('Y') }}</div>
                    </div>
                    @if($designer->city)
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <div class="text-xs text-gray-500 mb-0.5">{{ __('Location') }}</div>
                        <div class="font-medium text-gray-900 text-sm">{{ $designer->city }}</div>
                    </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    {{-- Like Button --}}
                    <button @click="toggleLike()" class="flex-1 flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold transition-all duration-200"
                        :class="liked ? 'bg-red-50 text-red-600 border-2 border-red-200' : 'bg-white text-gray-700 border-2 border-gray-200 hover:border-gray-300'">
                        <svg class="w-5 h-5" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span x-text="liked ? 'Liked' : 'Like'"></span>
                    </button>

                    {{-- Contact Button --}}
                    @auth('designer')
                        @if(auth('designer')->id() !== $designer->id)
                        <button @click="showContactModal = true" class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            {{ __('Contact') }}
                        </button>
                        @endif
                    @else
                        <a href="{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            {{ __('Contact') }}
                        </a>
                    @endauth

                    {{-- Share Button --}}
                    <x-share-button
                        :url="route('project.detail', ['locale' => app()->getLocale(), 'id' => $project->id])"
                        :title="$project->title"
                        :description="Str::limit($project->description, 150)"
                        variant="icon-only"
                        size="md"
                    />
                </div>
            </div>
        </div>

        {{-- Additional Images Gallery (if more than 2 images) --}}
        @if(count($imageUrls) > 2)
        <div class="mt-10 pt-8 border-t border-gray-200">
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('More Images') }}</h2>
            <div class="grid grid-cols-2 gap-2 sm:gap-3 md:grid-cols-3 lg:grid-cols-4 md:gap-4">
                @foreach($imageUrls as $index => $url)
                    @if($index > 0)
                    <button
                        @click="goTo({{ $index }}); window.scrollTo({top: 0, behavior: 'smooth'})"
                        class="rounded-xl overflow-hidden bg-gray-100 aspect-square hover:opacity-90 transition-opacity"
                    >
                        <img src="{{ $url }}" alt="{{ $project->title }} - Image {{ $index + 1 }}" class="w-full h-full object-cover">
                    </button>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Related Projects --}}
    @if(isset($relatedProjects) && $relatedProjects->count() > 0)
    <section class="bg-white py-8 sm:py-12 border-t border-gray-100 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl lg:text-2xl font-bold text-gray-900">{{ __('More Projects') }}</h2>
                    <p class="text-gray-500 text-sm mt-1">{{ __('Explore similar creative work') }}</p>
                </div>
                <a href="{{ route('projects', ['locale' => app()->getLocale()]) }}" class="hidden md:inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    {{ __('View All') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 auto-rows-fr">
                @foreach($relatedProjects as $relatedProject)
                    <x-home.project-card :project="$relatedProject" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Contact Designer Modal --}}
    <div x-show="showContactModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showContactModal = false"></div>

        {{-- Modal --}}
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- Close Button --}}
            <button @click="showContactModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Modal Content --}}
            <div class="text-center">
                {{-- Designer Avatar --}}
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex items-center justify-center text-white text-xl font-bold shadow-lg">
                    @if($designerAvatar)
                        <img src="{{ $designerAvatar }}" alt="{{ $designer->name }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($designer->name, 0, 2)) }}
                    @endif
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Contact') }} {{ $designer->name }}?</h3>
                <p class="text-gray-600 text-sm mb-6">{{ __("You're about to start a conversation about this project.") }}</p>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <button @click="showContactModal = false" class="flex-1 px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <a href="{{ route('messages.compose', ['locale' => app()->getLocale(), 'designerId' => $designer->id]) }}" class="flex-1 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all text-center">
                        {{ __('Send Message') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<x-image-lightbox />
@endsection
