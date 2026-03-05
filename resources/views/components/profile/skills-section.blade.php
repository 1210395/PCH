@props(['skills', 'skillOptions'])

{{-- Skills - Uses parent scope from layout.blade.php --}}
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
            <input type="text" x-model="customSkill" @input="selectedSkill = ''" @keydown.enter.prevent="addSkill()" :placeholder="'{{ __('Type a custom skill (e.g., 3D Printing, Embroidery, etc.)') }}'" maxlength="50" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
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
        <p x-show="skills.length > 0" class="text-xs text-gray-500 mt-2">
            <span x-text="skills.length"></span>/20 {{ __('skills added') }}
        </p>
    </div>
</div>
