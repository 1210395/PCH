<!-- Add Product Modal - REDESIGNED -->
<div x-show="addProductModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
         @click="addProductModal = false"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl border border-gray-200 max-w-2xl w-full p-4 sm:p-6 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">{{ __('Add New Product') }}</h3>

            <div class="space-y-4">
                <!-- Images Section - Moved to Top -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Images (Max 6)') }}</label>
                    <input type="file" accept="image/*" multiple @change="handleImageUpload($event, 6)"
                           class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                    <div class="mt-2 flex gap-2 flex-wrap" x-show="uploadedImages.length > 0">
                        <template x-for="(img, index) in uploadedImages" :key="index">
                            <div class="relative">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <button @click="removeImage(index)" type="button"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center text-xs sm:text-sm hover:bg-red-600 transition-colors">×</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Name') }} *</label>
                    <input type="text" x-model="currentItem.name"
                           class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }} *</label>
                    <textarea x-model="currentItem.description" rows="3"
                              class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Category') }} *</label>
                    <input type="text" x-model="currentItem.category"
                           class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base">
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button @click="addProductModal = false"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm sm:text-base">
                    {{ __('Cancel') }}
                </button>
                <button @click="submitAddProduct()"
                        :disabled="isSubmitting"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all font-medium disabled:opacity-50 text-sm sm:text-base">
                    <span x-show="!isSubmitting">{{ __('Create Product') }}</span>
                    <span x-show="isSubmitting">{{ __('Creating...') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
