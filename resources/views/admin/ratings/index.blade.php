@extends('admin.layouts.app')

@section('title', __('Profile Ratings Management'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Profile Ratings') }}</span>
@endsection

@section('content')
<div x-data="ratingsManager()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Profile Ratings') }}</h1>
            <p class="text-gray-500">{{ __('Manage and moderate profile ratings') }}</p>
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
                <span x-text="autoAcceptEnabled ? '{{ __("ON") }}' : '{{ __("OFF") }}'"
                      :class="autoAcceptEnabled ? 'text-green-600 font-medium' : 'text-gray-500'"
                      class="text-sm min-w-[28px]"></span>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[250px]">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('Search by comment, designer, or rater...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">{{ __('All Status') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
            </select>
            <select name="rating" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">{{ __('All Ratings') }}</option>
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} {{ $i > 1 ? __('Stars') : __('Star') }}</option>
                @endfor
            </select>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-filter mr-2"></i>{{ __('Filter') }}
            </button>
            @if(request()->hasAny(['search', 'status', 'rating']))
                <a href="{{ route('admin.ratings.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <!-- Bulk Actions -->
    <div x-show="selectedIds.length > 0" x-transition class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
        <span class="text-blue-700 font-medium"><span x-text="selectedIds.length"></span> {{ __('rating(s) selected') }}</span>
        <div class="flex gap-2">
            <button @click="bulkAction('approve')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"><i class="fas fa-check mr-1"></i>{{ __('Approve') }}</button>
            <button @click="showRejectModal = true; rejectingIds = [...selectedIds]" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm"><i class="fas fa-trash mr-1"></i>{{ __('Reject/Delete') }}</button>
            <button @click="selectedIds = []" class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm">{{ __('Clear') }}</button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left"><input type="checkbox" @change="toggleAllSelection($event)" :checked="selectedIds.length === {{ $ratings->count() }} && {{ $ratings->count() }} > 0" class="rounded"></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Profile Rated') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Rated By') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Rating') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Comment') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Date') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($ratings as $rating)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4"><input type="checkbox" value="{{ $rating->id }}" x-model.number="selectedIds" class="rounded"></td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 overflow-hidden flex-shrink-0">
                                        @if($rating->designer->avatar)
                                            <img src="{{ asset('storage/' . $rating->designer->avatar) }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white text-xs font-bold">
                                                {{ strtoupper(substr($rating->designer->name ?? 'U', 0, 2)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $rating->designer->name ?? __('Unknown') }}</p>
                                        <p class="text-xs text-gray-500">{{ $rating->designer->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 overflow-hidden flex-shrink-0">
                                        @if($rating->rater->avatar)
                                            <img src="{{ asset('storage/' . $rating->rater->avatar) }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white text-xs font-bold">
                                                {{ strtoupper(substr($rating->rater->name ?? 'U', 0, 2)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $rating->rater->name ?? __('Unknown') }}</p>
                                        <p class="text-xs text-gray-500">{{ $rating->rater->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    <span class="ml-1 text-sm text-gray-600">({{ $rating->rating }})</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-gray-700 text-sm max-w-xs truncate" title="{{ $rating->comment }}">{{ Str::limit($rating->comment, 60) }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $rating->status === 'approved' ? 'bg-green-100 text-green-800' :
                                       ($rating->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ __(ucfirst($rating->status)) }}
                                </span>
                                @if($rating->status === 'rejected' && $rating->rejection_reason)
                                    <p class="text-xs text-red-500 mt-1" title="{{ $rating->rejection_reason }}">{{ __('Reason') }}: {{ Str::limit($rating->rejection_reason, 30) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500">{{ $rating->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="viewRating({{ $rating->id }}, @js($rating))" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="{{ __('View Details') }}"><i class="fas fa-eye"></i></button>
                                    @if($rating->status !== 'approved')
                                        <button @click="approve({{ $rating->id }})" class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="{{ __('Approve') }}"><i class="fas fa-check"></i></button>
                                    @endif
                                    @if($rating->status !== 'rejected')
                                        <button @click="openRejectModal({{ $rating->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="{{ __('Delete/Reject') }}"><i class="fas fa-trash"></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <p class="text-gray-500">{{ __('No ratings found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ratings->hasPages())<div class="px-4 py-3 border-t">{{ $ratings->links() }}</div>@endif
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak @click.self="showRejectModal = false" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Delete Rating') }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ __('The user who submitted this rating will be notified with the reason.') }}</p>
            </div>
            <div class="p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Reason for deletion') }} <span class="text-red-500">*</span></label>
                <textarea x-model="rejectReason" placeholder="{{ __('Please provide a reason for deleting this rating...') }}" rows="3" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                <p class="text-xs text-gray-400 mt-2">{{ __('Minimum 10 characters required') }}</p>
            </div>
            <div class="p-6 border-t flex justify-end gap-3">
                <button @click="showRejectModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">{{ __('Cancel') }}</button>
                <button @click="submitReject()" :disabled="rejectReason.length < 10" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">{{ __('Delete Rating') }}</button>
            </div>
        </div>
    </div>

    <!-- View Rating Modal -->
    <div x-show="showViewModal" x-cloak @click.self="showViewModal = false" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-lg w-full">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Rating Details') }}</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white font-bold" x-text="viewingRating?.rater?.name?.substring(0, 2).toUpperCase() || 'UN'"></div>
                    <div>
                        <p class="font-semibold text-gray-800" x-text="viewingRating?.rater?.name || '{{ __('Unknown') }}'"></p>
                        <p class="text-sm text-gray-500">{{ __('rated') }}</p>
                        <p class="font-semibold text-gray-800" x-text="viewingRating?.designer?.name || '{{ __('Unknown') }}'"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <template x-for="i in 5">
                        <svg :class="i <= viewingRating?.rating ? 'text-yellow-400' : 'text-gray-300'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </template>
                    <span class="text-gray-600 font-medium" x-text="viewingRating?.rating + ' {{ __('out of') }} 5'"></span>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700" x-text="viewingRating?.comment"></p>
                </div>

                <div class="flex items-center justify-between text-sm text-gray-500">
                    <span x-text="'{{ __('Status:') }} ' + (viewingRating?.status || 'pending')"></span>
                    <span x-text="viewingRating?.created_at ? new Date(viewingRating.created_at).toLocaleDateString() : ''"></span>
                </div>

                <template x-if="viewingRating?.rejection_reason">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-red-700">{{ __('Rejection Reason:') }}</p>
                        <p class="text-red-600 text-sm mt-1" x-text="viewingRating?.rejection_reason"></p>
                    </div>
                </template>
            </div>
            <div class="p-6 border-t flex justify-end">
                <button @click="showViewModal = false" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function ratingsManager() {
    return {
        selectedIds: [],
        itemIds: @json($ratings->pluck('id')),
        showRejectModal: false,
        showViewModal: false,
        rejectingIds: [],
        rejectReason: '',
        viewingRating: null,
        autoAcceptEnabled: {{ $autoAcceptEnabled ? 'true' : 'false' }},

        async toggleAutoAccept() {
            try {
                const response = await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/ratings/toggle-auto-accept`, { method: 'POST' });
                this.autoAcceptEnabled = response.data.auto_accept_enabled;
                showToast(response.message, 'success');
            } catch (error) {
                showToast(error.message || '{{ __("Failed to toggle auto-accept") }}', 'error');
            }
        },

        toggleAllSelection(e) {
            this.selectedIds = e.target.checked ? [...this.itemIds] : [];
        },

        viewRating(id, rating) {
            this.viewingRating = rating;
            this.showViewModal = true;
        },

        async approve(id) {
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/ratings/${id}/approve`, { method: 'POST' });
                showToast('{{ __("Rating approved") }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        openRejectModal(id) {
            this.rejectingIds = [id];
            this.rejectReason = '';
            this.showRejectModal = true;
        },

        async submitReject() {
            if (this.rejectReason.length < 10) {
                showToast('{{ __("Please provide a reason (minimum 10 characters)") }}', 'error');
                return;
            }

            try {
                if (this.rejectingIds.length === 1) {
                    await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/ratings/${this.rejectingIds[0]}/reject`, {
                        method: 'POST',
                        body: JSON.stringify({ reason: this.rejectReason })
                    });
                } else {
                    await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/ratings/bulk-action`, {
                        method: 'POST',
                        body: JSON.stringify({ ids: this.rejectingIds, action: 'reject', reason: this.rejectReason })
                    });
                }
                showToast('{{ __("Rating deleted and user notified") }}', 'success');
                this.showRejectModal = false;
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async bulkAction(action) {
            if (action === 'reject') {
                this.rejectingIds = [...this.selectedIds];
                this.rejectReason = '';
                this.showRejectModal = true;
                return;
            }

            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/ratings/bulk-action`, {
                    method: 'POST',
                    body: JSON.stringify({ ids: this.selectedIds, action })
                });
                showToast('{{ __("Action completed") }}', 'success');
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
