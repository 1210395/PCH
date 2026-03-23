<!-- Edit Project Modal - REDESIGNED -->
<div x-show="editProjectModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
         @click="editProjectModal = false"></div>
    <div class="flex items-end sm:items-center justify-center min-h-screen p-0 sm:p-4">
        <div class="relative bg-white rounded-t-xl sm:rounded-xl shadow-xl border border-gray-200 max-w-2xl w-full p-4 sm:p-6 h-[95vh] sm:h-auto sm:max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">{{ __('Edit Project') }}</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Title') }}</label>
                    <input type="text" x-model="currentItem.title"
                           class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }}</label>
                    <textarea x-model="currentItem.description" rows="3"
                              class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Category') }} *</label>
                    <div x-data="searchableProjectCategory()" class="relative">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @click="isOpen = true"
                            @focus="isOpen = true"
                            @input="isOpen = true"
                            @blur="updateCategory()"
                            @keydown.escape="isOpen = false"
                            @keydown.arrow-down.prevent="highlightNext()"
                            @keydown.arrow-up.prevent="highlightPrevious()"
                            @keydown.enter.prevent="selectHighlighted()"
                            :placeholder="selectedValue || 'Select project category'"
                            class="w-full px-3 sm:px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div x-show="isOpen && filteredOptions.length > 0"
                             @click.away="isOpen = false"
                             x-transition
                             style="z-index: 9999;"
                             class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                            <template x-for="(option, index) in filteredOptions" :key="option">
                                <div @mousedown.prevent="selectOption(option)"
                                     :class="{'bg-blue-50': index === highlightedIndex}"
                                     class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors"
                                     x-text="option">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Your Role') }} *</label>
                    <div x-data="searchableProjectRole()" class="relative">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @click="isOpen = true"
                            @focus="isOpen = true"
                            @input="isOpen = true"
                            @blur="updateRole()"
                            @keydown.escape="isOpen = false"
                            @keydown.arrow-down.prevent="highlightNext()"
                            @keydown.arrow-up.prevent="highlightPrevious()"
                            @keydown.enter.prevent="selectHighlighted()"
                            :placeholder="selectedValue || 'Select your role'"
                            class="w-full px-3 sm:px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div x-show="isOpen && filteredOptions.length > 0"
                             @click.away="isOpen = false"
                             x-transition
                             style="z-index: 9999;"
                             class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                            <template x-for="(option, index) in filteredOptions" :key="option">
                                <div @mousedown.prevent="selectOption(option)"
                                     :class="{'bg-blue-50': index === highlightedIndex}"
                                     class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors"
                                     x-text="option">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

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
            </div>

            <div class="flex gap-3 mt-6">
                <button @click="editProjectModal = false"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm sm:text-base">
                    {{ __('Cancel') }}
                </button>
                <button @click="submitEditProject()"
                        :disabled="isSubmitting"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all font-medium disabled:opacity-50 text-sm sm:text-base">
                    <span x-show="!isSubmitting">{{ __('Update Project') }}</span>
                    <span x-show="isSubmitting">{{ __('Updating...') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
