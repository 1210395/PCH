{{-- Fullscreen Image Lightbox Component --}}
{{-- Usage: <x-image-lightbox /> --}}
{{-- Then dispatch event: $dispatch('open-lightbox', { images: [...urls], index: 0 }) --}}

<div x-data="{
    open: false,
    images: [],
    current: 0,
    get activeImage() { return this.images[this.current] || ''; },
    get total() { return this.images.length; },
    openLightbox(data) {
        this.images = data.images || [];
        this.current = data.index || 0;
        this.open = true;
        document.body.style.overflow = 'hidden';
    },
    close() {
        this.open = false;
        document.body.style.overflow = '';
    },
    next() {
        if (this.total > 1) this.current = (this.current + 1) % this.total;
    },
    prev() {
        if (this.total > 1) this.current = (this.current - 1 + this.total) % this.total;
    }
}"
@open-lightbox.window="openLightbox($event.detail)"
@keydown.escape.window="close()"
@keydown.right.window="if(open) next()"
@keydown.left.window="if(open) prev()"
x-cloak>

    {{-- Fullscreen Overlay --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/95"
         @click.self="close()">

        {{-- Close Button --}}
        <button @click="close()"
                class="absolute top-4 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }} z-10 w-11 h-11 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Image Counter --}}
        <div x-show="total > 1" class="absolute top-4 left-1/2 -translate-x-1/2 z-10 px-4 py-1.5 bg-white/10 text-white text-sm font-medium rounded-full">
            <span x-text="(current + 1) + ' / ' + total"></span>
        </div>

        {{-- Previous Button --}}
        <button x-show="total > 1"
                @click.stop="prev()"
                class="absolute {{ app()->getLocale() === 'ar' ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 z-10 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        {{-- Main Image --}}
        <img :src="activeImage"
             alt=""
             class="max-w-[90vw] max-h-[85vh] object-contain select-none"
             @click.stop>

        {{-- Next Button --}}
        <button x-show="total > 1"
                @click.stop="next()"
                class="absolute {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }} top-1/2 -translate-y-1/2 z-10 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        {{-- Thumbnail Strip --}}
        <div x-show="total > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 z-10 flex gap-2 max-w-[90vw] overflow-x-auto py-2 px-3 rounded-xl bg-black/40 backdrop-blur-sm">
            <template x-for="(img, idx) in images" :key="idx">
                <button @click.stop="current = idx"
                        class="flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden border-2 transition-all"
                        :class="current === idx ? 'border-white opacity-100' : 'border-transparent opacity-50 hover:opacity-75'">
                    <img :src="img" alt="" class="w-full h-full object-cover">
                </button>
            </template>
        </div>
    </div>
</div>
