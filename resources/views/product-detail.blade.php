@extends('layout.main')

@php
    // UTF-8 sanitization for head section
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];
    $headTitle = $sanitize($product->name ?? $product->title ?? '');
    $headDescription = $sanitize($product->description ?? '');
    $headCategory = $sanitize($product->category ?? '');
    $headDesignerName = $sanitize($product->designer->name ?? '');
@endphp
@php
    $ogImage = $product->images->first() ? url('media/' . $product->images->first()->image_path) : url('media/images/logo.png');
@endphp

@section('head')
<title>{{ $headTitle }} - {{ config('app.name') }}</title>
<meta name="description" content="{{ Str::limit($headDescription, 160) }}">
<meta name="keywords" content="product, {{ $headCategory }}, {{ $headDesignerName }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta property="og:title" content="{{ $headTitle }} - {{ config('app.name') }}">
<meta property="og:description" content="{{ Str::limit($headDescription, 160) }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:type" content="product">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $headTitle }}">
<meta name="twitter:description" content="{{ Str::limit($headDescription, 160) }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<style>
    .gallery-main {
        aspect-ratio: 16/10;
    }
    .gallery-thumb {
        aspect-ratio: 1/1;
    }
    .gallery-thumb.active {
        ring: 2px;
        ring-color: #2563eb;
    }
</style>
@endsection

@section('content')
@php
    // UTF-8 sanitization helper
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];

    $designer = $product->designer;
    $designerAvatar = $designer && $designer->avatar ? url('media/' . $designer->avatar) : null;

    // Handle images
    if (is_object($product->images) && method_exists($product->images, 'count')) {
        $images = $product->images;
    } else {
        $imagesArray = is_string($product->images) ? json_decode($product->images, true) : (is_array($product->images) ? $product->images : []);
        $images = collect($imagesArray);
    }

    // Build images array for Alpine.js
    $imageUrls = [];
    foreach($images as $image) {
        $imagePath = is_object($image) && isset($image->image_path) ? $image->image_path : $image;
        $imageUrls[] = url('media/' . $imagePath);
    }

    $firstImage = count($imageUrls) > 0 ? $imageUrls[0] : null;

    // Sanitize text fields
    $productName = $sanitize($product->name ?? $product->title ?? '');
    $productDescription = $sanitize($product->description ?? '');
    $productCategory = $sanitize($product->category ?? '');
    $designerName = $sanitize($designer->name ?? '');
    $designerTitle = $sanitize($designer->title ?? 'Creative Professional');
    $designerCity = $sanitize($designer->city ?? '');

    // Check if user has liked this product
    $hasLiked = false;
    if (auth('designer')->check()) {
        $hasLiked = \App\Models\Like::where('designer_id', auth('designer')->id())
            ->where('likeable_type', 'App\Models\Product')
            ->where('likeable_id', $product->id)
            ->exists();
    }
@endphp

<div class="min-h-screen bg-gray-50">
    {{-- Breadcrumb --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center gap-2 text-sm">
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Home') }}</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('products', ['locale' => app()->getLocale()]) }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Products') }}</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium truncate max-w-[200px]">{{ $product->name ?? $product->title }}</span>
            </nav>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-12">

            {{-- Left Column - Image Gallery --}}
            <div class="space-y-4" x-data="{
                images: {{ json_encode($imageUrls, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) }},
                currentIndex: 0,
                get activeImage() { return this.images[this.currentIndex] || ''; },
                next() { this.currentIndex = (this.currentIndex + 1) % this.images.length; },
                prev() { this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length; },
                goTo(index) { this.currentIndex = index; }
            }">
                {{-- Main Image --}}
                <div class="relative bg-white rounded-2xl overflow-hidden shadow-lg group/gallery">
                    <div class="gallery-main">
                        <img
                            :src="activeImage"
                            alt="{{ $product->name ?? $product->title }}"
                            class="w-full h-full object-cover"
                            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 class=%27w-full h-full%27 viewBox=%270 0 24 24%27%3E%3C/svg%3E'; this.parentElement.classList.add('bg-gradient-to-br', 'from-blue-500', 'to-green-400');"
                        >
                    </div>

                    {{-- Navigation Arrows - Center with Blue-Green Gradient --}}
                    @if(count($imageUrls) > 1)
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
                        <span x-text="(currentIndex + 1) + ' / ' + images.length"></span>
                    </div>
                    @endif

                    {{-- Category Badge - Bottom Left --}}
                    @if($product->category)
                    <div class="absolute bottom-4 left-4 z-10">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold bg-white/90 backdrop-blur-sm text-gray-800 shadow-sm">
                            {{ $product->localized_category }}
                        </span>
                    </div>
                    @endif

                    {{-- Featured Badge - Top Right --}}
                    @if($product->featured)
                    <div class="absolute top-4 right-4 z-10">
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-semibold bg-gradient-to-r from-amber-400 to-orange-500 text-white shadow-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            {{ __('Featured') }}
                        </span>
                    </div>
                    @endif
                </div>

                {{-- Thumbnail Gallery --}}
                @if(count($imageUrls) > 1)
                <div class="flex justify-center gap-3 py-3 px-2 mt-6">
                    @foreach($imageUrls as $index => $imageUrl)
                        <button
                            @click="goTo({{ $index }})"
                            :class="currentIndex === {{ $index }} ? 'ring-2 ring-blue-500 ring-offset-2' : 'ring-1 ring-gray-200 hover:ring-gray-300'"
                            class="flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden bg-white transition-all duration-200"
                        >
                            <img src="{{ $imageUrl }}" alt="Thumbnail {{ $index + 1 }}" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Right Column - Product Info --}}
            <div class="space-y-6" x-data="{
                showContactModal: false,
                liked: {{ $hasLiked ? 'true' : 'false' }},
                likesCount: {{ $product->likes_count ?? 0 }},
                async toggleLike() {
                    @auth('designer')
                    try {
                        const response = await fetch('{{ route('product.like', ['locale' => app()->getLocale(), 'id' => $product->id]) }}', {
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
                {{-- Title --}}
                <div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-3">{{ $product->name ?? $product->title }}</h1>
                </div>

                {{-- Designer Card --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="flex items-center gap-4 group">
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex items-center justify-center text-white text-lg font-bold shadow-md group-hover:shadow-lg transition-shadow">
                                @if($designerAvatar)
                                    <img src="{{ $designerAvatar }}" alt="{{ $designer->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($designer->name, 0, 2)) }}
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $designer->name }}</span>
                                    @if($designer->email_verified_at)
                                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500">{{ $designer->title ?? __('Creative Professional') }}</p>
                            </div>
                        </a>
                        <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                            {{ __('View Profile') }}
                        </a>
                    </div>
                </div>

                {{-- Description --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('About This Product') }}</h2>
                    <p class="text-gray-600 leading-relaxed whitespace-pre-wrap">{{ $product->description }}</p>
                </div>

                {{-- Product Details --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Product Details') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if($product->category)
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('Category') }}</div>
                            <div class="font-medium text-gray-900">{{ $product->localized_category }}</div>
                        </div>
                        @endif

                        <div class="p-4 bg-gray-50 rounded-xl">
                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('Designer') }}</div>
                            <div class="font-medium text-gray-900">{{ $designer->name }}</div>
                        </div>

                        @if($designer->city)
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('Location') }}</div>
                            <div class="font-medium text-gray-900">{{ $designer->city }}</div>
                        </div>
                        @endif

                        <div class="p-4 bg-gray-50 rounded-xl">
                            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('Added') }}</div>
                            <div class="font-medium text-gray-900">{{ $product->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    {{-- Like Button --}}
                    <button @click="toggleLike()" class="flex-1 flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl font-semibold transition-all duration-200"
                        :class="liked ? 'bg-red-50 text-red-600 border-2 border-red-200' : 'bg-white text-gray-700 border-2 border-gray-200 hover:border-gray-300'">
                        <svg class="w-5 h-5" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span x-text="liked ? '{{ __('Liked') }}' : '{{ __('Like') }}'"></span>
                    </button>

                    @auth('designer')
                        @if(auth('designer')->id() !== $designer->id)
                        <button @click="showContactModal = true" class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            {{ __('Contact') }}
                        </button>
                        @endif
                    @else
                        <a href="{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            {{ __('Contact') }}
                        </a>
                    @endauth

                    <x-share-button
                        :url="route('product.detail', ['locale' => app()->getLocale(), 'id' => $product->id])"
                        :title="$product->name ?? $product->title"
                        :description="Str::limit($product->description, 150)"
                        variant="default"
                        size="md"
                    />
                </div>

                {{-- Contact Designer Modal --}}
                <div x-show="showContactModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                    {{-- Backdrop --}}
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showContactModal = false"></div>

                    {{-- Modal --}}
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-4 sm:p-6 transform transition-all"
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
                            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                                @if($designerAvatar)
                                    <img src="{{ $designerAvatar }}" alt="{{ $designer->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($designer->name, 0, 2)) }}
                                @endif
                            </div>

                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('Contact') }} {{ $designer->name }}?</h3>
                            <p class="text-gray-600 mb-6">{{ __("You're about to start a conversation with") }} {{ $designer->name }} {{ __('about this product.') }}</p>

                            {{-- Action Buttons --}}
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button @click="showContactModal = false" class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                                    {{ __('Cancel') }}
                                </button>
                                <a href="{{ route('messages.compose', ['locale' => app()->getLocale(), 'designerId' => $designer->id]) }}" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all text-center">
                                    {{ __('Send Message') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Related Products --}}
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
    <section class="bg-white py-10 sm:py-16 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ __('More Like This') }}</h2>
                    <p class="text-gray-500 mt-1">{{ __('Discover similar products you might love') }}</p>
                </div>
                <a href="{{ route('products', ['locale' => app()->getLocale(), 'category' => $product->category]) }}" class="hidden md:inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    {{ __('View All') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 auto-rows-fr">
                @foreach($relatedProducts as $relatedProduct)
                    <x-home.product-card :product="$relatedProduct" />
                @endforeach
            </div>
        </div>
    </section>
    @endif
</div>
@endsection
