@extends('admin.layouts.app')

@section('title', __('Services Management'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Services') }}</span>
@endsection

@section('content')
<div x-data="contentManager()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Services') }}</h1>
            <p class="text-gray-500">{{ __('Manage and approve service listings') }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($pendingCount > 0)
                <span class="px-4 py-2 bg-orange-100 text-orange-700 rounded-full text-sm font-medium">{{ $pendingCount }} {{ __('pending') }}</span>
            @endif
            <!-- Auto-Accept Toggle -->
            <div class="flex items-center gap-2 bg-white rounded-lg px-4 py-2 shadow-sm border border-gray-200">
                <span class="text-sm text-gray-600">{{ __('Auto-Accept:') }}</span>
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

    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[250px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" :placeholder="__('Search...')" class="w-full pl-10 pr-4 py-2 border rounded-lg">
                </div>
            </div>
            <select name="status" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All Status') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
            </select>
            <select name="category" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All Categories') }}</option>
                @foreach($categories as $cat)<option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>@endforeach
            </select>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg"><i class="fas fa-filter mr-2"></i>{{ __('Filter') }}</button>
            @if(request()->hasAny(['search', 'status', 'category']))<a href="{{ route('admin.services.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600">{{ __('Clear') }}</a>@endif
        </form>
    </div>

    <div x-show="selectedIds.length > 0" x-transition class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
        <span class="text-blue-700 font-medium"><span x-text="selectedIds.length"></span> {{ __('selected') }}</span>
        <div class="flex gap-2">
            <button @click="bulkAction('approve')" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">{{ __('Approve') }}</button>
            <button @click="showRejectModal = true; rejectingIds = [...selectedIds]" class="px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm">{{ __('Reject') }}</button>
            <button @click="bulkAction('delete')" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm">{{ __('Delete') }}</button>
            <button @click="selectedIds = []" class="px-4 py-2 text-gray-600 text-sm">{{ __('Clear') }}</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
        <table class="w-full min-w-[700px]">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left"><input type="checkbox" @change="toggleAllSelection($event)" :checked="selectedIds.length === {{ $services->count() }}" class="rounded"></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Service') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Designer') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Category') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Created') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($services as $service)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4"><input type="checkbox" value="{{ $service->id }}" x-model.number="selectedIds" class="rounded"></td>
                        <td class="px-4 py-4">
                            <p class="font-medium text-gray-800">{{ Str::limit($service->name, 40) }}</p>
                            <p class="text-xs text-gray-400">ID: {{ $service->id }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-gray-800">{{ $service->designer->name ?? __('Unknown') }}</p>
                            <p class="text-xs text-gray-500">{{ $service->designer->email ?? '' }}</p>
                        </td>
                        <td class="px-4 py-4"><span class="px-2 py-1 text-xs bg-gray-100 rounded">{{ $service->category ?? '-' }}</span></td>
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $service->approval_status === 'approved' ? 'bg-green-100 text-green-800' : ($service->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($service->approval_status ?? 'pending') }}</span>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-500">{{ $service->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.services.edit', ['locale' => app()->getLocale(), 'id' => $service->id]) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="{{ __('Edit') }}"><i class="fas fa-edit"></i></a>
                                @if($service->approval_status !== 'approved')<button @click="approve({{ $service->id }})" class="p-2 text-green-600 hover:bg-green-50 rounded-lg"><i class="fas fa-check"></i></button>@endif
                                @if($service->approval_status !== 'rejected')<button @click="openRejectModal({{ $service->id }})" class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg"><i class="fas fa-times"></i></button>@endif
                                <button @click="deleteItem({{ $service->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">{{ __('No services found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($services->hasPages())<div class="px-4 py-3 border-t">{{ $services->links() }}</div>@endif
    </div>

    <div x-show="showRejectModal" x-cloak @click.self="showRejectModal = false" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('Reject Service') }}</h3>
            <textarea x-model="rejectReason" :placeholder="__('Reason (optional)...')" rows="3" class="w-full px-4 py-3 border rounded-lg mb-4"></textarea>
            <div class="flex justify-end gap-3">
                <button @click="showRejectModal = false" class="px-4 py-2 text-gray-600">{{ __('Cancel') }}</button>
                <button @click="submitReject()" class="px-6 py-2 bg-yellow-600 text-white rounded-lg">{{ __('Reject') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function contentManager() {
    return {
        selectedIds: [], itemIds: @json($services->pluck('id')), showRejectModal: false, rejectingIds: [], rejectReason: '',
        autoAcceptEnabled: {{ \App\Models\AdminSetting::isAutoAcceptEnabled('services') ? 'true' : 'false' }},
        async toggleAutoAccept() {
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/settings/auto-accept/services/toggle`, { method: 'POST' });
                this.autoAcceptEnabled = response.data.enabled;
                showToast(response.message, 'success');
            } catch (error) { showToast(error.message || 'Failed to toggle auto-accept', 'error'); }
        },
        toggleAllSelection(e) { this.selectedIds = e.target.checked ? [...this.itemIds] : []; },
        async approve(id) { try { await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/services/${id}/approve`, { method: 'POST' }); showToast('{{ __("Approved") }}', 'success'); setTimeout(() => location.reload(), 1000); } catch (e) { showToast(e.message, 'error'); } },
        openRejectModal(id) { this.rejectingIds = [id]; this.rejectReason = ''; this.showRejectModal = true; },
        async submitReject() { try { const url = this.rejectingIds.length === 1 ? `{{ url('') }}/{{ app()->getLocale() }}/admin/services/${this.rejectingIds[0]}/reject` : `{{ url('') }}/{{ app()->getLocale() }}/admin/services/bulk-action`; const body = this.rejectingIds.length === 1 ? { reason: this.rejectReason } : { ids: this.rejectingIds, action: 'reject', reason: this.rejectReason }; await adminFetch(url, { method: 'POST', body: JSON.stringify(body) }); showToast('{{ __("Rejected") }}', 'success'); this.showRejectModal = false; setTimeout(() => location.reload(), 1000); } catch (e) { showToast(e.message, 'error'); } },
        async deleteItem(id) { if (!confirm('{{ __("Delete?") }}')) return; try { await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/services/${id}`, { method: 'DELETE' }); showToast('{{ __("Deleted") }}', 'success'); setTimeout(() => location.reload(), 1000); } catch (e) { showToast(e.message, 'error'); } },
        async bulkAction(action) { if (action === 'delete' && !confirm('{{ __("Delete selected?") }}')) return; try { await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/services/bulk-action`, { method: 'POST', body: JSON.stringify({ ids: this.selectedIds, action }) }); showToast('{{ __("Done") }}', 'success'); setTimeout(() => location.reload(), 1000); } catch (e) { showToast(e.message, 'error'); } }
    }
}
</script>
@endpush
@endsection
