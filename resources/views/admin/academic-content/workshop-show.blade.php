@extends('admin.layouts.app')

@section('title', __('Workshop Details'))

@section('breadcrumb')
    <a href="{{ route('admin.academic-content.workshops', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Academic Workshops') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ Str::limit($workshop->title, 30) }}</span>
@endsection

@section('content')
<div x-data="workshopDetails()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if($workshop->image)
                <img src="{{ url('media/' . $workshop->image) }}" alt="{{ $workshop->title }}" class="w-16 h-16 rounded-xl object-cover shadow-lg">
            @else
                <div class="w-16 h-16 rounded-xl bg-green-100 flex items-center justify-center text-green-600 shadow-lg">
                    <i class="fas fa-tools text-2xl"></i>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $workshop->title }}</h1>
                <p class="text-gray-500">{{ __('Submitted by') }} {{ $workshop->academicAccount->name ?? __('Unknown') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($workshop->approval_status === 'pending')
                <button @click="approve()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-check mr-2"></i>{{ __('Approve') }}
                </button>
                <button @click="showRejectModal = true" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-times mr-2"></i>{{ __('Reject') }}
                </button>
            @endif
            <a href="{{ route('admin.academic-content.workshops', ['locale' => app()->getLocale()]) }}"
               class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Status Badges -->
    <div class="flex items-center gap-3">
        <span class="px-3 py-1 rounded-full text-sm font-medium
            @if($workshop->approval_status === 'approved') bg-green-100 text-green-700
            @elseif($workshop->approval_status === 'rejected') bg-red-100 text-red-700
            @else bg-orange-100 text-orange-700 @endif">
            {{ ucfirst($workshop->approval_status) }}
        </span>
        @if($workshop->is_expired)
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                {{ __('Expired') }}
            </span>
        @endif
    </div>

    @if($workshop->approval_status === 'rejected' && $workshop->rejection_reason)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <h4 class="font-medium text-red-800 mb-1">{{ __('Rejection Reason') }}</h4>
            <p class="text-red-700">{{ $workshop->rejection_reason }}</p>
        </div>
    @endif

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Description') }}</h3>
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($workshop->description)) !!}
                </div>
            </div>

            <!-- What You'll Learn -->
            @if($workshop->objectives)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Objectives') }}</h3>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($workshop->objectives)) !!}
                    </div>
                </div>
            @endif

            <!-- Requirements -->
            @if($workshop->requirements)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Requirements') }}</h3>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($workshop->requirements)) !!}
                    </div>
                </div>
            @endif

            <!-- Image -->
            @if($workshop->image)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Workshop Image') }}</h3>
                    <img src="{{ url('media/' . $workshop->image) }}" alt="{{ $workshop->title }}" class="rounded-lg max-w-full">
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Workshop Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Workshop Details') }}</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Date') }}</p>
                        <p class="font-medium text-gray-800">{{ $workshop->workshop_date ? $workshop->workshop_date->format('F d, Y') : __('Not set') }}</p>
                    </div>
                    @if($workshop->start_time)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Time') }}</p>
                            <p class="font-medium text-gray-800">{{ $workshop->start_time }} - {{ $workshop->end_time ?? __('TBD') }}</p>
                        </div>
                    @endif
                    @if($workshop->duration)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Duration') }}</p>
                            <p class="font-medium text-gray-800">{{ $workshop->duration }}</p>
                        </div>
                    @endif
                    @if($workshop->location)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Location') }}</p>
                            <p class="font-medium text-gray-800">{{ $workshop->location }}</p>
                        </div>
                    @endif
                    @if($workshop->is_online !== null)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Mode') }}</p>
                            <p class="font-medium text-gray-800">{{ $workshop->is_online ? __('Online') : __('In-Person') }}</p>
                        </div>
                    @endif
                    @if($workshop->instructor)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Instructor') }}</p>
                            <p class="font-medium text-gray-800">{{ $workshop->instructor }}</p>
                        </div>
                    @endif
                    @if($workshop->max_participants)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Maximum Participants') }}</p>
                            <p class="font-medium text-gray-800">{{ $workshop->max_participants }}</p>
                        </div>
                    @endif
                    @if($workshop->price !== null)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Price') }}</p>
                            <p class="font-medium text-gray-800">{{ $workshop->is_free ? __('Free') : '$' . number_format($workshop->price, 2) }}</p>
                        </div>
                    @endif
                    @if($workshop->registration_link)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Registration Link') }}</p>
                            <a href="{{ $workshop->registration_link }}" target="_blank" class="text-blue-600 hover:underline break-all">{{ $workshop->registration_link }}</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Institution Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Institution') }}</h3>
                <div class="flex items-center gap-3 mb-4">
                    @if($workshop->academicAccount->logo)
                        <img src="{{ url('media/' . $workshop->academicAccount->logo) }}" class="w-12 h-12 rounded-lg object-cover">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($workshop->academicAccount->name ?? 'U', 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-medium text-gray-800">{{ $workshop->academicAccount->name ?? __('Unknown') }}</p>
                        <p class="text-sm text-gray-500">{{ $workshop->academicAccount->institution_type_label ?? '' }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.academic-accounts.show', ['locale' => app()->getLocale(), 'id' => $workshop->academic_account_id]) }}"
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
                        <p class="text-gray-800">{{ $workshop->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('Last Updated') }}</p>
                        <p class="text-gray-800">{{ $workshop->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('ID') }}</p>
                        <p class="text-gray-800">#{{ $workshop->id }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showRejectModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Reject Workshop') }}</h3>
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
function workshopDetails() {
    return {
        showRejectModal: false,
        rejectReason: '',

        async approve() {
            if (!confirm('{{ __('Approve this workshop?') }}')) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-content/workshops/{{ $workshop->id }}/approve`, { method: 'POST' });
                showToast('{{ __('Workshop approved') }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to approve') }}', 'error');
            }
        },

        async reject() {
            if (!this.rejectReason) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-content/workshops/{{ $workshop->id }}/reject`, {
                    method: 'POST',
                    body: JSON.stringify({ reason: this.rejectReason })
                });
                showToast('{{ __('Workshop rejected') }}', 'success');
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
