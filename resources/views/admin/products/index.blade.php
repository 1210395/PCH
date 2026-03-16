@extends('admin.layouts.app')

@section('title', __('Products Management'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Products') }}</span>
@endsection

@section('content')
<div x-data="contentManager()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Products') }}</h1>
            <p class="text-gray-500">{{ __('Manage and approve product listings') }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($pendingCount > 0)
                <span class="px-4 py-2 bg-orange-100 text-orange-700 rounded-full text-sm font-medium">
                    {{ $pendingCount }} {{ __('pending approval') }}
                </span>
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

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[250px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('Search by title, description, or designer...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Status Filter -->
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('All Status') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
            </select>

            <!-- Category Filter -->
            <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('All Categories') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                @endforeach
            </select>

            <!-- Submit -->
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-filter mr-2"></i>{{ __('Filter') }}
            </button>

            @if(request()->hasAny(['search', 'status', 'category']))
                <a href="{{ route('admin.products.index', ['locale' => app()->getLocale()]) }}"
                   class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    {{ __('Clear Filters') }}
                </a>
            @endif
        </form>
    </div>

    <!-- Bulk Actions Bar -->
    <div x-show="selectedIds.length > 0"
         x-transition
         class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
        <span class="text-blue-700 font-medium">
            <span x-text="selectedIds.length"></span> {{ __('item(s) selected') }}
        </span>
        <div class="flex gap-2">
            <button @click="bulkAction('approve')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                <i class="fas fa-check mr-1"></i>{{ __('Approve All') }}
            </button>
            <button @click="showRejectModal = true; rejectingIds = [...selectedIds]"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                <i class="fas fa-times mr-1"></i>{{ __('Reject All') }}
            </button>
            <button @click="bulkAction('delete')"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                <i class="fas fa-trash mr-1"></i>{{ __('Delete All') }}
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
                                   :checked="selectedIds.length === {{ $products->count() }} && {{ $products->count() }} > 0"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Product') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Designer') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Category') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Created') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4">
                                <input type="checkbox"
                                       value="{{ $product->id }}"
                                       x-model.number="selectedIds"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                        @if($product->images->first())
                                            <img src="{{ url('media/' . $product->images->first()->image_path) }}"
                                                 class="w-full h-full object-cover" alt="">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ Str::limit($product->title, 40) }}</p>
                                        <p class="text-xs text-gray-400">ID: {{ $product->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-gray-800">{{ $product->designer->name ?? __('Unknown') }}</p>
                                <p class="text-xs text-gray-500">{{ $product->designer->email ?? '' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded">
                                    {{ $product->category ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $product->approval_status === 'approved' ? 'bg-green-100 text-green-800' :
                                       ($product->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($product->approval_status ?? 'pending') }}
                                </span>
                                @if($product->rejection_reason)
                                    <p class="text-xs text-red-500 mt-1 truncate max-w-[150px]" title="{{ $product->rejection_reason }}">
                                        {{ $product->rejection_reason }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500">
                                {{ $product->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Edit -->
                                    <a href="{{ route('admin.products.edit', ['locale' => app()->getLocale(), 'id' => $product->id]) }}"
                                       class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="{{ __('Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if($product->approval_status !== 'approved')
                                        <button @click="approve({{ $product->id }})"
                                                class="p-2 text-green-600 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors"
                                                title="{{ __('Approve') }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif

                                    @if($product->approval_status !== 'rejected')
                                        <button @click="openRejectModal({{ $product->id }})"
                                                class="p-2 text-yellow-600 hover:text-yellow-700 hover:bg-yellow-50 rounded-lg transition-colors"
                                                title="{{ __('Reject') }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif

                                    <button @click="deleteItem({{ $product->id }})"
                                            class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                            title="{{ __('Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-shopping-bag text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-500">{{ __('No products found') }}</p>
                                    @if(request()->hasAny(['search', 'status', 'category']))
                                        <a href="{{ route('admin.products.index', ['locale' => app()->getLocale()]) }}"
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
        @if($products->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal"
         x-cloak
         @click.self="showRejectModal = false"
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Reject Product') }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ __('Provide a reason for rejection (optional)') }}</p>
            </div>
            <div class="p-6">
                <textarea x-model="rejectReason"
                          placeholder="{{ __('Enter rejection reason...') }}"
                          rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                <button @click="showRejectModal = false; rejectReason = ''"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    {{ __('Cancel') }}
                </button>
                <button @click="submitReject()"
                        class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                    {{ __('Reject') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function contentManager() {
    return {
        selectedIds: [],
        itemIds: @json($products->pluck('id')),
        showRejectModal: false,
        rejectingIds: [],
        rejectReason: '',
        autoAcceptEnabled: {{ \App\Models\AdminSetting::isAutoAcceptEnabled('products') ? 'true' : 'false' }},

        async toggleAutoAccept() {
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/settings/auto-accept/products/toggle`, {
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
                this.selectedIds = [...this.itemIds];
            } else {
                this.selectedIds = [];
            }
        },

        async approve(id) {
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/products/${id}/approve`, {
                    method: 'POST'
                });
                showToast(response.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                showToast(error.message, 'error');
            }
        },

        openRejectModal(id) {
            this.rejectingIds = [id];
            this.rejectReason = '';
            this.showRejectModal = true;
        },

        async submitReject() {
            try {
                if (this.rejectingIds.length === 1) {
                    await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/products/${this.rejectingIds[0]}/reject`, {
                        method: 'POST',
                        body: JSON.stringify({ reason: this.rejectReason })
                    });
                } else {
                    await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/products/bulk-action`, {
                        method: 'POST',
                        body: JSON.stringify({
                            ids: this.rejectingIds,
                            action: 'reject',
                            reason: this.rejectReason
                        })
                    });
                }
                showToast('{{ __("Rejected successfully") }}', 'success');
                this.showRejectModal = false;
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                showToast(error.message, 'error');
            }
        },

        async deleteItem(id) {
            if (!confirm('{{ __("Are you sure you want to delete this product? This action cannot be undone.") }}')) return;

            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/products/${id}`, {
                    method: 'DELETE'
                });
                showToast(response.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                showToast(error.message, 'error');
            }
        },

        async bulkAction(action) {
            if (action === 'delete') {
                if (!confirm(`Are you sure you want to delete ${this.selectedIds.length} product(s)?`)) return;
            }

            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/products/bulk-action`, {
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
