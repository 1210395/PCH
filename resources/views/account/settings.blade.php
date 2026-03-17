@extends('layout.main')

@section('title', __('Account Settings') . ' | ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Account Settings') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('Manage your account security and preferences') }}</p>
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-600 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-600 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <div class="space-y-6">
            {{-- Account Information Card --}}
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Account Information') }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ __('Your account details') }}</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
                            <p class="text-gray-900">{{ $designer->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                            <p class="text-gray-900">{{ $designer->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Account Type') }}</label>
                            <p class="text-gray-900 capitalize">{{ __(ucfirst($designer->sector ?? 'N/A')) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Member Since') }}</label>
                            <p class="text-gray-900">{{ $designer->created_at ? $designer->created_at->format('F d, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Change Password Card --}}
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Change Password') }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ __('Update your password to keep your account secure') }}</p>
                </div>
                <div class="p-6">
                    <form action="{{ route('account.password.update', ['locale' => app()->getLocale()]) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Current Password') }}</label>
                            <input type="password" id="current_password" name="current_password" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('current_password')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('New Password') }}</label>
                            <input type="password" id="new_password" name="new_password" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">{{ __('Minimum 8 characters') }}</p>
                            @error('new_password')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Confirm New Password') }}</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                {{ __('Update Password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Privacy Settings Card --}}
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Privacy Settings') }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ __('Control what information is visible on your profile') }}</p>
                </div>
                <div class="p-6">
                    <form action="{{ route('account.privacy.update', ['locale' => app()->getLocale()]) }}" method="POST" class="space-y-4">
                        @csrf

                        {{-- Show Email --}}
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <div>
                                <p class="font-medium text-gray-900">{{ __('Show Email Address') }}</p>
                                <p class="text-sm text-gray-600">{{ __('Display your email on your public profile') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="show_email" value="1" class="sr-only peer" {{ $designer->show_email ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        {{-- Show Phone --}}
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <div>
                                <p class="font-medium text-gray-900">{{ __('Show Phone Number') }}</p>
                                <p class="text-sm text-gray-600">{{ __('Display your phone number on your public profile') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="show_phone" value="1" class="sr-only peer" {{ $designer->show_phone ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        {{-- Show Location --}}
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <div>
                                <p class="font-medium text-gray-900">{{ __('Show Location') }}</p>
                                <p class="text-sm text-gray-600">{{ __('Display your location on your public profile') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="show_location" value="1" class="sr-only peer" {{ $designer->show_location ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        {{-- Allow Messages --}}
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="font-medium text-gray-900">{{ __('Allow Direct Messages') }}</p>
                                <p class="text-sm text-gray-600">{{ __('Let other users send you messages') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="allow_messages" value="1" class="sr-only peer" {{ $designer->allow_messages ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                {{ __('Save Privacy Settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Email Preferences Card --}}
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Email Preferences') }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ __('Manage your email notification settings') }}</p>
                </div>
                <div class="p-6">
                    <form action="{{ route('account.email.update', ['locale' => app()->getLocale()]) }}" method="POST" class="space-y-4">
                        @csrf

                        {{-- Marketing Emails --}}
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <div>
                                <p class="font-medium text-gray-900">{{ __('Marketing Emails') }}</p>
                                <p class="text-sm text-gray-600">{{ __('Receive updates about new features and promotions') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="email_marketing" value="1" class="sr-only peer" {{ $designer->email_marketing ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        {{-- Account Notifications --}}
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="font-medium text-gray-900">{{ __('Account Notifications') }}</p>
                                <p class="text-sm text-gray-600">{{ __('Important updates about your account') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-not-allowed opacity-50">
                                <input type="checkbox" name="email_notifications" value="1" class="sr-only peer" checked disabled>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 italic">{{ __('Note: Account notifications cannot be disabled for security reasons') }}</p>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                {{ __('Save Email Preferences') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Danger Zone Card --}}
            <div class="bg-white rounded-lg shadow-sm border-2 border-red-200" x-data="dangerZone()">
                <div class="p-6 border-b border-red-200 bg-red-50">
                    <h2 class="text-lg font-semibold text-red-900">{{ __('Danger Zone') }}</h2>
                    <p class="text-sm text-red-700 mt-1">{{ __('Irreversible actions') }}</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ __('Delete Account') }}</p>
                            <p class="text-sm text-gray-600">{{ __('Permanently delete your account and all associated data') }}</p>
                        </div>
                        <button type="button" @click="showModal = true" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            {{ __('Delete Account') }}
                        </button>
                    </div>
                </div>

                {{-- Delete Account Modal --}}
                <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5)">
                    <div @click.away="showModal = false" class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                        <div class="text-center mb-4">
                            <div class="mx-auto w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.27 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Delete Account') }}</h3>
                        </div>

                        {{-- Step 1: Password confirmation --}}
                        <div x-show="step === 1">
                            <p class="text-sm text-gray-600 mb-4">{{ __('Enter your password to confirm account deletion. A verification code will be sent to your email.') }}</p>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Password') }}</label>
                                <input type="password" x-model="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" :class="error ? 'border-red-500' : ''">
                                <p x-show="error" x-text="error" class="text-red-600 text-sm mt-1"></p>
                            </div>
                            <div class="flex gap-3">
                                <button @click="showModal = false; error = ''" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">{{ __('Cancel') }}</button>
                                <button @click="sendCode()" :disabled="loading" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                                    <span x-show="!loading">{{ __('Send Verification Code') }}</span>
                                    <span x-show="loading">{{ __('Sending...') }}</span>
                                </button>
                            </div>
                        </div>

                        {{-- Step 2: Email verification code --}}
                        <div x-show="step === 2">
                            <p class="text-sm text-gray-600 mb-4">{{ __('A 6-digit verification code has been sent to your email. Enter it below to confirm deletion.') }}</p>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Verification Code') }}</label>
                                <input type="text" x-model="code" maxlength="6" pattern="[0-9]*" inputmode="numeric"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-center text-2xl tracking-widest font-mono" :class="error ? 'border-red-500' : ''">
                                <p x-show="error" x-text="error" class="text-red-600 text-sm mt-1"></p>
                                <p class="text-xs text-gray-500 mt-2">{{ __('Code expires in 10 minutes') }}</p>
                            </div>
                            <div class="flex gap-3">
                                <button @click="step = 1; error = ''; code = ''" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">{{ __('Back') }}</button>
                                <button @click="confirmDelete()" :disabled="loading || code.length !== 6" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                                    <span x-show="!loading">{{ __('Delete My Account') }}</span>
                                    <span x-show="loading">{{ __('Deleting...') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Back to Profile Link --}}
            <div class="flex justify-center">
                <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                    ← {{ __('Back to Profile Settings') }}
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function dangerZone() {
    return {
        showModal: false,
        step: 1,
        password: '',
        code: '',
        error: '',
        loading: false,

        async sendCode() {
            if (!this.password) {
                this.error = '{{ __("Password is required") }}';
                return;
            }
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch('{{ route("account.delete.send-code", ["locale" => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ password: this.password })
                });
                const data = await res.json();
                if (res.ok) {
                    this.step = 2;
                } else {
                    this.error = data.message || '{{ __("Invalid password") }}';
                }
            } catch (e) {
                this.error = '{{ __("An error occurred. Please try again.") }}';
            }
            this.loading = false;
        },

        async confirmDelete() {
            if (this.code.length !== 6) return;
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch('{{ route("account.delete.confirm", ["locale" => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: this.code, password: this.password })
                });
                const data = await res.json();
                if (res.ok) {
                    window.location.href = data.redirect || '{{ route("home", ["locale" => app()->getLocale()]) }}';
                } else {
                    this.error = data.message || '{{ __("Invalid verification code") }}';
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
