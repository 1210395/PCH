@extends('admin.layouts.app')

@section('title', __('Edit Academic Account') . ' - ' . $account->name)

@section('breadcrumb')
    <a href="{{ route('admin.academic-accounts.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Academic Accounts') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <a href="{{ route('admin.academic-accounts.show', ['locale' => app()->getLocale(), 'id' => $account->id]) }}" class="text-blue-600 hover:underline">{{ Str::limit($account->name, 20) }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ __('Edit') }}</span>
@endsection

@section('content')
<div x-data="editAccountForm()" class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Edit Academic Account') }}</h1>
            <p class="text-gray-500">{{ __('Update institution information') }}</p>
        </div>
        <a href="{{ route('admin.academic-accounts.show', ['locale' => app()->getLocale(), 'id' => $account->id]) }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
        </a>
    </div>

    <!-- Account Status Banner -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                @if($account->logo)
                    <img src="{{ asset('storage/' . $account->logo) }}" alt="{{ $account->name }}" class="w-12 h-12 rounded-lg object-cover">
                @else
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($account->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <p class="font-medium text-gray-800">{{ $account->email }}</p>
                    <p class="text-sm text-gray-500">{{ __('Account ID') }}: {{ $account->id }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $account->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $account->is_active ? __('Active') : __('Inactive') }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-{{ $account->institution_type_color }}-100 text-{{ $account->institution_type_color }}-700">
                    {{ $account->institution_type_label }}
                </span>
            </div>
        </div>
    </div>

    <form @submit.prevent="submitForm()" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Basic Information') }}</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Institution Name') }} <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.name" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }} <span class="text-red-500">*</span></label>
                        <input type="email" x-model="form.email" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Institution Type') }} <span class="text-red-500">*</span></label>
                        <select x-model="form.institution_type" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="university">{{ __('University') }}</option>
                            <option value="tvet">{{ __('TVET') }}</option>
                            <option value="college">{{ __('College') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }}</label>
                    <textarea x-model="form.description" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                              placeholder="{{ __('Brief description of the institution...') }}"></textarea>
                </div>
            </div>
        </div>

        <!-- Media / Images -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-image text-purple-500 mr-2"></i>{{ __('Media') }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Logo Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Logo') }}</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition-colors"
                         @click="$refs.logoInput.click()"
                         @dragover.prevent="$event.target.classList.add('border-blue-400')"
                         @dragleave.prevent="$event.target.classList.remove('border-blue-400')"
                         @drop.prevent="handleLogoDrop($event)">
                        <template x-if="logoPreview || '{{ $account->logo }}'">
                            <div class="relative inline-block">
                                <img :src="logoPreview || '{{ $account->logo ? asset('storage/' . $account->logo) : '' }}'"
                                     class="w-24 h-24 object-cover rounded-lg mx-auto mb-2">
                                <button type="button" @click.stop="removeLogo()"
                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                        <template x-if="!logoPreview && !'{{ $account->logo }}'">
                            <div>
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-500">{{ __('Click or drag to upload logo') }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ __('Recommended: 200x200px') }}</p>
                            </div>
                        </template>
                        <input type="file" x-ref="logoInput" @change="handleLogoChange($event)" accept="image/*" class="hidden">
                    </div>
                </div>

                <!-- Banner Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Banner / Cover Image') }}</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition-colors"
                         @click="$refs.bannerInput.click()"
                         @dragover.prevent="$event.target.classList.add('border-blue-400')"
                         @dragleave.prevent="$event.target.classList.remove('border-blue-400')"
                         @drop.prevent="handleBannerDrop($event)">
                        <template x-if="bannerPreview || '{{ $account->banner }}'">
                            <div class="relative inline-block w-full">
                                <img :src="bannerPreview || '{{ $account->banner ? asset('storage/' . $account->banner) : '' }}'"
                                     class="w-full h-24 object-cover rounded-lg mb-2">
                                <button type="button" @click.stop="removeBanner()"
                                        class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                        <template x-if="!bannerPreview && !'{{ $account->banner }}'">
                            <div>
                                <i class="fas fa-panorama text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-500">{{ __('Click or drag to upload banner') }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ __('Recommended: 1200x300px') }}</p>
                            </div>
                        </template>
                        <input type="file" x-ref="bannerInput" @change="handleBannerChange($event)" accept="image/*" class="hidden">
                    </div>
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
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('City') }}</label>
                        <input type="text" x-model="form.city"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Address') }}</label>
                        <input type="text" x-model="form.address"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Status -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-2 border-yellow-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-shield-alt text-yellow-600 mr-2"></i>{{ __('Account Status') }}
            </h3>
            <div class="flex items-center gap-3">
                <input type="checkbox" x-model="form.is_active" id="is_active" class="w-5 h-5 rounded text-green-600 focus:ring-green-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">{{ __('Account Active') }}</label>
                <span class="text-xs text-gray-500">({{ __('Inactive accounts cannot log in') }})</span>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.academic-accounts.show', ['locale' => app()->getLocale(), 'id' => $account->id]) }}" class="px-6 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">{{ __('Cancel') }}</a>
            <button type="submit" :disabled="submitting" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all disabled:opacity-50">
                <span x-show="!submitting">{{ __('Save Changes') }}</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Saving...') }}</span>
            </button>
        </div>
    </form>

    <!-- Reset Password Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 mt-6 border-2 border-red-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-key text-red-600 mr-2"></i>{{ __('Reset Password') }}
        </h3>
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('New Password') }}</label>
                    <input type="password" x-model="newPassword" minlength="8"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="{{ __('Minimum 8 characters') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Confirm New Password') }}</label>
                    <input type="password" x-model="newPasswordConfirmation"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="{{ __('Confirm new password') }}">
                </div>
            </div>
            <button @click="resetPassword()" type="button"
                    :disabled="!newPassword || newPassword !== newPasswordConfirmation || resettingPassword"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!resettingPassword">{{ __('Reset Password') }}</span>
                <span x-show="resettingPassword"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Resetting...') }}</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editAccountForm() {
    const account = @json($account);

    return {
        form: {
            name: account.name || '',
            email: account.email || '',
            institution_type: account.institution_type || '',
            description: account.description || '',
            website: account.website || '',
            phone: account.phone || '',
            city: account.city || '',
            address: account.address || '',
            is_active: account.is_active !== false
        },
        newPassword: '',
        newPasswordConfirmation: '',
        submitting: false,
        resettingPassword: false,
        logoFile: null,
        logoPreview: null,
        bannerFile: null,
        bannerPreview: null,
        removedLogo: false,
        removedBanner: false,

        handleLogoChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.logoFile = file;
                this.logoPreview = URL.createObjectURL(file);
                this.removedLogo = false;
            }
        },

        handleLogoDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                this.logoFile = file;
                this.logoPreview = URL.createObjectURL(file);
                this.removedLogo = false;
            }
        },

        removeLogo() {
            this.logoFile = null;
            this.logoPreview = null;
            this.removedLogo = true;
            this.$refs.logoInput.value = '';
        },

        handleBannerChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.bannerFile = file;
                this.bannerPreview = URL.createObjectURL(file);
                this.removedBanner = false;
            }
        },

        handleBannerDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                this.bannerFile = file;
                this.bannerPreview = URL.createObjectURL(file);
                this.removedBanner = false;
            }
        },

        removeBanner() {
            this.bannerFile = null;
            this.bannerPreview = null;
            this.removedBanner = true;
            this.$refs.bannerInput.value = '';
        },

        async submitForm() {
            this.submitting = true;
            try {
                const formData = new FormData();
                formData.append('_method', 'PUT');

                // Add form fields
                Object.keys(this.form).forEach(key => {
                    if (key === 'is_active') {
                        formData.append(key, this.form[key] ? '1' : '0');
                    } else if (this.form[key] !== null && this.form[key] !== undefined) {
                        formData.append(key, this.form[key]);
                    }
                });

                // Add logo if new file selected
                if (this.logoFile) {
                    formData.append('logo', this.logoFile);
                }

                // Add banner if new file selected
                if (this.bannerFile) {
                    formData.append('banner', this.bannerFile);
                }

                const response = await fetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-accounts/{{ $account->id }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || '{{ __("Failed to update account") }}');
                }

                showToast('{{ __("Account updated successfully") }}', 'success');
                setTimeout(() => window.location.href = `{{ url('') }}/{{ app()->getLocale() }}/admin/academic-accounts/{{ $account->id }}`, 1000);
            } catch (e) {
                showToast(e.message || '{{ __("Failed to update account") }}', 'error');
            } finally {
                this.submitting = false;
            }
        },

        async resetPassword() {
            if (this.newPassword !== this.newPasswordConfirmation) {
                showToast('{{ __("Passwords do not match") }}', 'error');
                return;
            }

            if (!confirm('{{ __("Are you sure you want to reset this account\'s password?") }}')) return;

            this.resettingPassword = true;
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-accounts/{{ $account->id }}/reset-password`, {
                    method: 'POST',
                    body: JSON.stringify({
                        password: this.newPassword,
                        password_confirmation: this.newPasswordConfirmation
                    })
                });
                showToast('{{ __("Password reset successfully") }}', 'success');
                this.newPassword = '';
                this.newPasswordConfirmation = '';
            } catch (e) {
                showToast(e.message || '{{ __("Failed to reset password") }}', 'error');
            } finally {
                this.resettingPassword = false;
            }
        }
    }
}
</script>
@endpush
@endsection
