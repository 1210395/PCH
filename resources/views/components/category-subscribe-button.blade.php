{{-- Category Subscribe Button Component with Modal --}}
{{-- Usage: <x-category-subscribe-button content-type="marketplace" /> --}}
@props(['contentType'])

@php
    $isAuthenticated = auth('designer')->check() || auth('academic')->check();
@endphp

<div
    x-data="categorySubscription({
        contentType: '{{ $contentType }}',
        isAuthenticated: {{ $isAuthenticated ? 'true' : 'false' }},
        getUrl: '{{ route('subscriptions.category.get', ['locale' => app()->getLocale(), 'contentType' => $contentType]) }}',
        saveUrl: '{{ route('subscriptions.category.save', ['locale' => app()->getLocale(), 'contentType' => $contentType]) }}',
        deleteUrl: '{{ route('subscriptions.category.delete', ['locale' => app()->getLocale(), 'contentType' => $contentType]) }}',
        loginUrl: '{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}'
    })"
    x-init="init()"
    class="inline-block"
>
    {{-- Subscribe Button --}}
    @if($isAuthenticated)
    <button
        type="button"
        @click="openModal()"
        class="flex items-center gap-2 px-4 py-2 rounded-lg border transition-all duration-200 text-sm font-medium"
        :class="hasSubscription
            ? 'bg-green-50 border-green-500 text-green-700 hover:bg-green-100'
            : 'bg-white border-gray-300 text-gray-700 hover:border-blue-500 hover:text-blue-600'"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-text="hasSubscription ? '{{ __('Notification Settings') }}' : '{{ __('Subscribe to Notifications') }}'"></span>
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
        <span>{{ __('Subscribe to Notifications') }}</span>
    </a>
    @endif

    {{-- Modal --}}
    @if($isAuthenticated)
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            {{-- Background overlay --}}
            <div
                x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="closeModal()"
            ></div>

            {{-- Modal content --}}
            <div
                x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block align-bottom bg-white rounded-xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
            >
                {{-- Close button --}}
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-500 transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div>
                    {{-- Header --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
                            {{ __('Notification Preferences') }}
                        </h3>
                    </div>

                    <p class="text-sm text-gray-600 mb-4">
                        <span x-show="contentType === 'marketplace'">{{ __('Select which tags you want to receive notifications for. Leave empty to receive all notifications.') }}</span>
                        <span x-show="contentType !== 'marketplace'">{{ __('Select which categories you want to receive notifications for. Leave empty to receive all notifications.') }}</span>
                    </p>

                    {{-- Loading state --}}
                    <div x-show="loadingOptions" class="py-8 text-center">
                        <svg class="animate-spin h-8 w-8 mx-auto text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-500 mt-2">{{ __('Loading options...') }}</p>
                    </div>

                    <div x-show="!loadingOptions" class="space-y-4">
                        {{-- Tags Selection (shown first for marketplace) --}}
                        <div x-show="availableOptions.tags && availableOptions.tags.length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Tags') }}
                            </label>
                            <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-2 space-y-1">
                                <template x-for="tag in availableOptions.tags" :key="tag.value || tag">
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :value="tag.value || tag"
                                            x-model="selectedTags"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        >
                                        <span x-text="tag.label || tag" class="text-sm text-gray-700"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- Categories Selection (hidden for marketplace since it uses tags) --}}
                        <div x-show="contentType !== 'marketplace' && availableOptions.categories && availableOptions.categories.length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Categories') }}
                            </label>
                            <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-2 space-y-1">
                                <template x-for="cat in availableOptions.categories" :key="cat.value || cat">
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :value="cat.value || cat"
                                            x-model="selectedCategories"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        >
                                        <span x-text="cat.label || cat" class="text-sm text-gray-700"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- Types Selection (for marketplace) --}}
                        <div x-show="availableOptions.types && availableOptions.types.length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Post Types') }}
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="type in availableOptions.types" :key="type.value">
                                    <label
                                        class="flex items-center gap-2 px-3 py-1.5 border rounded-full cursor-pointer transition-colors"
                                        :class="selectedTypes.includes(type.value) ? 'bg-blue-50 border-blue-500 text-blue-700' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400'"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="type.value"
                                            x-model="selectedTypes"
                                            class="sr-only"
                                        >
                                        <span x-text="type.label" class="text-sm"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- Levels Selection (for training) --}}
                        <div x-show="availableOptions.levels && availableOptions.levels.length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Difficulty Levels') }}
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="level in availableOptions.levels" :key="level.value">
                                    <label
                                        class="flex items-center gap-2 px-3 py-1.5 border rounded-full cursor-pointer transition-colors"
                                        :class="selectedLevels.includes(level.value) ? 'bg-blue-50 border-blue-500 text-blue-700' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400'"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="level.value"
                                            x-model="selectedLevels"
                                            class="sr-only"
                                        >
                                        <span x-text="level.label" class="text-sm"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <button
                        @click="closeModal()"
                        class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                    >
                        {{ __('Cancel') }}
                    </button>

                    <button
                        x-show="hasSubscription"
                        @click="unsubscribe()"
                        :disabled="saving"
                        class="w-full sm:w-auto px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors disabled:opacity-50"
                    >
                        {{ __('Unsubscribe') }}
                    </button>

                    <button
                        @click="save()"
                        :disabled="saving"
                        class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg text-sm font-medium hover:shadow-lg transition-all disabled:opacity-50"
                    >
                        <span x-show="!saving">{{ __('Save Preferences') }}</span>
                        <span x-show="saving" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('Saving...') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@once
@push('scripts')
<script>
function categorySubscription(config) {
    return {
        showModal: false,
        hasSubscription: false,
        saving: false,
        loadingOptions: false,
        contentType: config.contentType,
        isAuthenticated: config.isAuthenticated,
        availableOptions: {
            categories: [],
            tags: [],
            types: [],
            levels: []
        },
        selectedCategories: [],
        selectedTags: [],
        selectedTypes: [],
        selectedLevels: [],
        getUrl: config.getUrl,
        saveUrl: config.saveUrl,
        deleteUrl: config.deleteUrl,
        loginUrl: config.loginUrl,

        init() {
            if (this.isAuthenticated) {
                this.loadSubscription();
            }
        },

        async loadSubscription() {
            this.loadingOptions = true;
            try {
                const response = await fetch(this.getUrl, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (data.success) {
                    // Set available options
                    this.availableOptions = data.available_options || {
                        categories: [],
                        tags: [],
                        types: [],
                        levels: []
                    };

                    // Set current subscription state
                    if (data.subscription) {
                        this.hasSubscription = true;
                        this.selectedCategories = data.subscription.categories || [];
                        this.selectedTags = data.subscription.tags || [];
                        this.selectedTypes = data.subscription.types || [];
                        this.selectedLevels = data.subscription.levels || [];
                    }
                }
            } catch (error) {
                console.error('Failed to load subscription:', error);
            } finally {
                this.loadingOptions = false;
            }
        },

        openModal() {
            if (!this.isAuthenticated) {
                window.location.href = this.loginUrl;
                return;
            }
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        },

        async save() {
            if (this.saving) return;
            this.saving = true;

            try {
                const response = await fetch(this.saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        categories: this.selectedCategories.length > 0 ? this.selectedCategories : null,
                        tags: this.selectedTags.length > 0 ? this.selectedTags : null,
                        types: this.selectedTypes.length > 0 ? this.selectedTypes : null,
                        levels: this.selectedLevels.length > 0 ? this.selectedLevels : null,
                        is_active: true,
                    }),
                });

                const data = await response.json();
                if (data.success) {
                    this.hasSubscription = true;
                    this.closeModal();
                }
            } catch (error) {
                console.error('Failed to save subscription:', error);
            } finally {
                this.saving = false;
            }
        },

        async unsubscribe() {
            if (this.saving) return;
            this.saving = true;

            try {
                const response = await fetch(this.deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                });

                const data = await response.json();
                if (data.success) {
                    this.hasSubscription = false;
                    this.selectedCategories = [];
                    this.selectedTags = [];
                    this.selectedTypes = [];
                    this.selectedLevels = [];
                    this.closeModal();
                }
            } catch (error) {
                console.error('Failed to unsubscribe:', error);
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
@endpush
@endonce
