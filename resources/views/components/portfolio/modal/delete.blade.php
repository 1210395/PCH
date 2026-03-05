<!-- Delete Confirmation Modal - REDESIGNED -->
<div x-show="deleteModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
         @click="deleteModal = false"></div>

    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl border border-gray-200 max-w-md w-full p-4 sm:p-6"
             @click.away="deleteModal = false">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <h3 class="text-base sm:text-lg font-semibold text-gray-900 text-center mb-2">{{ __('Delete') }} <span x-text="deleteType.charAt(0).toUpperCase() + deleteType.slice(1)"></span></h3>
            <p class="text-xs sm:text-sm text-gray-600 text-center mb-6">
                {{ __('Are you sure you want to delete') }} "<span x-text="deleteName" class="font-semibold"></span>"? {{ __('This action cannot be undone.') }}
            </p>

            <div class="flex gap-3">
                <button @click="deleteModal = false"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm sm:text-base">
                    {{ __('Cancel') }}
                </button>
                <button @click="confirmDelete()"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm sm:text-base">
                    {{ __('Delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
