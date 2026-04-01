@extends('admin.layouts.app')

@section('title', __('Training Details'))

@section('breadcrumb')
    <a href="{{ route('admin.academic-content.trainings', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Academic Trainings') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ Str::limit($training->title, 30) }}</span>
@endsection

@section('content')
<div x-data="trainingDetails()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if($training->image)
                <img src="{{ url('media/' . $training->image) }}" alt="{{ $training->title }}" class="w-16 h-16 rounded-xl object-cover shadow-lg">
            @else
                <div class="w-16 h-16 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 shadow-lg">
                    <i class="fas fa-chalkboard-teacher text-2xl"></i>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $training->title }}</h1>
                <p class="text-gray-500">{{ __('Submitted by') }} {{ $training->academicAccount->name ?? __('Unknown') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($training->approval_status === 'pending')
                <button @click="approve()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-check mr-2"></i>{{ __('Approve') }}
                </button>
                <button @click="showRejectModal = true" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-times mr-2"></i>{{ __('Reject') }}
                </button>
            @endif
            <button @click="deleteTraining()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-trash mr-2"></i>{{ __('Delete') }}
            </button>
            <a href="{{ route('admin.academic-content.trainings', ['locale' => app()->getLocale()]) }}"
               class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Status Badges -->
    <div class="flex items-center gap-3">
        <span class="px-3 py-1 rounded-full text-sm font-medium
            @if($training->approval_status === 'approved') bg-green-100 text-green-700
            @elseif($training->approval_status === 'rejected') bg-red-100 text-red-700
            @else bg-orange-100 text-orange-700 @endif">
            {{ __(ucfirst($training->approval_status)) }}
        </span>
        @if($training->is_expired)
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                {{ __('Expired') }}
            </span>
        @endif
        @if($training->category)
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                {{ ucfirst($training->category) }}
            </span>
        @endif
    </div>

    @if($training->approval_status === 'rejected' && $training->rejection_reason)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <h4 class="font-medium text-red-800 mb-1">{{ __('Rejection Reason') }}</h4>
            <p class="text-red-700">{{ $training->rejection_reason }}</p>
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
                    {!! nl2br(e($training->description)) !!}
                </div>
            </div>

            <!-- Requirements -->
            @if($training->requirements)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Requirements') }}</h3>
                    <div class="prose max-w-none text-gray-700">
                        @if(is_array($training->requirements))
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($training->requirements as $requirement)
                                    <li>{{ $requirement }}</li>
                                @endforeach
                            </ul>
                        @else
                            {!! nl2br(e($training->requirements)) !!}
                        @endif
                    </div>
                </div>
            @endif

            <!-- Image -->
            @if($training->image)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Training Image') }}</h3>
                    <img src="{{ url('media/' . $training->image) }}" alt="{{ $training->title }}" class="rounded-lg max-w-full">
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Training Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Training Details') }}</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Start Date') }}</p>
                        <p class="font-medium text-gray-800">{{ $training->start_date->format('F d, Y') }}</p>
                    </div>
                    @if($training->end_date)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('End Date') }}</p>
                            <p class="font-medium text-gray-800">{{ $training->end_date->format('F d, Y') }}</p>
                        </div>
                    @endif
                    @if($training->duration)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Duration') }}</p>
                            <p class="font-medium text-gray-800">{{ $training->duration }}</p>
                        </div>
                    @endif
                    @if($training->location)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Location') }}</p>
                            <p class="font-medium text-gray-800">{{ $training->location }}</p>
                        </div>
                    @endif
                    @if($training->is_online !== null)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Mode') }}</p>
                            <p class="font-medium text-gray-800">{{ $training->is_online ? __('Online') : __('In-Person') }}</p>
                        </div>
                    @endif
                    @if($training->max_participants)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Maximum Participants') }}</p>
                            <p class="font-medium text-gray-800">{{ $training->max_participants }}</p>
                        </div>
                    @endif
                    @if($training->price)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Price') }}</p>
                            <p class="font-medium text-gray-800">{{ $training->is_free ? __('Free') : '$' . number_format($training->price, 2) }}</p>
                        </div>
                    @endif
                    @if($training->registration_link)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Registration Link') }}</p>
                            <a href="{{ $training->registration_link }}" target="_blank" class="text-blue-600 hover:underline break-all">{{ $training->registration_link }}</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Institution Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Institution') }}</h3>
                @if($training->academicAccount)
                <div class="flex items-center gap-3 mb-4">
                    @if($training->academicAccount->logo)
                        <img src="{{ url('media/' . $training->academicAccount->logo) }}" class="w-12 h-12 rounded-lg object-cover">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($training->academicAccount->name ?? 'U', 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-medium text-gray-800">{{ $training->academicAccount->name ?? __('Unknown') }}</p>
                        <p class="text-sm text-gray-500">{{ $training->academicAccount->institution_type_label ?? '' }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.academic-accounts.show', ['locale' => app()->getLocale(), 'id' => $training->academic_account_id]) }}"
                   class="text-blue-600 hover:underline text-sm">
                    <i class="fas fa-external-link-alt mr-1"></i>{{ __('View Institution') }}
                </a>
                @else
                <p class="text-gray-500">{{ __('Institution not found') }} ({{ __('Account ID') }}: {{ $training->academic_account_id }})</p>
                @endif
            </div>

            <!-- Metadata -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Metadata') }}</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">{{ __('Created') }}</p>
                        <p class="text-gray-800">{{ $training->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('Last Updated') }}</p>
                        <p class="text-gray-800">{{ $training->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('ID') }}</p>
                        <p class="text-gray-800">#{{ $training->id }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showRejectModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Reject Training') }}</h3>
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
function trainingDetails() {
    return {
        showRejectModal: false,
        rejectReason: '',

        async approve() {
            if (!confirm('{{ __('Approve this training?') }}')) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-content/trainings/{{ $training->id }}/approve`, { method: 'POST' });
                showToast('{{ __('Training approved') }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to approve') }}', 'error');
            }
        },

        async reject() {
            if (!this.rejectReason) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-content/trainings/{{ $training->id }}/reject`, {
                    method: 'POST',
                    body: JSON.stringify({ reason: this.rejectReason })
                });
                showToast('{{ __('Training rejected') }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to reject') }}', 'error');
            }
        },

        async deleteTraining() {
            if (!confirm('{{ __('Are you sure you want to delete this training? This action cannot be undone.') }}')) return;
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/academic-content/trainings/{{ $training->id }}`, { method: 'DELETE' });
                showToast('{{ __('Training deleted') }}', 'success');
                setTimeout(() => window.location.href = '{{ route('admin.academic-content.trainings', ['locale' => app()->getLocale()]) }}', 1000);
            } catch (e) {
                showToast(e.message || '{{ __('Failed to delete') }}', 'error');
            }
        }
    }
}
</script>
@endpush
@endsection
