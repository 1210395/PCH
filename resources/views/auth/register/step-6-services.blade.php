            <!-- Step 6: Services -->
            <div x-show="currentStep === 6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 md:p-8 max-w-4xl mx-auto overflow-visible">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Services Offered') }}</h2>
                    <p class="text-gray-600 mb-6">{{ __('List the services you provide (optional but recommended)') }}</p>

                    <!-- Error Messages -->
                    <div x-show="errors.services" class="mb-4 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 animate-fade-in">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-red-700" x-text="errors.services"></p>
                        </div>
                    </div>

                    <div class="space-y-6 overflow-visible">
                        <template x-for="(service, index) in formData.services" :key="service.id">
                            <div class="bg-white rounded-xl p-4 sm:p-5 md:p-6 border-2 border-gray-200 hover:border-blue-300 transition-all duration-300 shadow-sm hover:shadow-md overflow-visible">
                                <!-- Header with Icon and Remove Button -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <h3 class="font-semibold text-gray-900 text-lg">{{ __('Service') }} <span x-text="index + 1"></span></h3>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeService(service.id)"
                                        class="flex items-center gap-1 text-red-600 hover:text-red-700 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-all text-sm font-medium"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        {{ __('Remove') }}
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Service Name') }} *</label>
                                            <input
                                                type="text"
                                                :name="'services[' + index + '][name]'"
                                                x-model="service.serviceName"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                                placeholder="{{ __('Enter service name') }}"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }} *</label>
                                            <div x-data="searchableSelectServiceCategory(service.id)" class="relative">
                                                <input type="hidden" :name="'services[' + index + '][category]'" :value="selectedValue" x-model="selectedValue">
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
                                                     class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
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
                                                x-show="service.category === 'Other'"
                                                type="text"
                                                :name="'services[' + index + '][custom_category]'"
                                                x-model="service.customCategory"
                                                placeholder="{{ __('Specify category') }}"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all mt-2"
                                            >
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }} *</label>
                                        <textarea
                                            :name="'services[' + index + '][description]'"
                                            x-model="service.description"
                                            maxlength="500"
                                            rows="3"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                            placeholder="{{ __('Describe what this service includes...') }}"
                                        ></textarea>
                                        <p class="text-xs text-gray-500 mt-1"><span x-text="service.description ? service.description.length : 0"></span>/500 {{ __('characters') }}</p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <button
                            type="button"
                            @click="addService()"
                            class="group w-full py-6 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-blue-500 hover:bg-blue-50 transition-all duration-300"
                        >
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <span class="font-semibold group-hover:text-blue-600 transition-colors">{{ __('Add Service') }}</span>
                                <span class="text-sm text-gray-500">{{ __('Click to add a new service') }}</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

