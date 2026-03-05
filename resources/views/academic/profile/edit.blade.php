@extends('academic.layouts.app')

@section('title', __('Profile Settings'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Profile Settings') }}</span>
@endsection

@section('content')
<div x-data="profileManager()" class="max-w-4xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Profile Settings') }}</h1>
            <p class="text-gray-500">{{ __('Manage your institution profile and account settings') }}</p>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">{{ __('Institution Information') }}</h2>

        <form action="{{ route('academic.profile.update', ['locale' => app()->getLocale()]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Logo Section -->
            <div class="flex items-start gap-6 pb-6 border-b">
                <div>
                    @if($account->logo)
                        <img src="{{ asset('storage/' . $account->logo) }}" alt="{{ $account->name }}" class="w-24 h-24 rounded-xl object-cover shadow-lg">
                    @else
                        <div class="w-24 h-24 rounded-xl bg-gradient-to-br from-green-500 to-blue-500 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                            {{ strtoupper(substr($account->name, 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-gray-800 mb-2">{{ __('Institution Logo') }}</h3>
                    <p class="text-sm text-gray-500 mb-3">{{ __('Upload a logo for your institution (recommended: 200x200px)') }}</p>
                    <div class="flex gap-2">
                        <button type="button" @click="$refs.logoInput.click()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                            <i class="fas fa-upload mr-2"></i>{{ __('Upload Logo') }}
                        </button>
                        @if($account->logo)
                            <button type="button" @click="deleteLogo()" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm">
                                <i class="fas fa-trash mr-2"></i>{{ __('Remove') }}
                            </button>
                        @endif
                    </div>
                    <input type="file" x-ref="logoInput" @change="uploadLogo($event)" accept="image/*" class="hidden">
                </div>
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Institution Name') }} <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $account->name) }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email (readonly) -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email Address') }}</label>
                <input type="email" id="email" value="{{ $account->email }}" readonly
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('Contact administrator to change email') }}</p>
            </div>

            <!-- Institution Type (readonly) -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Institution Type') }}</label>
                <input type="text" id="type" value="{{ $account->institution_type_label }}" readonly
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }}</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('description') border-red-500 @enderror"
                          placeholder="{{ __('Tell us about your institution...') }}">{{ old('description', $account->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Website & Phone -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Website') }}</label>
                    <input type="url" id="website" name="website" value="{{ old('website', $account->website) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="https://example.edu">
                    @error('website')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Phone') }}</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $account->phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="+970 2 xxx xxxx">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Address & City -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Address') }}</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $account->address) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="{{ __('Street address') }}">
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">{{ __('City') }}</label>
                    <input type="text" id="city" name="city" value="{{ old('city', $account->city) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="{{ __('City') }}">
                    @error('city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end pt-4 border-t">
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-green-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition-all">
                    <i class="fas fa-save mr-2"></i>{{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">{{ __('Change Password') }}</h2>

        <form action="{{ route('academic.profile.password', ['locale' => app()->getLocale()]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Current Password') }} <span class="text-red-500">*</span></label>
                <input type="password" id="current_password" name="current_password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('current_password') border-red-500 @enderror">
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- New Password -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('New Password') }} <span class="text-red-500">*</span></label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Confirm New Password') }} <span class="text-red-500">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>

            <p class="text-sm text-gray-500">{{ __('Password must be at least 8 characters') }}</p>

            <!-- Submit -->
            <div class="flex justify-end pt-4 border-t">
                <button type="submit" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                    <i class="fas fa-key mr-2"></i>{{ __('Update Password') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Account Status -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Account Status') }}</h2>
        <div class="flex items-center gap-4">
            <span class="px-4 py-2 rounded-full text-sm font-medium {{ $account->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $account->is_active ? __('Active') : __('Inactive') }}
            </span>
            <span class="text-gray-500">{{ __('Member since') }} {{ $account->created_at->format('F Y') }}</span>
        </div>
        @if(!$account->is_active)
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ __('Your account is currently inactive. Please contact the administrator for assistance.') }}
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function profileManager() {
    return {
        async uploadLogo(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('logo', file);
            formData.append('_token', window.csrfToken);

            try {
                const response = await fetch(`{{ route('academic.profile.logo', ['locale' => app()->getLocale()]) }}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || '{{ __('Failed to upload logo') }}');
                }

                showToast('{{ __('Logo uploaded successfully') }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to upload logo') }}', 'error');
            }
        },

        async deleteLogo() {
            if (!confirm('{{ __('Are you sure you want to remove the logo?') }}')) return;

            try {
                const response = await fetch(`{{ route('academic.profile.logo.delete', ['locale' => app()->getLocale()]) }}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || '{{ __('Failed to delete logo') }}');
                }

                showToast('{{ __('Logo removed successfully') }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to delete logo') }}', 'error');
            }
        }
    }
}
</script>
@endpush
@endsection
