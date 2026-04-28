@extends('admin.layouts.app')

@section('title', __('FabLabs Management'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('FabLabs') }}</span>
@endsection

@section('content')
<div x-data="fablabManager()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('FabLabs') }}</h1>
            <p class="text-gray-500">{{ __('Manage fabrication laboratories') }}</p>
        </div>
        <a href="{{ route('admin.fablabs.create', ['locale' => app()->getLocale()]) }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-2"></i>{{ __('Add FabLab') }}
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" action="{{ route('admin.fablabs.index', ['locale' => app()->getLocale()]) }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[250px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by name, location, or city...') }}" class="w-full pl-10 pr-4 py-2 border rounded-lg">
                </div>
            </div>
            <select name="city" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All Cities') }}</option>
                @foreach($cities as $city)<option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>@endforeach
            </select>
            <select name="type" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All Types') }}</option>
                @foreach($types as $type)<option value="{{ $type['value'] }}" {{ request('type') === $type['value'] ? 'selected' : '' }}>{{ $type['label'] }}</option>@endforeach
            </select>
            <select name="verified" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All') }}</option>
                <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>{{ __('Verified') }}</option>
                <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>{{ __('Not Verified') }}</option>
            </select>
            @include("admin.partials.completeness-filter")
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg"><i class="fas fa-filter mr-2"></i>{{ __('Filter') }}</button>
            @if(request()->hasAny(['search', 'city', 'type', 'verified']))<a href="{{ route('admin.fablabs.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600">{{ __('Clear') }}</a>@endif
        </form>
    </div>

    <div x-show="selectedIds.length > 0" x-transition class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
        <span class="text-blue-700 font-medium"><span x-text="selectedIds.length"></span> {{ __('selected') }}</span>
        <div class="flex gap-2">
            <button @click="bulkAction('delete')" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm">{{ __('Delete') }}</button>
            <button @click="selectedIds = []" class="px-4 py-2 text-gray-600 text-sm">{{ __('Clear') }}</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
        <table class="w-full min-w-[700px]">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left"><input type="checkbox" @change="toggleAllSelection($event)" :checked="selectedIds.length === {{ $fablabs->count() }}" class="rounded"></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('FabLab') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Location') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Type') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Rating') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Members') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($fablabs as $fablab)
                    <tr class="{{ \App\Helpers\CompletenessHelper::isIncomplete($fablab, 'fablab') ? 'bg-amber-50 hover:bg-amber-100' : (\App\Helpers\CompletenessHelper::hasOther($fablab, 'fablab') ? 'bg-orange-50 hover:bg-orange-100' : 'hover:bg-gray-50') }} transition-colors">
                        <td class="px-4 py-4"><input type="checkbox" value="{{ $fablab->id }}" x-model.number="selectedIds" class="rounded"></td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                @if($fablab->image)
                                    <img src="{{ url('media/' . $fablab->image) }}" alt="{{ $fablab->name }}" class="w-12 h-12 rounded-lg object-cover">
                                @else
                                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center"><i class="fas fa-industry text-gray-400"></i></div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-800">{{ $fablab->name }}</p>
                                    <p class="text-xs text-gray-400">ID: {{ $fablab->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-gray-800">{{ $fablab->city ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ Str::limit($fablab->location, 30) }}</p>
                        </td>
                        <td class="px-4 py-4"><span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">{{ ucfirst($fablab->type ?? '-') }}</span></td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-star text-yellow-400"></i>
                                <span class="text-sm text-gray-700">{{ number_format($fablab->rating ?? 0, 1) }}</span>
                                <span class="text-xs text-gray-400">({{ $fablab->reviews_count ?? 0 }})</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700">{{ $fablab->members ?? 0 }}</td>
                        <td class="px-4 py-4">
                            @if($fablab->verified)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>{{ __('Verified') }}</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ __('Not Verified') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.fablabs.edit', ['locale' => app()->getLocale(), 'id' => $fablab->id]) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="{{ __('Edit') }}"><i class="fas fa-edit"></i></a>
                                <button @click="deleteItem({{ $fablab->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="{{ __('Delete') }}"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-500">{{ __('No FabLabs found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($fablabs->hasPages())<div class="px-4 py-3 border-t">{{ $fablabs->links() }}</div>@endif
    </div>
</div>

@push('scripts')
<script>
function fablabManager() {
    return {
        selectedIds: [], itemIds: @json($fablabs->pluck('id')),
        toggleAllSelection(e) { this.selectedIds = e.target.checked ? [...this.itemIds] : []; },
        async deleteItem(id) { if (!confirm('{{ __("Delete this FabLab?") }}')) return; try { await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/fablabs/${id}`, { method: 'DELETE' }); showToast('{{ __("Deleted") }}', 'success'); setTimeout(() => location.reload(), 1000); } catch (e) { showToast(e.message, 'error'); } },
        async bulkAction(action) { if (action === 'delete' && !confirm('{{ __("Delete selected FabLabs?") }}')) return; try { await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/fablabs/bulk-action`, { method: 'POST', body: JSON.stringify({ ids: this.selectedIds, action }) }); showToast('{{ __("Done") }}', 'success'); setTimeout(() => location.reload(), 1000); } catch (e) { showToast(e.message, 'error'); } }
    }
}
</script>
@endpush
@endsection
