{{--
    One-time welcome popup shown after a designer's first login when their
    portfolio has no products / projects / services yet. Triggered by the
    'show_welcome_popup' session flag set in AuthController::login() and
    consumed in DesignerController::show().

    The three CTAs dispatch the same window events the existing add-modals
    listen for, so clicking one closes the popup and opens that modal.
--}}
<div
    x-data="{ show: true }"
    x-show="show"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="show = false"
    class="fixed inset-0 z-50 overflow-y-auto"
>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm" @click="show = false"></div>

        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 sm:p-8"
        >
            <button
                @click="show = false"
                aria-label="{{ __('Close') }}"
                class="absolute top-4 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }} text-gray-400 hover:text-gray-600 transition-colors"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-gradient-to-br from-blue-600 to-green-500 rounded-full flex items-center justify-center mb-4 shadow-lg">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>

                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                    {{ __('Welcome to Palestine Creative Hub!') }}
                </h2>
                <p class="text-gray-600 mb-6 sm:mb-8 max-w-md mx-auto">
                    {{ __("Your profile is live. Start building your portfolio by adding your first project, product, or service.") }}
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6 text-left">
                    {{-- Add Project --}}
                    <button
                        type="button"
                        @click="show = false; $nextTick(() => window.dispatchEvent(new CustomEvent('open-add-project')))"
                        class="group flex flex-col items-center text-center gap-2 p-5 rounded-xl border-2 border-purple-200 bg-purple-50/30 hover:bg-purple-50 hover:border-purple-400 hover:shadow-md transition-all"
                    >
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-600 to-pink-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-900">{{ __('Add Project') }}</span>
                        <span class="text-xs text-gray-500">{{ __('Showcase work you\'ve done') }}</span>
                    </button>

                    {{-- Add Product --}}
                    <button
                        type="button"
                        @click="show = false; $nextTick(() => window.dispatchEvent(new CustomEvent('open-add-product')))"
                        class="group flex flex-col items-center text-center gap-2 p-5 rounded-xl border-2 border-green-200 bg-green-50/30 hover:bg-green-50 hover:border-green-400 hover:shadow-md transition-all"
                    >
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-600 to-blue-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-900">{{ __('Add Product') }}</span>
                        <span class="text-xs text-gray-500">{{ __('List items you sell') }}</span>
                    </button>

                    {{-- Add Service --}}
                    <button
                        type="button"
                        @click="show = false; $nextTick(() => window.dispatchEvent(new CustomEvent('open-add-service')))"
                        class="group flex flex-col items-center text-center gap-2 p-5 rounded-xl border-2 border-blue-200 bg-blue-50/30 hover:bg-blue-50 hover:border-blue-400 hover:shadow-md transition-all"
                    >
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-900">{{ __('Add Service') }}</span>
                        <span class="text-xs text-gray-500">{{ __('Offer what you do') }}</span>
                    </button>
                </div>

                <button
                    type="button"
                    @click="show = false"
                    class="text-sm text-gray-500 hover:text-gray-700 underline"
                >
                    {{ __('Maybe later') }}
                </button>
            </div>
        </div>
    </div>
</div>
