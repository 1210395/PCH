@extends('admin.layouts.app')

@section('title', __('Academic Workshops'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Academic Workshops') }}</span>
@endsection

@section('content')
<div x-data="workshopsManager()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Academic Workshops') }}</h1>
            <p class="text-gray-500">{{ __('Review and approve workshops submitted by academic institutions') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Auto-Accept Toggle -->
            <div class="flex items-center gap-2 bg-white rounded-lg px-4 py-2 shadow-sm border border-gray-200">
                <span class="text-sm text-gray-600">{{ __('Auto-Accept:') }}</span>
                <button @click="toggleAutoAccept()"
                        :class="autoAcceptEnabled ? 'bg-green-500' : 'bg-gray-300'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <span :class="autoAcceptEnabled ? 'translate-x-5' : 'translate-x-0'"
                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
                <span x-text="autoAcceptEnabled ? '{{ __('ON') }}' : '{{ __('OFF') }}'"
                      :class="autoAcceptEnabled ? 'text-green-600 font-medium' : 'text-gray-500'"
                      class="text-sm min-w-[28px]"></span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">{{ __('Total') }}</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-orange-500">
            <p class="text-sm text-gray-500">{{ __('Pending') }}</p>
            <p class="text-2xl font-bold text-orange-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">{{ __('Approved') }}</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-500">
            <p class="text-sm text-gray-500">{{ __('Rejected') }}</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-gray-500">
            <p class="text-sm text-gray-500">{{ __('Expired') }}</p>
            <p class="text-2xl font-bold text-gray-600">{{ $stats['expired'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('admin.academic-content.workshops', ['locale' => app()->getLocale()]) }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[250px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('Search workshops or institutions...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="all">{{ __('All Status') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
            </select>
            <select name="expired" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('All') }}</option>
                <option value="no" {{ request('expired') === 'no' ? 'selected' : '' }}>{{ __('Active Only') }}</option>
                <option value="yes" {{ request('expired') === 'yes' ? 'selected' : '' }}>{{ __('Expired Only') }}</option>
            </select>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-filter mr-2"></i>{{ __('Filter') }}
            </button>
            @if(request()->hasAny(['search', 'status', 'expired']))
                <a href="{{ route('admin.academic-content.workshops', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <!-- Bulk Actions -->
    <div x-show="selectedIds.length > 0" x-transition class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
        <span class="text-blue-700 font-medium"><span x-text="selectedIds.length"></span> {{ __('selected') }}</span>
        <div class="flex gap-2">
            <button @click="bulkAction('approve')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                <i class="fas fa-check mr-1"></i>{{ __('Approve') }}
            </button>
            <button @click="showRejectModal = true" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                <i class="fas fa-times mr-1"></i>{{ __('Reject') }}
            </button>
            <button @click="selectedIds = []" class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm">{{ __('Clear') }}</button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" @change="toggleAllSelection($event)" :checked="selectedIds.length === {{ $workshops->count() }}" class="rounded border-gray-300 text-blue-600">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Workshop') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Institution') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Date & Time') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Capacity') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($workshops as $workshop)
                        <tr class="hover:bg-gray-50 {{ $workshop->is_expired ? 'bg-gray-50 opacity-75' : '' }}">
                            <td class="px-4 py-4">
                                <input type="checkbox" value="{{ $workshop->id }}" x-model.number="selectedIds" class="rounded border-gray-300 text-blue-600">
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    @if($workshop->image)
                                        <img src="{{ url('media/' . $workshop->image) }}" class="w-12 h-12 rounded-lg object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                                            <i class="fas fa-tools text-green-600"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-800">{{ Str::limit($workshop->title, 40) }}</p>
                                        <p class="text-sm text-gray-500">{{ $workshop->location ?? __('No location') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-medium text-gray-800">{{ $workshop->academicAccount->name ?? __('Unknown') }}</p>
                                <p class="text-sm text-gray-500">{{ $workshop->academicAccount->institution_type_label ?? '' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-gray-800">{{ $workshop->workshop_date->format('M d, Y') }}</p>
                                @if($workshop->start_time)
                                    <p class="text-sm text-gray-500">{{ $workshop->start_time }} - {{ $workshop->end_time ?? __('TBD') }}</p>
                                @endif
                                @if($workshop->is_expired)
                                    <span class="text-xs text-red-600 font-medium">{{ __('Expired') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if($workshop->max_participants)
                                    <p class="text-gray-800">{{ $workshop->max_participants }} {{ __('spots') }}</p>
                                @else
                                    <p class="text-gray-500">{{ __('Unlimited') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if($workshop->approval_status === 'approved') bg-green-100 text-green-700
                                    @elseif($workshop->approval_status === 'rejected') bg-red-100 text-red-700
                                    @else bg-orange-100 text-orange-700 @endif">
                                    {{ ucfirst($workshop->approval_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.academic-content.workshops.show', ['locale' => app()->getLocale(), 'id' => $workshop->id]) }}"
                                       class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="{{ __('View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($workshop->approval_status === 'pending')
                                        <button @click="approve({{ $workshop->id }})"
                                                class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg" title="{{ __('Approve') }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button @click="rejectId = {{ $workshop->id }}; showRejectModal = true"
                                                class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg" title="{{ __('Reject') }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                    <button @click="deleteWorkshop({{ $workshop->id }})"
                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg" title="{{ __('Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                <i class="fas fa-tools text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg font-medium">{{ __('No workshops found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($workshops->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $workshops->links() }}</div>
        @endif
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showRejectModal = false; rejectId = null"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Reject Workshop') }}</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Reason for rejection') }} <span class="text-red-500">*</span></label>
                <textarea x-model="rejectReason" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="{{ __('Please provide a reason...') }}"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button @click="showRejectModal = false; rejectId = null; rejectReason = ''" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">{{ __('Cancel') }}</button>
                <button @click="reject()" :disabled="!rejectReason" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">{{ __('Reject') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function workshopsManager() {
    return {
        selectedIds: [],
        showRejectModal: false,
        rejectId: null,
        rejectReason: '',
        autoAcceptEnabled: {{ \App\Models\AdminSetting::isAutoAcceptEnabled('workshops') ? 'true' : 'false' }},

        async toggleAutoAccept() {
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/settings/auto-accept/workshops/toggle`, {
                    method: 'POST'
                });
                this.autoAcceptEnabled = response.data.enabled;
                showToast(response.message, 'success');
            } catch (error) {
                showToast(error.message || '{{ __('Failed to toggle auto-accept') }}', 'error');
            }
        },

        toggleAllSelection(event) {
            if (event.target.checked) {
                this.selectedIds = @json($workshops->pluck('id'));
            } else {
                this.selectedIds = [];
            }
        },

        async approve(id) {
            if (!confirm('{{ __('Approve this workshop?') }}')) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-content/workshops/${id}/approve`, { method: 'POST' });
                showToast('{{ __('Workshop approved') }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to approve') }}', 'error');
            }
        },

        async reject() {
            if (!this.rejectReason) return;
            try {
                const ids = this.rejectId ? [this.rejectId] : this.selectedIds;
                for (const id of ids) {
                    await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-content/workshops/${id}/reject`, {
                        method: 'POST',
                        body: JSON.stringify({ reason: this.rejectReason })
                    });
                }
                showToast('{{ __('Workshop(s) rejected') }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to reject') }}', 'error');
            }
        },

        async bulkAction(action) {
            if (action === 'approve') {
                if (!confirm(`{{ __('Approve') }} ${this.selectedIds.length} {{ __('workshop(s)?') }}`)) return;
                for (const id of this.selectedIds) {
                    await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-content/workshops/${id}/approve`, { method: 'POST' });
                }
                showToast('{{ __('Workshops approved') }}', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        },

        async deleteWorkshop(id) {
            if (!confirm('{{ __('Are you sure you want to delete this workshop? This action cannot be undone.') }}')) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-content/workshops/${id}`, { method: 'DELETE' });
                showToast('{{ __('Workshop deleted') }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to delete') }}', 'error');
            }
        }
    }
}
</script>
@endpush
@endsection
