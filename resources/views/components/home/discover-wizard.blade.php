{{-- Discover Wizard - 3-Step Bottom Sheet (guests only) --}}
@guest('designer')
@php
    $locale = app()->getLocale();
    $baseUrl = url($locale);
    $sectors = \App\Helpers\DropdownHelper::sectorOptions();
    $productCategories = \App\Helpers\DropdownHelper::productCategories();
    $projectCategories = \App\Helpers\DropdownHelper::projectCategories();
    $serviceCategories = \App\Helpers\DropdownHelper::serviceCategories();
    $cities = \App\Helpers\DropdownHelper::cities();
@endphp

<div x-data="discoverWizard()" x-init="init()" x-show="show" x-cloak
     class="fixed inset-0 z-[100]" style="display: none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="dismiss()">
    </div>

    {{-- Bottom Sheet --}}
    <div class="absolute bottom-0 inset-x-0 flex justify-center"
         x-show="show"
         x-transition:enter="transition ease-out duration-400 transform"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-300 transform"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full">

        <div class="bg-white rounded-t-3xl shadow-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto">
            {{-- Handle bar --}}
            <div class="flex justify-center pt-3 pb-1">
                <div class="w-12 h-1.5 bg-gray-200 rounded-full"></div>
            </div>

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 pb-3">
                <div class="flex items-center gap-3">
                    {{-- Step indicator --}}
                    <div class="flex gap-1.5">
                        <template x-for="s in totalSteps" :key="s">
                            <div class="h-1.5 rounded-full transition-all duration-300"
                                 :class="s <= step ? 'w-7 bg-gradient-to-r from-blue-600 to-blue-500' : 'w-4 bg-gray-200'"></div>
                        </template>
                    </div>
                    <span class="text-xs text-gray-400 font-medium" x-text="`${step} / ${totalSteps}`"></span>
                </div>
                <button @click="dismiss()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Step 1: Intent --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="px-6 pb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-1">{{ __('What are you looking for?') }}</h2>
                <p class="text-sm text-gray-500 mb-5">{{ __('We\'ll help you find exactly what you need') }}</p>

                <div class="grid grid-cols-2 gap-3">
                    {{-- Find Talent --}}
                    <button @click="selectIntent('talent')"
                            class="flex flex-col items-center gap-2.5 p-4 rounded-2xl border-2 border-gray-100 hover:border-blue-400 hover:bg-gradient-to-b hover:from-blue-50 hover:to-white hover:shadow-md transition-all duration-200 group">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 group-hover:bg-blue-100 flex items-center justify-center transition-all duration-200 group-hover:scale-110">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 group-hover:text-blue-700">{{ __('Find Talent') }}</span>
                    </button>

                    {{-- Browse Work --}}
                    <button @click="selectIntent('work')"
                            class="flex flex-col items-center gap-2.5 p-4 rounded-2xl border-2 border-gray-100 hover:border-emerald-400 hover:bg-gradient-to-b hover:from-emerald-50 hover:to-white hover:shadow-md transition-all duration-200 group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 group-hover:bg-emerald-100 flex items-center justify-center transition-all duration-200 group-hover:scale-110">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 group-hover:text-emerald-700">{{ __('Browse Work') }}</span>
                    </button>

                    {{-- Find Services --}}
                    <button @click="selectIntent('services')"
                            class="flex flex-col items-center gap-2.5 p-4 rounded-2xl border-2 border-gray-100 hover:border-violet-400 hover:bg-gradient-to-b hover:from-violet-50 hover:to-white hover:shadow-md transition-all duration-200 group">
                        <div class="w-12 h-12 rounded-xl bg-violet-50 group-hover:bg-violet-100 flex items-center justify-center transition-all duration-200 group-hover:scale-110">
                            <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 group-hover:text-violet-700">{{ __('Find Services') }}</span>
                    </button>

                    {{-- Learn & Train --}}
                    <button @click="selectIntent('learn')"
                            class="flex flex-col items-center gap-2.5 p-4 rounded-2xl border-2 border-gray-100 hover:border-amber-400 hover:bg-gradient-to-b hover:from-amber-50 hover:to-white hover:shadow-md transition-all duration-200 group">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 group-hover:bg-amber-100 flex items-center justify-center transition-all duration-200 group-hover:scale-110">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 group-hover:text-amber-700">{!! __('Learn & Train') !!}</span>
                    </button>

                    {{-- Suppliers --}}
                    <button @click="selectIntent('suppliers')"
                            class="flex flex-col items-center gap-2.5 p-4 rounded-2xl border-2 border-gray-100 hover:border-orange-400 hover:bg-gradient-to-b hover:from-orange-50 hover:to-white hover:shadow-md transition-all duration-200 group">
                        <div class="w-12 h-12 rounded-xl bg-orange-50 group-hover:bg-orange-100 flex items-center justify-center transition-all duration-200 group-hover:scale-110">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 group-hover:text-orange-700">{{ __('Suppliers') }}</span>
                    </button>

                    {{-- FabLabs --}}
                    <button @click="selectIntent('fablabs')"
                            class="flex flex-col items-center gap-2.5 p-4 rounded-2xl border-2 border-gray-100 hover:border-teal-400 hover:bg-gradient-to-b hover:from-teal-50 hover:to-white hover:shadow-md transition-all duration-200 group">
                        <div class="w-12 h-12 rounded-xl bg-teal-50 group-hover:bg-teal-100 flex items-center justify-center transition-all duration-200 group-hover:scale-110">
                            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 group-hover:text-teal-700">{{ __('FabLabs') }}</span>
                    </button>
                </div>

                <button @click="dismiss()" class="w-full mt-5 py-2.5 text-sm text-gray-400 hover:text-gray-600 transition-colors font-medium">
                    {{ __('Just browsing') }}
                </button>
            </div>

            {{-- Step 2: Category --}}
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="px-6 pb-6">
                {{-- Back button --}}
                <button @click="step = 1; intent = null;" class="flex items-center gap-1 text-sm text-gray-400 hover:text-gray-600 mb-3 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    {{ __('Back') }}
                </button>

                <h2 class="text-xl font-bold text-gray-900 mb-1" x-text="step2Title"></h2>
                <p class="text-sm text-gray-500 mb-4" x-text="step2Subtitle"></p>

                <div class="space-y-2 max-h-[45vh] overflow-y-auto pr-1">
                    <template x-for="option in step2Options" :key="option.value">
                        <button @click="selectCategory(option.value, option.redirectPath)"
                                class="flex items-center gap-3 w-full p-3.5 rounded-xl border border-gray-100 hover:border-blue-300 hover:bg-blue-50/50 hover:shadow-sm transition-all duration-200 text-left group">
                            <div class="w-9 h-9 rounded-lg bg-gray-50 group-hover:bg-blue-100 flex items-center justify-center flex-shrink-0 transition-all duration-200">
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors" x-text="option.label"></span>
                        </button>
                    </template>
                </div>

                <button @click="dismiss()" class="w-full mt-5 py-2.5 text-sm text-gray-400 hover:text-gray-600 transition-colors font-medium">
                    {{ __('Skip') }}
                </button>
            </div>

            {{-- Step 3: City Filter --}}
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="px-6 pb-6">
                {{-- Back button --}}
                <button @click="step = 2" class="flex items-center gap-1 text-sm text-gray-400 hover:text-gray-600 mb-3 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    {{ __('Back') }}
                </button>

                <h2 class="text-xl font-bold text-gray-900 mb-1">{{ __('Where are you located?') }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ __('Find what\'s near you, or explore everything') }}</p>

                <div class="space-y-2 max-h-[45vh] overflow-y-auto pr-1">
                    {{-- All cities option --}}
                    <button @click="goToPage(null)"
                            class="flex items-center gap-3 w-full p-3.5 rounded-xl border-2 border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 hover:border-blue-400 hover:shadow-sm transition-all duration-200 text-left group">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 group-hover:bg-blue-200 flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-blue-700">{{ __('All Cities') }}</span>
                    </button>

                    <template x-for="city in cities" :key="city">
                        <button @click="goToPage(city)"
                                class="flex items-center gap-3 w-full p-3.5 rounded-xl border border-gray-100 hover:border-blue-300 hover:bg-blue-50/50 hover:shadow-sm transition-all duration-200 text-left group">
                            <div class="w-9 h-9 rounded-lg bg-gray-50 group-hover:bg-blue-100 flex items-center justify-center flex-shrink-0 transition-all duration-200">
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors" x-text="city"></span>
                        </button>
                    </template>
                </div>

                <button @click="dismiss()" class="w-full mt-5 py-2.5 text-sm text-gray-400 hover:text-gray-600 transition-colors font-medium">
                    {{ __('Skip') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function discoverWizard() {
    const baseUrl = @json($baseUrl);
    return {
        show: false,
        step: 1,
        totalSteps: 3,
        intent: null,
        redirectPath: '',
        redirectParams: {},
        cities: @json($cities),
        sectors: @json($sectors),
        productCategories: @json($productCategories),
        projectCategories: @json($projectCategories),
        serviceCategories: @json($serviceCategories),

        // Computed step 2 data
        step2Title: '',
        step2Subtitle: '',
        step2Options: [],

        init() {
            const lastDismissed = localStorage.getItem('discoverWizardDismissed');
            if (lastDismissed) {
                const minutesAgo = (Date.now() - parseInt(lastDismissed)) / 1000 / 60;
                if (minutesAgo < 30) return;
            }
            setTimeout(() => { this.show = true; }, 800);
        },

        dismiss() {
            this.show = false;
            localStorage.setItem('discoverWizardDismissed', Date.now().toString());
        },

        selectIntent(intent) {
            this.intent = intent;

            if (intent === 'fablabs') {
                this.redirectPath = '/fab-labs';
                this.redirectParams = {};
                this.step = 3;
                return;
            }

            this.buildStep2(intent);
            this.step = 2;
        },

        buildStep2(intent) {
            switch (intent) {
                case 'talent':
                    this.step2Title = '{{ __("What type of talent?") }}';
                    this.step2Subtitle = '{{ __("Choose a specialization") }}';
                    this.step2Options = this.sectors.map(s => ({
                        value: s.value,
                        label: s.label,
                        redirectPath: '/designers?type=designers&sector=' + encodeURIComponent(s.value)
                    }));
                    break;

                case 'work':
                    this.step2Title = '{{ __("What type of work?") }}';
                    this.step2Subtitle = '{{ __("Browse creative output") }}';
                    this.step2Options = [
                        { value: 'products_all', label: '{{ __("All Products") }}', redirectPath: '/products' },
                        ...this.productCategories.map(c => ({
                            value: 'p_' + c,
                            label: c + ' ({{ __("Products") }})',
                            redirectPath: '/products?category=' + encodeURIComponent(c)
                        })),
                        { value: 'projects_all', label: '{{ __("All Projects") }}', redirectPath: '/projects' },
                        ...this.projectCategories.map(c => ({
                            value: 'pr_' + c,
                            label: c + ' ({{ __("Projects") }})',
                            redirectPath: '/projects?category=' + encodeURIComponent(c)
                        }))
                    ];
                    break;

                case 'services':
                    this.step2Title = '{{ __("What service do you need?") }}';
                    this.step2Subtitle = '{{ __("Find the right service provider") }}';
                    this.step2Options = [
                        { value: 'all', label: '{{ __("All Services") }}', redirectPath: '/services' },
                        ...this.serviceCategories.map(c => ({
                            value: c,
                            label: c,
                            redirectPath: '/services?category=' + encodeURIComponent(c)
                        }))
                    ];
                    break;

                case 'learn':
                    this.step2Title = '{{ __("How would you like to learn?") }}';
                    this.step2Subtitle = '{{ __("Explore training and opportunities") }}';
                    this.step2Options = [
                        { value: 'trainings', label: '{!! __("Trainings & Workshops") !!}', redirectPath: '/trainings' },
                        { value: 'tenders', label: '{!! __("Tenders & Opportunities") !!}', redirectPath: '/tenders' },
                        { value: 'academic', label: '{{ __("Academic Institutions") }}', redirectPath: '/academic-tevets' }
                    ];
                    break;

                case 'suppliers':
                    this.step2Title = '{{ __("What type of supplier?") }}';
                    this.step2Subtitle = '{{ __("Find manufacturers and suppliers") }}';
                    this.step2Options = [
                        { value: 'all', label: '{!! __("All Suppliers & Manufacturers") !!}', redirectPath: '/designers?type=manufacturers' },
                        { value: 'manufacturer', label: '{{ __("Manufacturers") }}', redirectPath: '/designers?type=manufacturers&sector=manufacturer' },
                        { value: 'showroom', label: '{{ __("Showrooms") }}', redirectPath: '/designers?type=manufacturers&sector=showroom' },
                        { value: 'vendor', label: '{{ __("Vendors / Suppliers") }}', redirectPath: '/designers?type=manufacturers&sector=vendor' }
                    ];
                    break;
            }
        },

        selectCategory(value, redirectPath) {
            this.redirectPath = redirectPath;

            // For "learn" intents that are simple pages (no city filter useful), go directly
            if (this.intent === 'learn') {
                this.dismiss();
                window.location.href = baseUrl + redirectPath;
                return;
            }

            this.step = 3;
        },

        goToPage(city) {
            let url = baseUrl + this.redirectPath;

            if (city) {
                const separator = url.includes('?') ? '&' : '?';
                url += separator + 'search=' + encodeURIComponent(city);
            }

            localStorage.setItem('discoverWizardDismissed', Date.now().toString());
            this.show = false;
            window.location.href = url;
        }
    };
}
</script>
@endguest
