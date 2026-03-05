{{-- Marketplace Post Modal --}}
<div x-show="marketplaceModal" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape.window="closeMarketplaceModal()">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
         @click="closeMarketplaceModal()"></div>

    {{-- Modal Container --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl transform transition-all"
             @click.stop
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-purple-600 to-pink-500 rounded-t-2xl">
                <h3 class="text-xl font-semibold text-white" x-text="marketplaceForm.id ? '{{ __('Edit Marketplace Post') }}' : '{{ __('Create Marketplace Post') }}'"></h3>
                <button @click="closeMarketplaceModal()" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form @submit.prevent="saveMarketplacePost()" class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                {{-- Import from Product/Project --}}
                <div x-show="!marketplaceForm.id" class="bg-purple-50 rounded-xl p-4 border border-purple-100">
                    <label class="block text-sm font-medium text-purple-800 mb-3">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        {{ __('Import from existing content (Optional)') }}
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <select x-model="marketplaceForm.sourceType"
                                    @change="marketplaceForm.sourceId = ''"
                                    class="w-full px-3 py-2.5 border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm bg-white">
                                <option value="">{{ __('Select type...') }}</option>
                                <option value="product">{{ __('Product') }}</option>
                                <option value="project">{{ __('Project') }}</option>
                            </select>
                        </div>
                        <div>
                            <select x-model="marketplaceForm.sourceId"
                                    :disabled="!marketplaceForm.sourceType"
                                    @change="loadSourceData()"
                                    class="w-full px-3 py-2.5 border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm bg-white disabled:bg-gray-100 disabled:cursor-not-allowed">
                                <option value="">{{ __('Select item...') }}</option>
                                <template x-for="product in (marketplaceForm.sourceType === 'product' ? products : [])" :key="'product-' + product.id">
                                    <option :value="product.id" x-text="product.name || product.title"></option>
                                </template>
                                <template x-for="project in (marketplaceForm.sourceType === 'project' ? projects : [])" :key="'project-' + project.id">
                                    <option :value="project.id" x-text="project.title"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <p class="text-xs text-purple-600 mt-2">{{ __('Select a product or project to auto-fill details') }}</p>
                </div>

                {{-- Title --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ __('Title') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="marketplaceForm.title" required maxlength="255"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                           placeholder="{{ __('Enter post title') }}">
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ __('Description') }} <span class="text-red-500">*</span>
                    </label>
                    <textarea x-model="marketplaceForm.description" required rows="4" maxlength="2000"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all resize-none"
                              placeholder="{{ __('Describe your post...') }}"></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <span x-text="(marketplaceForm.description || '').length"></span>/2000 {{ __('characters') }}
                    </p>
                </div>

                {{-- Type & Category --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('Type') }} <span class="text-red-500">*</span>
                        </label>
                        <select x-model="marketplaceForm.type" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                            <option value="">{{ __('Select type...') }}</option>
                            @foreach(\App\Helpers\DropdownHelper::marketplaceTypes() as $mtype)
                                <option value="{{ $mtype['value'] }}">{{ $mtype['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ __('Category') }} <span class="text-red-500">*</span>
                        </label>
                        <select x-model="marketplaceForm.category" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                            <option value="">{{ __('Select category...') }}</option>
                            @foreach(\App\Helpers\DropdownHelper::marketplaceCategories() as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tags --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ __('Tags') }}
                    </label>
                    <div class="flex flex-wrap gap-2 p-3 border border-gray-300 rounded-lg max-h-40 overflow-y-auto bg-white">
                        @foreach(\App\Helpers\DropdownHelper::marketplaceTags() as $tag)
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border cursor-pointer transition-colors text-sm"
                                   :class="marketplaceForm.selectedTags.includes('{{ $tag }}') ? 'bg-purple-100 border-purple-500 text-purple-700' : 'bg-gray-50 border-gray-200 text-gray-600 hover:border-gray-300'">
                                <input type="checkbox" value="{{ $tag }}" x-model="marketplaceForm.selectedTags" class="sr-only">
                                <span>{{ $tag }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Select relevant tags for your post') }}</p>
                </div>

                {{-- Image Upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ __('Image') }}
                    </label>
                    <div class="flex items-start gap-4">
                        {{-- Image Preview --}}
                        <div class="relative w-32 h-24 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            <template x-if="marketplaceForm.imagePreview || marketplaceForm.image">
                                <img :src="marketplaceForm.imagePreview || marketplaceForm.image"
                                     class="w-full h-full object-cover">
                            </template>
                            <template x-if="!marketplaceForm.imagePreview && !marketplaceForm.image">
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </template>
                        </div>

                        {{-- Upload Button --}}
                        <div class="flex-1">
                            <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-purple-500 hover:bg-purple-50 transition-all">
                                <div class="flex flex-col items-center justify-center pt-2 pb-3" x-show="!marketplaceForm.uploading">
                                    <svg class="w-6 h-6 mb-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-xs text-gray-500"><span class="font-semibold">{{ __('Click to upload') }}</span></p>
                                    <p class="text-xs text-gray-400">{{ __('PNG, JPG, GIF (Max 5MB)') }}</p>
                                </div>
                                <div x-show="marketplaceForm.uploading" class="flex items-center gap-2 text-purple-600">
                                    <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span class="text-sm">{{ __('Uploading...') }}</span>
                                </div>
                                <input type="file" class="hidden" accept="image/*"
                                       @change="handleMarketplaceImageUpload($event)"
                                       :disabled="marketplaceForm.uploading">
                            </label>
                            <button type="button" x-show="marketplaceForm.imagePreview || marketplaceForm.image"
                                    @click="removeMarketplaceImage()"
                                    class="mt-2 text-sm text-red-600 hover:text-red-700">
                                {{ __('Remove image') }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Pending Notice --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-sm text-yellow-800">
                            {{ __('Your post will be reviewed before appearing publicly. This usually takes 1-2 business days.') }}
                        </p>
                    </div>
                </div>
            </form>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50 rounded-b-2xl">
                <button type="button"
                        @click="closeMarketplaceModal()"
                        class="px-4 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">
                    {{ __('Cancel') }}
                </button>
                <button type="button"
                        @click="saveMarketplacePost()"
                        :disabled="marketplaceSubmitting"
                        class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 text-white font-medium rounded-lg hover:from-purple-700 hover:to-pink-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg x-show="marketplaceSubmitting" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span x-text="marketplaceForm.id ? '{{ __('Update Post') }}' : '{{ __('Create Post') }}'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
