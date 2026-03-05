{{-- Share Button Component --}}
{{-- Usage: <x-share-button :url="$url" :title="$title" :description="$description" /> --}}
@props([
    'url' => null,
    'title' => '',
    'description' => '',
    'size' => 'md', // sm, md, lg
    'variant' => 'default', // default, icon-only, text
    'position' => 'bottom-end' // bottom-end, bottom-start, top-end, top-start
])

@php
    $shareUrl = $url ?? request()->url();
    $encodedUrl = urlencode($shareUrl);
    $encodedTitle = urlencode($title);
    $encodedDescription = urlencode($description);

    $sizeClasses = match($size) {
        'sm' => 'px-2 py-1.5 text-xs',
        'lg' => 'px-4 py-2.5 text-base',
        default => 'px-3 py-2 text-sm'
    };

    $iconSizeClasses = match($size) {
        'sm' => 'w-3.5 h-3.5',
        'lg' => 'w-5 h-5',
        default => 'w-4 h-4'
    };

    $dropdownPositionClasses = match($position) {
        'bottom-start' => 'left-0 top-full mt-2',
        'top-end' => 'right-0 bottom-full mb-2',
        'top-start' => 'left-0 bottom-full mb-2',
        default => 'right-0 top-full mt-2' // bottom-end
    };
@endphp

<div x-data="{
    open: false,
    copied: false,
    shareUrl: '{{ $shareUrl }}',
    shareTitle: '{{ addslashes($title) }}',

    async copyLink() {
        try {
            await navigator.clipboard.writeText(this.shareUrl);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        } catch (err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = this.shareUrl;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    },

    shareToFacebook() {
        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(this.shareUrl), '_blank', 'width=600,height=400');
        this.open = false;
    },

    shareToTwitter() {
        window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(this.shareUrl) + '&text=' + encodeURIComponent(this.shareTitle), '_blank', 'width=600,height=400');
        this.open = false;
    },

    shareToLinkedIn() {
        window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(this.shareUrl), '_blank', 'width=600,height=400');
        this.open = false;
    },

    shareToWhatsApp() {
        window.open('https://wa.me/?text=' + encodeURIComponent(this.shareTitle + ' ' + this.shareUrl), '_blank');
        this.open = false;
    },

    shareToTelegram() {
        window.open('https://t.me/share/url?url=' + encodeURIComponent(this.shareUrl) + '&text=' + encodeURIComponent(this.shareTitle), '_blank');
        this.open = false;
    },

    async nativeShare() {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: this.shareTitle,
                    url: this.shareUrl
                });
                this.open = false;
            } catch (err) {
                // Share was cancelled or failed
            }
        }
    }
}"
class="relative inline-block"
@click.away="open = false"
>
    {{-- Share Button --}}
    @if($variant === 'icon-only')
        <button
            @click="open = !open"
            type="button"
            class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            title="{{ __('Share') }}"
        >
            <svg class="{{ $iconSizeClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
        </button>
    @elseif($variant === 'text')
        <button
            @click="open = !open"
            type="button"
            class="{{ $sizeClasses }} text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-1.5"
        >
            <svg class="{{ $iconSizeClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
            <span>{{ __('Share') }}</span>
        </button>
    @else
        <button
            @click="open = !open"
            type="button"
            class="{{ $sizeClasses }} bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 rounded-lg transition-all flex items-center gap-2 font-medium shadow-sm"
        >
            <svg class="{{ $iconSizeClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
            <span>{{ __('Share') }}</span>
        </button>
    @endif

    {{-- Dropdown Menu --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute {{ $dropdownPositionClasses }} w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50"
        style="display: none;"
    >
        {{-- Copy Link --}}
        <button
            @click="copyLink()"
            type="button"
            class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors"
        >
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                <svg x-show="!copied" class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                </svg>
                <svg x-show="copied" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy Link') }}'"></span>
        </button>

        <div class="border-t border-gray-100 my-2"></div>

        {{-- Facebook --}}
        <button
            @click="shareToFacebook()"
            type="button"
            class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors"
        >
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </div>
            <span>Facebook</span>
        </button>

        {{-- Twitter/X --}}
        <button
            @click="shareToTwitter()"
            type="button"
            class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors"
        >
            <div class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
            </div>
            <span>X (Twitter)</span>
        </button>

        {{-- LinkedIn --}}
        <button
            @click="shareToLinkedIn()"
            type="button"
            class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors"
        >
            <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                </svg>
            </div>
            <span>LinkedIn</span>
        </button>

        {{-- WhatsApp --}}
        <button
            @click="shareToWhatsApp()"
            type="button"
            class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors"
        >
            <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </div>
            <span>WhatsApp</span>
        </button>

        {{-- Telegram --}}
        <button
            @click="shareToTelegram()"
            type="button"
            class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors"
        >
            <div class="w-8 h-8 rounded-full bg-sky-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                </svg>
            </div>
            <span>Telegram</span>
        </button>

        {{-- Native Share (for mobile devices) --}}
        <template x-if="'share' in navigator">
            <div>
                <div class="border-t border-gray-100 my-2"></div>
                <button
                    @click="nativeShare()"
                    type="button"
                    class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors"
                >
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-green-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                    </div>
                    <span>{{ __('More options...') }}</span>
                </button>
            </div>
        </template>
    </div>
</div>
