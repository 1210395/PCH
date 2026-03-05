@extends('admin.layouts.app')

@section('title', __('Edit Account') . ' - ' . $designer->name)

@section('breadcrumb')
    <a href="{{ route('admin.designers.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Accounts') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="text-blue-600 hover:underline">{{ Str::limit($designer->name, 20) }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ __('Edit') }}</span>
@endsection

@section('content')
<div x-data="designerForm()" class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Edit Account') }}</h1>
            <p class="text-gray-500">{{ __('Update designer profile information') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url(app()->getLocale() . '/designer/' . $designer->id) }}" target="_blank" class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                <i class="fas fa-external-link-alt mr-2"></i>{{ __('View Public Profile') }}
            </a>
            <a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Account Status Banner -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                @if($designer->avatar)
                    <img src="{{ asset('storage/' . $designer->avatar) }}" alt="{{ $designer->name }}" class="w-12 h-12 rounded-full object-cover">
                @else
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold">
                        {{ substr($designer->name ?? 'D', 0, 1) }}
                    </div>
                @endif
                <div>
                    <p class="font-medium text-gray-800">{{ $designer->email }}</p>
                    <p class="text-sm text-gray-500">{{ __('Account ID') }}: {{ $designer->id }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $designer->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $designer->is_active ? __('Active') : __('Inactive') }}
                </span>
                @if($designer->is_trusted)
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">{{ __('Trusted') }}</span>
                @endif
                @if($designer->is_admin)
                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">{{ __('Admin') }}</span>
                @endif
            </div>
        </div>
    </div>

    <form @submit.prevent="submitForm()" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Basic Information') }}</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Full Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.name" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Professional Title') }}</label>
                        <input type="text" x-model="form.title"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                               :placeholder="__('e.g., UI/UX Designer, Product Designer')">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Bio') }}</label>
                    <textarea x-model="form.bio" rows="4" maxlength="500"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none transition-all"
                              :placeholder="__('Tell us about yourself and your work...')"></textarea>
                    <p class="text-sm mt-1 transition-colors" :class="(form.bio || '').length >= 500 ? 'text-red-600' : 'text-gray-500'">
                        <span x-text="(form.bio || '').length"></span>/500 {{ __('characters') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }} <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <input type="email" x-model="form.email" required
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Phone') }}</label>
                        <input type="tel" x-model="form.phone_number"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                               placeholder="+972 XXX XXX XXX">
                    </div>
                </div>

                <!-- Governorate Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Governorate') }}</label>
                    <select x-model="form.city"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">{{ __('Select your city/governorate') }}</option>
                        @foreach(\App\Helpers\DropdownHelper::cities() as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Address') }}</label>
                    <textarea x-model="form.address" rows="2" maxlength="200"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none transition-all"
                              :placeholder="__('Street address, building number, etc.')"></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <span x-text="(form.address || '').length"></span>/200 {{ __('characters') }}
                    </p>
                </div>

            </div>
        </div>

        <!-- Professional Information with Searchable Dropdowns -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Professional Information') }}</h3>
            <div class="space-y-4">
                <!-- Sector Searchable Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Sector') }}</label>
                    <div x-data="searchableSector()" class="relative">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @click="isOpen = true"
                            @focus="isOpen = true"
                            @input="isOpen = true"
                            @blur="updateSector()"
                            @keydown.escape="isOpen = false"
                            @keydown.arrow-down.prevent="highlightNext()"
                            @keydown.arrow-up.prevent="highlightPrevious()"
                            @keydown.enter.prevent="selectHighlighted()"
                            :placeholder="selectedValue || '{{ __('Select sector') }}'"
                            class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div x-show="isOpen && filteredOptions.length > 0"
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

                <!-- Sub-Sector Searchable Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Sub-Sector') }}</label>
                    <div x-data="searchableSubSector()" class="relative">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @click="isOpen = true"
                            @focus="isOpen = true"
                            @input="isOpen = true"
                            @blur="updateSubSector()"
                            @keydown.escape="isOpen = false"
                            @keydown.arrow-down.prevent="highlightNext()"
                            @keydown.arrow-up.prevent="highlightPrevious()"
                            @keydown.enter.prevent="selectHighlighted()"
                            :placeholder="selectedValue || '{{ __('Select sub-sector') }}'"
                            class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div x-show="isOpen && filteredOptions.length > 0"
                             @click.away="isOpen = false"
                             x-transition
                             style="z-index: 9998;"
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Company Name') }}</label>
                        <input type="text" x-model="form.company_name"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Position') }}</label>
                        <input type="text" x-model="form.position"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Years of Experience') }}</label>
                        <select x-model="form.years_of_experience"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="">{{ __('Select years of experience') }}</option>
                            @foreach(\App\Helpers\DropdownHelper::yearsOfExperience() as $experience)
                                <option value="{{ $experience }}">{{ $experience }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skills & Expertise -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Skills & Expertise') }}</h3>
            <div class="space-y-4">
                <!-- Dropdown for predefined skills -->
                <div class="flex gap-2">
                    <select x-model="selectedSkill" @change="customSkill = ''"
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">{{ __('Select a skill to add') }}</option>
                        <template x-for="skill in availableSkills()" :key="skill">
                            <option :value="skill" x-text="skill"></option>
                        </template>
                    </select>
                    <button type="button" @click="addSkill()" :disabled="!selectedSkill && !customSkill"
                            class="px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        {{ __('Add') }}
                    </button>
                </div>

                <!-- Text input for custom skills -->
                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ __("Don't see your skill? Type it below:") }}</span>
                    </div>
                    <input type="text" x-model="customSkill" @input="selectedSkill = ''" @keydown.enter.prevent="addSkill()"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                           :placeholder="__('Type a custom skill (e.g., 3D Printing, Embroidery, etc.)')" maxlength="50">
                    <p class="text-xs text-gray-500 mt-1">{{ __('Press Enter or click "Add" to add your custom skill') }}</p>
                </div>

                <!-- Selected skills display -->
                <div x-show="skills.length > 0" class="flex flex-wrap gap-2">
                    <template x-for="skill in skills" :key="skill">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-50 border border-blue-200 text-blue-700">
                            <span x-text="skill"></span>
                            <button type="button" @click="removeSkill(skill)" class="ml-2 text-blue-600 hover:text-blue-800 font-bold">&times;</button>
                        </span>
                    </template>
                </div>
                <p x-show="skills.length === 0" class="text-sm text-gray-500">{{ __('Add at least one skill to showcase your expertise') }}</p>
            </div>
        </div>

        <!-- Social Links -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Social Links') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-linkedin mr-1 text-blue-600"></i>{{ __('LinkedIn') }}
                    </label>
                    <input type="url" x-model="form.linkedin"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                           placeholder="https://linkedin.com/in/...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-instagram mr-1 text-pink-600"></i>{{ __('Instagram') }}
                    </label>
                    <input type="url" x-model="form.instagram"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                           placeholder="https://instagram.com/...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-facebook mr-1 text-blue-700"></i>{{ __('Facebook') }}
                    </label>
                    <input type="url" x-model="form.facebook"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                           placeholder="https://facebook.com/...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fab fa-behance mr-1 text-blue-500"></i>{{ __('Behance') }}
                    </label>
                    <input type="url" x-model="form.behance"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                           placeholder="https://behance.net/...">
                </div>
            </div>
        </div>

        <!-- Privacy Settings -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Privacy Settings') }}</h3>
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <input type="checkbox" x-model="form.show_email" id="show_email" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500">
                    <label for="show_email" class="text-sm font-medium text-gray-700">{{ __('Show email on public profile') }}</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" x-model="form.show_phone" id="show_phone" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500">
                    <label for="show_phone" class="text-sm font-medium text-gray-700">{{ __('Show phone number on public profile') }}</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" x-model="form.show_location" id="show_location" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500">
                    <label for="show_location" class="text-sm font-medium text-gray-700">{{ __('Show location on public profile') }}</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" x-model="form.allow_messages" id="allow_messages" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500">
                    <label for="allow_messages" class="text-sm font-medium text-gray-700">{{ __('Allow messages from other users') }}</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" x-model="form.email_marketing" id="email_marketing" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500">
                    <label for="email_marketing" class="text-sm font-medium text-gray-700">{{ __('Receive marketing emails') }}</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" x-model="form.email_notifications" id="email_notifications" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500">
                    <label for="email_notifications" class="text-sm font-medium text-gray-700">{{ __('Receive email notifications') }}</label>
                </div>
            </div>
        </div>

        <!-- Account Settings (Admin Only) -->
        @if(!$designer->is_admin)
        <div class="bg-white rounded-xl shadow-sm p-6 border-2 border-yellow-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-shield-alt text-yellow-600 mr-2"></i>{{ __('Account Settings (Admin Only)') }}
            </h3>
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <input type="checkbox" x-model="form.is_active" id="is_active" class="w-5 h-5 rounded text-green-600 focus:ring-green-500">
                    <label for="is_active" class="text-sm font-medium text-gray-700">{{ __('Account Active') }}</label>
                    <span class="text-xs text-gray-500">({{ __('Inactive accounts cannot log in') }})</span>
                </div>

                <!-- Is Workplace Learning Center checkbox - only for manufacturers/showrooms -->
                <div x-show="form.sector === 'manufacturer' || form.sector === 'showroom'" class="flex items-center gap-3">
                    <input type="checkbox" x-model="form.is_tevet" id="is_tevet" class="w-5 h-5 rounded text-purple-600 focus:ring-purple-500">
                    <label for="is_tevet" class="text-sm font-medium text-gray-700">{{ __('Workplace Learning Center') }}</label>
                    <span class="text-xs text-gray-500">({{ __('Mark this manufacturer/showroom as a workplace learning center') }})</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="px-6 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">{{ __('Cancel') }}</a>
            <button type="submit" :disabled="submitting" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all disabled:opacity-50">
                <span x-show="!submitting">{{ __('Save Changes') }}</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Saving...') }}</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Get dropdown options from database via DropdownHelper
const sectorOptions = @json(\App\Helpers\DropdownHelper::sectorsForJs());
const subSectorsByType = @json(\App\Helpers\DropdownHelper::subsectorsByType());
const allSkills = @json(\App\Helpers\DropdownHelper::skills());
const allCities = @json(\App\Helpers\DropdownHelper::cities());
const yearsOfExperienceOptions = @json(\App\Helpers\DropdownHelper::yearsOfExperience());

// Searchable Sector Dropdown
function searchableSector() {
    const initialValue = @json($designer->sector ?? '');
    const initialOption = sectorOptions.find(o => o.value === initialValue || o.label === initialValue);

    return {
        searchQuery: initialOption ? initialOption.label : initialValue,
        selectedValue: initialOption ? initialOption.label : initialValue,
        isOpen: false,
        highlightedIndex: -1,

        get filteredOptions() {
            if (!this.searchQuery) return sectorOptions;
            return sectorOptions.filter(opt =>
                opt.label.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },

        selectOption(option) {
            this.searchQuery = option.label;
            this.selectedValue = option.label;
            this.isOpen = false;
            // Update parent form
            const form = Alpine.$data(this.$root.closest('[x-data*="designerForm"]'));
            if (form) {
                form.form.sector = option.value;
                // Update subsectors from the subSectorsByType object
                form.currentSubSectors = subSectorsByType[option.value] || option.subSectors || [];
            }
        },

        updateSector() {
            setTimeout(() => {
                this.isOpen = false;
                const form = Alpine.$data(this.$root.closest('[x-data*="designerForm"]'));
                if (form) form.form.sector = this.searchQuery;
            }, 150);
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) this.highlightedIndex++;
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) this.highlightedIndex--;
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.filteredOptions[this.highlightedIndex]) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    }
}

// Searchable Sub-Sector Dropdown
function searchableSubSector() {
    const initialValue = @json($designer->sub_sector ?? '');

    return {
        searchQuery: initialValue,
        selectedValue: initialValue,
        isOpen: false,
        highlightedIndex: -1,

        get filteredOptions() {
            const form = Alpine.$data(this.$root.closest('[x-data*="designerForm"]'));
            // Use subSectorsByType from database or fallback to sectorOptions structure
            let subSectors = [];
            if (form && form.form.sector) {
                subSectors = subSectorsByType[form.form.sector] || [];
            }
            if (!this.searchQuery) return subSectors;
            return subSectors.filter(opt =>
                opt.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },

        selectOption(option) {
            this.searchQuery = option;
            this.selectedValue = option;
            this.isOpen = false;
            const form = Alpine.$data(this.$root.closest('[x-data*="designerForm"]'));
            if (form) form.form.sub_sector = option;
        },

        updateSubSector() {
            setTimeout(() => {
                this.isOpen = false;
                const form = Alpine.$data(this.$root.closest('[x-data*="designerForm"]'));
                if (form) form.form.sub_sector = this.searchQuery;
            }, 150);
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) this.highlightedIndex++;
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) this.highlightedIndex--;
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.filteredOptions[this.highlightedIndex]) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    }
}

function designerForm() {
    const designer = @json($designer);
    const existingSkills = @json($designer->skills ? $designer->skills->pluck('name')->toArray() : []);

    // Get current sub-sectors based on initial sector using subSectorsByType from database
    const initialSubSectors = designer.sector ? (subSectorsByType[designer.sector] || []) : [];

    return {
        form: {
            name: designer.name || '',
            first_name: designer.first_name || '',
            last_name: designer.last_name || '',
            title: designer.title || '',
            email: designer.email || '',
            bio: designer.bio || '',
            sector: designer.sector || '',
            sub_sector: designer.sub_sector || '',
            company_name: designer.company_name || '',
            position: designer.position || '',
            years_of_experience: designer.years_of_experience || '',
            phone_number: designer.phone_number || '',
            city: designer.city || '',
            location: designer.location || '',
            address: designer.address || '',
            linkedin: designer.linkedin || '',
            instagram: designer.instagram || '',
            facebook: designer.facebook || '',
            behance: designer.behance || '',
            show_email: designer.show_email || false,
            show_phone: designer.show_phone || false,
            show_location: designer.show_location || false,
            allow_messages: designer.allow_messages || false,
            email_marketing: designer.email_marketing || false,
            email_notifications: designer.email_notifications || false,
            is_active: designer.is_active !== false,
            is_tevet: !!designer.is_tevet
        },
        skills: existingSkills,
        selectedSkill: '',
        customSkill: '',
        currentSubSectors: initialSubSectors,
        submitting: false,

        availableSkills() {
            return allSkills.filter(skill => !this.skills.includes(skill));
        },

        addSkill() {
            const skillToAdd = this.selectedSkill || this.customSkill.trim();
            if (skillToAdd && !this.skills.includes(skillToAdd)) {
                this.skills.push(skillToAdd);
            }
            this.selectedSkill = '';
            this.customSkill = '';
        },

        removeSkill(skill) {
            this.skills = this.skills.filter(s => s !== skill);
        },

        async submitForm() {
            this.submitting = true;
            try {
                const payload = {
                    ...this.form,
                    skills: this.skills
                };

                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/designers/{{ $designer->id }}`, {
                    method: 'PUT',
                    body: JSON.stringify(payload)
                });
                showToast('{{ __("Account updated successfully") }}', 'success');
                setTimeout(() => window.location.href = `{{ url('') }}/{{ app()->getLocale() }}/admin/designers/{{ $designer->id }}`, 1000);
            } catch (e) {
                showToast(e.message, 'error');
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
@endsection
