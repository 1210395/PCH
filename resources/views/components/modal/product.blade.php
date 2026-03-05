{{-- Product Modal - Enhanced Design --}}
<div x-show="productModal"
     x-cloak
     @click.self="closeProductModal()"
     class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div @click.stop
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">

        {{-- Modal Header with Gradient --}}
        <div class="relative p-4 sm:p-6 md:p-8 bg-gradient-to-r from-blue-600 to-green-500 text-white overflow-hidden">
            {{-- Animated Background Pattern --}}
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full -translate-x-32 -translate-y-32"></div>
                <div class="absolute bottom-0 right-0 w-48 h-48 bg-white rounded-full translate-x-24 translate-y-24"></div>
            </div>

            <div class="relative flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <h2 class="text-xl sm:text-2xl md:text-3xl font-bold tracking-tight" x-text="productForm.id ? '{{ __('Edit Product') }}' : '{{ __('New Product') }}'"></h2>
                    </div>
                    <p class="text-white/90 text-xs sm:text-sm ml-12 sm:ml-15" x-text="productForm.id ? '{{ __('Update your product details below') }}' : '{{ __('Add a new product to your portfolio') }}'"></p>
                </div>
                <button @click="closeProductModal()"
                        class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/25 backdrop-blur-sm flex items-center justify-center transition-all duration-200 hover:scale-110 hover:rotate-90 shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Modal Body --}}
        <div class="overflow-y-auto max-h-[calc(90vh-200px)] sm:max-h-[calc(90vh-240px)]">
            <div class="p-4 sm:p-6 md:p-8 space-y-4 sm:space-y-6">
                {{-- Product Name --}}
                <div class="group">
                    <label class="block text-sm font-bold text-gray-800 mb-2.5 flex items-center gap-2">
                        <span>{{ __('Product Name') }}</span>
                        <span class="text-red-500 text-lg">*</span>
                    </label>
                    <input type="text"
                           x-model="productForm.name"
                           placeholder="{{ __('e.g., Handcrafted Oak Dining Table') }}"
                           class="w-full px-5 py-3.5 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none group-hover:border-gray-300 text-gray-900 placeholder-gray-400 font-medium">
                </div>

                {{-- Description --}}
                <div class="group">
                    <label class="block text-sm font-bold text-gray-800 mb-2.5 flex items-center gap-2">
                        <span>{{ __('Description') }}</span>
                        <span class="text-red-500 text-lg">*</span>
                    </label>
                    <textarea x-model="productForm.description"
                              placeholder="{{ __('Describe your product\'s features, materials, and unique qualities...') }}"
                              rows="4"
                              maxlength="500"
                              class="w-full px-5 py-3.5 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none resize-none group-hover:border-gray-300 text-gray-900 placeholder-gray-400"></textarea>
                    <p class="mt-2 text-xs text-gray-500 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="(productForm.description ? productForm.description.length : 0) + '/500'"></span> {{ __('characters') }}
                    </p>
                </div>

                {{-- Category --}}
                <div class="group">
                    <label class="block text-sm font-bold text-gray-800 mb-2.5 flex items-center gap-2">
                        <span>{{ __('Category') }}</span>
                        <span class="text-red-500 text-lg">*</span>
                    </label>
                    <select x-model="productForm.category"
                            @change="if (productForm.category !== 'Other') { productForm.customCategory = '' }"
                            class="w-full px-5 py-3.5 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none appearance-none bg-white group-hover:border-gray-300 cursor-pointer text-gray-900 font-medium"
                            style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 1rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 3rem;">
                        <option value="">{{ __('Select category') }}</option>
                        @foreach(\App\Helpers\DropdownHelper::productCategories() as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                    <input x-show="productForm.category === 'Other'"
                           x-transition:enter="transition ease-out duration-300"
                           x-transition:enter-start="opacity-0 transform -translate-y-2"
                           x-transition:enter-end="opacity-100 transform translate-y-0"
                           type="text"
                           x-model="productForm.customCategory"
                           placeholder="{{ __('Specify category') }}"
                           class="w-full px-5 py-3.5 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none mt-3 text-gray-900 placeholder-gray-400">
                </div>

                {{-- Product Images --}}
                <div class="group">
                    <label class="block text-sm font-bold text-gray-800 mb-2.5">{{ __('Product Images') }}</label>
                    <div class="space-y-4">
                        <div class="relative">
                            <input type="file"
                                   accept="image/*"
                                   multiple
                                   @change="handleProductImageUpload($event)"
                                   class="w-full px-5 py-4 border-2 border-dashed border-gray-300 rounded-2xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all duration-200 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-gradient-to-r file:from-blue-600 file:to-green-500 file:text-white hover:file:shadow-lg file:cursor-pointer file:transition-all file:duration-200">
                            <p class="text-sm text-gray-500 mt-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                </svg>
                                <span x-show="productForm.images && productForm.images.length > 0" x-text="productForm.images.length + ' of 6 images uploaded. '"></span>
                                <span>{{ __('Select multiple images (Max 6, 5MB each)') }}</span>
                            </p>
                        </div>

                        {{-- Upload Progress --}}
                        <div x-show="productForm.uploading"
                             x-transition
                             class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-semibold text-blue-700">{{ __('Uploading images...') }}</span>
                        </div>

                        {{-- Image Previews --}}
                        <div x-show="productForm.images && productForm.images.length > 0"
                             x-transition
                             class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <template x-for="(image, index) in productForm.images" :key="index">
                                <div class="relative group/img">
                                    <img :src="image.preview"
                                         class="w-full h-28 object-cover rounded-xl border-2 border-gray-200 shadow-sm group-hover/img:shadow-lg transition-shadow duration-200">
                                    <button type="button"
                                            @click="removeProductImage(index)"
                                            class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-full opacity-0 group-hover/img:opacity-100 transition-all duration-200 flex items-center justify-center hover:scale-110 shadow-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="p-4 sm:p-6 border-t border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100/50 flex flex-col sm:flex-row gap-3 sm:gap-4 justify-end">
            <button @click="closeProductModal()"
                    class="px-6 sm:px-8 py-3 sm:py-3.5 border-2 border-gray-300 hover:border-gray-400 bg-white text-gray-700 rounded-xl font-bold transition-all duration-200 hover:shadow-lg hover:scale-105 active:scale-95 w-full sm:w-auto">
                {{ __('Cancel') }}
            </button>
            <button @click="saveProduct()"
                    :disabled="productSubmitting"
                    :class="productSubmitting ? 'opacity-70 cursor-not-allowed' : 'hover:shadow-2xl hover:shadow-blue-500/40 hover:scale-105 active:scale-95'"
                    class="px-6 sm:px-8 py-3 sm:py-3.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-xl font-bold transition-all duration-200 flex items-center justify-center gap-3 shadow-lg w-full sm:w-auto">
                <svg x-show="productSubmitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="productForm.id ? '{{ __('Update Product') }}' : '{{ __('Add Product') }}'"></span>
                <svg x-show="!productSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </button>
        </div>
    </div>
</div>
