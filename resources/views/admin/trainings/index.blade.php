@extends('admin.layouts.app')

@section('title', __('Trainings Management'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Trainings') }}</span>
@endsection

@section('content')
<div x-data="trainingManager()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Trainings & Workshops') }}</h1>
            <p class="text-gray-500">{{ __('Manage training courses and workshops') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.trainings.create', ['locale' => app()->getLocale()]) }}" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:from-blue-700 hover:to-green-600 transition">
                <i class="fas fa-plus mr-2"></i>{{ __('Add Training') }}
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[250px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by title, description, or instructor...') }}" class="w-full pl-10 pr-4 py-2 border rounded-lg">
                </div>
            </div>
            <select name="category" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All Categories') }}</option>
                @foreach($categories as $cat)<option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>@endforeach
            </select>
            <select name="level" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All Levels') }}</option>
                <option value="beginner" {{ request('level') === 'beginner' ? 'selected' : '' }}>{{ __('Beginner') }}</option>
                <option value="intermediate" {{ request('level') === 'intermediate' ? 'selected' : '' }}>{{ __('Intermediate') }}</option>
                <option value="advanced" {{ request('level') === 'advanced' ? 'selected' : '' }}>{{ __('Advanced') }}</option>
            </select>
            <select name="type" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All Types') }}</option>
                <option value="online" {{ request('type') === 'online' ? 'selected' : '' }}>{{ __('Online') }}</option>
                <option value="in-person" {{ request('type') === 'in-person' ? 'selected' : '' }}>{{ __('In-Person') }}</option>
                <option value="hybrid" {{ request('type') === 'hybrid' ? 'selected' : '' }}>{{ __('Hybrid') }}</option>
            </select>
            <select name="featured" class="px-4 py-2 border rounded-lg">
                <option value="">{{ __('All') }}</option>
                <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>{{ __('Featured') }}</option>
                <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>{{ __('Not Featured') }}</option>
            </select>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg"><i class="fas fa-filter mr-2"></i>{{ __('Filter') }}</button>
            @if(request()->hasAny(['search', 'category', 'level', 'type', 'featured']))<a href="{{ route('admin.trainings.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600">{{ __('Clear') }}</a>@endif
        </form>
    </div>

    <div x-show="selectedIds.length > 0" x-transition class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
        <span class="text-blue-700 font-medium"><span x-text="selectedIds.length"></span> {{ __('selected') }}</span>
        <div class="flex gap-2">
            <button @click="bulkAction('feature')" class="px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm"><i class="fas fa-star mr-1"></i>{{ __('Feature') }}</button>
            <button @click="bulkAction('unfeature')" class="px-4 py-2 bg-gray-500 text-white rounded-lg text-sm"><i class="far fa-star mr-1"></i>{{ __('Unfeature') }}</button>
            <button @click="bulkAction('delete')" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm"><i class="fas fa-trash mr-1"></i>{{ __('Delete') }}</button>
            <button @click="selectedIds = []" class="px-4 py-2 text-gray-600 text-sm">{{ __('Clear') }}</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
        <table class="w-full min-w-[700px]">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left"><input type="checkbox" @change="toggleAllSelection($event)" :checked="selectedIds.length === {{ $trainings->count() }}" class="rounded"></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Training') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Instructor') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Category') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Level') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Type') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Dates') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Featured') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($trainings as $training)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4"><input type="checkbox" value="{{ $training->id }}" x-model.number="selectedIds" class="rounded"></td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                @if($training->image)
                                    <img src="{{ asset('storage/' . $training->image) }}" alt="" class="w-12 h-12 rounded-lg object-cover">
                                @else
                                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-green-500 rounded-lg flex items-center justify-center"><i class="fas fa-graduation-cap text-white"></i></div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-800">{{ Str::limit($training->title, 40) }}</p>
                                    <p class="text-xs text-gray-400">ID: {{ $training->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-gray-800">{{ $training->instructor_name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ Str::limit($training->instructor_title, 25) }}</p>
                        </td>
                        <td class="px-4 py-4"><span class="px-2 py-1 text-xs bg-gray-100 rounded">{{ $training->category ?? '-' }}</span></td>
                        <td class="px-4 py-4">
                            @php
                                $levelColors = ['beginner' => 'bg-green-100 text-green-700', 'intermediate' => 'bg-orange-100 text-orange-700', 'advanced' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded {{ $levelColors[$training->level] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($training->level ?? __('N/A')) }}</span>
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $typeColors = ['online' => 'bg-blue-100 text-blue-700', 'in-person' => 'bg-green-100 text-green-700', 'hybrid' => 'bg-purple-100 text-purple-700'];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded {{ $typeColors[$training->location_type] ?? 'bg-gray-100 text-gray-700' }}">{{ $training->location_type_label }}</span>
                        </td>
                        <td class="px-4 py-4">
                            @if($training->start_date)
                                <p class="text-sm text-gray-700">{{ $training->start_date->format('M d') }}</p>
                                <p class="text-xs text-gray-500">{{ $training->duration ?? '-' }}</p>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($training->featured)
                                <span class="text-yellow-500"><i class="fas fa-star"></i></span>
                            @else
                                <span class="text-gray-300"><i class="far fa-star"></i></span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.trainings.edit', ['locale' => app()->getLocale(), 'id' => $training->id]) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="{{ __('Edit') }}"><i class="fas fa-edit"></i></a>
                                <button @click="toggleFeatured({{ $training->id }}, {{ $training->featured ? 'true' : 'false' }})" class="p-2 {{ $training->featured ? 'text-yellow-500' : 'text-gray-400' }} hover:bg-yellow-50 rounded-lg" title="{{ $training->featured ? __('Unfeature') : __('Feature') }}"><i class="{{ $training->featured ? 'fas' : 'far' }} fa-star"></i></button>
                                <button @click="deleteItem({{ $training->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="{{ __('Delete') }}"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-12 text-center text-gray-500">{{ __('No trainings found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($trainings->hasPages())<div class="px-4 py-3 border-t">{{ $trainings->links() }}</div>@endif
    </div>
</div>

@push('scripts')
<script>
function trainingManager() {
    return {
        selectedIds: [],
        itemIds: @json($trainings->pluck('id')),

        toggleAllSelection(e) {
            this.selectedIds = e.target.checked ? [...this.itemIds] : [];
        },

        async toggleFeatured(id, currentlyFeatured) {
            const action = currentlyFeatured ? 'unfeature' : 'feature';
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/trainings/bulk-action`, {
                    method: 'POST',
                    body: JSON.stringify({ ids: [id], action: action })
                });
                showToast(currentlyFeatured ? 'Unfeatured' : 'Featured', 'success');
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async deleteItem(id) {
            if (!confirm('{{ __("Delete this training?") }}')) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/trainings/${id}`, { method: 'DELETE' });
                showToast('{{ __("Deleted") }}', 'success');
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async bulkAction(action) {
            if (action === 'delete' && !confirm('{{ __("Delete selected trainings?") }}')) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/trainings/bulk-action`, {
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
