@extends('admin.layouts.app')

@section('title', $parent->label . ' - ' . __('Sub-Options'))

@section('breadcrumb')
    <a href="{{ route('admin.settings.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Settings') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <a href="{{ route('admin.dropdowns.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Dropdowns') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <a href="{{ route('admin.dropdowns.show', ['locale' => app()->getLocale(), 'type' => $type]) }}" class="text-blue-600 hover:underline">{{ $typeConfig['label'] }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ $parent->label }}</span>
@endsection

@section('content')
<div x-data="childrenManager()" class="max-w-4xl">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-sitemap text-purple-600"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $parent->label }}</h1>
                    <p class="text-gray-500">{{ $typeConfig['child_label'] ?? __('Sub-Options') }} {{ __('for this') }} {{ strtolower($typeConfig['label']) }}</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dropdowns.show', ['locale' => app()->getLocale(), 'type' => $type]) }}"
               class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Back to') }} {{ $typeConfig['label'] }}
            </a>
            <button @click="sortAlphabetically()"
                    :disabled="sorting"
                    class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50">
                <i class="fas fa-sort-alpha-down" :class="sorting && 'fa-spin'"></i>
                <span x-text="sorting ? '{{ __('Sorting...') }}' : '{{ __('Sort A-Z') }}'"></span>
            </button>
            <button @click="openAddModal()"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>{{ __('Add Sub-Option') }}</span>
            </button>
        </div>
    </div>

    <!-- Parent Info Card -->
    <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl border border-purple-200 p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-purple-600 font-medium">{{ __('Parent Option') }}</p>
                <p class="text-lg font-semibold text-gray-800">{{ $parent->label }}</p>
                <p class="text-sm text-gray-500">{{ __('Value') }}: <code class="bg-white px-1.5 py-0.5 rounded text-xs">{{ $parent->value }}</code></p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-purple-600">{{ $options->count() }}</p>
                <p class="text-sm text-gray-500">{{ __('Sub-Options') }}</p>
            </div>
        </div>
    </div>

    <!-- Sub-Options List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6">
            @if($options->count() > 0)
            <div class="space-y-3" x-ref="sortableList" data-parent="{{ $parent->id }}">
                @foreach($options as $option)
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors cursor-move"
                     data-id="{{ $option->id }}">
                    <!-- Drag Handle -->
                    <div class="text-gray-400 hover:text-gray-600 cursor-grab active:cursor-grabbing">
                        <i class="fas fa-grip-vertical"></i>
                    </div>

                    <!-- Option Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-medium text-gray-800">{{ $option->label }}</span>
                            @if($option->label_ar)
                            <span class="text-sm text-gray-500 font-arabic" dir="rtl">({{ $option->label_ar }})</span>
                            @endif
                            @if($option->is_system)
                            <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded">{{ __('System') }}</span>
                            @endif
                            @if(!$option->is_active)
                            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded">{{ __('Inactive') }}</span>
                            @endif
                        </div>
                        <span class="text-sm text-gray-500">{{ __('Value') }}: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">{{ $option->value }}</code></span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <button @click="toggleActive({{ $option->id }}, {{ $option->is_active ? 'true' : 'false' }})"
                                class="px-3 py-1.5 rounded-lg text-sm transition-colors {{ $option->is_active ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' }}"
                                title="{{ $option->is_active ? __('Deactivate') : __('Activate') }}">
                            <i class="fas {{ $option->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                        </button>

                        <button @click="openEditModal(@js($option))"
                                class="px-3 py-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button @click="deleteOption({{ $option->id }}, '{{ addslashes($option->label) }}')"
                                class="px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="{{ __('Delete option') }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-sitemap text-purple-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-700 mb-2">{{ __('No sub-options yet') }}</h3>
                <p class="text-gray-500 mb-4">{{ __('Add sub-options for') }} "{{ $parent->label }}"</p>
                <button @click="openAddModal()"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>{{ __('Add Sub-Option') }}
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div x-show="showModal" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showModal = false"
         @keydown.escape.window="showModal = false"
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800" x-text="editingId ? '{{ __('Edit Sub-Option') }}' : '{{ __('Add New Sub-Option') }}'"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="bg-purple-50 rounded-lg p-3 mb-4">
                <p class="text-sm text-purple-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ __('Adding to') }}: <strong>{{ $parent->label }}</strong>
                </p>
            </div>

            <form @submit.prevent="saveOption()">
                <div class="space-y-4">
                    <!-- Label (English) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Label (English)') }} <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.label" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                               placeholder="{{ __('e.g., Graphic Designer') }}">
                    </div>

                    <!-- Value -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Value') }} <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.value" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all font-mono text-sm"
                               placeholder="{{ __('e.g., graphic_designer') }}"
                               @input="form.value = form.value.toLowerCase().replace(/[^a-z0-9_]/g, '_')">
                        <p class="text-xs text-gray-500 mt-1">{{ __('Internal identifier (lowercase, underscores only)') }}</p>
                    </div>

                    <!-- Label (Arabic) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Label (Arabic)') }}</label>
                        <input type="text" x-model="form.label_ar" dir="rtl"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all font-arabic"
                               placeholder="{{ __('e.g., مصمم جرافيك') }}">
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center gap-3">
                        <input type="checkbox" x-model="form.is_active" id="is_active"
                               class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-gray-300">
                        <label for="is_active" class="text-sm text-gray-700">{{ __('Active (visible in dropdowns)') }}</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" @click="showModal = false"
                            class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" :disabled="saving"
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50">
                        <span x-show="!saving" x-text="editingId ? '{{ __('Update') }}' : '{{ __('Create') }}'"></span>
                        <span x-show="saving"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Saving...') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function childrenManager() {
    return {
        showModal: false,
        editingId: null,
        saving: false,
        sorting: false,
        form: {
            value: '',
            label: '',
            label_ar: '',
            is_active: true,
            parent_id: {{ $parent->id }}
        },

        init() {
            const list = this.$refs.sortableList;
            if (list) {
                new Sortable(list, {
                    animation: 150,
                    handle: '.fa-grip-vertical',
                    ghostClass: 'bg-purple-50',
                    onEnd: (evt) => this.handleReorder(evt)
                });
            }
        },

        openAddModal() {
            this.editingId = null;
            this.form = {
                value: '',
                label: '',
                label_ar: '',
                is_active: true,
                parent_id: {{ $parent->id }}
            };
            this.showModal = true;
        },

        openEditModal(option) {
            this.editingId = option.id;
            this.form = {
                value: option.value,
                label: option.label,
                label_ar: option.label_ar || '',
                is_active: option.is_active,
                parent_id: {{ $parent->id }}
            };
            this.showModal = true;
        },

        async saveOption() {
            if (!this.form.value && this.form.label) {
                this.form.value = this.form.label.toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_');
            }

            if (!this.form.value || !this.form.label) {
                showToast('{{ __('Please fill in required fields') }}', 'error');
                return;
            }

            this.saving = true;
            try {
                // Use sector type for storing subsectors (controller handles the actual type)
                const type = '{{ $type }}';
                const url = this.editingId
                    ? `{{ url(app()->getLocale() . '/admin/dropdowns') }}/${type}/${this.editingId}`
                    : `{{ url(app()->getLocale() . '/admin/dropdowns') }}/${type}`;

                const response = await adminFetch(url, {
                    method: this.editingId ? 'PUT' : 'POST',
                    body: JSON.stringify(this.form)
                });

                showToast(response.message, 'success');
                this.showModal = false;
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                showToast(e.message || '{{ __('An error occurred') }}', 'error');
            } finally {
                this.saving = false;
            }
        },

        async deleteOption(id, label) {
            if (!confirm(`{{ __('Are you sure you want to delete') }} "${label}"?`)) return;

            try {
                const type = '{{ $childType }}';
                await adminFetch(`{{ url(app()->getLocale() . '/admin/dropdowns') }}/${type}/${id}`, {
                    method: 'DELETE'
                });
                showToast('{{ __('Sub-option deleted') }}', 'success');
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                showToast(e.message || '{{ __('An error occurred') }}', 'error');
            }
        },

        async toggleActive(id, currentStatus) {
            try {
                const type = '{{ $childType }}';
                const response = await adminFetch(`{{ url(app()->getLocale() . '/admin/dropdowns') }}/${type}/${id}/toggle-active`, {
                    method: 'POST'
                });
                showToast(response.message, 'success');
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                showToast(e.message || '{{ __('An error occurred') }}', 'error');
            }
        },

        async handleReorder(evt) {
            const items = [...evt.from.children].map(el => parseInt(el.dataset.id));
            const type = '{{ $childType }}';

            try {
                await adminFetch(`{{ url(app()->getLocale() . '/admin/dropdowns') }}/${type}/reorder`, {
                    method: 'POST',
                    body: JSON.stringify({ order: items })
                });
                showToast('{{ __('Order updated') }}', 'success');
            } catch (e) {
                showToast(e.message || '{{ __('Failed to update order') }}', 'error');
                location.reload();
            }
        },

        async sortAlphabetically() {
            if (!confirm('{{ __('Sort all sub-options alphabetically (A-Z)? This will reset any custom ordering.') }}')) {
                return;
            }

            this.sorting = true;
            const type = '{{ $childType }}';
            const parentId = {{ $parent->id }};

            try {
                await adminFetch(`{{ url(app()->getLocale() . '/admin/dropdowns') }}/${type}/sort-alphabetically?parent_id=${parentId}`, {
                    method: 'POST'
                });
                showToast('{{ __('Sub-options sorted alphabetically') }}', 'success');
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to sort sub-options') }}', 'error');
            } finally {
                this.sorting = false;
            }
        }
    };
}
</script>
@endpush
@endsection
