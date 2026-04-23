@extends('admin.layouts.app')

@section('title', __('Accounts Management'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Accounts') }}</span>
@endsection

@section('content')
<div x-data="designersManager()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Accounts') }}</h1>
            <p class="text-gray-500">{{ __('Manage designer accounts, activate/deactivate, and set trust levels') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Auto-Accept Toggle -->
            <div class="flex items-center gap-2 bg-white rounded-lg px-4 py-2 shadow-sm border border-gray-200">
                <span class="text-sm text-gray-600">{{ __('Auto-Accept') }}:</span>
                <button @click="toggleAutoAccept()"
                        :class="autoAcceptEnabled ? 'bg-green-500' : 'bg-gray-300'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <span :class="autoAcceptEnabled ? 'translate-x-5' : 'translate-x-0'"
                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
                <span x-text="autoAcceptEnabled ? 'ON' : 'OFF'"
                      :class="autoAcceptEnabled ? 'text-green-600 font-medium' : 'text-gray-500'"
                      class="text-sm min-w-[28px]"></span>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('admin.designers.index', ['locale' => app()->getLocale()]) }}" class="flex flex-wrap gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[250px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('Search by email, name, or ID...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Sector Filter -->
            <select name="sector" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('All Sectors') }}</option>
                @foreach($sectors as $sector)
                    <option value="{{ $sector['value'] }}" {{ request('sector') === $sector['value'] ? 'selected' : '' }}>
                        {{ $sector['label'] }}
                    </option>
                @endforeach
            </select>

            <!-- Status Filter -->
            <select name="is_active" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('All Status') }}</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </select>

            <!-- Trusted Filter -->
            <select name="is_trusted" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('All Trust Levels') }}</option>
                <option value="1" {{ request('is_trusted') === '1' ? 'selected' : '' }}>{{ __('Trusted') }}</option>
                <option value="0" {{ request('is_trusted') === '0' ? 'selected' : '' }}>{{ __('Not Trusted') }}</option>
            </select>

            <!-- Submit -->
            @include("admin.partials.completeness-filter")
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-filter mr-2"></i>{{ __('Filter') }}
            </button>

            @if(request()->hasAny(['search', 'sector', 'is_active', 'is_trusted']))
                <a href="{{ route('admin.designers.index', ['locale' => app()->getLocale()]) }}"
                   class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    {{ __('Clear Filters') }}
                </a>
            @endif
        
                <a href="{{ route('admin.designers.export', array_merge(['locale' => app()->getLocale()], request()->only(['search','sector','sub_sector','city','is_active','is_trusted']))) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                    {{ __('Export to Excel') }}
                </a>
                </form>
    </div>

    <!-- Bulk Actions Bar -->
    <div x-show="selectedIds.length > 0"
         x-transition
         class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
        <span class="text-blue-700 font-medium">
            <span x-text="selectedIds.length"></span> {{ __('account(s) selected') }}
        </span>
        <div class="flex gap-2">
            <button @click="bulkAction('activate')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                <i class="fas fa-check mr-1"></i>{{ __('Activate') }}
            </button>
            <button @click="bulkAction('deactivate')"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                <i class="fas fa-ban mr-1"></i>{{ __('Deactivate') }}
            </button>
            <button @click="bulkAction('set_trusted')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                <i class="fas fa-shield-alt mr-1"></i>{{ __('Set Trusted') }}
            </button>
            <button @click="bulkAction('unset_trusted')"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm">
                <i class="fas fa-shield-alt mr-1"></i>{{ __('Unset Trusted') }}
            </button>
            <button @click="bulkAction('delete')"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                <i class="fas fa-trash mr-1"></i>{{ __('Delete') }}
            </button>
            <button @click="selectedIds = []" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors text-sm">
                {{ __('Clear') }}
            </button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox"
                                   @change="toggleAllSelection($event)"
                                   :checked="selectedIds.length === {{ $designers->count() }} && {{ $designers->count() }} > 0"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Designer') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Sector') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Trust') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Joined') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($designers as $designer)
                        <tr class="{{ \App\Helpers\CompletenessHelper::isIncomplete($designer, 'designer') ? 'bg-amber-50 hover:bg-amber-100' : (\App\Helpers\CompletenessHelper::hasOther($designer, 'designer') ? 'bg-orange-50 hover:bg-orange-100' : 'hover:bg-gray-50') }} transition-colors">
                            <td class="px-4 py-4">
                                <input type="checkbox"
                                       value="{{ $designer->id }}"
                                       x-model.number="selectedIds"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       {{ $designer->is_admin ? 'disabled' : '' }}>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold flex-shrink-0">
                                        {{ substr($designer->name ?? 'D', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium text-gray-800">{{ $designer->name }}</p>
                                            @if($designer->is_admin)
                                                <span class="px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700 rounded-full">{{ __('Admin') }}</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $designer->email }}</p>
                                        @if(!empty($designer->phone_number))
                                            <p class="text-xs text-gray-600"><svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>{{ trim(($designer->phone_country ?? "") . " " . ($designer->phone_number ?? "")) }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400">ID: {{ $designer->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-gray-800">{{ $designer->sector ?? '-' }}</p>
                                <p class="text-sm text-gray-500">{{ $designer->sub_sector ?? '' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                @if($designer->is_active !== false)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>{{ __('Active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>{{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if($designer->is_trusted)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-shield-alt mr-1"></i>{{ __('Trusted') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500">
                                {{ $designer->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- View Public Profile -->
                                    <a href="{{ url(app()->getLocale() . '/designer/' . $designer->id) }}"
                                       target="_blank"
                                       class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                       title="{{ __('View Public Profile') }}">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>

                                    <!-- View Details / Manage -->
                                    <a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}"
                                       class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="{{ __('View & Manage') }}">
                                        <i class="fas fa-user-cog"></i>
                                    </a>

                                    @if(!$designer->is_admin)
                                        <!-- Toggle Active -->
                                        <button @click="toggleActive({{ $designer->id }})"
                                                class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                                :class="'{{ $designer->is_active !== false ? 'text-yellow-600 hover:text-yellow-700' : 'text-green-600 hover:text-green-700' }}'"
                                                title="{{ $designer->is_active !== false ? __('Deactivate') : __('Activate') }}">
                                            <i class="fas {{ $designer->is_active !== false ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>

                                        <!-- Toggle Trusted -->
                                        <button @click="toggleTrusted({{ $designer->id }})"
                                                class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                                :class="'{{ $designer->is_trusted ? 'text-blue-600' : 'text-gray-400' }}'"
                                                title="{{ $designer->is_trusted ? __('Remove Trust') : __('Set as Trusted') }}">
                                            <i class="fas fa-shield-alt"></i>
                                        </button>

                                        <!-- Delete -->
                                        <button @click="deleteDesigner({{ $designer->id }}, '{{ addslashes($designer->name) }}')"
                                                class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                                title="{{ __('Delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-500">{{ __('No designers found') }}</p>
                                    @if(request()->hasAny(['search', 'sector', 'is_active', 'is_trusted']))
                                        <a href="{{ route('admin.designers.index', ['locale' => app()->getLocale()]) }}"
                                           class="mt-2 text-blue-600 hover:text-blue-700">
                                            {{ __('Clear filters') }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($designers->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $designers->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function designersManager() {
    return {
        selectedIds: [],
        designerIds: @json($designers->filter(fn($d) => !$d->is_admin)->pluck('id')->values()),
        autoAcceptEnabled: {{ \App\Models\AdminSetting::isAutoAcceptEnabled('designers') ? 'true' : 'false' }},

        async toggleAutoAccept() {
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/settings/auto-accept/designers/toggle`, {
                    method: 'POST'
                });
                this.autoAcceptEnabled = response.data.enabled;
                showToast(response.message, 'success');
            } catch (error) {
                showToast(error.message || 'Failed to toggle auto-accept', 'error');
            }
        },

        toggleAllSelection(event) {
            if (event.target.checked) {
                this.selectedIds = [...this.designerIds];
            } else {
                this.selectedIds = [];
            }
        },

        async toggleActive(id) {
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/designers/${id}/toggle-active`, {
                    method: 'POST'
                });
                showToast(response.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                showToast(error.message, 'error');
            }
        },

        async toggleTrusted(id) {
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/designers/${id}/toggle-trusted`, {
                    method: 'POST'
                });
                showToast(response.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                showToast(error.message, 'error');
            }
        },

        async deleteDesigner(id, name) {
            if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) return;

            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/designers/${id}`, {
                    method: 'DELETE'
                });
                showToast(response.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                showToast(error.message, 'error');
            }
        },

        async bulkAction(action) {
            const actionLabels = {
                activate: 'activate',
                deactivate: 'deactivate',
                set_trusted: 'set as trusted',
                unset_trusted: 'remove trust from',
                delete: 'delete'
            };

            if (!confirm(`Are you sure you want to ${actionLabels[action]} ${this.selectedIds.length} account(s)?`)) return;

            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/designers/bulk-action`, {
                    method: 'POST',
                    body: JSON.stringify({
                        ids: this.selectedIds,
                        action: action
                    })
                });
                showToast(response.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                showToast(error.message, 'error');
            }
        }
    }
}
</script>
@endpush
@endsection
