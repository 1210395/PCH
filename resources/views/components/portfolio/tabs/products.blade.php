@props(['designer', 'products', 'isOwner'])

<div id="products-tab" class="tab-content">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @if($designer->products->count() > 0)
        @foreach($designer->products as $product)
        @php
            $isRejected = $product->approval_status === 'rejected';
            $isPending = $product->approval_status === 'pending';
            // Only show rejected/pending items to owner
            if (!$isOwner && ($isRejected || $isPending)) {
                continue;
            }
        @endphp
        <!-- Product Card -->
        <a href="{{ route('product.detail', ['locale' => app()->getLocale(), 'id' => $product->id]) }}" class="group block bg-white rounded-xl border {{ $isRejected ? 'border-red-300' : ($isPending ? 'border-yellow-300' : 'border-gray-200') }} overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 relative">
            <!-- Product Image -->
            <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                @php
                    try {
                        $primaryImage = $product->images && $product->images->count() > 0 ? $product->images->where('is_primary', 1)->first() : null;
                        $imageUrl = $primaryImage ? asset('storage/' . $primaryImage->image_path) : asset('images/placeholder.jpg');
                    } catch (\Exception $e) {
                        $imageUrl = asset('images/placeholder.jpg');
                        \Log::error('Error loading product image', ['error' => $e->getMessage(), 'product_id' => $product->id]);
                    }
                @endphp
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" onerror="this.style.display='none'; this.parentElement.classList.add('bg-gradient-to-br', 'from-blue-600', 'to-green-500');"/>
                {{-- Hover overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            </div>

            {{-- Status Badge (Owner Only) --}}
            @if($isOwner && ($isRejected || $isPending))
                <div class="absolute top-3 right-3 z-10">
                    @if($isRejected)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-600 text-white">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            {{ __('Rejected') }}
                        </span>
                    @elseif($isPending)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-500 text-white">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('Pending') }}
                        </span>
                    @endif
                </div>
            @endif

            <!-- Product Info -->
            <div class="p-4 sm:p-5">
                @if($product->category)
                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 mb-2">
                    {{ $product->category }}
                </span>
                @endif
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 line-clamp-1">{{ $product->title ?? $product->name ?? __('Untitled Product') }}</h3>
                <p class="text-xs sm:text-sm text-gray-600 line-clamp-2">{{ $product->description }}</p>

                {{-- Rejection Reason (Owner Only) --}}
                @if($isOwner && $isRejected && $product->rejection_reason)
                    <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-xs text-red-700">
                            <strong>{{ __('Reason:') }}</strong> {{ $product->rejection_reason }}
                        </p>
                        <span class="text-xs text-red-600 hover:text-red-800 underline mt-1 inline-block">
                            {{ __('Edit & Resubmit') }}
                        </span>
                    </div>
                @endif
            </div>
        </a>
        @endforeach
        @endif

        <!-- Add New Product Card (Owner Only) -->
        @if($isOwner)
        <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}#products" class="group bg-white rounded-xl border-2 border-dashed border-gray-300 hover:border-green-500 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer flex items-center justify-center min-h-[200px] sm:min-h-[300px]">
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 group-hover:text-green-600 transition-colors">{{ __('Add New Product') }}</h3>
                <p class="text-sm text-gray-500 mt-2">{{ __('Click to create a new product') }}</p>
            </div>
        </a>
        @endif
    </div>
</div>
