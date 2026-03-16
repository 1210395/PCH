@extends('admin.layouts.app')

@section('title', __('Designer Details') . ' - ' . $designer->name)

@section('breadcrumb')
    <a href="{{ route('admin.designers.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:text-blue-700">{{ __('Accounts') }}</a>
    <span class="mx-2 text-gray-400">/</span>
    <span class="text-gray-700">{{ $designer->name }}</span>
@endsection

@section('content')
<div x-data="designerDetail()" class="space-y-6">
    <!-- Back Button & Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.designers.index', ['locale' => app()->getLocale()]) }}"
               class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $designer->name }}</h1>
                <p class="text-gray-500">{{ __('Account ID') }}: {{ $designer->id }}</p>
            </div>
        </div>

        <div class="flex gap-2">
            <!-- Edit Profile Button -->
            <a href="{{ route('admin.designers.edit', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}"
               class="px-4 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all">
                <i class="fas fa-edit mr-2"></i>{{ __('Edit Profile') }}
            </a>

            @if(!$designer->is_admin)
            <button @click="toggleActive()"
                    class="px-4 py-2 rounded-lg transition-colors {{ $designer->is_active ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                <i class="fas {{ $designer->is_active ? 'fa-ban' : 'fa-check' }} mr-2"></i>
                {{ $designer->is_active ? __('Deactivate') : __('Activate') }}
            </button>
            <button @click="toggleTrusted()"
                    class="px-4 py-2 rounded-lg transition-colors {{ $designer->is_trusted ? 'bg-blue-100 text-blue-700 hover:bg-blue-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                <i class="fas fa-shield-alt mr-2"></i>
                {{ $designer->is_trusted ? __('Remove Trust') : __('Set Trusted') }}
            </button>
            <button @click="deleteDesigner()"
                    class="px-4 py-2 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg transition-colors">
                <i class="fas fa-trash mr-2"></i>{{ __('Delete') }}
            </button>
            @endif
        </div>
    </div>

    <!-- Profile Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Profile Information') }}</h2>

            <div class="flex items-start gap-6">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    @if($designer->avatar)
                        <img src="{{ url('media/' . $designer->avatar) }}"
                             alt="{{ $designer->name }}"
                             class="w-24 h-24 rounded-full object-cover">
                    @else
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white text-3xl font-bold">
                            {{ substr($designer->name ?? 'D', 0, 1) }}
                        </div>
                    @endif
                </div>

                <!-- Details -->
                <div class="flex-1 grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-500">{{ __('Email') }}</label>
                        <p class="text-gray-800">{{ $designer->email }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">{{ __('Phone') }}</label>
                        <p class="text-gray-800">{{ $designer->phone_number ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">{{ __('Sector') }}</label>
                        <p class="text-gray-800">{{ $designer->sector ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">{{ __('Sub-Sector') }}</label>
                        <p class="text-gray-800">{{ $designer->sub_sector ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">{{ __('City') }}</label>
                        <p class="text-gray-800">{{ $designer->city ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">{{ __('Joined') }}</label>
                        <p class="text-gray-800">{{ $designer->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            @if($designer->bio)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <label class="text-sm text-gray-500">{{ __('Bio') }}</label>
                <p class="text-gray-800 mt-1">{{ $designer->bio }}</p>
            </div>
            @endif

            @if($designer->skills && $designer->skills->count() > 0)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <label class="text-sm text-gray-500">{{ __('Skills') }}</label>
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($designer->skills as $skill)
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">{{ $skill->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Status Card -->
        <div class="space-y-6">
            <!-- Account Status -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Account Status') }}</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Status') }}</span>
                        @if($designer->is_active)
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                                <i class="fas fa-check-circle mr-1"></i>{{ __('Active') }}
                            </span>
                        @else
                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                                <i class="fas fa-times-circle mr-1"></i>{{ __('Inactive') }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Trust Level') }}</span>
                        @if($designer->is_trusted)
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                                <i class="fas fa-shield-alt mr-1"></i>{{ __('Trusted') }}
                            </span>
                        @else
                            <span class="text-gray-400">{{ __('Not Trusted') }}</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Role') }}</span>
                        @if($designer->is_admin)
                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                                <i class="fas fa-crown mr-1"></i>{{ __('Admin') }}
                            </span>
                        @else
                            <span class="text-gray-800">{{ __('User') }}</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">{{ __('Email Verified') }}</span>
                        @if($designer->email_verified_at)
                            <span class="text-green-600"><i class="fas fa-check"></i> {{ __('Verified') }}</span>
                        @else
                            <span class="text-yellow-600"><i class="fas fa-clock"></i> {{ __('Pending') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Content Stats -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Content Statistics') }}</h2>
                <div class="space-y-3">
                    @foreach($contentStats as $type => $stats)
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-gray-700 capitalize">{{ $type }}</span>
                            <span class="text-gray-500">{{ $stats['total'] }} {{ __('total') }}</span>
                        </div>
                        <div class="flex gap-2 text-xs">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded">{{ $stats['pending'] }} {{ __('pending') }}</span>
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded">{{ $stats['approved'] }} {{ __('approved') }}</span>
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded">{{ $stats['rejected'] }} {{ __('rejected') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Quick Actions') }}</h2>
                <div class="space-y-2">
                    <a href="{{ route('admin.designers.edit', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}"
                       class="flex items-center gap-2 w-full px-4 py-2 text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                        <i class="fas fa-edit"></i>
                        <span>{{ __('Edit Profile') }}</span>
                    </a>
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}"
                       target="_blank"
                       class="flex items-center gap-2 w-full px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-external-link-alt"></i>
                        <span>{{ __('View Public Profile') }}</span>
                    </a>
                    <button @click="showResetPasswordModal = true"
                            class="flex items-center gap-2 w-full px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-key"></i>
                        <span>{{ __('Reset Password') }}</span>
                    </button>
                    @if($contentStats['products']['pending'] > 0 || $contentStats['projects']['pending'] > 0)
                    <a href="{{ route('admin.products.index', ['locale' => app()->getLocale(), 'designer_id' => $designer->id]) }}"
                       class="flex items-center gap-2 w-full px-4 py-2 text-yellow-700 hover:bg-yellow-50 rounded-lg transition-colors">
                        <i class="fas fa-clock"></i>
                        <span>{{ __('Review Pending Content') }}</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div x-show="showResetPasswordModal"
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @click.self="showResetPasswordModal = false">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Reset Password') }}</h3>
            <p class="text-gray-600 mb-4">{{ __('Enter a new password for') }} {{ $designer->name }}.</p>

            <form @submit.prevent="resetPassword()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('New Password') }}</label>
                        <input type="password"
                               x-model="newPassword"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               minlength="8"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Confirm Password') }}</label>
                        <input type="password"
                               x-model="confirmPassword"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               minlength="8"
                               required>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button"
                            @click="showResetPasswordModal = false"
                            class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function designerDetail() {
    return {
        showResetPasswordModal: false,
        newPassword: '',
        confirmPassword: '',

        async toggleActive() {
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/designers/{{ $designer->id }}/toggle-active`, {
                    method: 'POST'
                });
                showToast(response.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                showToast(error.message, 'error');
            }
        },

        async toggleTrusted() {
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/designers/{{ $designer->id }}/toggle-trusted`, {
                    method: 'POST'
                });
                showToast(response.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                showToast(error.message, 'error');
            }
        },

        async deleteDesigner() {
            if (!confirm('{{ __("Are you sure you want to delete this account? This action cannot be undone.") }}')) return;

            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/designers/{{ $designer->id }}`, {
                    method: 'DELETE'
                });
                showToast(response.message, 'success');
                setTimeout(() => window.location.href = '{{ route("admin.designers.index", ["locale" => app()->getLocale()]) }}', 1000);
            } catch (error) {
                showToast(error.message, 'error');
            }
        },

        async resetPassword() {
            if (this.newPassword !== this.confirmPassword) {
                showToast('{{ __("Passwords do not match") }}', 'error');
                return;
            }

            if (this.newPassword.length < 8) {
                showToast('{{ __("Password must be at least 8 characters") }}', 'error');
                return;
            }

            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/designers/{{ $designer->id }}/reset-password`, {
                    method: 'POST',
                    body: JSON.stringify({
                        password: this.newPassword,
                        password_confirmation: this.confirmPassword
                    })
                });
                showToast(response.message, 'success');
                this.showResetPasswordModal = false;
                this.newPassword = '';
                this.confirmPassword = '';
            } catch (error) {
                showToast(error.message, 'error');
            }
        }
    }
}
</script>
@endpush
@endsection
