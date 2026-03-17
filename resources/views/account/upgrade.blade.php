@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Upgrade Account') }}</title>
<meta name="robots" content="noindex, nofollow">
<style>[x-cloak] { display: none !important; }</style>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-amber-50 via-white to-orange-50 py-8 px-4">
    <div class="max-w-2xl mx-auto" x-data="upgradeWizard()">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-amber-500 to-orange-600 rounded-full mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Upgrade Your Account') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('Complete your profile to appear in search, get rated, and showcase your work') }}</p>
        </div>

        {{-- Progress Steps --}}
        <div class="flex items-center justify-center gap-2 mb-8">
            <template x-for="i in totalSteps" :key="i">
                <div class="flex items-center">
                    <div :class="step >= i ? 'bg-amber-500 text-white' : 'bg-gray-200 text-gray-500'"
                         class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors">
                        <span x-text="i"></span>
                    </div>
                    <div x-show="i < totalSteps" class="w-8 h-0.5" :class="step > i ? 'bg-amber-500' : 'bg-gray-200'"></div>
                </div>
            </template>
        </div>

        {{-- Step 1: Choose Sector --}}
        <div x-show="step === 1" x-cloak class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Choose Your Profile Type') }}</h2>
            <p class="text-gray-600 mb-6">{{ __('Select what best describes you') }}</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <button @click="formData.sector = 'designer'" type="button"
                    :class="formData.sector === 'designer' ? 'border-amber-500 bg-amber-50 ring-2 ring-amber-500' : 'border-gray-200 hover:border-amber-300'"
                    class="p-6 border-2 rounded-xl text-center transition-all">
                    <div class="text-3xl mb-2">🎨</div>
                    <div class="font-bold text-gray-900">{{ __('Designer') }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ __('Creative Professional') }}</div>
                </button>
                <button @click="formData.sector = 'manufacturer'" type="button"
                    :class="formData.sector === 'manufacturer' ? 'border-amber-500 bg-amber-50 ring-2 ring-amber-500' : 'border-gray-200 hover:border-amber-300'"
                    class="p-6 border-2 rounded-xl text-center transition-all">
                    <div class="text-3xl mb-2">🏭</div>
                    <div class="font-bold text-gray-900">{{ __('Manufacturer') }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ __('Production & Manufacturing') }}</div>
                </button>
                <button @click="formData.sector = 'showroom'" type="button"
                    :class="formData.sector === 'showroom' ? 'border-amber-500 bg-amber-50 ring-2 ring-amber-500' : 'border-gray-200 hover:border-amber-300'"
                    class="p-6 border-2 rounded-xl text-center transition-all">
                    <div class="text-3xl mb-2">🏪</div>
                    <div class="font-bold text-gray-900">{{ __('Showroom') }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ __('Retail & Display') }}</div>
                </button>
            </div>

            <div class="flex justify-end mt-6">
                <button @click="if(formData.sector) step = 2" :disabled="!formData.sector"
                    :class="formData.sector ? 'bg-amber-500 hover:bg-amber-600' : 'bg-gray-300 cursor-not-allowed'"
                    class="px-6 py-3 text-white rounded-xl font-semibold transition-all">
                    {{ __('Next') }} →
                </button>
            </div>
        </div>

        {{-- Step 2: Profile Details --}}
        <div x-show="step === 2" x-cloak class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Profile Details') }}</h2>
            <p class="text-gray-600 mb-6">{{ __('Tell us about yourself') }}</p>

            <div class="space-y-4">
                {{-- Sub Sector --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Specialization') }}</label>
                    <input type="text" x-model="formData.sub_sector" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                           placeholder="{{ __('e.g., Interior Design, Product Design, Furniture') }}">
                </div>

                {{-- Company Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Company Name') }} <span class="text-gray-400">({{ __('optional') }})</span></label>
                    <input type="text" x-model="formData.company_name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                </div>

                {{-- Position --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Title / Position') }}</label>
                    <input type="text" x-model="formData.position" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                           placeholder="{{ __('e.g., Senior Designer, CEO') }}">
                </div>

                {{-- City --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('City') }}</label>
                    <select x-model="formData.city" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <option value="">{{ __('Select City') }}</option>
                        @foreach(\App\Helpers\DropdownHelper::citiesKeyValue() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Bio --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Bio') }}</label>
                    <textarea x-model="formData.bio" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                              placeholder="{{ __('Tell others about yourself and your work...') }}"></textarea>
                </div>

                {{-- Years of Experience --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Years of Experience') }}</label>
                    <select x-model="formData.years_of_experience" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <option value="">{{ __('Select') }}</option>
                        <option value="0-1">0-1</option>
                        <option value="1-3">1-3</option>
                        <option value="3-5">3-5</option>
                        <option value="5-10">5-10</option>
                        <option value="10+">10+</option>
                    </select>
                </div>

                {{-- Skills --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Skills') }} <span class="text-gray-400">({{ __('comma separated') }})</span></label>
                    <input type="text" x-model="skillsInput" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                           placeholder="{{ __('e.g., AutoCAD, 3D Modeling, Branding') }}">
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <button @click="step = 1" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-all">
                    ← {{ __('Back') }}
                </button>
                <button @click="step = 3" class="px-6 py-3 bg-amber-500 text-white rounded-xl font-semibold hover:bg-amber-600 transition-all">
                    {{ __('Next') }} →
                </button>
            </div>
        </div>

        {{-- Step 3: Social & Links --}}
        <div x-show="step === 3" x-cloak class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('Social Links') }} <span class="text-gray-400 text-sm font-normal">({{ __('optional') }})</span></h2>
            <p class="text-gray-600 mb-6">{{ __('Add your social media and website links') }}</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Website') }}</label>
                    <input type="url" x-model="formData.website" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                           placeholder="https://yourwebsite.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label>
                    <input type="url" x-model="formData.linkedin" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                           placeholder="https://linkedin.com/in/yourprofile">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                    <input type="url" x-model="formData.instagram" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                           placeholder="https://instagram.com/yourprofile">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Behance</label>
                    <input type="url" x-model="formData.behance" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                           placeholder="https://behance.net/yourprofile">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                    <input type="url" x-model="formData.facebook" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                           placeholder="https://facebook.com/yourprofile">
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <button @click="step = 2" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-all">
                    ← {{ __('Back') }}
                </button>
                <button @click="submitUpgrade()" :disabled="loading"
                    class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all disabled:opacity-50">
                    <span x-show="!loading">{{ __('Upgrade Account') }} 🚀</span>
                    <span x-show="loading">{{ __('Upgrading...') }}</span>
                </button>
            </div>

            {{-- Error message --}}
            <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-700 text-sm" x-text="error"></p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function upgradeWizard() {
    return {
        step: 1,
        totalSteps: 3,
        loading: false,
        error: '',
        skillsInput: '',
        formData: {
            sector: '',
            sub_sector: '{{ $designer->sub_sector ?? '' }}',
            company_name: '{{ $designer->company_name ?? '' }}',
            position: '{{ $designer->position ?? '' }}',
            bio: @json($designer->bio ?? ''),
            city: '{{ $designer->city ?? '' }}',
            years_of_experience: '{{ $designer->years_of_experience ?? '' }}',
            phone_number: '{{ $designer->phone_number ?? '' }}',
            phone_country: '{{ $designer->phone_country ?? '' }}',
            website: '{{ $designer->website ?? '' }}',
            linkedin: '{{ $designer->linkedin ?? '' }}',
            instagram: '{{ $designer->instagram ?? '' }}',
            facebook: '{{ $designer->facebook ?? '' }}',
            behance: '{{ $designer->behance ?? '' }}',
        },

        async submitUpgrade() {
            this.loading = true;
            this.error = '';

            const data = { ...this.formData };

            // Parse skills from comma-separated input
            if (this.skillsInput.trim()) {
                data.skills = this.skillsInput.split(',').map(s => s.trim()).filter(s => s.length > 0);
            }

            try {
                const res = await fetch('{{ route("account.upgrade.submit", ["locale" => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await res.json();

                if (res.ok && result.success) {
                    window.location.href = result.redirect;
                } else {
                    this.error = result.message || '{{ __("An error occurred. Please try again.") }}';
                    if (result.errors) {
                        const firstError = Object.values(result.errors)[0];
                        this.error = Array.isArray(firstError) ? firstError[0] : firstError;
                    }
                }
            } catch (e) {
                this.error = '{{ __("An error occurred. Please try again.") }}';
            }

            this.loading = false;
        }
    }
}
</script>
@endpush
@endsection
