@props(['designer'])

<!-- Edit Skills Modal - REDESIGNED with Combobox Interface -->
<div x-show="editSkillsModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
         @click="editSkillsModal = false"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl border border-gray-200 max-w-2xl w-full p-4 sm:p-6 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">{{ __('Edit Skills') }}</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Skills & Expertise') }}</label>

                    <!-- Dropdown for predefined skills -->
                    <div class="flex gap-2 mb-3">
                        <select
                            x-model="selectedSkill"
                            @change="customSkill = ''"
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                        >
                            <option value="">{{ __('Select a skill to add') }}</option>
                            <template x-for="skill in availableSkills()" :key="skill">
                                <option :value="skill" x-text="skill"></option>
                            </template>
                        </select>
                        <button
                            type="button"
                            @click="addSkill()"
                            :disabled="!selectedSkill && !customSkill"
                            class="px-4 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
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
                            <span>{{ __("Don't see your skill? Type it below:") }}</span>
                        </div>
                        <input
                            type="text"
                            x-model="customSkill"
                            @input="selectedSkill = ''"
                            @keydown.enter.prevent="addSkill()"
                            placeholder="{{ __('Type a custom skill (e.g., 3D Printing, Embroidery, etc.)') }}"
                            maxlength="50"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                        >
                        <p class="text-xs text-gray-500 mt-1">{{ __('Press Enter or click "Add" to add your custom skill') }}</p>
                    </div>

                    <!-- Display added skills as chips -->
                    <div x-show="skillsArray.length > 0" class="flex flex-wrap gap-2 mt-3">
                        <template x-for="skill in skillsArray" :key="skill">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gradient-to-r from-blue-50 to-green-50 border border-gray-200 text-gray-700">
                                <span x-text="skill"></span>
                                <button type="button" @click="removeSkill(skill)" class="ml-2 text-gray-600 hover:text-gray-800 font-bold">&times;</button>
                            </span>
                        </template>
                    </div>
                    <p x-show="skillsArray.length === 0" class="text-sm text-gray-500 mt-2">{{ __('Add at least one skill to showcase your expertise') }}</p>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button @click="editSkillsModal = false"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm sm:text-base">
                    {{ __('Cancel') }}
                </button>
                <button @click="submitEditSkills()"
                        :disabled="isSubmitting"
                        class="flex-1 px-3 sm:px-4 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all font-medium disabled:opacity-50 text-sm sm:text-base">
                    <span x-show="!isSubmitting">{{ __('Update Skills') }}</span>
                    <span x-show="isSubmitting">{{ __('Updating...') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
