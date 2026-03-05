    {{-- Sticky Header --}}
    <div class="bg-white border-b sticky top-0 z-10 shadow-sm transition-shadow duration-300">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <a href="{{ route('profile', ['locale' => app()->getLocale()]) }}" class="flex-shrink-0 p-2 hover:bg-gray-100 rounded-lg transition-all duration-200 hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-lg sm:text-xl font-bold truncate">{{ __('Edit Profile') }}</h1>
                        <p class="text-sm text-gray-600 hidden sm:block">{{ __('Update your profile information and settings') }}</p>
                    </div>
                </div>
                <div class="flex gap-2 sm:gap-3 flex-shrink-0">
                    <a href="{{ route('profile', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-3 sm:px-4 py-2 border border-gray-300 hover:border-gray-400 bg-white text-gray-700 rounded-lg font-medium transition-all duration-200 hover:shadow-md text-sm">
                        <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span class="hidden sm:inline">{{ __('Cancel') }}</span>
                    </a>
                    <button @click="saveProfile()" :disabled="saving" :class="saving ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90 hover:shadow-lg'" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg font-medium transition-all duration-200 text-sm transform hover:scale-105">
                        <svg x-show="!saving" class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg x-show="saving" class="animate-spin w-4 h-4 sm:mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="hidden sm:inline" x-text="saving ? '{{ __('Saving...') }}' : '{{ __('Save Changes') }}'"></span>
                        <span class="sm:hidden" x-text="saving ? '{{ __('Saving...') }}' : '{{ __('Save') }}'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

