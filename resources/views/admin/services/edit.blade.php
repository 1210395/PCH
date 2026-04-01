@extends('admin.layouts.app')

@section('title', __('Edit Service'))

@section('breadcrumb')
    <a href="{{ route('admin.services.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Services') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">Edit: {{ Str::limit($service->name, 30) }}</span>
@endsection

@section('content')
<div x-data="serviceForm()" class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Edit Service') }}</h1>
            <p class="text-gray-500">{{ __('Update service details') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($service->designer)
                <a href="{{ url(app()->getLocale() . '/designer/' . $service->designer->id) }}" target="_blank" class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                    <i class="fas fa-external-link-alt mr-2"></i>{{ __('View Designer Profile') }}
                </a>
            @endif
            <a href="{{ route('admin.services.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Current Status -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ __('Current Status:') }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $service->approval_status === 'approved' ? 'bg-green-100 text-green-800' :
                       ($service->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ __(ucfirst($service->approval_status ?? 'pending')) }}
                </span>
                @if($service->rejection_reason)
                    <span class="text-sm text-red-600">{{ __('Reason:') }} {{ $service->rejection_reason }}</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($service->approval_status !== 'approved')
                    <button @click="quickApprove()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                        <i class="fas fa-check mr-1"></i>{{ __('Approve') }}
                    </button>
                @endif
                @if($service->approval_status !== 'rejected')
                    <button @click="showRejectModal = true" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm">
                        <i class="fas fa-times mr-1"></i>{{ __('Reject') }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    <form @submit.prevent="submitForm()" class="space-y-6">
        <!-- Service Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Service Information') }}</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Name') }} <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.name"
                           class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }}</label>
                    <textarea x-model="form.description" rows="3"
                              class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"></textarea>
                </div>

                <!-- Category with Searchable Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Category') }}</label>
                    <div x-data="searchableCategory()" class="relative">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @click="isOpen = true"
                            @focus="isOpen = true"
                            @input="isOpen = true"
                            @blur="updateCategory()"
                            @keydown.escape="isOpen = false"
                            @keydown.arrow-down.prevent="highlightNext()"
                            @keydown.arrow-up.prevent="highlightPrevious()"
                            @keydown.enter.prevent="selectHighlighted()"
                            :placeholder="selectedValue || 'Select service category'"
                            class="w-full px-3 sm:px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div x-show="isOpen && filteredOptions.length > 0"
                             @click.away="isOpen = false"
                             x-transition
                             style="z-index: 9999;"
                             class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                            <template x-for="(option, index) in filteredOptions" :key="option">
                                <div @mousedown.prevent="selectOption(option)"
                                     :class="{'bg-blue-50': index === highlightedIndex}"
                                     class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors"
                                     x-text="option">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Designer Info (Read Only) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Designer Information') }}</h3>
            <div class="flex items-center gap-4">
                @if($service->designer && $service->designer->avatar)
                    <img src="{{ url('media/' . $service->designer->avatar) }}" class="w-12 h-12 rounded-full object-cover">
                @else
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold">
                        {{ substr($service->designer->name ?? 'D', 0, 1) }}
                    </div>
                @endif
                <div>
                    <p class="font-medium text-gray-800">{{ $service->designer->name ?? __('Unknown') }}</p>
                    <p class="text-sm text-gray-500">{{ $service->designer->email ?? '' }}</p>
                </div>
                @if($service->designer)
                    <a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $service->designer->id]) }}" class="ml-auto px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg text-sm">
                        <i class="fas fa-user mr-1"></i>{{ __('View Account') }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.services.index', ['locale' => app()->getLocale()]) }}" class="px-6 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">{{ __('Cancel') }}</a>
            <button type="submit" :disabled="submitting" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all disabled:opacity-50">
                <span x-show="!submitting">{{ __('Save Changes') }}</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Saving...') }}</span>
            </button>
        </div>
    </form>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak @click.self="showRejectModal = false" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('Reject Service') }}</h3>
            <textarea x-model="rejectReason" placeholder="{{ __('Reason for rejection (optional)...') }}" rows="3" class="w-full px-4 py-3 border rounded-lg mb-4"></textarea>
            <div class="flex justify-end gap-3">
                <button @click="showRejectModal = false" class="px-4 py-2 text-gray-600">{{ __('Cancel') }}</button>
                <button @click="submitReject()" class="px-6 py-2 bg-yellow-600 text-white rounded-lg">{{ __('Reject') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Service categories from database
const serviceCategories = @json(\App\Helpers\DropdownHelper::serviceCategories());

// Searchable Category Dropdown
function searchableCategory() {
    const initialValue = @json($service->category ?? '');

    return {
        searchQuery: initialValue,
        selectedValue: initialValue,
        isOpen: false,
        highlightedIndex: -1,
        options: serviceCategories,

        get filteredOptions() {
            if (!this.searchQuery) return this.options;
            return this.options.filter(opt =>
                opt.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },

        selectOption(option) {
            this.searchQuery = option;
            this.selectedValue = option;
            this.isOpen = false;
            // Update parent form
            const form = Alpine.$data(this.$root.closest('[x-data*="serviceForm"]'));
            if (form) form.form.category = option;
        },

        updateCategory() {
            setTimeout(() => {
                this.isOpen = false;
                if (this.searchQuery && !this.options.includes(this.searchQuery)) {
                    // Allow custom category
                    this.selectedValue = this.searchQuery;
                }
                const form = Alpine.$data(this.$root.closest('[x-data*="serviceForm"]'));
                if (form) form.form.category = this.searchQuery || this.selectedValue;
            }, 150);
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.filteredOptions[this.highlightedIndex]) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    }
}

function serviceForm() {
    const service = @json($service);
    return {
        form: {
            name: service.name || '',
            description: service.description || '',
            category: service.category || ''
        },
        submitting: false,
        showRejectModal: false,
        rejectReason: '',

        async submitForm() {
            this.submitting = true;
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/services/{{ $service->id }}`, {
                    method: 'PUT',
                    body: JSON.stringify(this.form)
                });
                showToast('{{ __("Service updated successfully") }}', 'success');
                setTimeout(() => window.location.href = `{{ url('') }}/{{ app()->getLocale() }}/admin/services`, 1000);
            } catch (e) {
                showToast(e.message, 'error');
            } finally {
                this.submitting = false;
            }
        },

        async quickApprove() {
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/services/{{ $service->id }}/approve`, { method: 'POST' });
                showToast('{{ __("Service approved") }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async submitReject() {
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/services/{{ $service->id }}/reject`, {
                    method: 'POST',
                    body: JSON.stringify({ reason: this.rejectReason })
                });
                showToast('{{ __("Service rejected") }}', 'success');
                this.showRejectModal = false;
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message, 'error');
            }
        }
    }
}
</script>
@endpush
@endsection
