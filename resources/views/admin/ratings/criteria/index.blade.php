@extends('admin.layouts.app')

@section('title', __('Rating Criteria'))

@section('breadcrumb')
    <a href="{{ route('admin.ratings.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Profile Ratings') }}</a>
    <span class="mx-2 text-gray-400">/</span>
    <span class="text-gray-700">{{ __('Criteria') }}</span>
@endsection

@section('content')
<div x-data="criteriaManager()" class="space-y-6">

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Rating Criteria') }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ __('Manage the checkbox questions shown to users when they rate a designer profile.') }}</p>
        </div>
        <button @click="openAddModal()"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg font-semibold hover:shadow-lg transition-all">
            <i class="fas fa-plus"></i>
            {{ __('Add Criterion') }}
        </button>
    </div>

    <!-- Criteria List -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">{{ __('Drag rows to reorder. Changes are saved automatically.') }}</p>
            <span class="text-sm font-medium text-gray-700">{{ $criteria->count() }} {{ __('criteria') }}</span>
        </div>

        @if($criteria->count() > 0)
        <table class="w-full" id="criteriaTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 w-8"></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('English Label') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Arabic Label') }}</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="criteriaBody">
                @foreach($criteria as $criterion)
                <tr class="hover:bg-gray-50 transition-colors" data-id="{{ $criterion->id }}">
                    <td class="px-4 py-4">
                        <i class="fas fa-grip-vertical text-gray-300 cursor-grab active:cursor-grabbing drag-handle"></i>
                    </td>
                    <td class="px-4 py-4">
                        <span class="font-medium text-gray-800">{{ $criterion->en_label }}</span>
                    </td>
                    <td class="px-4 py-4">
                        <span class="text-gray-700" dir="rtl">{{ $criterion->ar_label }}</span>
                    </td>
                    <td class="px-4 py-4 text-center">
                        <button @click="toggleActive({{ $criterion->id }}, $event)"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 {{ $criterion->is_active ? 'bg-green-500' : 'bg-gray-300' }}"
                                data-active="{{ $criterion->is_active ? 'true' : 'false' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $criterion->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </td>
                    <td class="px-4 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button @click="openEditModal({{ $criterion->id }}, '{{ addslashes($criterion->en_label) }}', '{{ addslashes($criterion->ar_label) }}')"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="{{ __('Edit') }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button @click="confirmDelete({{ $criterion->id }})"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="{{ __('Delete') }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="py-16 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-square text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-1">{{ __('No criteria yet') }}</h3>
            <p class="text-gray-500 text-sm mb-4">{{ __('Add your first rating criterion to get started.') }}</p>
            <button @click="openAddModal()" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{ __('Add Criterion') }}
            </button>
        </div>
        @endif
    </div>

    <!-- Add/Edit Modal -->
    <div x-show="showModal" x-cloak @click.self="showModal = false"
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-lg w-full">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800" x-text="editingId ? '{{ __('Edit Criterion') }}' : '{{ __('Add Criterion') }}'"></h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('English Label') }} <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.en_label" maxlength="255"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="{{ __('e.g. Did you get the product you wanted?') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Arabic Label') }} <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.ar_label" maxlength="255" dir="rtl"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="مثال: هل حصلت على المنتج الذي أردته؟">
                </div>
            </div>
            <div class="p-6 border-t flex justify-end gap-3">
                <button @click="showModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">{{ __('Cancel') }}</button>
                <button @click="save()"
                        :disabled="!form.en_label.trim() || !form.ar_label.trim()"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-text="editingId ? '{{ __('Save Changes') }}' : '{{ __('Add') }}'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div x-show="showDeleteModal" x-cloak @click.self="showDeleteModal = false"
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">{{ __('Delete Criterion') }}</h3>
                    <p class="text-sm text-gray-500 mt-0.5">{{ __('This cannot be undone. Historical response data will be preserved in analytics.') }}</p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button @click="showDeleteModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">{{ __('Cancel') }}</button>
                <button @click="deleteCriterion()" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function criteriaManager() {
    return {
        showModal: false,
        showDeleteModal: false,
        editingId: null,
        deletingId: null,
        form: { en_label: '', ar_label: '' },

        openAddModal() {
            this.editingId = null;
            this.form = { en_label: '', ar_label: '' };
            this.showModal = true;
        },

        openEditModal(id, enLabel, arLabel) {
            this.editingId = id;
            this.form = { en_label: enLabel, ar_label: arLabel };
            this.showModal = true;
        },

        async save() {
            if (!this.form.en_label.trim() || !this.form.ar_label.trim()) return;

            try {
                const url = this.editingId
                    ? `{{ url('') }}/{{ app()->getLocale() }}/admin/ratings/criteria/${this.editingId}`
                    : `{{ url('') }}/{{ app()->getLocale() }}/admin/ratings/criteria`;

                const method = this.editingId ? 'PUT' : 'POST';

                await adminFetch(url, {
                    method,
                    body: JSON.stringify(this.form)
                });

                showToast(this.editingId ? '{{ __("Criterion updated") }}' : '{{ __("Criterion added") }}', 'success');
                this.showModal = false;
                setTimeout(() => location.reload(), 800);
            } catch (e) {
                showToast(e.message || '{{ __("An error occurred") }}', 'error');
            }
        },

        async toggleActive(id, event) {
            const btn = event.currentTarget;
            const wasActive = btn.dataset.active === 'true';
            try {
                const res = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/ratings/criteria/${id}/toggle`, { method: 'POST' });
                const isNowActive = res.data.is_active;

                btn.dataset.active = isNowActive ? 'true' : 'false';
                btn.classList.toggle('bg-green-500', isNowActive);
                btn.classList.toggle('bg-gray-300', !isNowActive);
                const knob = btn.querySelector('span');
                knob.classList.toggle('translate-x-5', isNowActive);
                knob.classList.toggle('translate-x-0', !isNowActive);

                showToast(isNowActive ? '{{ __("Criterion enabled") }}' : '{{ __("Criterion disabled") }}', 'success');
            } catch (e) {
                showToast(e.message || '{{ __("Failed to toggle status") }}', 'error');
            }
        },

        confirmDelete(id) {
            this.deletingId = id;
            this.showDeleteModal = true;
        },

        async deleteCriterion() {
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/ratings/criteria/${this.deletingId}`, { method: 'DELETE' });
                showToast('{{ __("Criterion deleted") }}', 'success');
                this.showDeleteModal = false;
                setTimeout(() => location.reload(), 800);
            } catch (e) {
                showToast(e.message || '{{ __("Failed to delete") }}', 'error');
            }
        },
    }
}

// Drag-to-reorder with SortableJS
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('criteriaBody');
    if (!tbody) return;

    Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: async function () {
            const rows = tbody.querySelectorAll('tr[data-id]');
            const order = Array.from(rows).map(r => parseInt(r.dataset.id));

            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/ratings/criteria/reorder`, {
                    method: 'POST',
                    body: JSON.stringify({ order })
                });
                showToast('{{ __("Order saved") }}', 'success');
            } catch (e) {
                showToast('{{ __("Failed to save order") }}', 'error');
            }
        }
    });
});
</script>
@endpush
