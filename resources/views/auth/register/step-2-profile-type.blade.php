            <!-- Step 2: Profile Type -->
            <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 md:p-8 max-w-2xl mx-auto overflow-visible">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Choose Your Profile Type') }}</h2>
                    <p class="text-gray-600 mb-6">{{ __('Help us understand what you do') }}</p>

                    <div class="space-y-4 overflow-visible">
                        <div>
                            <label for="sector" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Profile Type') }} *</label>
                            <!-- Hidden input bound to wizard's formData, outside the searchable component -->
                            <input type="hidden" name="sector" x-model="formData.sector">
                            <div x-data="searchableSelectSectorWithOptions()" class="relative">
                                <div class="relative">
                                    <input
                                        type="text"
                                        x-model="searchQuery"
                                        @click="isOpen = true"
                                        @focus="isOpen = true"
                                        @input="isOpen = true"
                                        @keydown.escape="isOpen = false"
                                        @keydown.arrow-down.prevent="highlightNext()"
                                        @keydown.arrow-up.prevent="highlightPrevious()"
                                        @keydown.enter.prevent="selectHighlighted()"
                                        :placeholder="selectedLabel || '{{ __('Select your profile type') }}'"
                                        :class="errors.sector ? 'border-red-500' : 'border-gray-300'"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all pr-10 text-gray-900 placeholder-gray-500"
                                        autocomplete="off"
                                        required
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
                                    <template x-for="(option, index) in filteredOptions" :key="option.value">
                                        <div @mousedown.prevent="selectOption(option)"
                                             :class="{'bg-blue-50': index === highlightedIndex}"
                                             class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors border-b"
                                             x-text="option.label">
                                        </div>
                                    </template>
                                    <div x-show="filteredOptions.length === 0" class="px-4 py-2 text-red-500">
                                        {{ __('No options available!') }}
                                    </div>
                                </div>
                            </div>
                            <p x-show="errors.sector" x-text="errors.sector" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div x-show="formData.sector && formData.sector !== 'guest'" x-transition>
                            <label for="subSector" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Specialization') }} *</label>
                            <!-- Hidden input bound to wizard's formData, outside the searchable component -->
                            <input type="hidden" name="sub_sector" x-model="formData.subSector">
                            <div x-data="searchableSelectSubSector()" class="relative">
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
                                        :placeholder="selectedValue || '{{ __('Select your specialization') }}'"
                                        :class="errors.subSector ? 'border-red-500' : 'border-gray-300'"
                                        :required="formData.sector !== '' && formData.sector !== 'guest'"
                                        :disabled="formData.sector === 'guest'"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all pr-10 disabled:bg-gray-100 disabled:cursor-not-allowed"
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
                                    <template x-for="(option, index) in filteredOptions" :key="option">
                                        <div @mousedown.prevent="selectOption(option)"
                                             :class="{'bg-blue-50': index === highlightedIndex}"
                                             class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors"
                                             x-text="option">
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <p x-show="errors.subSector" x-text="errors.subSector" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Hidden input for guest users - always submit when guest -->
                        <input type="hidden" x-bind:name="formData.sector === 'guest' ? 'sub_sector' : ''" x-bind:value="formData.sector === 'guest' ? 'Guest' : ''">

                        <!-- Showroom Field for Manufacturers -->
                        <div x-show="formData.sector === 'manufacturer'" x-transition>
                            <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('Do you have a Showroom?') }}</label>
                            <div class="flex gap-6">
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="radio"
                                        name="has_showroom"
                                        value="yes"
                                        x-model="formData.hasShowroom"
                                        class="w-4 h-4 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                    >
                                    <span class="ml-2 text-gray-700">{{ __('Yes') }}</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="radio"
                                        name="has_showroom"
                                        value="no"
                                        x-model="formData.hasShowroom"
                                        class="w-4 h-4 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                    >
                                    <span class="ml-2 text-gray-700">{{ __('No') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

