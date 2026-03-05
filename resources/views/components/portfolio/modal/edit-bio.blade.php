@props(['designer'])

<!-- Edit Bio Modal - REDESIGNED -->
<div x-show="editBioModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
         @click="editBioModal = false"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl border border-gray-200 max-w-2xl w-full p-4 sm:p-6">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">{{ __('Edit Bio') }}</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('About Me') }}</label>
                    <textarea x-model="bioText" rows="6"
                              class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"
                              placeholder="{{ __('Tell us about yourself...') }}"></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button @click="editBioModal = false"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm sm:text-base">
                    {{ __('Cancel') }}
                </button>
                <button @click="submitEditBio()"
                        :disabled="isSubmitting"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all font-medium disabled:opacity-50 text-sm sm:text-base">
                    <span x-show="!isSubmitting">{{ __('Update Bio') }}</span>
                    <span x-show="isSubmitting">{{ __('Updating...') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
