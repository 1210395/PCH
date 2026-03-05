@extends('admin.layouts.app')

@section('title', __('Announcement Details'))

@section('breadcrumb')
    <a href="{{ route('admin.academic-content.announcements', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Academic Announcements') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ Str::limit($announcement->title, 30) }}</span>
@endsection

@section('content')
<div x-data="announcementDetails()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if($announcement->image)
                <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="w-16 h-16 rounded-xl object-cover shadow-lg">
            @else
                <div class="w-16 h-16 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 shadow-lg">
                    <i class="fas fa-bullhorn text-2xl"></i>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $announcement->title }}</h1>
                <p class="text-gray-500">{{ __('Submitted by') }} {{ $announcement->academicAccount->name ?? __('Unknown') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($announcement->approval_status === 'pending')
                <button @click="approve()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-check mr-2"></i>{{ __('Approve') }}
                </button>
                <button @click="showRejectModal = true" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-times mr-2"></i>{{ __('Reject') }}
                </button>
            @endif
            <a href="{{ route('admin.academic-content.announcements', ['locale' => app()->getLocale()]) }}"
               class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Status Badges -->
    <div class="flex items-center gap-3 flex-wrap">
        <span class="px-3 py-1 rounded-full text-sm font-medium
            @if($announcement->approval_status === 'approved') bg-green-100 text-green-700
            @elseif($announcement->approval_status === 'rejected') bg-red-100 text-red-700
            @else bg-orange-100 text-orange-700 @endif">
            {{ ucfirst($announcement->approval_status) }}
        </span>
        @if($announcement->is_expired)
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                {{ __('Expired') }}
            </span>
        @endif
        <span class="px-3 py-1 rounded-full text-sm font-medium
            @if($announcement->category === 'admission') bg-blue-100 text-blue-700
            @elseif($announcement->category === 'scholarship') bg-green-100 text-green-700
            @elseif($announcement->category === 'job') bg-purple-100 text-purple-700
            @elseif($announcement->category === 'event') bg-orange-100 text-orange-700
            @else bg-gray-100 text-gray-700 @endif">
            {{ ucfirst($announcement->category ?? 'general') }}
        </span>
        @if($announcement->priority === 'urgent')
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ __('Urgent') }}
            </span>
        @elseif($announcement->priority === 'important')
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-700">
                <i class="fas fa-star mr-1"></i>{{ __('Important') }}
            </span>
        @endif
    </div>

    @if($announcement->approval_status === 'rejected' && $announcement->rejection_reason)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <h4 class="font-medium text-red-800 mb-1">{{ __('Rejection Reason') }}</h4>
            <p class="text-red-700">{{ $announcement->rejection_reason }}</p>
        </div>
    @endif

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Content -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Content') }}</h3>
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($announcement->content)) !!}
                </div>
            </div>

            <!-- Image -->
            @if($announcement->image)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Announcement Image') }}</h3>
                    <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="rounded-lg max-w-full">
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Announcement Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Announcement Details') }}</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Publish Date') }}</p>
                        <p class="font-medium text-gray-800">{{ $announcement->publish_date->format('F d, Y') }}</p>
                    </div>
                    @if($announcement->expiry_date)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Expiry Date') }}</p>
                            <p class="font-medium text-gray-800">{{ $announcement->expiry_date->format('F d, Y') }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Category') }}</p>
                        <p class="font-medium text-gray-800">{{ ucfirst($announcement->category ?? 'general') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Priority') }}</p>
                        <p class="font-medium text-gray-800">{{ ucfirst($announcement->priority ?? 'normal') }}</p>
                    </div>
                    @if($announcement->external_link)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('External Link') }}</p>
                            <a href="{{ $announcement->external_link }}" target="_blank" class="text-blue-600 hover:underline break-all">{{ $announcement->external_link }}</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Institution Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Institution') }}</h3>
                <div class="flex items-center gap-3 mb-4">
                    @if($announcement->academicAccount->logo)
                        <img src="{{ asset('storage/' . $announcement->academicAccount->logo) }}" class="w-12 h-12 rounded-lg object-cover">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($announcement->academicAccount->name ?? 'U', 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-medium text-gray-800">{{ $announcement->academicAccount->name ?? __('Unknown') }}</p>
                        <p class="text-sm text-gray-500">{{ $announcement->academicAccount->institution_type_label ?? '' }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.academic-accounts.show', ['locale' => app()->getLocale(), 'id' => $announcement->academic_account_id]) }}"
                   class="text-blue-600 hover:underline text-sm">
                    <i class="fas fa-external-link-alt mr-1"></i>{{ __('View Institution') }}
                </a>
            </div>

            <!-- Metadata -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Metadata') }}</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">{{ __('Created') }}</p>
                        <p class="text-gray-800">{{ $announcement->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('Last Updated') }}</p>
                        <p class="text-gray-800">{{ $announcement->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('ID') }}</p>
                        <p class="text-gray-800">#{{ $announcement->id }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showRejectModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Reject Announcement') }}</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Reason for rejection') }} <span class="text-red-500">*</span></label>
                <textarea x-model="rejectReason" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="{{ __('Please provide a reason...') }}"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button @click="showRejectModal = false; rejectReason = ''" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">{{ __('Cancel') }}</button>
                <button @click="reject()" :disabled="!rejectReason" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">{{ __('Reject') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function announcementDetails() {
    return {
        showRejectModal: false,
        rejectReason: '',

        async approve() {
            if (!confirm('{{ __('Approve this announcement?') }}')) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-content/announcements/{{ $announcement->id }}/approve`, { method: 'POST' });
                showToast('{{ __('Announcement approved') }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to approve') }}', 'error');
            }
        },

        async reject() {
            if (!this.rejectReason) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-content/announcements/{{ $announcement->id }}/reject`, {
                    method: 'POST',
                    body: JSON.stringify({ reason: this.rejectReason })
                });
                showToast('{{ __('Announcement rejected') }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to reject') }}', 'error');
            }
        }
    }
}
</script>
@endpush
@endsection
