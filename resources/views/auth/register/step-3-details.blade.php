            <!-- Step 3: Profile Details -->
            <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 md:p-8 max-w-2xl mx-auto overflow-visible">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Profile Details') }}</h2>
                    <p class="text-gray-600 mb-6">{{ __('Tell us about your background and expertise') }}</p>

                    <div class="space-y-4 overflow-visible">
                        <!-- Profile Image Upload -->
                        <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Profile Picture') }} *</label>
    <div class="flex items-center gap-6 mt-2">
        <div
            @click="$refs.profileImageInput.click()"
            class="w-24 h-24 rounded-full overflow-hidden bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center text-white text-2xl font-bold cursor-pointer hover:opacity-90 hover:scale-105 transition-all duration-300 group relative flex-shrink-0">
            <template x-if="profileImagePreview">
                <div class="relative w-full h-full">
                    <img :src="profileImagePreview" alt="{{ __('Profile') }}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300 flex items-center justify-center">
                        <svg class="w-8 h-8 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </template>
            <template x-if="!profileImagePreview">
                <div class="flex flex-col items-center justify-center">
                    <span x-text="getInitials()" class="text-2xl font-bold"></span>
                    <svg class="w-6 h-6 mt-1 opacity-0 group-hover:opacity-100 transition-opacity absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
            </template>
        </div>
        <div class="flex-1">
            <input
                x-ref="profileImageInput"
                type="file"
                name="profile_image"
                accept="image/*"
                @change="handleProfileImageChange"
                class="hidden"
            >
            <p class="text-sm text-gray-700 font-medium mb-1">{{ __('Click the circle to upload your photo') }}</p>
            <p class="text-sm text-gray-500">{{ __('JPG, PNG or GIF (max. 5MB)') }}</p>
            <p x-show="errors.profile_image" x-text="errors.profile_image" class="mt-1 text-sm text-red-600"></p>
            @error('profile_image')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

                        <!-- Hero/Cover Image Upload -->
                        <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Hero Image (Cover Photo)') }}</label>
    <div class="mt-2">
        <div
            @click="$refs.coverImageInput.click()"
            :class="errors.cover_image ? 'border-red-500' : 'border-gray-300'"
            class="w-full h-32 sm:h-40 md:h-48 rounded-lg overflow-hidden bg-gradient-to-r from-gray-100 to-gray-200 flex items-center justify-center border-2 border-dashed cursor-pointer hover:border-blue-500 hover:bg-gradient-to-r hover:from-blue-50 hover:to-green-50 transition-all duration-300 group">
            <template x-if="heroImagePreview">
                <div class="relative w-full h-full">
                    <img :src="heroImagePreview" alt="{{ __('Hero') }}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 flex items-center justify-center">
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity text-white text-center">
                            <svg class="mx-auto h-12 w-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-sm font-medium">{{ __('Click to change image') }}</p>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="!heroImagePreview">
                <div class="text-center text-gray-500 group-hover:text-blue-600 transition-colors">
                    <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-blue-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="mt-2 text-sm font-medium">{{ __('Click to upload hero image') }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ __('(1920x600 recommended)') }}</p>
                </div>
            </template>
        </div>
        <input
            x-ref="coverImageInput"
            type="file"
            name="cover_image"
            accept="image/*"
            @change="handleHeroImageChange"
            class="hidden"
        >
        <p class="text-sm text-gray-500 mt-2">{{ __('JPG, PNG or GIF (max. 5MB). Recommended size: 1920x600px for best display.') }}</p>
        <p x-show="errors.cover_image" x-text="errors.cover_image" class="mt-1 text-sm text-red-600"></p>
        @error('cover_image')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

                       <div>
    <label for="companyName" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Company/Business Name') }} *</label>
    <input
        type="text"
        id="companyName"
        name="company_name"
        x-model="formData.companyName"
        :class="errors.companyName ? 'border-red-500' : 'border-gray-300'"
        :required="formData.sector !== 'guest'"
        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
        placeholder="{{ __('Your company or business name') }}"
    >
    <p x-show="errors.companyName" x-text="errors.companyName" class="mt-1 text-sm text-red-600"></p>
    @error('company_name')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>  

                        <div>
                            <label for="position" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Current Position/Title') }} *</label>
                            <input
                                type="text"
                                id="position"
                                name="position"
                                x-model="formData.position"
                                :class="errors.position ? 'border-red-500' : 'border-gray-300'"
                                :required="formData.sector !== 'guest'"
                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                placeholder="{{ __('e.g., Senior UX Designer, CEO, Freelance Developer') }}"
                            >
                            <p x-show="errors.position" x-text="errors.position" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="phoneNumber" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone Number') }} *</label>
                            <div class="flex gap-2">
                                <select
                                    id="phoneCountry"
                                    name="phone_country"
                                    x-model="formData.phoneCountry"
                                    class="w-32 px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                >
                                    <optgroup label="{{ __('Palestine & Levant') }}">
                                        <option value="PS">🇵🇸 +970</option>
                                        <option value="JO">🇯🇴 +962</option>
                                        <option value="LB">🇱🇧 +961</option>
                                        <option value="SY">🇸🇾 +963</option>
                                        <option value="IL">🇮🇱 +972</option>
                                    </optgroup>
                                    <optgroup label="{{ __('Gulf Countries') }}">
                                        <option value="SA">🇸🇦 +966</option>
                                        <option value="AE">🇦🇪 +971</option>
                                        <option value="KW">🇰🇼 +965</option>
                                        <option value="QA">🇶🇦 +974</option>
                                        <option value="BH">🇧🇭 +973</option>
                                        <option value="OM">🇴🇲 +968</option>
                                    </optgroup>
                                    <optgroup label="{{ __('North Africa') }}">
                                        <option value="EG">🇪🇬 +20</option>
                                        <option value="MA">🇲🇦 +212</option>
                                        <option value="DZ">🇩🇿 +213</option>
                                        <option value="TN">🇹🇳 +216</option>
                                        <option value="LY">🇱🇾 +218</option>
                                    </optgroup>
                                    <optgroup label="{{ __('Other Arab Countries') }}">
                                        <option value="IQ">🇮🇶 +964</option>
                                        <option value="YE">🇾🇪 +967</option>
                                        <option value="SD">🇸🇩 +249</option>
                                        <option value="SO">🇸🇴 +252</option>
                                        <option value="DJ">🇩🇯 +253</option>
                                        <option value="MR">🇲🇷 +222</option>
                                        <option value="KM">🇰🇲 +269</option>
                                    </optgroup>
                                    <optgroup label="{{ __('Other Countries') }}">
                                        <option value="US">🇺🇸 +1</option>
                                        <option value="GB">🇬🇧 +44</option>
                                        <option value="DE">🇩🇪 +49</option>
                                        <option value="FR">🇫🇷 +33</option>
                                        <option value="TR">🇹🇷 +90</option>
                                    </optgroup>
                                </select>
                                <input
                                    type="tel"
                                    id="phoneNumber"
                                    name="phone_number"
                                    x-model="formData.phoneNumber"
                                    @input="validatePhoneNumber()"
                                    :class="errors.phoneNumber ? 'border-red-500' : 'border-gray-300'"
                                    :required="formData.sector !== 'guest'"
                                    class="flex-1 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    :placeholder="getPhonePlaceholder()"
                                >
                            </div>
                            <p class="text-xs text-gray-500 mt-1" x-text="getPhoneHint()"></p>
                            <p x-show="errors.phoneNumber" x-text="errors.phoneNumber" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">{{ __('City/Governorate') }} *</label>
                            <input type="hidden" name="city" x-model="formData.city">
                            <div x-data="searchableSelectCity()" class="relative">
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
                                        :placeholder="selectedValue || '{{ __('Select your city/governorate') }}'"
                                        :class="errors.city ? 'border-red-500' : 'border-gray-300'"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all pr-10"
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
                            <p x-show="errors.city" x-text="errors.city" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }} *</label>
                            <textarea
                                id="address"
                                name="address"
                                x-model="formData.address"
                                :class="errors.address ? 'border-red-500' : 'border-gray-300'"
                                maxlength="200"
                                :required="formData.sector !== 'guest'"
                                rows="2"
                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                placeholder="{{ __('Street address, building number, etc.') }}"
                            ></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                <span x-text="formData.address ? formData.address.length : 0"></span>/200 {{ __('characters') }}
                            </p>
                            <p x-show="errors.address" x-text="errors.address" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="yearsOfExperience" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Years of Experience') }} *</label>
                            <input type="hidden" name="years_of_experience" x-model="formData.yearsOfExperience">
                            <div x-data="searchableSelectYearsOfExperience()" class="relative">
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
                                        :placeholder="selectedValue || '{{ __('Select years of experience') }}'"
                                        :class="errors.yearsOfExperience ? 'border-red-500' : 'border-gray-300'"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all pr-10"
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
                            <p x-show="errors.yearsOfExperience" x-text="errors.yearsOfExperience" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Professional Bio') }} *</label>
                            <textarea
                                id="bio"
                                name="bio"
                                x-model="formData.bio"
                                :class="errors.bio ? 'border-red-500' : 'border-gray-300'"
                                maxlength="500"
                                :required="formData.sector !== 'guest'"
                                rows="5"
                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                placeholder="{{ __('Tell us about yourself, your experience, and what you\'re passionate about...') }}"
                            ></textarea>
                            <p x-show="!errors.bio" class="text-sm text-gray-500 mt-1">
                                <span x-text="formData.bio.length"></span>/500 {{ __('characters') }} - {{ __('At least 50 required') }}
                            </p>
                            <p x-show="errors.bio" x-text="errors.bio" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Skills & Expertise') }}</label>

                            <!-- Dropdown for predefined skills -->
                            <div class="flex gap-2 mb-3">
                                <div x-data="searchableSelectSkills()" class="flex-1 relative">
                                    <input
                                        type="text"
                                        x-model="searchQuery"
                                        @click="isOpen = true"
                                        @input="isOpen = true"
                                        @keydown.escape="isOpen = false"
                                        @keydown.arrow-down.prevent="highlightNext()"
                                        @keydown.arrow-up.prevent="highlightPrevious()"
                                        @keydown.enter.prevent="selectHighlighted()"
                                        placeholder="{{ __('Search for a skill') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all pr-10 text-gray-900"
                                        autocomplete="off"
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
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
                                            {{ __('No skills found') }}
                                        </div>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="addSkill()"
                                    :disabled="!selectedSkill && !customSkill"
                                    class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                                >
                                    {{ __('Add') }}
                                </button>
                            </div>

                            <!-- Text input for custom skills -->
                            <div class="mb-3">
                                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ __('Or type a custom skill:') }}</span>
                                </div>
                                <input
                                    type="text"
                                    x-model="customSkill"
                                    @input="selectedSkill = ''"
                                    @keydown.enter.prevent="addSkill()"
                                    placeholder="{{ __('Type a custom skill (e.g., 3D Printing, Embroidery, etc.)') }}"
                                    maxlength="50"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-gray-900"
                                >
                                <p class="text-xs text-gray-500 mt-1">{{ __('Press Enter or click "Add" to add your custom skill') }}</p>
                            </div>

                            <input type="hidden" name="skills" :value="JSON.stringify(formData.skills)">

                            <div x-show="formData.skills.length > 0" class="flex flex-wrap gap-2 mt-3">
                                <template x-for="(skill, index) in formData.skills" :key="'skill-' + index">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-50 border border-blue-200 text-blue-700">
                                        <span x-text="skill"></span>
                                        <button type="button" @click="removeSkill(skill)" class="ml-2 text-blue-600 hover:text-blue-800 font-bold">&times;</button>
                                    </span>
                                </template>
                            </div>
                            <p x-show="formData.skills.length === 0" class="text-sm text-gray-500 mt-2">{{ __('Add at least one skill to showcase your expertise') }}</p>
                        </div>

                        <!-- Education & Certifications (designer sector only) -->
                        <div x-show="formData.sector === 'designer'" x-transition>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Education & Certifications') }} *</label>
                            <p class="text-sm text-gray-500 mb-3">
                                {{ __('Upload up to 3 PDF files of your education certificates or professional certifications.') }}
                            </p>

                            <!-- Upload area -->
                            <div x-show="formData.certifications.length < 3">
                                <label class="flex items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all duration-300">
                                    <div class="text-center">
                                        <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <p class="mt-1 text-sm text-gray-600">{{ __('Click to upload PDF') }}</p>
                                        <p class="text-xs text-gray-400">{{ __('PDF only, max 10MB each') }}</p>
                                    </div>
                                    <input type="file" accept=".pdf,application/pdf" multiple
                                           @change="handleCertificationUpload($event)" class="hidden">
                                </label>
                            </div>

                            <!-- Max reached message -->
                            <div x-show="formData.certifications.length >= 3" class="flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-green-700">{{ __('Maximum 3 certifications uploaded') }}</span>
                            </div>

                            <!-- Uploaded files list -->
                            <div class="space-y-2 mt-3">
                                <template x-for="cert in formData.certifications" :key="cert.id">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <svg class="w-8 h-8 text-red-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                            </svg>
                                            <span x-text="cert.name" class="text-sm text-gray-700 truncate"></span>
                                            <span x-show="cert.uploading" class="text-xs text-blue-600 flex items-center gap-1 flex-shrink-0">
                                                <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                {{ __('Uploading...') }}
                                            </span>
                                            <span x-show="!cert.uploading && cert.path" class="text-xs text-green-600 flex-shrink-0">{{ __('Uploaded') }}</span>
                                        </div>
                                        <button type="button" @click="removeCertification(cert.id)"
                                                class="text-red-500 hover:text-red-700 flex-shrink-0 p-1">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <p class="text-xs text-gray-500 mt-2">
                                <span x-text="formData.certifications.length"></span>/3 {{ __('files uploaded') }}
                            </p>
                            <p x-show="errors.certifications" x-text="errors.certifications" class="mt-1 text-sm text-red-600"></p>
                        </div>
                    </div>
                </div>
            </div>

