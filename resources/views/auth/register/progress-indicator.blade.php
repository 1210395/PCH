        <!-- Progress Indicator -->
        <div class="mb-8 sm:mb-12">
            <!-- Mobile: Simple Step Counter -->
            <div class="block sm:hidden">
                <div class="text-center mb-4">
                    <p class="text-sm text-gray-600 mb-2">
                        {{ __('Step') }} <span class="font-bold text-blue-600" x-text="visibleStepIndex + 1"></span> {{ __('of') }} <span x-text="visibleSteps.length"></span>
                    </p>
                    <h3 class="text-lg font-semibold text-gray-900" x-text="steps[currentStep - 1].title"></h3>
                </div>
                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                        class="bg-gradient-to-r from-blue-600 to-green-500 h-2 rounded-full transition-all duration-300"
                        :style="`width: ${((visibleStepIndex + 1) / visibleSteps.length) * 100}%`"
                    ></div>
                </div>
            </div>

            <!-- Desktop: Full Progress Indicator -->
            <div class="hidden sm:block">
                <div class="flex items-center justify-between">
                    <template x-for="(step, index) in visibleSteps" :key="step.number">
                        <div class="flex items-center flex-1">
                            <div class="flex flex-col items-center">
                                <div
                                    @click="goToStep(step.number)"
                                    :class="{
                                        'bg-green-500 text-white cursor-pointer hover:bg-green-600': currentStep > step.number,
                                        'bg-gradient-to-r from-blue-600 to-green-500 text-white': currentStep === step.number,
                                        'bg-gray-200 text-gray-400': currentStep < step.number
                                    }"
                                    class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center transition-all"
                                    :title="currentStep > step.number ? 'Click to go back to ' + step.title : ''"
                                >
                                    <template x-if="currentStep > step.number">
                                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </template>
                                    <template x-if="currentStep <= step.number">
                                        <span x-html="step.icon" class="[&>svg]:w-5 [&>svg]:h-5 md:[&>svg]:w-6 md:[&>svg]:h-6"></span>
                                    </template>
                                </div>
                                <span
                                    :class="currentStep >= step.number ? 'text-gray-900' : 'text-gray-400'"
                                    class="text-xs sm:text-sm mt-2 text-center whitespace-nowrap"
                                    x-text="step.title"
                                ></span>
                            </div>
                            <template x-if="index < visibleSteps.length - 1">
                                <div
                                    :class="currentStep > step.number ? 'bg-green-500' : 'bg-gray-200'"
                                    class="flex-1 h-1 mx-3 md:mx-4 step-connector"
                                ></div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
