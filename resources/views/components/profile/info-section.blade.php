@props(['designer'])

{{-- Basic Information --}}
<div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
    <div class="p-4 sm:p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold">{{ __('Basic Information') }}</h2>
        <p class="text-sm text-gray-600 mt-1">{{ __('Your public profile information') }}</p>
    </div>
    <div class="p-4 sm:p-6 space-y-4">
        <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
            <label for="fullName" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Full Name') }}</label>
            <input type="text" id="fullName" x-model="form.name" :placeholder="'{{ __('Enter your full name') }}'" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
        </div>

        <div class="transform transition-all duration-200 focus-within:scale-[1.01]" style="position: relative; z-index: 30;">
            <label for="sector" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Profile Type') }}</label>
            <div x-data="searchableSelectSector()" class="relative">
                <input
                    type="text"
                    x-model="searchQuery"
                    @click="openDropdown()"
                    @focus="openDropdown()"
                    @input="openDropdown()"
                    @keydown.escape="isOpen = false"
                    @keydown.arrow-down.prevent="highlightNext()"
                    @keydown.arrow-up.prevent="highlightPrevious()"
                    @keydown.enter.prevent="selectHighlighted()"
                    :placeholder="selectedLabel || '{{ __('Select your profile type') }}'"
                    class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                    autocomplete="off"
                >
                <div @click="toggleDropdown()" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
                <div x-show="isOpen"
                     @click.away="isOpen = false"
                     x-transition
                     style="z-index: 9999;"
                     class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                    <template x-for="(option, index) in filteredOptions" :key="option.value">
                        <div @mousedown.prevent="selectOption(option)"
                             :class="{'bg-blue-50': index === highlightedIndex}"
                             class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors"
                             x-text="option.label">
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="transform transition-all duration-200 focus-within:scale-[1.01]" x-show="form.sector" style="position: relative; z-index: 20;">
            <label for="subSector" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Specialization') }}</label>
            <div x-data="searchableSelectSubSector()" class="relative">
                <input
                    type="text"
                    x-model="searchQuery"
                    @click="openDropdown()"
                    @focus="openDropdown()"
                    @input="openDropdown()"
                    @keydown.escape="isOpen = false"
                    @keydown.arrow-down.prevent="highlightNext()"
                    @keydown.arrow-up.prevent="highlightPrevious()"
                    @keydown.enter.prevent="selectHighlighted()"
                    :placeholder="selectedValue || '{{ __('Select your specialization') }}'"
                    class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                    autocomplete="off"
                >
                <div @click="toggleDropdown()" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer">
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

        <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
            <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Bio') }}</label>
            <textarea id="bio" x-model="form.bio" :placeholder="'{{ __('Tell us about yourself and your work...') }}'" rows="4" maxlength="500" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none transition-all duration-200"></textarea>
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
                <div class="flex gap-2">
                    <select
                        id="phoneCountry"
                        x-model="form.phoneCountry"
                        class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
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
                    <input type="tel" id="phone" x-model="form.phone" :placeholder="getPhonePlaceholder()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                </div>
                <p class="text-xs text-gray-500 mt-1" x-text="getPhoneHint()"></p>
            </div>
        </div>

        <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Governorate') }}</label>
            <select id="city" x-model="form.city" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                <option value="">{{ __('Select your city/governorate') }}</option>
                @foreach(\App\Helpers\DropdownHelper::citiesKeyValue() as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Address') }}</label>
            <textarea id="address" x-model="form.address" :placeholder="'{{ __('Street address, building number, etc.') }}'" rows="2" maxlength="200" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-none"></textarea>
            <p class="text-xs text-gray-500 mt-1">
                <span x-text="form.address ? form.address.length : 0"></span>/200 {{ __('characters') }}
            </p>
        </div>

        <!-- Social Media Links -->
        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-700">{{ __('Social Media Links') }}</h3>

            <!-- LinkedIn -->
            <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
                <label for="linkedin" class="block text-sm font-medium text-gray-700 mb-2">{{ __('LinkedIn') }}</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                    <input type="url" id="linkedin" x-model="form.linkedin"
                           @blur="validateSocialLink('linkedin')"
                           placeholder="https://www.linkedin.com/in/yourprofile"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                           :class="{'border-red-500': socialLinkErrors.linkedin}">
                </div>
                <p x-show="socialLinkErrors.linkedin" x-text="socialLinkErrors.linkedin" class="text-xs text-red-600 mt-1"></p>
            </div>

            <!-- Instagram -->
            <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
                <label for="instagram" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Instagram') }}</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                    <input type="url" id="instagram" x-model="form.instagram"
                           @blur="validateSocialLink('instagram')"
                           placeholder="https://www.instagram.com/yourprofile"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                           :class="{'border-red-500': socialLinkErrors.instagram}">
                </div>
                <p x-show="socialLinkErrors.instagram" x-text="socialLinkErrors.instagram" class="text-xs text-red-600 mt-1"></p>
            </div>

            <!-- Facebook -->
            <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
                <label for="facebook" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Facebook') }}</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <input type="url" id="facebook" x-model="form.facebook"
                           @blur="validateSocialLink('facebook')"
                           placeholder="https://www.facebook.com/yourprofile"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                           :class="{'border-red-500': socialLinkErrors.facebook}">
                </div>
                <p x-show="socialLinkErrors.facebook" x-text="socialLinkErrors.facebook" class="text-xs text-red-600 mt-1"></p>
            </div>

            <!-- Behance -->
            <div class="transform transition-all duration-200 focus-within:scale-[1.01]">
                <label for="behance" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Behance') }}</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M22 7h-7v-2h7v2zm1.726 10c-.442 1.297-2.029 3-5.101 3-3.074 0-5.564-1.729-5.564-5.675 0-3.91 2.325-5.92 5.466-5.92 3.082 0 4.964 1.782 5.375 4.426.078.506.109 1.188.095 2.14h-8.027c.13 3.211 3.483 3.312 4.588 2.029h3.168zm-7.686-4h4.965c-.105-1.547-1.136-2.219-2.477-2.219-1.466 0-2.277.768-2.488 2.219zm-9.574 6.988h-6.466v-14.967h6.953c5.476.081 5.58 5.444 2.72 6.906 3.461 1.26 3.577 8.061-3.207 8.061zm-3.466-8.988h3.584c2.508 0 2.906-3-.312-3h-3.272v3zm3.391 3h-3.391v3.016h3.341c3.055 0 2.868-3.016.05-3.016z"/>
                    </svg>
                    <input type="url" id="behance" x-model="form.behance"
                           @blur="validateSocialLink('behance')"
                           placeholder="https://www.behance.net/yourprofile"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                           :class="{'border-red-500': socialLinkErrors.behance}">
                </div>
                <p x-show="socialLinkErrors.behance" x-text="socialLinkErrors.behance" class="text-xs text-red-600 mt-1"></p>
            </div>
        </div>
    </div>
</div>
