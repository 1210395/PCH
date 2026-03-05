{{-- Profile Subscribe Button Component --}}
{{-- Usage: <x-profile-subscribe-button profile-type="designer" :profile-id="$designer->id" /> --}}
@props(['profileType', 'profileId'])

@php
    $isAuthenticated = auth('designer')->check() || auth('academic')->check();
    $currentUser = auth('designer')->user() ?? auth('academic')->user();
    $currentUserType = auth('designer')->check() ? 'designer' : (auth('academic')->check() ? 'academic' : null);

    // Check if viewing own profile
    $isSelf = $isAuthenticated && $currentUserType === $profileType && $currentUser?->id == $profileId;
@endphp

@if(!$isSelf)
<div
    x-data="profileSubscription({
        profileType: '{{ $profileType }}',
        profileId: {{ $profileId }},
        isAuthenticated: {{ $isAuthenticated ? 'true' : 'false' }},
        checkUrl: '{{ route('subscriptions.profile.check', ['locale' => app()->getLocale()]) }}',
        toggleUrl: '{{ route('subscriptions.profile.toggle', ['locale' => app()->getLocale()]) }}',
        loginUrl: '{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}'
    })"
    x-init="init()"
    class="inline-block"
>
    @if($isAuthenticated)
    <button
        type="button"
        @click="toggle()"
        :disabled="loading"
        class="flex items-center gap-2 px-4 py-2 rounded-lg border transition-all duration-200 text-sm font-medium"
        :class="subscribed
            ? 'bg-green-50 border-green-500 text-green-700 hover:bg-green-100'
            : 'bg-white border-gray-300 text-gray-700 hover:border-blue-500 hover:text-blue-600'"
    >
        {{-- Bell icon --}}
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path x-show="!subscribed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            <path x-show="subscribed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        {{-- Checkmark when subscribed --}}
        <svg x-show="subscribed" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="subscribed ? '{{ __('Subscribed') }}' : '{{ __('Subscribe') }}'"></span>
        {{-- Loading spinner --}}
        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </button>
    @else
    {{-- Guest: Link to login --}}
    <a
        href="{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}"
        class="flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:border-blue-500 hover:text-blue-600 transition-all duration-200 text-sm font-medium"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span>{{ __('Subscribe') }}</span>
    </a>
    @endif
</div>
@endif

@once
@push('scripts')
<script>
function profileSubscription(config) {
    return {
        subscribed: false,
        loading: false,
        profileType: config.profileType,
        profileId: config.profileId,
        isAuthenticated: config.isAuthenticated,
        checkUrl: config.checkUrl,
        toggleUrl: config.toggleUrl,
        loginUrl: config.loginUrl,

        init() {
            if (this.isAuthenticated) {
                this.checkSubscription();
            }
        },

        async checkSubscription() {
            try {
                const response = await fetch(`${this.checkUrl}?subscribable_type=${this.profileType}&subscribable_id=${this.profileId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.subscribed = data.subscribed || false;
            } catch (error) {
                console.error('Failed to check subscription:', error);
            }
        },

        async toggle() {
            if (!this.isAuthenticated) {
                window.location.href = this.loginUrl;
                return;
            }

            if (this.loading) return;
            this.loading = true;

            try {
                const response = await fetch(this.toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        subscribable_type: this.profileType,
                        subscribable_id: this.profileId,
                    }),
                });

                const data = await response.json();
                if (data.success) {
                    this.subscribed = data.subscribed;
                }
            } catch (error) {
                console.error('Failed to toggle subscription:', error);
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
@endonce
