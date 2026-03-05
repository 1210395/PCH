@props(['type', 'items', 'title', 'iconPath'])

@php
    $itemsVar = $type . 's';
    $modalFunc = 'open' . ucfirst($type) . 'Modal';
    $editFunc = 'edit' . ucfirst($type);
    $deleteFunc = 'delete' . ucfirst($type);
@endphp

<div x-show="activeTab === '{{ $itemsVar }}'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-6" x-data="{
    {{ $itemsVar }}: @js($items),
}">
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
        <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex-1">
                <h2 class="text-base sm:text-lg font-semibold">{{ __('Manage') }} {{ $title }}</h2>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">{{ __('Add, edit, or remove your portfolio items') }}</p>
            </div>
            <button @click="{{ $modalFunc }}()" class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white hover:opacity-90 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 text-sm hover:shadow-lg transform hover:scale-105 w-full sm:w-auto flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>{{ __('Add') }} {{ rtrim($title, 's') }}</span>
            </button>
        </div>
        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 sm:gap-6">
                <template x-for="(item, index) in {{ $itemsVar }}" :key="item.id">
                    @if($type === 'service')
                    {{-- Service Card - Icon-based design without images --}}
                    <div x-show="item" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 relative p-4 sm:p-6">
                        <!-- Service Icon -->
                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center mb-3 sm:mb-4">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>

                        <!-- Edit/Delete Buttons (Top Right) -->
                        <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <button @click="{{ $editFunc }}(item)" class="p-2 hover:bg-blue-50 rounded-lg transition-colors duration-200" title="{{ __('Edit Service') }}">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button @click="{{ $deleteFunc }}(item.id, index)" class="p-2 hover:bg-red-50 rounded-lg transition-colors duration-200" title="{{ __('Delete Service') }}">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Service Info -->
                        <div>
                            <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 mb-3" x-text="item.category || '{{ __('Uncategorized') }}'"></span>
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3" x-text="item.name"></h3>
                            <p class="text-sm sm:text-base text-gray-600 leading-relaxed" x-text="item.description"></p>
                        </div>
                    </div>
                    @else
                    {{-- Project/Product Card - Image-based design --}}
                    <div x-show="item" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 transform scale-90" class="bg-white rounded-lg border overflow-hidden group hover:shadow-lg transition-all duration-300">
                        <div class="relative h-40 sm:h-48 bg-gray-100">
                            <template x-if="item.image_paths && item.image_paths.length > 0">
                                <img :src="item.image_paths[0]" :alt="item.title || item.name" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            </template>
                            <template x-if="!item.image_paths || item.image_paths.length === 0">
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-600 to-green-500 text-white text-xl font-semibold">
                                    <span x-text="(item.title || item.name || '').substring(0, 2).toUpperCase()"></span>
                                </div>
                            </template>
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-2 px-2">
                                <button @click="{{ $editFunc }}(item)" class="px-2 sm:px-3 py-1.5 bg-white hover:bg-gray-100 text-gray-900 rounded text-xs sm:text-sm font-medium flex items-center gap-1 transform hover:scale-110 transition-all duration-200">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span class="hidden xs:inline">{{ __('Edit') }}</span>
                                </button>
                                <button @click="{{ $deleteFunc }}(item.id, index)" class="px-2 sm:px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs sm:text-sm font-medium flex items-center gap-1 transform hover:scale-110 transition-all duration-200">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span class="hidden xs:inline">{{ __('Delete') }}</span>
                                </button>
                            </div>
                        </div>
                        <div class="p-3 sm:p-4">
                            @if($type === 'project')
                                <span class="inline-block px-2 sm:px-3 py-0.5 sm:py-1 bg-gray-100 text-gray-700 text-[10px] sm:text-xs font-medium rounded-full mb-2" x-text="item.role || '{{ __('No role specified') }}'"></span>
                            @elseif($type === 'product')
                                <span class="inline-block px-2 sm:px-3 py-0.5 sm:py-1 bg-gray-100 text-gray-700 text-[10px] sm:text-xs font-medium rounded-full mb-2" x-text="item.category || '{{ __('No category') }}'"></span>
                            @endif
                            <h3 class="text-sm sm:text-base font-semibold mb-1 sm:mb-2 line-clamp-2" x-text="item.title || item.name"></h3>
                            <p class="text-xs sm:text-sm text-gray-600 line-clamp-2" x-text="item.description"></p>
                        </div>
                    </div>
                    @endif
                </template>
            </div>
            <template x-if="{{ $itemsVar }}.length === 0">
                <div class="text-center py-12 animate-fade-in">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/>
                    </svg>
                    <p class="text-gray-600 mb-4">{{ __('No items yet') }}</p>
                    <button @click="{{ $modalFunc }}()" class="inline-flex items-center px-4 py-2 border border-gray-300 hover:border-gray-400 bg-white text-gray-700 rounded-lg font-medium transition-all duration-200 hover:shadow-md transform hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Add Your First') }} {{ rtrim($title, 's') }}
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>
