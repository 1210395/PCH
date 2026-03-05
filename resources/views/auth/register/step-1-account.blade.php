            <!-- Step 1: Account Creation -->
            <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 md:p-8 max-w-2xl mx-auto">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Create Your Account') }}</h2>
                    <p class="text-gray-600 mb-6">{{ __("Let's start with your basic information") }}</p>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">{{ __('First Name') }} *</label>
                                <input
                                    type="text"
                                    id="firstName"
                                    name="first_name"
                                    x-model="formData.firstName"
                                    :class="errors.firstName ? 'border-red-500' : 'border-gray-300'"
                                    :aria-invalid="!!errors.firstName"
                                    :aria-describedby="errors.firstName ? 'firstName-error' : null"
                                    required
                                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="{{ __('Enter your first name') }}"
                                >
                                <p x-show="errors.firstName" x-text="errors.firstName" id="firstName-error" role="alert" class="mt-1 text-sm text-red-600"></p>
                            </div>
                            <div>
                                <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Last Name') }} *</label>
                                <input
                                    type="text"
                                    id="lastName"
                                    name="last_name"
                                    x-model="formData.lastName"
                                    :class="errors.lastName ? 'border-red-500' : 'border-gray-300'"
                                    :aria-invalid="!!errors.lastName"
                                    :aria-describedby="errors.lastName ? 'lastName-error' : null"
                                    required
                                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="{{ __('Enter your last name') }}"
                                >
                                <p x-show="errors.lastName" x-text="errors.lastName" id="lastName-error" role="alert" class="mt-1 text-sm text-red-600"></p>
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email Address') }} *</label>
                            <div class="relative">
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    x-model="formData.email"
                                    :class="errors.email ? 'border-red-500' : 'border-gray-300'"
                                    :aria-invalid="!!errors.email"
                                    :aria-describedby="errors.email ? 'email-error' : null"
                                    required
                                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="your.email@example.com"
                                >
                                <div x-show="isValidating" class="absolute right-3 top-3">
                                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p x-show="errors.email" x-text="errors.email" id="email-error" role="alert" class="mt-1 text-sm text-red-600"></p>
                            @error('email')
                            <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __("Don't have an email?") }}
                                <a href="https://accounts.google.com/signup" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline font-medium">{{ __('Create one!') }}</a>
                            </p>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Password') }} *</label>
                            <div class="relative">
                                <input
                                    :type="showPassword ? 'text' : 'password'"
                                    id="password"
                                    name="password"
                                    x-model="formData.password"
                                    @input="updatePasswordStrength()"
                                    :class="errors.password ? 'border-red-500' : 'border-gray-300'"
                                    :aria-invalid="!!errors.password"
                                    :aria-describedby="errors.password ? 'password-error' : 'password-requirements'"
                                    required
                                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all pr-10"
                                    placeholder="{{ __('Create a strong password') }}"
                                >
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                >
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Password Strength Indicator -->
                            <div x-show="formData.password.length > 0" class="mt-2">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div
                                            class="h-full transition-all duration-300"
                                            :class="{
                                                'bg-red-500': passwordStrength.score === 1,
                                                'bg-orange-500': passwordStrength.score === 2,
                                                'bg-yellow-500': passwordStrength.score === 3,
                                                'bg-green-500': passwordStrength.score === 4
                                            }"
                                            :style="`width: ${passwordStrength.score * 25}%`"
                                        ></div>
                                    </div>
                                    <span
                                        class="text-xs font-medium"
                                        :class="{
                                            'text-red-600': passwordStrength.score === 1,
                                            'text-orange-600': passwordStrength.score === 2,
                                            'text-yellow-600': passwordStrength.score === 3,
                                            'text-green-600': passwordStrength.score === 4
                                        }"
                                        x-text="passwordStrength.label"
                                    ></span>
                                </div>

                                <!-- Password Requirements Checklist -->
                                <div class="space-y-1 text-xs">
                                    <div class="flex items-center gap-2">
                                        <svg :class="passwordChecks.length ? 'text-green-500' : 'text-gray-400'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span :class="passwordChecks.length ? 'text-green-600' : 'text-gray-500'">{{ __('At least 8 characters') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg :class="passwordChecks.uppercase ? 'text-green-500' : 'text-gray-400'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span :class="passwordChecks.uppercase ? 'text-green-600' : 'text-gray-500'">{{ __('One uppercase letter') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg :class="passwordChecks.lowercase ? 'text-green-500' : 'text-gray-400'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span :class="passwordChecks.lowercase ? 'text-green-600' : 'text-gray-500'">{{ __('One lowercase letter') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg :class="passwordChecks.number ? 'text-green-500' : 'text-gray-400'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span :class="passwordChecks.number ? 'text-green-600' : 'text-gray-500'">{{ __('One number') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg :class="passwordChecks.special ? 'text-green-500' : 'text-gray-400'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span :class="passwordChecks.special ? 'text-green-600' : 'text-gray-500'">{{ __('One special character (!@#$%^&*)') }}</span>
                                    </div>
                                </div>
                            </div>

                            <p x-show="errors.password" x-text="errors.password" class="mt-2 text-sm text-red-600"></p>
                            @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Confirm Password') }} *</label>
                            <div class="relative">
                                <input
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    id="confirmPassword"
                                    name="password_confirmation"
                                    x-model="formData.confirmPassword"
                                    :class="errors.confirmPassword ? 'border-red-500' : 'border-gray-300'"
                                    required
                                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all pr-10"
                                    placeholder="{{ __('Re-enter your password') }}"
                                >
                                <button
                                    type="button"
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                >
                                    <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                            </div>
                            <p x-show="errors.confirmPassword" x-text="errors.confirmPassword" class="mt-1 text-sm text-red-600"></p>
                        </div>
                    </div>
                </div>
            </div>

