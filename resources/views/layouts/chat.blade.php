<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Palestine Creative Hub') }} - @yield('title', 'Chat')</title>

    <!-- Prevent favicon.ico 404 errors -->
    <link rel="icon" href="data:;base64,=" />

    <!-- Hide page until Tailwind CSS is ready -->
    <style>body { opacity: 0; } body.ready { opacity: 1; transition: opacity 0.15s; }</style>

    <!-- Preconnect hints for external resources -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @if(app()->getLocale() === 'ar')
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet" />
    @endif

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('ready'));</script>

    <!-- AlpineJS Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')

    <style>
        body {
            font-family: {{ app()->getLocale() === 'ar' ? "'Noto Sans Arabic', " : "" }}'Instrument Sans', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        /* RTL layout fixes for chat */
        html[dir="rtl"] { text-align: right; }
        html[dir="rtl"] .text-left { text-align: right; }
        html[dir="rtl"] .text-right { text-align: left; }
        html[dir="rtl"] .ml-1 { margin-left: 0; margin-right: 0.25rem; }
        html[dir="rtl"] .ml-2 { margin-left: 0; margin-right: 0.5rem; }
        html[dir="rtl"] .ml-3 { margin-left: 0; margin-right: 0.75rem; }
        html[dir="rtl"] .ml-4 { margin-left: 0; margin-right: 1rem; }
        html[dir="rtl"] .mr-1 { margin-right: 0; margin-left: 0.25rem; }
        html[dir="rtl"] .mr-2 { margin-right: 0; margin-left: 0.5rem; }
        html[dir="rtl"] .mr-3 { margin-right: 0; margin-left: 0.75rem; }
        html[dir="rtl"] .mr-4 { margin-right: 0; margin-left: 1rem; }
        html[dir="rtl"] .left-0 { left: auto; right: 0; }
        html[dir="rtl"] .right-0 { right: auto; left: 0; }
        html[dir="rtl"] .right-4 { right: auto; left: 1rem; }
        html[dir="rtl"] .space-x-2 > :not([hidden]) ~ :not([hidden]),
        html[dir="rtl"] .space-x-3 > :not([hidden]) ~ :not([hidden]),
        html[dir="rtl"] .space-x-4 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        html[dir="rtl"] input[type="text"],
        html[dir="rtl"] textarea {
            text-align: right;
        }
        html[dir="rtl"] .bg-gradient-to-r { background-image: linear-gradient(to left, var(--tw-gradient-stops)); }

        /* Ensure chat fills available space without overflow */
        html, body {
            height: 100%;
            overflow: hidden;
        }

        .chat-container {
            height: 100vh;
            height: 100dvh; /* Dynamic viewport height for mobile */
        }

        /* Account for navbar height - approximately 72px */
        .chat-main {
            height: calc(100vh - 72px);
            height: calc(100dvh - 72px);
        }

        /* On mobile, also account for subheader if visible */
        @media (max-width: 640px) {
            .chat-main {
                height: calc(100vh - 72px);
                height: calc(100dvh - 72px);
            }
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased overflow-hidden" x-data="{
    toast: {
        show: false,
        message: '',
        type: 'success'
    },
    showToast(message, type = 'success') {
        this.toast.message = message;
        this.toast.type = type;
        this.toast.show = true;
        setTimeout(() => {
            this.toast.show = false;
        }, 4000);
    }
}" @toast.window="showToast($event.detail.message, $event.detail.type)">

    <!-- Global Toast Notification -->
    <div x-show="toast.show"
         x-cloak
         @click="toast.show = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="fixed top-4 left-1/2 transform -translate-x-1/2 z-[9999] max-w-md w-full mx-4 cursor-pointer">
        <div :class="{
                'bg-gradient-to-r from-blue-600 to-green-500': toast.type === 'success',
                'bg-gradient-to-r from-red-500 to-red-600': toast.type === 'error',
                'bg-gradient-to-r from-yellow-500 to-orange-500': toast.type === 'warning'
             }"
             class="rounded-2xl shadow-2xl p-4 flex items-center gap-4 text-white backdrop-blur-sm">

            <!-- Icon -->
            <div class="flex-shrink-0">
                <template x-if="toast.type === 'success'">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'error'">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'warning'">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </template>
            </div>

            <!-- Message -->
            <div class="flex-1">
                <p class="text-white font-semibold text-base leading-tight" x-text="toast.message"></p>
            </div>

            <!-- Close Button -->
            <button @click.stop="toast.show = false"
                    class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 hover:bg-white/25 backdrop-blur-sm flex items-center justify-center transition-all duration-200 hover:scale-110 hover:rotate-90">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Full height chat container (no navbar/footer) -->
    <main class="chat-container flex flex-col">
        @yield('content')
    </main>

    @stack('scripts')

    <!-- Global Toast Helper Function -->
    <script>
        window.showToast = function(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { message, type }
            }));
        };
    </script>
</body>
</html>
