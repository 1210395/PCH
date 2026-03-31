@extends('admin.layouts.app')

@section('title', __('Tenders Management'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Tenders') }}</span>
@endsection

@section('content')
<div x-data="tenderManager()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Tenders & Opportunities') }}</h1>
            <p class="text-gray-500">{{ __('Manage tender opportunities for designers') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tenders.create', ['locale' => app()->getLocale()]) }}" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:from-blue-700 hover:to-green-600 transition">
                <i class="fas fa-plus mr-2"></i>{{ __('Add Tender') }}
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[250px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by title, publisher, or description...') }}" class="w-full pl-10 pr-4 py-2 border rounded-lg">
                </div>
            </div>
            <select name="tender_status" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All Status') }}</option>
                <option value="open" {{ request('tender_status') === 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                <option value="closing_soon" {{ request('tender_status') === 'closing_soon' ? 'selected' : '' }}>{{ __('Closing Soon') }}</option>
                <option value="closed" {{ request('tender_status') === 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
            </select>
            <select name="publisher_type" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All Publisher Types') }}</option>
                <option value="government" {{ request('publisher_type') === 'government' ? 'selected' : '' }}>{{ __('Government') }}</option>
                <option value="ngo" {{ request('publisher_type') === 'ngo' ? 'selected' : '' }}>{{ __('NGO') }}</option>
                <option value="private" {{ request('publisher_type') === 'private' ? 'selected' : '' }}>{{ __('Private Sector') }}</option>
                <option value="academic" {{ request('publisher_type') === 'academic' ? 'selected' : '' }}>{{ __('Academic') }}</option>
                <option value="media" {{ request('publisher_type') === 'media' ? 'selected' : '' }}>{{ __('Media') }}</option>
            </select>
            <select name="visibility" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All Visibility') }}</option>
                <option value="visible" {{ request('visibility') === 'visible' ? 'selected' : '' }}>{{ __('Visible') }}</option>
                <option value="hidden" {{ request('visibility') === 'hidden' ? 'selected' : '' }}>{{ __('Hidden') }}</option>
            </select>
            <select name="source" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All Sources') }}</option>
                <option value="api" {{ request('source') === 'api' ? 'selected' : '' }}>{{ __('From API') }}</option>
                <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}>{{ __('Manual') }}</option>
            </select>
            @include("admin.partials.completeness-filter")
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg"><i class="fas fa-filter mr-2"></i>{{ __('Filter') }}</button>
            @if(request()->hasAny(['search', 'tender_status', 'publisher_type', 'visibility', 'source']))<a href="{{ route('admin.tenders.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600">{{ __('Clear') }}</a>@endif
        </form>
    </div>

    <div x-show="selectedIds.length > 0" x-transition class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
        <span class="text-blue-700 font-medium"><span x-text="selectedIds.length"></span> {{ __('selected') }}</span>
        <div class="flex gap-2">
            <button @click="bulkAction('show')" class="px-4 py-2 bg-green-500 text-white rounded-lg text-sm"><i class="fas fa-eye mr-1"></i>{{ __('Show') }}</button>
            <button @click="bulkAction('hide')" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm"><i class="fas fa-eye-slash mr-1"></i>{{ __('Hide') }}</button>
            <button @click="bulkAction('delete')" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm"><i class="fas fa-trash mr-1"></i>{{ __('Delete') }}</button>
            <button @click="selectedIds = []" class="px-4 py-2 text-gray-600 text-sm">{{ __('Clear') }}</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
        <table class="w-full min-w-[700px]">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left"><input type="checkbox" @change="toggleAllSelection($event)" :checked="selectedIds.length === {{ $tenders->count() }}" class="rounded"></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Tender') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Publisher') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Budget') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Deadline') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Visible') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Source') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($tenders as $tender)
                    <tr class="{{ \App\Helpers\CompletenessHelper::isIncomplete($tender, 'tender') ? 'bg-amber-50 hover:bg-amber-100' : (\App\Helpers\CompletenessHelper::hasOther($tender, 'tender') ? 'bg-orange-50 hover:bg-orange-100' : 'hover:bg-gray-50') }} {{ !$tender->is_visible ? 'opacity-60' : '' }} transition-colors">
                        <td class="px-4 py-4"><input type="checkbox" value="{{ $tender->id }}" x-model.number="selectedIds" class="rounded"></td>
                        <td class="px-4 py-4">
                            <div>
                                <p class="font-medium text-gray-800">{{ Str::limit($tender->title, 40) }}</p>
                                <p class="text-xs text-gray-400">ID: {{ $tender->id }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-gray-800">{{ Str::limit($tender->publisher, 25) ?? '-' }}</p>
                            @php
                                $publisherColors = ['government' => 'bg-blue-100 text-blue-700', 'ngo' => 'bg-green-100 text-green-700', 'private' => 'bg-purple-100 text-purple-700', 'academic' => 'bg-orange-100 text-orange-700', 'media' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="px-2 py-0.5 text-xs rounded {{ $publisherColors[$tender->publisher_type] ?? 'bg-gray-100 text-gray-700' }}">{{ $tender->publisher_type_label }}</span>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700">{{ $tender->budget ?? '-' }}</td>
                        <td class="px-4 py-4">
                            @if($tender->deadline)
                                <p class="text-sm text-gray-700">{{ $tender->deadline->format('M d, Y') }}</p>
                                @if($tender->days_until_deadline !== null && $tender->days_until_deadline >= 0 && $tender->days_until_deadline <= 14)
                                    <p class="text-xs text-orange-600">{{ $tender->days_until_deadline }} {{ __('days left') }}</p>
                                @elseif($tender->days_until_deadline !== null && $tender->days_until_deadline < 0)
                                    <p class="text-xs text-red-600">{{ __('Expired') }}</p>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $statusColors = ['open' => 'bg-green-100 text-green-800', 'closing_soon' => 'bg-orange-100 text-orange-800', 'closed' => 'bg-gray-100 text-gray-800'];
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$tender->status] ?? 'bg-gray-100 text-gray-800' }}">{{ $tender->status_label }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <button @click="toggleVisibility({{ $tender->id }}, {{ $tender->is_visible ? 'true' : 'false' }})"
                                    class="p-2 rounded-lg {{ $tender->is_visible ? 'text-green-600 hover:bg-green-50' : 'text-gray-400 hover:bg-gray-100' }}"
                                    title="{{ $tender->is_visible ? __('Click to hide') : __('Click to show') }}">
                                <i class="fas {{ $tender->is_visible ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                            </button>
                        </td>
                        <td class="px-4 py-4">
                            @if($tender->external_id)
                                <span class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-700">{{ __('API') }}</span>
                            @else
                                <span class="px-2 py-0.5 text-xs rounded bg-purple-100 text-purple-700">{{ __('Manual') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.tenders.edit', ['locale' => app()->getLocale(), 'id' => $tender->id]) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="{{ __('Edit') }}"><i class="fas fa-edit"></i></a>
                                <button @click="deleteItem({{ $tender->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="{{ __('Delete') }}"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-12 text-center text-gray-500">{{ __('No tenders found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($tenders->hasPages())<div class="px-4 py-3 border-t">{{ $tenders->links() }}</div>@endif
    </div>
</div>

@push('scripts')
<script>
function tenderManager() {
    return {
        selectedIds: [],
        itemIds: @json($tenders->pluck('id')),

        toggleAllSelection(e) {
            this.selectedIds = e.target.checked ? [...this.itemIds] : [];
        },

        async toggleVisibility(id, currentlyVisible) {
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/tenders/${id}/toggle-visibility`, {
                    method: 'POST'
                });
                showToast(currentlyVisible ? 'Tender hidden' : 'Tender visible', 'success');
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async deleteItem(id) {
            if (!confirm('{{ __("Delete this tender?") }}')) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/tenders/${id}`, { method: 'DELETE' });
                showToast('Deleted', 'success');
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async bulkAction(action) {
            if (action === 'delete' && !confirm('{{ __("Delete selected tenders?") }}')) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/tenders/bulk-action`, {
                    method: 'POST',
                    body: JSON.stringify({ ids: this.selectedIds, action })
                });
                showToast('{{ __("Done") }}', 'success');
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                showToast(e.message, 'error');
            }
        }
    }
}
</script>
@endpush
@endsection
