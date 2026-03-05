    {{-- Project Modal --}}
    <div x-show="projectModal" x-cloak @click.self="closeProjectModal()" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90 translate-y-8" x-transition:enter-end="opacity-100 transform scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden border border-gray-100">
            {{-- Modal Header with Gradient --}}
            <div class="relative p-6 pb-8 bg-gradient-to-br from-blue-700 via-blue-600 to-cyan-600 text-white overflow-hidden">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDE2YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6TTEyIDM2YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
                <div class="relative flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold tracking-tight" x-text="projectForm.id ? '{{ __('Edit Project') }}' : '{{ __('Add New Project') }}'"></h2>
                        </div>
                        <p class="text-blue-50 text-sm ml-13" x-text="projectForm.id ? '{{ __('Update your project details') }}' : '{{ __('Add a new project to your portfolio') }}'"></p>
                    </div>
                    <button @click="closeProjectModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur-sm flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="overflow-y-auto max-h-[calc(90vh-220px)]">
                <div class="p-6 space-y-5">
                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <span>{{ __('Project Title') }}</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" x-model="projectForm.title" placeholder="e.g., Modern Villa Design" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none group-hover:border-gray-300">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <span>{{ __('Description') }}</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="projectForm.description" :placeholder="'{{ __('Describe your project\'s goals, challenges, and solutions...') }}'" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none resize-none group-hover:border-gray-300"></textarea>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Share what makes this project special') }}</p>
                    </div>

                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <span>{{ __('Your Role') }}</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <select x-model="projectForm.role" @change="if (projectForm.role !== 'Other') { projectForm.customRole = '' }" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none appearance-none bg-white group-hover:border-gray-300 cursor-pointer" style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                            <option value="">{{ __('Select your role') }}</option>
                            <option value="Lead Designer">Lead Designer</option>
                            <option value="Designer">Designer</option>
                            <option value="Architect">Architect</option>
                            <option value="Interior Designer">Interior Designer</option>
                            <option value="Interior Architect">Interior Architect</option>
                            <option value="Lead Interior & Furniture Designer">Lead Interior & Furniture Designer</option>
                            <option value="Interior Architect & Fit-Out Designer">Interior Architect & Fit-Out Designer</option>
                            <option value="Interior Designer & Revit Modeler">Interior Designer & Revit Modeler</option>
                            <option value="Key Urban Planner">Key Urban Planner</option>
                            <option value="Lead Graphic Designer">Lead Graphic Designer</option>
                            <option value="Lead UI/UX Designer">Lead UI/UX Designer</option>
                            <option value="Lead Social Media Designer">Lead Social Media Designer</option>
                            <option value="3D Rendering Specialist">3D Rendering Specialist</option>
                            <option value="Project Manager">Project Manager</option>
                            <option value="Planning & Supervision">Planning & Supervision</option>
                            <option value="Developer">Developer</option>
                            <option value="Services Provider">Services Provider</option>
                            <option value="Other">Other</option>
                        </select>
                        <input
                            x-show="projectForm.role === 'Other'"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            type="text"
                            x-model="projectForm.customRole"
                            placeholder="{{ __('Specify your role') }}"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none mt-3"
                        >
                    </div>

                    {{-- Project Images Section --}}
                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Project Images') }}</label>
                        <div class="space-y-3">
                            <div>
                                <input
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    @change="handleProjectImageUpload($event)"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-gray-300 transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                >
                                <p class="text-sm text-gray-500 mt-2">
                                    <span x-show="projectForm.images && projectForm.images.length > 0" x-text="projectForm.images.length + ' of 6 images uploaded. '"></span>
                                    {{ __('Select multiple images (Ctrl/Cmd + Click). Max 6 images, 5MB each.') }}
                                </p>
                            </div>
                            <p x-show="projectForm.uploading" class="text-sm text-blue-600 flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('Uploading images...') }}
                            </p>

                            {{-- Image Previews --}}
                            <div x-show="projectForm.images && projectForm.images.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <template x-for="(image, index) in projectForm.images" :key="index">
                                    <div class="relative group/img">
                                        <img :src="image.preview" class="w-full h-24 object-cover rounded-lg border-2 border-gray-200">
                                        <button
                                            type="button"
                                            @click="removeProjectImage(index)"
                                            class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center hover:bg-red-600"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
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
            <div class="p-6 border-t border-gray-100 bg-gray-50/50 flex gap-3 justify-end">
                <button @click="closeProjectModal()" class="px-6 py-3 border-2 border-gray-200 hover:border-gray-300 bg-white text-gray-700 rounded-xl font-semibold transition-all duration-200 hover:shadow-md hover:scale-[1.02] active:scale-95">
                    {{ __('Cancel') }}
                </button>
                <button @click="saveProject()" :disabled="projectSubmitting" :class="projectSubmitting ? 'opacity-70 cursor-not-allowed' : 'hover:shadow-xl hover:shadow-blue-500/30 hover:scale-[1.02] active:scale-95'" class="px-6 py-3 bg-gradient-to-r from-blue-600 via-blue-700 to-cyan-600 text-white rounded-xl font-semibold transition-all duration-200 flex items-center gap-2">
                    <svg x-show="projectSubmitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="projectForm.id ? '{{ __('Update Project') }}' : '{{ __('Add Project') }}'"></span>
                    <svg x-show="!projectSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Product Modal --}}
    <div x-show="productModal" x-cloak @click.self="closeProductModal()" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90 translate-y-8" x-transition:enter-end="opacity-100 transform scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden border border-gray-100">
            {{-- Modal Header with Gradient --}}
            <div class="relative p-6 pb-8 bg-gradient-to-br from-blue-700 via-blue-600 to-cyan-600 text-white overflow-hidden">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDE2YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6TTEyIDM2YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
                <div class="relative flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold tracking-tight" x-text="productForm.id ? '{{ __('Edit Product') }}' : '{{ __('Add New Product') }}'"></h2>
                        </div>
                        <p class="text-blue-50 text-sm ml-13" x-text="productForm.id ? '{{ __('Update your product details') }}' : '{{ __('Add a new product to showcase') }}'"></p>
                    </div>
                    <button @click="closeProductModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur-sm flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="overflow-y-auto max-h-[calc(90vh-220px)]">
                <div class="p-6 space-y-5">
                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <span>{{ __('Product Name') }}</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" x-model="productForm.name" placeholder="e.g., Handcrafted Oak Dining Table" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none group-hover:border-gray-300">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <span>{{ __('Description') }}</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="productForm.description" :placeholder="'{{ __('Describe your product\'s features, materials, and unique qualities...') }}'" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none resize-none group-hover:border-gray-300"></textarea>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Highlight what makes this product special') }}</p>
                    </div>

                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <span>{{ __('Category') }}</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <select x-model="productForm.category" @change="if (productForm.category !== 'Other') { productForm.customCategory = '' }" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none appearance-none bg-white group-hover:border-gray-300 cursor-pointer" style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                            <option value="">{{ __('Select category') }}</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Interior Design">Interior Design</option>
                            <option value="Architecture">Architecture</option>
                            <option value="Decoration Pieces">Decoration Pieces</option>
                            <option value="Artwork">Artwork</option>
                            <option value="Printmaking Artwork">Printmaking Artwork</option>
                            <option value="Kitchens">Kitchens</option>
                            <option value="Bedrooms">Bedrooms</option>
                            <option value="Dining Tables">Dining Tables</option>
                            <option value="Sofas & Seating">Sofas & Seating</option>
                            <option value="Wood Works">Wood Works</option>
                            <option value="Sanitary Ware">Sanitary Ware</option>
                            <option value="Glass Products">Glass Products</option>
                            <option value="Fabrics & Textiles">Fabrics & Textiles</option>
                            <option value="Lighting">Lighting</option>
                            <option value="Space Planning">Space Planning</option>
                            <option value="Product Design">Product Design</option>
                            <option value="Drawing on Glass">Drawing on Glass</option>
                            <option value="Building">Building</option>
                            <option value="Designing">Designing</option>
                            <option value="Other">Other</option>
                        </select>
                        <input
                            x-show="productForm.category === 'Other'"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            type="text"
                            x-model="productForm.customCategory"
                            placeholder="{{ __('Specify category') }}"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none mt-3"
                        >
                    </div>

                    {{-- Product Images Section --}}
                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Product Images') }}</label>
                        <div class="space-y-3">
                            <div>
                                <input
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    @change="handleProductImageUpload($event)"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-gray-300 transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                >
                                <p class="text-sm text-gray-500 mt-2">
                                    <span x-show="productForm.images && productForm.images.length > 0" x-text="productForm.images.length + ' of 6 images uploaded. '"></span>
                                    {{ __('Select multiple images (Ctrl/Cmd + Click). Max 6 images, 5MB each.') }}
                                </p>
                            </div>
                            <p x-show="productForm.uploading" class="text-sm text-blue-600 flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('Uploading images...') }}
                            </p>

                            {{-- Image Previews --}}
                            <div x-show="productForm.images && productForm.images.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <template x-for="(image, index) in productForm.images" :key="index">
                                    <div class="relative group/img">
                                        <img :src="image.preview" class="w-full h-24 object-cover rounded-lg border-2 border-gray-200">
                                        <button
                                            type="button"
                                            @click="removeProductImage(index)"
                                            class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center hover:bg-red-600"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
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
            <div class="p-6 border-t border-gray-100 bg-gray-50/50 flex gap-3 justify-end">
                <button @click="closeProductModal()" class="px-6 py-3 border-2 border-gray-200 hover:border-gray-300 bg-white text-gray-700 rounded-xl font-semibold transition-all duration-200 hover:shadow-md hover:scale-[1.02] active:scale-95">
                    {{ __('Cancel') }}
                </button>
                <button @click="saveProduct()" :disabled="productSubmitting" :class="productSubmitting ? 'opacity-70 cursor-not-allowed' : 'hover:shadow-xl hover:shadow-blue-500/30 hover:scale-[1.02] active:scale-95'" class="px-6 py-3 bg-gradient-to-r from-blue-600 via-blue-700 to-cyan-600 text-white rounded-xl font-semibold transition-all duration-200 flex items-center gap-2">
                    <svg x-show="productSubmitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="productForm.id ? '{{ __('Update Product') }}' : '{{ __('Add Product') }}'"></span>
                    <svg x-show="!productSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Service Modal --}}
    <div x-show="serviceModal" x-cloak @click.self="closeServiceModal()" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90 translate-y-8" x-transition:enter-end="opacity-100 transform scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden border border-gray-100">
            {{-- Modal Header with Gradient --}}
            <div class="relative p-6 pb-8 bg-gradient-to-br from-blue-700 via-blue-600 to-cyan-600 text-white overflow-hidden">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDE2YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6TTEyIDM2YzAtMy4zMTQgMi42ODYtNiA2LTZzNiAyLjY4NiA2IDYtMi42ODYgNi02IDYtNi0yLjY4Ni02LTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
                <div class="relative flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold tracking-tight" x-text="serviceForm.id ? '{{ __('Edit Service') }}' : '{{ __('Add New Service') }}'"></h2>
                        </div>
                        <p class="text-blue-50 text-sm ml-13" x-text="serviceForm.id ? '{{ __('Update your service details') }}' : '{{ __('Add a new service you provide') }}'"></p>
                    </div>
                    <button @click="closeServiceModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur-sm flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="overflow-y-auto max-h-[calc(90vh-220px)]">
                <div class="p-6 space-y-5">
                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <span>{{ __('Service Name') }}</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" x-model="serviceForm.name" placeholder="e.g., Interior Design Consultation" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none group-hover:border-gray-300">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <span>{{ __('Description') }}</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="serviceForm.description" :placeholder="'{{ __('Describe your service, what clients can expect, and the value you provide...') }}'" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none resize-none group-hover:border-gray-300"></textarea>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Explain the benefits and deliverables of this service') }}</p>
                    </div>

                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <span>{{ __('Category') }}</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <select x-model="serviceForm.category" @change="if (serviceForm.category !== 'Other') { serviceForm.customCategory = '' }" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none appearance-none bg-white group-hover:border-gray-300 cursor-pointer" style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                            <option value="">{{ __('Select category') }}</option>
                            <option value="Design">Design</option>
                            <option value="Consultation">Consultation</option>
                            <option value="Development">Development</option>
                            <option value="Supervision">Supervision</option>
                            <option value="Manufacturing">Manufacturing</option>
                            <option value="Carpentry">Carpentry</option>
                            <option value="Photography">Photography</option>
                            <option value="Graphic Design">Graphic Design</option>
                            <option value="Digital Illustration">Digital Illustration</option>
                            <option value="Material Specification">Material Specification</option>
                            <option value="Installation">Installation</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Strategy">Strategy</option>
                            <option value="Other">Other</option>
                        </select>
                        <input
                            x-show="serviceForm.category === 'Other'"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            type="text"
                            x-model="serviceForm.customCategory"
                            placeholder="{{ __('Specify category') }}"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-200 outline-none mt-3"
                        >
                    </div>

                    {{-- Service Image Section --}}
                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Service Image') }}</label>
                        <div class="space-y-3">
                            <div>
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleServiceImageUpload($event)"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-gray-300 transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                >
                                <p class="text-sm text-gray-500 mt-2">
                                    {{ __('JPG, PNG or GIF (max. 5MB)') }}
                                </p>
                            </div>
                            <p x-show="serviceForm.uploading" class="text-sm text-blue-600 flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('Uploading image...') }}
                            </p>

                            {{-- Image Preview --}}
                            <div x-show="serviceForm.imagePreview" class="relative inline-block">
                                <img :src="serviceForm.imagePreview" class="w-32 h-32 object-cover rounded-lg border-2 border-gray-200">
                                <button
                                    type="button"
                                    @click="removeServiceImage()"
                                    class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="p-6 border-t border-gray-100 bg-gray-50/50 flex gap-3 justify-end">
                <button @click="closeServiceModal()" class="px-6 py-3 border-2 border-gray-200 hover:border-gray-300 bg-white text-gray-700 rounded-xl font-semibold transition-all duration-200 hover:shadow-md hover:scale-[1.02] active:scale-95">
                    {{ __('Cancel') }}
                </button>
                <button @click="saveService()" :disabled="serviceSubmitting" :class="serviceSubmitting ? 'opacity-70 cursor-not-allowed' : 'hover:shadow-xl hover:shadow-blue-500/30 hover:scale-[1.02] active:scale-95'" class="px-6 py-3 bg-gradient-to-r from-blue-600 via-blue-700 to-cyan-600 text-white rounded-xl font-semibold transition-all duration-200 flex items-center gap-2">
                    <svg x-show="serviceSubmitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="serviceForm.id ? '{{ __('Update Service') }}' : '{{ __('Add Service') }}'"></span>
                    <svg x-show="!serviceSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

</div>
{{-- Closes the main Alpine.js container opened in profile-edit-alpine-data.blade.php --}}