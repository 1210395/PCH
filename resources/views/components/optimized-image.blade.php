@props([
    'src',
    'alt' => '',
    'class' => '',
    'width' => null,
    'height' => null,
    'eager' => false,  // Set to true for above-the-fold images
    'aspectRatio' => null,  // e.g., '16/9', '4/3', '1/1'
    'fallback' => null,  // Gradient fallback style
    'objectFit' => 'cover',  // 'cover', 'contain', 'fill'
])

@php
    // Generate unique ID for this image
    $imageId = 'img-' . uniqid();

    // Determine if we should use lazy loading
    $shouldLazyLoad = !$eager;

    // Container classes
    $containerClass = 'optimized-image-container relative overflow-hidden bg-gray-100';
    if ($aspectRatio) {
        $containerClass .= ' aspect-[' . $aspectRatio . ']';
    }

    // Default fallback gradient
    $defaultFallback = 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);';
@endphp

<div class="{{ $containerClass }}" data-image-container="{{ $imageId }}">
    {{-- Loading Skeleton --}}
    <div class="absolute inset-0 bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 animate-pulse"
         data-skeleton="{{ $imageId }}"
         style="animation: shimmer 2s infinite;">
    </div>

    {{-- Actual Image --}}
    <img
        id="{{ $imageId }}"
        @if($shouldLazyLoad)
            data-src="{{ $src }}"
            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {{ $width ?? 400 }} {{ $height ?? 300 }}'%3E%3C/svg%3E"
            loading="lazy"
            class="lazy-image {{ $class }} w-full h-full object-{{ $objectFit }} opacity-0 transition-opacity duration-500"
        @else
            src="{{ $src }}"
            class="{{ $class }} w-full h-full object-{{ $objectFit }} opacity-0 transition-opacity duration-500"
        @endif
        alt="{{ $alt }}"
        @if($width) width="{{ $width }}" @endif
        @if($height) height="{{ $height }}" @endif
        data-loaded="false"
        onload="this.style.opacity='1'; this.dataset.loaded='true'; const skeleton = document.querySelector('[data-skeleton={{ $imageId }}]'); if(skeleton) skeleton.remove();"
        onerror="this.style.display='none'; const container = this.closest('[data-image-container={{ $imageId }}]'); const fallback = document.createElement('div'); fallback.className='absolute inset-0 flex items-center justify-center'; fallback.style='{{ $fallback ?? $defaultFallback }}'; fallback.innerHTML='<svg class=\'w-16 h-16 text-white/30\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'></path></svg>'; container.appendChild(fallback); const skeleton = document.querySelector('[data-skeleton={{ $imageId }}]'); if(skeleton) skeleton.remove();"
    >

    {{ $slot }}
</div>

<style>
@keyframes shimmer {
    0% {
        background-position: -1000px 0;
    }
    100% {
        background-position: 1000px 0;
    }
}

.optimized-image-container [data-skeleton] {
    background-size: 1000px 100%;
}
</style>
