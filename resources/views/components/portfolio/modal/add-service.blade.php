<!-- Add Service Modal - REDESIGNED -->
<div x-show="addServiceModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
         @click="addServiceModal = false"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl border border-gray-200 max-w-2xl w-full p-4 sm:p-6 max-h-[90vh] overflow-y-auto overflow-visible">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">{{ __('Add New Service') }}</h3>

            <div class="space-y-4">
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

                <div x-data="serviceCategorySelect()" x-init="initFromParent()">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Category') }} *</label>
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
                            @blur="validateAndUpdate()"
                            :placeholder="'{{ __('Select a category') }}'"
                            class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base pr-10"
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
                         class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                        <template x-for="(option, optIdx) in filteredOptions" :key="option">
                            <div @mousedown.prevent="selectOption(option)"
                                 :class="{'bg-blue-50': optIdx === highlightedIndex}"
                                 class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors"
                                 x-text="option">
                            </div>
                        </template>
                        <div x-show="filteredOptions.length === 0" class="px-4 py-2 text-gray-500 text-sm">
                            {{ __('No matches found') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button @click="addServiceModal = false"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm sm:text-base">
                    {{ __('Cancel') }}
                </button>
                <button @click="submitAddService()"
                        :disabled="isSubmitting"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all font-medium disabled:opacity-50 text-sm sm:text-base">
                    <span x-show="!isSubmitting">{{ __('Create Service') }}</span>
                    <span x-show="isSubmitting">{{ __('Creating...') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
