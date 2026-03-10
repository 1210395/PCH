            <!-- Step 7: Review -->
            <div x-show="currentStep === 7" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 md:p-8 max-w-4xl mx-auto">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Review Your Profile') }}</h2>
                    <p class="text-gray-600 mb-6">{{ __('Please review your information before publishing') }}</p>

                    <div class="space-y-6">
                        <!-- Account Information -->
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-4">{{ __('Account Information') }}</h3>
                            <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-lg transition-all duration-300">
                                <div class="flex items-start gap-4">
                                    <!-- Icon -->
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-600 to-blue-500 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <!-- Info -->
                                    <div class="flex-1 space-y-3">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Full Name') }}</p>
                                            <p class="text-sm font-medium text-gray-900" x-text="formData.firstName + ' ' + formData.lastName"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Email Address') }}</p>
                                            <p class="text-sm font-medium text-gray-900 break-all" x-text="formData.email"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        <!-- Profile Type -->
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-4">{{ __('Profile Type') }}</h3>
                            <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-lg transition-all duration-300">
                                <div class="flex items-start gap-4">
                                    <!-- Icon -->
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-teal-600 to-cyan-500 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <!-- Info -->
                                    <div class="flex-1 space-y-3">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Sector') }}</p>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-teal-50 text-teal-700 mt-1" x-text="getSectorLabel()"></span>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Specialization') }}</p>
                                            <p class="text-sm font-medium text-gray-900 mt-1" x-text="formData.subSector"></p>
                                        </div>
                                        <!-- Show Showroom badge for manufacturers -->
                                        <div x-show="formData.sector === 'manufacturer' && formData.hasShowroom">
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Showroom') }}</p>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-50 text-amber-700 mt-1" x-text="formData.hasShowroom === 'yes' ? '{{ __('Has Showroom') }}' : '{{ __('No Showroom') }}'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        <!-- Professional Experience (Skip for guests) -->
                        <template x-if="formData.sector !== 'guest'">
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Professional Experience') }}</h3>
                                <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-lg transition-all duration-300">
                                    <div class="flex items-start gap-4">
                                        <!-- Icon -->
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-600 to-red-500 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                            </svg>
                                        </div>
                                        <!-- Info -->
                                        <div class="flex-1 space-y-3 min-w-0">
                                            <template x-if="formData.companyName">
                                                <div>
                                                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Company') }}</p>
                                                    <p class="text-sm font-medium text-gray-900 break-words mt-1" x-text="formData.companyName"></p>
                                                </div>
                                            </template>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Position') }}</p>
                                                <p class="text-sm font-medium text-gray-900 break-words mt-1" x-text="formData.position"></p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Years of Experience') }}</p>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-50 text-orange-700 mt-1">
                                                    <span x-text="formData.yearsOfExperience"></span>
                                                </span>
                                            </div>
                                            <template x-if="formData.bio">
                                                <div>
                                                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Bio') }}</p>
                                                    <p class="text-sm text-gray-700 leading-relaxed break-words overflow-wrap-anywhere max-w-full mt-1" x-text="formData.bio"></p>
                                                </div>
                                            </template>
                                            <template x-if="formData.skills.length > 0">
                                                <div>
                                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">{{ __('Skills') }}</p>
                                                    <div class="flex flex-wrap gap-2">
                                                        <template x-for="(skill, index) in formData.skills" :key="'review-skill-' + index">
                                                            <span class="px-3 py-1 text-sm bg-gray-100 text-gray-700 font-medium rounded-full break-words" x-text="skill"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="formData.products.length > 0">
                            <div>
                                <hr class="border-gray-200 mb-6">
                                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Products') }} (<span x-text="formData.products.length"></span>)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template x-for="product in formData.products" :key="product.id">
                                        <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-lg transition-all duration-300">
                                            <!-- Product Icon -->
                                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-600 to-blue-500 flex items-center justify-center mb-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            </div>

                                            <!-- Category Badge -->
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 mb-3" x-text="product.category || 'Uncategorized'"></span>

                                            <!-- Product Info -->
                                            <h4 class="text-lg font-semibold text-gray-900 mb-2 break-words" x-text="product.name"></h4>
                                            <p class="text-sm text-gray-600 leading-relaxed break-words" x-text="product.description"></p>

                                            <!-- Image Count Indicator (if images exist) -->
                                            <div x-show="product.images && product.images.length > 0" class="mt-3 flex items-center text-xs text-gray-500">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span x-text="product.images.length + ' image(s)'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="formData.projects.length > 0">
                            <div>
                                <hr class="border-gray-200 mb-6">
                                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Projects') }} (<span x-text="formData.projects.length"></span>)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template x-for="project in formData.projects" :key="project.id">
                                        <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-lg transition-all duration-300">
                                            <!-- Project Icon -->
                                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-600 to-pink-500 flex items-center justify-center mb-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>

                                            <!-- Category Badge -->
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700 mb-3" x-text="project.category || 'Uncategorized'"></span>

                                            <!-- Project Info -->
                                            <h4 class="text-lg font-semibold text-gray-900 mb-2 break-words" x-text="project.title"></h4>

                                            <!-- Role Badge -->
                                            <div class="mb-2">
                                                <span class="inline-flex items-center text-xs font-medium text-gray-700">
                                                    <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    <span x-text="project.role"></span>
                                                </span>
                                            </div>

                                            <p class="text-sm text-gray-600 leading-relaxed break-words" x-text="project.description"></p>

                                            <!-- Image Count Indicator (if images exist) -->
                                            <div x-show="project.images && project.images.length > 0" class="mt-3 flex items-center text-xs text-gray-500">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span x-text="project.images.length + ' image(s)'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="formData.services.length > 0">
                            <div>
                                <hr class="border-gray-200 mb-6">
                                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Services') }} (<span x-text="formData.services.length"></span>)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template x-for="service in formData.services" :key="service.id">
                                        <div class="bg-white border border-gray-200 rounded-xl p-5 hover:shadow-lg transition-all duration-300">
                                            <!-- Service Icon -->
                                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center mb-3">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                            </div>

                                            <!-- Category Badge -->
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 mb-3" x-text="service.category || 'Uncategorized'"></span>

                                            <!-- Service Info -->
                                            <h4 class="text-lg font-semibold text-gray-900 mb-2 break-words" x-text="service.serviceName"></h4>
                                            <p class="text-sm text-gray-600 leading-relaxed break-words" x-text="service.description"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Certifications Review (Designer only) --}}
                        <template x-if="formData.sector === 'designer' && formData.certifications.length > 0">
                            <div>
                                <hr class="border-gray-200 mb-6">
                                <h3 class="font-semibold text-gray-900 mb-4">{{ __('Education & Certifications') }} (<span x-text="formData.certifications.length"></span>)</h3>
                                <div class="space-y-3">
                                    <template x-for="cert in formData.certifications" :key="cert.id">
                                        <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition-all duration-300">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate" x-text="cert.name"></p>
                                                <p class="text-xs text-gray-500">{{ __('PDF') }}</p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ __('Uploaded') }}
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Terms & Privacy Checkbox --}}
                        <div class="mt-6">
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 transition-all cursor-pointer"
                                   :class="termsAccepted ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300 bg-gray-50'">
                                <div class="flex-shrink-0 mt-0.5">
                                    <input type="checkbox"
                                           x-model="termsAccepted"
                                           class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ __('Terms of Service & Privacy Policy') }}</p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ __('I have read and agree to the') }}
                                        <button type="button"
                                                @click.prevent="showPoliciesModal = true"
                                                class="text-blue-600 hover:text-blue-800 underline font-medium">
                                            {{ __('Terms of Service and Privacy Policy') }}
                                        </button>.
                                        {{ __('Your profile will be visible to all members of the Palestine Creative Hub community.') }}
                                    </p>
                                </div>
                            </label>
                            <p x-show="!termsAccepted && errors.terms" class="mt-2 text-sm text-red-600">{{ __('You must accept the Terms of Service & Privacy Policy to continue.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Publish Confirmation Modal --}}
            @php
                $siteSettings = \App\Models\SiteSetting::get('registration_policies');
                $policiesContent = $siteSettings['content'] ?? 'Our platform policies govern how you interact with our community. By using Palestine Creative Hub, you agree to respect intellectual property rights, maintain professional conduct, and adhere to our content guidelines. For detailed information, please contact our support team.';
            @endphp
            <div x-show="showPublishConfirmModal"
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 overflow-y-auto"
                 @keydown.escape.window="showPublishConfirmModal = false">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    {{-- Backdrop --}}
                    <div class="fixed inset-0 transition-opacity bg-gray-900/75" @click="showPublishConfirmModal = false"></div>

                    {{-- Modal Content --}}
                    <div class="relative inline-block w-full max-w-lg p-6 my-8 text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         @click.away="showPublishConfirmModal = false">

                        {{-- Close Button --}}
                        <button @click="showPublishConfirmModal = false"
                                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        {{-- Header --}}
                        <div class="mb-6">
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center mb-4">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">{{ __('Before You Publish') }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ __('Please confirm the following to complete your registration') }}</p>
                        </div>

                        {{-- Checkboxes --}}
                        <div class="space-y-4 mb-6">
                            {{-- Media Ownership Checkbox --}}
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 transition-all cursor-pointer"
                                   :class="mediaOwnershipConfirmed ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300 bg-gray-50'">
                                <div class="flex-shrink-0 mt-0.5">
                                    <input type="checkbox"
                                           x-model="mediaOwnershipConfirmed"
                                           class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ __('Media Ownership Declaration') }}</p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ __('I confirm that all images, photos, and media I upload are either my own original work or I have the legal right to use them. I understand that I am solely responsible for the content I upload, and the platform is not liable for any copyright or intellectual property disputes.') }}
                                    </p>
                                </div>
                            </label>

                            {{-- Policies Agreement Checkbox --}}
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 transition-all cursor-pointer"
                                   :class="policiesConfirmed ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300 bg-gray-50'">
                                <div class="flex-shrink-0 mt-0.5">
                                    <input type="checkbox"
                                           x-model="policiesConfirmed"
                                           class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ __('Terms & Policies Agreement') }}</p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ __("I have read and agree to the platform's") }}
                                        <button type="button"
                                                @click.prevent="showPoliciesModal = true"
                                                class="text-blue-600 hover:text-blue-800 underline font-medium">
                                            {{ __('Terms of Service and Community Policies') }}
                                        </button>.
                                        {{ __('I understand that violation of these policies may result in account suspension or removal.') }}
                                    </p>
                                </div>
                            </label>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    @click="showPublishConfirmModal = false"
                                    class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                                {{ __('Cancel') }}
                            </button>
                            <button type="button"
                                    @click="proceedWithPublish()"
                                    :disabled="!canPublish()"
                                    :class="canPublish() ? 'bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-700 hover:to-green-600' : 'bg-gray-300 cursor-not-allowed'"
                                    class="flex-1 px-4 py-3 text-white rounded-xl font-medium transition-all flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="formData.sector === 'guest' ? '{{ __('Create Account') }}' : '{{ __('Confirm & Publish') }}'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Policies Modal --}}
            <div x-show="showPoliciesModal"
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[60] overflow-y-auto"
                 @keydown.escape.window="showPoliciesModal = false">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    {{-- Backdrop --}}
                    <div class="fixed inset-0 transition-opacity bg-gray-900/75" @click="showPoliciesModal = false"></div>

                    {{-- Modal Content --}}
                    <div class="relative inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         @click.away="showPoliciesModal = false">

                        {{-- Close Button --}}
                        <button @click="showPoliciesModal = false"
                                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        {{-- Header --}}
                        <div class="mb-6">
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center mb-4">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">{{ __('Terms of Service & Community Policies') }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ __('Please review our terms and policies') }}</p>
                        </div>

                        {{-- Policies Content --}}
                        <div class="max-h-[50vh] overflow-y-auto mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="prose prose-sm max-w-none text-gray-700">
                                {!! nl2br(e($policiesContent)) !!}
                            </div>
                        </div>

                        {{-- Close Button --}}
                        <div class="flex justify-end">
                            <button type="button"
                                    @click="showPoliciesModal = false"
                                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-xl font-medium hover:from-blue-700 hover:to-green-600 transition-all">
                                {{ __('I Understand') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex items-center justify-between mt-6 sm:mt-8 max-w-4xl mx-auto gap-2 sm:gap-4">
                <button
                    type="button"
                    @click="currentStep === 1 ? window.history.back() : prevStep()"
                    class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 md:px-6 md:py-3 border border-gray-300 rounded-lg text-sm sm:text-base text-gray-700 bg-white hover:bg-gray-50 transition-all"
                >
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span x-text="currentStep === 1 ? '{{ __('Cancel') }}' : '{{ __('Previous') }}'"></span>
                </button>

                <template x-if="currentStep < 7">
                    <button
                        type="button"
                        @click="nextStep()"
                        class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 md:px-6 md:py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:from-blue-700 hover:to-green-600 transition-all text-sm sm:text-base"
                    >
                        {{ __('Next') }}
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 ml-1 sm:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </template>

                <template x-if="currentStep === 7">
                    <button
                        type="submit"
                        :disabled="!termsAccepted"
                        :class="termsAccepted ? 'bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-700 hover:to-green-600' : 'bg-gray-300 cursor-not-allowed'"
                        class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 md:px-6 md:py-3 text-white rounded-lg transition-all text-sm sm:text-base"
                    >
                        <span x-text="formData.sector === 'guest' ? '{{ __('Create Account') }}' : '{{ __('Publish Profile') }}'"></span>
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 ml-1 sm:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </template>
            </div>
