@extends('admin.layouts.app')

@section('title', __('Academic Accounts Management'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Academic Accounts') }}</span>
@endsection

@section('content')
<div x-data="academicAccountsManager()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Academic Accounts') }}</h1>
            <p class="text-gray-500">{{ __('Manage university, TVET, and college accounts') }}</p>
        </div>
        <a href="{{ route('admin.academic-accounts.create', ['locale' => app()->getLocale()]) }}"
           class="px-4 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all">
            <i class="fas fa-plus mr-2"></i>{{ __('Add New Account') }}
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Total Accounts') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-university text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Active') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['active'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Universities') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['universities'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ __('TVETs') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['tvets'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tools text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('admin.academic-accounts.index', ['locale' => app()->getLocale()]) }}" class="flex flex-wrap gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[250px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 rtl:right-3 rtl:left-auto top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('Search by name, email, or city...') }}"
                           class="w-full pl-10 rtl:pr-10 rtl:pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Type Filter -->
            <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="all">{{ __('All Types') }}</option>
                <option value="university" {{ request('type') === 'university' ? 'selected' : '' }}>{{ __('University') }}</option>
                <option value="tvet" {{ request('type') === 'tvet' ? 'selected' : '' }}>{{ __('TVET') }}</option>
                <option value="college" {{ request('type') === 'college' ? 'selected' : '' }}>{{ __('College') }}</option>
                <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
            </select>

            <!-- Status Filter -->
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('All Status') }}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </select>

            <!-- Submit -->
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-filter mr-2"></i>{{ __('Filter') }}
            </button>

            @if(request()->hasAny(['search', 'type', 'status']))
                <a href="{{ route('admin.academic-accounts.index', ['locale' => app()->getLocale()]) }}"
                   class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    {{ __('Clear Filters') }}
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Institution') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Type') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('City') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Content') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Joined') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($accounts as $account)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    @if($account->logo)
                                        <img src="{{ asset('storage/' . $account->logo) }}" alt="{{ $account->name }}" class="w-10 h-10 rounded-lg object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($account->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $account->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $account->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    @if($account->institution_type === 'university') bg-blue-100 text-blue-700
                                    @elseif($account->institution_type === 'tvet') bg-purple-100 text-purple-700
                                    @elseif($account->institution_type === 'college') bg-green-100 text-green-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ $account->institution_type_label }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-gray-600">
                                {{ $account->city ?? '-' }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded">{{ $account->trainings_count }} {{ __('trainings') }}</span>
                                    <span class="px-2 py-1 bg-green-50 text-green-700 rounded">{{ $account->workshops_count }} {{ __('workshops') }}</span>
                                    <span class="px-2 py-1 bg-purple-50 text-purple-700 rounded">{{ $account->announcements_count }} {{ __('announcements') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <button @click="toggleActive({{ $account->id }})"
                                        class="px-3 py-1 rounded-full text-xs font-medium transition-colors
                                        {{ $account->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                    {{ $account->is_active ? __('Active') : __('Inactive') }}
                                </button>
                            </td>
                            <td class="px-4 py-4 text-gray-500 text-sm">
                                {{ $account->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.academic-accounts.show', ['locale' => app()->getLocale(), 'id' => $account->id]) }}"
                                       class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="{{ __('View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.academic-accounts.edit', ['locale' => app()->getLocale(), 'id' => $account->id]) }}"
                                       class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                       title="{{ __('Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button @click="deleteAccount({{ $account->id }}, '{{ $account->name }}')"
                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="{{ __('Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-university text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">{{ __('No academic accounts found') }}</p>
                                    <p class="text-sm">{{ __('Create your first academic account to get started') }}</p>
                                    <a href="{{ route('admin.academic-accounts.create', ['locale' => app()->getLocale()]) }}"
                                       class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-plus mr-2"></i>{{ __('Add New Account') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($accounts->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $accounts->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function academicAccountsManager() {
    return {
        async toggleActive(id) {
            if (!confirm('{{ __("Are you sure you want to toggle this account\'s status?") }}')) return;

            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-accounts/${id}/toggle-active`, {
                    method: 'POST'
                });
                showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __("Failed to toggle status") }}', 'error');
            }
        },

        async deleteAccount(id, name) {
            if (!confirm(`{{ __("Are you sure you want to delete") }} "${name}"? {{ __("This action cannot be undone.") }}`)) return;

            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-accounts/${id}`, {
                    method: 'DELETE'
                });
                showToast(response.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __("Failed to delete account") }}', 'error');
            }
        }
    }
}
</script>
@endpush
@endsection
