@extends('admin.layouts.app')

@section('title', __('Create Academic Account'))

@section('breadcrumb')
    <a href="{{ route('admin.academic-accounts.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Academic Accounts') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ __('Create') }}</span>
@endsection

@section('content')
<div x-data="createAccountForm()" class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Create Academic Account') }}</h1>
            <p class="text-gray-500">{{ __('Add a new university, TVET, or college account') }}</p>
        </div>
        <a href="{{ route('admin.academic-accounts.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
        </a>
    </div>

    <form @submit.prevent="submitForm()" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Basic Information') }}</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Institution Name') }} <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.name" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :placeholder="__('e.g., Palestine Technical University')">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }} <span class="text-red-500">*</span></label>
                        <input type="email" x-model="form.email" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="admin@university.edu">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Institution Type') }} <span class="text-red-500">*</span></label>
                        <select x-model="form.institution_type" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">{{ __('Select type') }}</option>
                            <option value="university">{{ __('University') }}</option>
                            <option value="tvet">{{ __('TVET') }}</option>
                            <option value="college">{{ __('College') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Password') }} <span class="text-red-500">*</span></label>
                        <input type="password" x-model="form.password" required minlength="8"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               :placeholder="__('Minimum 8 characters')">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Confirm Password') }} <span class="text-red-500">*</span></label>
                        <input type="password" x-model="form.password_confirmation" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               :placeholder="__('Confirm password')">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }}</label>
                    <textarea x-model="form.description" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                              :placeholder="__('Brief description of the institution...')"></textarea>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Contact Information') }}</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Website') }}</label>
                        <input type="url" x-model="form.website"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="https://www.university.edu">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Phone') }}</label>
                        <input type="tel" x-model="form.phone"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="+970 XXX XXX XXX">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('City') }}</label>
                        <input type="text" x-model="form.city"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               :placeholder="__('e.g., Ramallah')">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Address') }}</label>
                        <input type="text" x-model="form.address"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               :placeholder="__('Street address')">
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Status -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Account Status') }}</h3>
            <div class="flex items-center gap-3">
                <input type="checkbox" x-model="form.is_active" id="is_active" class="w-5 h-5 rounded text-green-600 focus:ring-green-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">{{ __('Account Active') }}</label>
                <span class="text-xs text-gray-500">({{ __('Active accounts can log in and manage content') }})</span>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.academic-accounts.index', ['locale' => app()->getLocale()]) }}" class="px-6 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">{{ __('Cancel') }}</a>
            <button type="submit" :disabled="submitting" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all disabled:opacity-50">
                <span x-show="!submitting">{{ __('Create Account') }}</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Creating...') }}</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function createAccountForm() {
    return {
        form: {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            institution_type: '',
            description: '',
            website: '',
            phone: '',
            city: '',
            address: '',
            is_active: true
        },
        submitting: false,

        async submitForm() {
            if (this.form.password !== this.form.password_confirmation) {
                showToast('{{ __("Passwords do not match") }}', 'error');
                return;
            }

            this.submitting = true;
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-accounts`, {
                    method: 'POST',
                    body: JSON.stringify(this.form)
                });
                showToast('{{ __("Academic account created successfully") }}', 'success');
                setTimeout(() => window.location.href = `{{ url('') }}/{{ app()->getLocale() }}/admin/academic-accounts/${response.data.id}`, 1000);
            } catch (e) {
                showToast(e.message || '{{ __("Failed to create account") }}', 'error');
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
@endsection
