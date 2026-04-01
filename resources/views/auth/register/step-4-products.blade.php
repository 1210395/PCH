            <!-- Step 4: Sample Products -->
            <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 md:p-8 max-w-4xl mx-auto overflow-visible">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Sample Products') }}</h2>
                    <p class="text-gray-600 mb-6">{{ __("Add products you've created or sell (optional but recommended)") }}</p>

                    <!-- Error Messages -->
                    <div x-show="errors.products" class="mb-4 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 animate-fade-in">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-red-700" x-text="errors.products"></p>
                        </div>
                    </div>

                    <div class="space-y-6 overflow-visible">
                        <template x-for="(product, index) in formData.products" :key="product.id">
                            <div class="bg-gray-50 rounded-lg p-4 sm:p-5 md:p-6 border border-gray-200 overflow-visible">
                                <div class="flex items-start justify-between mb-4">
                                    <h3 class="font-semibold text-gray-900">{{ __('Product') }} <span x-text="index + 1"></span></h3>
                                    <button
                                        type="button"
                                        @click="removeProduct(product.id)"
                                        class="text-red-600 hover:text-red-700 text-sm font-medium"
                                    >
                                        {{ __('Remove') }}
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <!-- Multiple Images Upload -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('Product Images (Max 6 images, 5MB each)') }}
                                        </label>

                                        <!-- Image Previews Grid -->
                                        <div x-show="product.images && product.images.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-3">
                                            <template x-for="(image, imgIndex) in product.images" :key="image.id">
                                                <div class="relative group">
                                                    <div class="relative w-full h-32 rounded-lg overflow-hidden bg-gray-200 border-2 border-gray-300">
                                                        <img :src="image.preview" :alt="'Product image ' + (imgIndex + 1)" class="w-full h-full object-cover">
                                                    </div>
                                                    <button
                                                        type="button"
                                                        @click="removeProductImage(product.id, image.id)"
                                                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700 opacity-0 group-hover:opacity-100 transition-opacity"
                                                        title="{{ __('Remove image') }}"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                    <span class="absolute bottom-1 right-1 bg-black bg-opacity-60 text-white text-xs px-2 py-1 rounded">
                                                        <span x-text="imgIndex + 1"></span>/<span x-text="product.images.length"></span>
                                                    </span>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Upload Button (only show if less than 6 images) -->
                                        <div x-show="!product.images || product.images.length < 6">
                                            <label
                                                :for="'product-images-' + product.id"
                                                class="flex flex-col items-center justify-center w-full h-32 px-4 py-6 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 hover:border-blue-500 transition-all"
                                                @dragover.prevent="$el.classList.add('border-blue-500', 'bg-blue-50')"
                                                @dragleave.prevent="$el.classList.remove('border-blue-500', 'bg-blue-50')"
                                                @drop.prevent="handleProductImageChange(product.id, $event); $el.classList.remove('border-blue-500', 'bg-blue-50')"
                                                :class="product.isUploadingImage ? 'pointer-events-none opacity-60' : ''"
                                            >
                                                <div class="flex flex-col items-center justify-center text-center">
                                                    <div x-show="product.isUploadingImage" class="flex flex-col items-center">
                                                        <svg class="w-10 h-10 mb-2 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        <p class="text-sm text-blue-600 font-medium">{{ __('Uploading images...') }}</p>
                                                    </div>
                                                    <div x-show="!product.isUploadingImage">
                                                        <svg class="w-10 h-10 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                        </svg>
                                                        <p class="text-sm text-gray-600 font-medium">
                                                            <span class="text-blue-600">{{ __('Click to upload') }}</span> {{ __('or drag and drop') }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            <span x-text="product.images ? `${product.images.length}/6 images ` : '0/6 images '"></span>
                                                            {{ __('PNG, JPG up to 5MB each') }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <input
                                                    :id="'product-images-' + product.id"
                                                    type="file"
                                                    :name="'products[' + index + '][images][]'"
                                                    accept="image/*"
                                                    multiple
                                                    @change="handleProductImageChange(product.id, $event)"
                                                    class="hidden"
                                                >
                                            </label>
                                        </div>
                                        <p x-show="product.images && product.images.length >= 6" class="text-sm text-green-600 mt-1">
                                            {{ __('Maximum 6 images uploaded') }}
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Product Name') }} *</label>
                                            <input
                                                type="text"
                                                :name="'products[' + index + '][name]'"
                                                x-model="product.name"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                                placeholder="{{ __('Enter product name') }}"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }} *</label>
                                            <div x-data="searchableSelectProductCategory(product.id)" class="relative">
                                                <input type="hidden" :name="'products[' + index + '][category]'" :value="selectedValue" x-model="selectedValue">
                                                <div class="relative">
                                                    <input
                                                        type="text"
                                                        x-model="searchQuery"
                                                        @click="isOpen = true"
                                                        @input="isOpen = true"
                                                        @keydown.escape="isOpen = false"
                                                        @keydown.arrow-down.prevent="highlightNext()"
                                                        @keydown.arrow-up.prevent="highlightPrevious()"
                                                        @keydown.enter.prevent="selectHighlighted()"
                                                        @blur="validateAndUpdateCategory()"
                                                        :placeholder="selectedValue || '{{ __('Select a category') }}'"
                                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all pr-10"
                                                        autocomplete="off"
                                                    >
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div x-show="isOpen"
                                                     @click.away="isOpen = false"
                                                     x-transition
                                                     style="z-index: 9999;"
                                                     class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 sm:max-h-60 overflow-auto">
                                                    <template x-for="(option, index) in filteredOptions" :key="option">
                                                        <div @mousedown.prevent="selectOption(option)"
                                                             :class="{'bg-blue-50': index === highlightedIndex}"
                                                             class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors"
                                                             x-text="option">
                                                        </div>
                                                    </template>
                                                    <div x-show="filteredOptions.length === 0" class="px-4 py-2 text-gray-500 text-sm">
                                                        {{ __('No matches found') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <input
                                                x-show="product.category === 'Other'"
                                                type="text"
                                                :name="'products[' + index + '][custom_category]'"
                                                x-model="product.customCategory"
                                                placeholder="{{ __('Specify category') }}"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all mt-2"
                                            >
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }} *</label>
                                        <textarea
                                            :name="'products[' + index + '][description]'"
                                            x-model="product.description"
                                            maxlength="500"
                                            rows="3"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                            placeholder="{{ __('Describe your product...') }}"
                                        ></textarea>
                                        <p class="text-xs text-gray-500 mt-1"><span x-text="product.description ? product.description.length : 0"></span>/500 {{ __('characters') }}</p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <button
                            type="button"
                            @click="addProduct()"
                            class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-500 transition-all"
                            :class="{'opacity-50 cursor-not-allowed': formData.products.length >= 5}"
                        >
                            <span x-text="formData.products.length >= 5 ? '✓ Maximum 5 Products Reached' : '+ Add Product (' + formData.products.length + '/5)'"></span>
                        </button>
                    </div>
                </div>
            </div>

