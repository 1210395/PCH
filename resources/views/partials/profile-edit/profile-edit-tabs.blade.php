    {{-- Main Content --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        {{-- Tab Navigation --}}
        <div class="flex gap-2 mb-6 overflow-x-auto border-b border-gray-200 -mx-4 px-4 sm:mx-0 sm:px-0">
            <button @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'border-blue-600 text-blue-600 scale-105' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'" class="flex items-center gap-2 px-3 sm:px-4 py-2 border-b-2 font-medium transition-all duration-200 whitespace-nowrap text-sm">
                <svg class="w-4 h-4 transition-transform duration-200" :class="activeTab === 'profile' ? 'scale-110' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ __('Profile') }}
            </button>
            <button @click="activeTab = 'projects'" :class="activeTab === 'projects' ? 'border-blue-600 text-blue-600 scale-105' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'" class="flex items-center gap-2 px-3 sm:px-4 py-2 border-b-2 font-medium transition-all duration-200 whitespace-nowrap text-sm">
                <svg class="w-4 h-4 transition-transform duration-200" :class="activeTab === 'projects' ? 'scale-110' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                {{ __('Projects') }} <span x-show="projects.length > 0" x-text="'(' + projects.length + ')'" class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full ml-1"></span>
            </button>
            <button @click="activeTab = 'products'" :class="activeTab === 'products' ? 'border-blue-600 text-blue-600 scale-105' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'" class="flex items-center gap-2 px-3 sm:px-4 py-2 border-b-2 font-medium transition-all duration-200 whitespace-nowrap text-sm">
                <svg class="w-4 h-4 transition-transform duration-200" :class="activeTab === 'products' ? 'scale-110' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                {{ __('Products') }} <span x-show="products.length > 0" x-text="'(' + products.length + ')'" class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full ml-1"></span>
            </button>
            <button @click="activeTab = 'services'" :class="activeTab === 'services' ? 'border-blue-600 text-blue-600 scale-105' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'" class="flex items-center gap-2 px-3 sm:px-4 py-2 border-b-2 font-medium transition-all duration-200 whitespace-nowrap text-sm">
                <svg class="w-4 h-4 transition-transform duration-200" :class="activeTab === 'services' ? 'scale-110' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                {{ __('Services') }} <span x-show="services.length > 0" x-text="'(' + services.length + ')'" class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full ml-1"></span>
            </button>
        </div>

        {{-- Profile Tab --}}
        <div x-show="activeTab === 'profile'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-6">
            {{-- Cover & Profile Photos --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">{{ __('Profile Photos') }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ __('Update your profile and cover photos') }}</p>
                </div>
                <div class="p-4 sm:p-6 space-y-6">
                    {{-- Cover Photo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Cover Photo') }}</label>
                        <div class="relative group">
                            <div class="h-32 sm:h-48 w-full bg-gradient-to-br from-blue-600 to-green-500 rounded-lg overflow-hidden">
                                @if($designer->cover_image)
                                    <img :src="form.coverPreview || '{{ str_replace("'", "\\'", asset("storage/" . $designer->cover_image)) }}'" alt="Cover" class="w-full h-full object-cover" @error="$event.target.style.display='none';">
                                @else
                                    <img :src="form.coverPreview" alt="Cover" class="w-full h-full object-cover" x-show="form.coverPreview" @error="$event.target.style.display='none';">
                                @endif
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center">
                                    <input type="file" id="coverInput" accept="image/*" @change="handleCoverUpload($event)" class="hidden">
                                    <button type="button" @click="document.getElementById('coverInput').click()" class="px-4 py-2 bg-white/90 hover:bg-white rounded-lg text-sm font-medium flex items-center gap-2 transform hover:scale-105 transition-transform duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span x-text="form.coverUploading ? '{{ __('Uploading...') }}' : '{{ __('Change Cover') }}'"></span>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">{{ __('Recommended: 1200x400px or similar ratio') }}</p>
                        </div>
                    </div>

                    {{-- Profile Photo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Profile Photo') }}</label>
                        <div class="flex flex-col sm:flex-row items-center gap-6">
                            <div class="relative group cursor-pointer" @click="document.getElementById('avatarInput').click()">
                                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white text-2xl font-semibold overflow-hidden transition-transform duration-300 group-hover:scale-105">
                                    @if($designer->avatar)
                                        <img :src="form.avatarPreview || '{{ str_replace("'", "\\'", asset("storage/" . $designer->avatar)) }}'" alt="{{ $designer->name ?? 'Avatar' }}" class="w-full h-full object-cover" @error="$event.target.style.display='none';">
                                    @else
                                        <img :src="form.avatarPreview" alt="Avatar" class="w-full h-full object-cover" x-show="form.avatarPreview" @error="$event.target.style.display='none';">
                                        <span x-show="!form.avatarPreview">{{ strtoupper(substr($designer->name ?? 'DS', 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 text-center sm:text-left">
                                <input type="file" id="avatarInput" accept="image/*" @change="handleAvatarUpload($event)" class="hidden">
                                <p class="text-sm text-gray-600 mb-2">{{ __('Recommended: Square image, at least 400x400px') }}</p>
                                <button type="button" @click="document.getElementById('avatarInput').click()" class="px-4 py-2 border border-gray-300 hover:border-gray-400 bg-white text-gray-700 rounded-lg text-sm font-medium transition-all duration-200 hover:shadow-md">
                                    <span x-text="form.avatarUploading ? '{{ __('Uploading...') }}' : '{{ __('Upload Photo') }}'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Basic Information --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">{{ __('Basic Information') }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ __('Your public profile information') }}</p>
                </div>
                <div class="p-4 sm:p-6 space-y-4">
                    <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
                        <label for="fullName" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Full Name') }}</label>
                        <input type="text" id="fullName" x-model="form.name" placeholder="{{ __('Enter your full name') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Professional Title') }}</label>
                        <input type="text" id="title" x-model="form.title" placeholder="e.g., UI/UX Designer, Product Designer" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Bio') }}</label>
                        <textarea id="bio" x-model="form.bio" placeholder="{{ __('Tell us about yourself and your work...') }}" rows="4" maxlength="500" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none transition-all duration-200"></textarea>
                        <p class="text-sm mt-1 transition-colors duration-200" :class="form.bio.length >= 500 ? 'text-red-600' : 'text-gray-500'">
                            <span x-text="form.bio.length"></span>/500 {{ __('characters') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="transform transition-all duration-200">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }}</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <input type="email" id="email" x-model="form.email" readonly disabled class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ __('Email cannot be changed') }}</p>
                        </div>

                        <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Phone') }}</label>
                            <input type="tel" id="phone" x-model="form.phone" placeholder="+972 XXX XXX XXX" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        </div>
                    </div>

                    <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Governorate') }}</label>
                        <select id="city" x-model="form.city" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                            <option value="">{{ __('Select your city/governorate') }}</option>
                            @foreach(\App\Helpers\DropdownHelper::cities() as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Address') }}</label>
                        <textarea id="address" x-model="form.address" placeholder="{{ __('Street address, building number, etc.') }}" rows="2" maxlength="200" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-none"></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <span x-text="form.address ? form.address.length : 0"></span>/200 {{ __('characters') }}
                        </p>
                    </div>

                    <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
                        <label for="website" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Website') }}</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <input type="url" id="website" x-model="form.website" placeholder="www.yourwebsite.com" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Skills --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">{{ __('Skills & Expertise') }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ __('Add your professional skills and areas of expertise') }}</p>
                </div>
                <div class="p-4 sm:p-6 space-y-4">
                    {{-- Dropdown for predefined skills --}}
                    <div class="flex gap-2 mb-3">
                        <select x-model="selectedSkill" @change="customSkill = ''" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="">{{ __('Select a skill to add') }}</option>
                            <template x-for="skill in availableSkills()" :key="skill">
                                <option :value="skill" x-text="skill"></option>
                            </template>
                        </select>
                        <button type="button" @click="addSkill()" :disabled="!selectedSkill && !customSkill" class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                            {{ __('Add') }}
                        </button>
                    </div>

                    {{-- Text input for custom skills --}}
                    <div class="mb-3">
                        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ __('Don\'t see your skill? Type it below:') }}</span>
                        </div>
                        <input type="text" x-model="customSkill" @input="selectedSkill = ''" @keydown.enter.prevent="addSkill()" placeholder="{{ __('Type a custom skill (e.g., 3D Printing, Embroidery, etc.)') }}" maxlength="50" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <p class="text-xs text-gray-500 mt-1">{{ __('Press Enter or click "Add" to add your custom skill') }}</p>
                    </div>

                    {{-- Selected skills display --}}
                    <div x-show="skills.length > 0" class="flex flex-wrap gap-2 mt-3">
                        <template x-for="skill in skills" :key="skill">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-50 border border-blue-200 text-blue-700">
                                <span x-text="skill"></span>
                                <button type="button" @click="removeSkill(skill)" class="ml-2 text-blue-600 hover:text-blue-800 font-bold">&times;</button>
                            </span>
                        </template>
                    </div>
                    <p x-show="skills.length === 0" class="text-sm text-gray-500 mt-2">{{ __('Add at least one skill to showcase your expertise') }}</p>
                </div>
            </div>
        </div>

        {{-- Projects Tab --}}
        <div x-show="activeTab === 'projects'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <div>
                        <h2 class="text-lg font-semibold">{{ __('Manage Projects') }}</h2>
                        <p class="text-sm text-gray-600 mt-1">{{ __('Add, edit, or remove your portfolio projects') }}</p>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="(project, index) in projects" :key="project.id">
                            <div x-show="project" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 transform scale-90" class="bg-white rounded-lg border overflow-hidden group hover:shadow-lg transition-all duration-300">
                                <div class="relative h-48 bg-gray-100">
                                    <template x-if="project.image_paths && project.image_paths.length > 0">
                                        <img :src="project.image_paths[0]" :alt="project.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    </template>
                                    <template x-if="!project.image_paths || project.image_paths.length === 0">
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-600 to-green-500 text-white text-xl font-semibold">
                                            <span x-text="project.title.substring(0, 2).toUpperCase()"></span>
                                        </div>
                                    </template>
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-2">
                                        <button @click="editProject(project)" class="px-3 py-1.5 bg-white hover:bg-gray-100 text-gray-900 rounded text-sm font-medium flex items-center gap-1 transform hover:scale-110 transition-all duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            {{ __('Edit') }}
                                        </button>
                                        <button @click="deleteProject(project.id, index)" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-medium flex items-center gap-1 transform hover:scale-110 transition-all duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            {{ __('Delete') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full mb-2" x-text="project.role || 'No role specified'"></span>
                                    <h3 class="font-semibold mb-2 line-clamp-2" x-text="project.title"></h3>
                                    <p class="text-sm text-gray-600 line-clamp-2" x-text="project.description"></p>
                                </div>
                            </div>
                        </template>

                        <!-- Add New Project Card -->
                        <div @click="openProjectModal()" class="bg-white rounded-lg border-2 border-dashed border-gray-300 hover:border-blue-500 overflow-hidden group hover:shadow-lg transition-all duration-300 cursor-pointer flex items-center justify-center min-h-[200px] sm:min-h-[300px]">
                            <div class="text-center p-6">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-700 group-hover:text-blue-600 transition-colors">{{ __('Add New Project') }}</h3>
                                <p class="text-sm text-gray-500 mt-2">{{ __('Click to create a new project') }}</p>
                            </div>
                        </div>
                    </div>
                    <template x-if="projects.length === 0">
                        <div class="text-center py-12 animate-fade-in">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <p class="text-gray-600 mb-4">{{ __('No projects yet. Click below to add your first project.') }}</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Products Tab --}}
        <div x-show="activeTab === 'products'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <div>
                        <h2 class="text-lg font-semibold">{{ __('Manage Products') }}</h2>
                        <p class="text-sm text-gray-600 mt-1">{{ __('Add, edit, or remove products you offer') }}</p>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="(product, index) in products" :key="product.id">
                            <div x-show="product" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" class="bg-white rounded-lg border overflow-hidden group hover:shadow-lg transition-all duration-300">
                                <div class="relative h-48 bg-gray-100">
                                    <template x-if="product.image_paths && product.image_paths.length > 0">
                                        <img :src="product.image_paths[0]" :alt="product.name" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    </template>
                                    <template x-if="!product.image_paths || product.image_paths.length === 0">
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-600 to-green-500 text-white text-xl font-semibold">
                                            <span x-text="product.name.substring(0, 2).toUpperCase()"></span>
                                        </div>
                                    </template>
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-2">
                                        <button @click="editProduct(product)" class="px-3 py-1.5 bg-white hover:bg-gray-100 text-gray-900 rounded text-sm font-medium flex items-center gap-1 transform hover:scale-110 transition-all duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            {{ __('Edit') }}
                                        </button>
                                        <button @click="deleteProduct(product.id, index)" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-medium flex items-center gap-1 transform hover:scale-110 transition-all duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            {{ __('Delete') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full mb-2" x-text="product.category || 'Uncategorized'"></span>
                                    <h3 class="font-semibold mb-2 line-clamp-2" x-text="product.name"></h3>
                                    <p class="text-sm text-gray-600 line-clamp-2" x-text="product.description"></p>
                                </div>
                            </div>
                        </template>

                        <!-- Add New Product Card -->
                        <div @click="openProductModal()" class="bg-white rounded-lg border-2 border-dashed border-gray-300 hover:border-green-500 overflow-hidden group hover:shadow-lg transition-all duration-300 cursor-pointer flex items-center justify-center min-h-[200px] sm:min-h-[300px]">
                            <div class="text-center p-6">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-700 group-hover:text-green-600 transition-colors">{{ __('Add New Product') }}</h3>
                                <p class="text-sm text-gray-500 mt-2">{{ __('Click to create a new product') }}</p>
                            </div>
                        </div>
                    </div>
                    <template x-if="products.length === 0">
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="text-gray-600 mb-4">{{ __('No products yet. Click below to add your first product.') }}</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Services Tab --}}
        <div x-show="activeTab === 'services'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <template x-for="(service, index) in services" :key="service.id">
                            <div x-show="service" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 relative p-4 sm:p-6">
                                <!-- Service Icon -->
                                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center mb-3 sm:mb-4">
                                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>

                                <!-- Edit/Delete Buttons (Top Right) -->
                                <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <button @click="editService(service)" class="p-2 hover:bg-blue-50 rounded-lg transition-colors duration-200" title="Edit Service">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click="deleteService(service.id, index)" class="p-2 hover:bg-red-50 rounded-lg transition-colors duration-200" title="Delete Service">
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Service Info -->
                                <div>
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 mb-3" x-text="service.category || 'Uncategorized'"></span>
                                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3" x-text="service.name"></h3>
                                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed" x-text="service.description"></p>
                                </div>
                            </div>
                        </template>

                        <!-- Add New Service Card -->
                        <div @click="openServiceModal()" class="group bg-white rounded-xl border-2 border-dashed border-gray-300 hover:border-blue-500 shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer flex items-center justify-center min-h-[200px]">
                            <div class="text-center">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-700 group-hover:text-blue-600 transition-colors">{{ __('Add New Service') }}</h3>
                                <p class="text-sm text-gray-500 mt-2">{{ __('Click to create a new service') }}</p>
                            </div>
                        </div>
                    </div>
                    <template x-if="services.length === 0">
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <p class="text-gray-600 mb-4">{{ __('No services yet. Click below to add your first service.') }}</p>
                        </div>
                    </template>
            </div>
        </div>
    </div>
