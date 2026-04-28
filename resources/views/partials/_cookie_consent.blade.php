{{--
    Minimal GDPR / ePrivacy cookie-consent banner.

    Persists the user's choice in localStorage under `pch_cookie_consent`
    so the banner doesn't reappear on every page load. The site only sets
    a session cookie (essential, exempt from consent) and a track.page
    analytics row, so this banner is informational + record-of-consent
    rather than a functional gate. If you later add Google Analytics /
    Hotjar / Meta Pixel, gate them behind `pch_cookie_consent === 'accepted'`.

    Lives at the body bottom. Hidden once a choice is recorded; user can
    re-open via `localStorage.removeItem('pch_cookie_consent'); location.reload()`.
--}}
<div
    x-data="{
        show: false,
        accept() { localStorage.setItem('pch_cookie_consent', 'accepted'); this.show = false; },
        decline() { localStorage.setItem('pch_cookie_consent', 'declined'); this.show = false; },
    }"
    x-init="show = !localStorage.getItem('pch_cookie_consent')"
    x-show="show"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    role="dialog"
    aria-live="polite"
    aria-label="{{ __('Cookie consent') }}"
    class="fixed bottom-0 inset-x-0 z-[60] bg-white border-t border-gray-200 shadow-2xl"
>
    <div class="max-w-6xl mx-auto px-4 py-4 sm:py-5 flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div class="flex items-start gap-3 flex-1">
            <div class="hidden sm:flex flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-blue-100 to-green-100 items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-sm text-gray-700 leading-relaxed">
                <p class="font-semibold text-gray-900 mb-0.5">{{ __('We use cookies') }}</p>
                <p>
                    {{ __('We use essential cookies to keep you signed in and a small amount of analytics to improve the site. By continuing you accept these.') }}
                    <a href="{{ url(app()->getLocale() . '/privacy') }}" class="text-blue-600 hover:text-blue-700 font-medium underline">{{ __('Privacy policy') }}</a>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-stretch sm:self-auto">
            <button
                type="button"
                @click="decline()"
                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
            >
                {{ __('Decline') }}
            </button>
            <button
                type="button"
                @click="accept()"
                class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-700 hover:to-green-600 rounded-lg shadow transition-all"
            >
                {{ __('Accept') }}
            </button>
        </div>
    </div>
</div>
