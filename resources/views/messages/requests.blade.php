@extends('layout.main')

@section('title', __('Message Requests') . ' | ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-6 sm:py-8">
    <div class="max-w-4xl mx-auto px-4">
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}"
               class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors mb-4 group">
                <div class="w-8 h-8 rounded-lg bg-white shadow-sm border border-gray-100 flex items-center justify-center group-hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </div>
                <span class="font-medium">{{ __('Back to Messages') }}</span>
            </a>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('Message Requests') }}</h1>
                    <p class="text-gray-500 mt-1">{{ __('Manage connection requests from other designers') }}</p>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-1.5 inline-flex gap-1">
                <button onclick="switchTab('received')" id="receivedTab"
                        class="tab-btn px-6 py-2.5 rounded-xl font-semibold text-sm transition-all bg-gradient-to-r from-blue-600 to-green-500 text-white shadow-md">
                    {{ __('Received') }}
                    @if($receivedRequests->count() > 0)
                        <span class="ml-2 px-2 py-0.5 text-xs bg-white/20 text-white rounded-full">{{ $receivedRequests->count() }}</span>
                    @endif
                </button>
                <button onclick="switchTab('sent')" id="sentTab"
                        class="tab-btn px-6 py-2.5 rounded-xl font-semibold text-sm transition-all text-gray-600 hover:bg-gray-100">
                    {{ __('Sent') }}
                    @if($sentRequests->count() > 0)
                        <span class="ml-2 px-2 py-0.5 text-xs bg-gray-200 text-gray-700 rounded-full">{{ $sentRequests->count() }}</span>
                    @endif
                </button>
            </div>
        </div>

        {{-- Received Requests Tab --}}
        <div id="receivedContent" class="tab-content">
            @if($receivedRequests->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-20 h-20 mx-auto bg-gradient-to-br from-blue-100 to-green-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No Message Requests') }}</h3>
                <p class="text-gray-500 max-w-md mx-auto">{{ __('You don\'t have any pending message requests at the moment. When other designers want to connect with you, their requests will appear here.') }}</p>
            </div>
            @else
            <div class="space-y-4">
                @foreach($receivedRequests as $request)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow" data-request-id="{{ $request->id }}">
                    <div class="p-5 sm:p-6">
                        <div class="flex items-start gap-4">
                            {{-- Avatar --}}
                            <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $request->fromDesigner->id]) }}" class="flex-shrink-0 group">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden shadow-md group-hover:shadow-lg transition-shadow">
                                    @if($request->fromDesigner->avatar)
                                        <img src="{{ asset('storage/' . $request->fromDesigner->avatar) }}" alt="{{ $request->fromDesigner->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-white text-lg font-bold">
                                            {{ strtoupper(substr($request->fromDesigner->name, 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                            </a>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between mb-1">
                                    <div>
                                        <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $request->fromDesigner->id]) }}"
                                           class="font-bold text-gray-900 hover:text-blue-600 transition-colors text-base sm:text-lg">
                                            {{ $request->fromDesigner->name }}
                                        </a>
                                        <p class="text-sm text-gray-500">{{ $request->fromDesigner->sub_sector ?? $request->fromDesigner->sector ?? __('Designer') }}</p>
                                    </div>
                                    <span class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0 ml-2 bg-gray-100 px-2 py-1 rounded-lg">
                                        {{ $request->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                {{-- Message --}}
                                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 mt-4 border-l-4 border-blue-500">
                                    <p class="text-sm text-gray-700 whitespace-pre-wrap break-words leading-relaxed">{{ $request->message }}</p>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex flex-col sm:flex-row gap-3 mt-4">
                                    <button onclick="acceptRequest({{ $request->id }})"
                                            class="accept-btn flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ __('Accept') }}
                                    </button>
                                    <button onclick="declineRequest({{ $request->id }})"
                                            class="decline-btn flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border-2 border-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-50 hover:border-gray-300 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        {{ __('Decline') }}
                                    </button>
                                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $request->fromDesigner->id]) }}"
                                       class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 text-gray-600 hover:text-gray-900 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ __('View Profile') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Sent Requests Tab --}}
        <div id="sentContent" class="tab-content hidden">
            @if($sentRequests->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No Sent Requests') }}</h3>
                <p class="text-gray-500 max-w-md mx-auto">{{ __('You haven\'t sent any message requests yet. Visit a designer\'s profile to send them a connection request.') }}</p>
            </div>
            @else
            <div class="space-y-4">
                @foreach($sentRequests as $request)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 sm:p-6">
                        <div class="flex items-start gap-4">
                            {{-- Avatar --}}
                            <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $request->toDesigner->id]) }}" class="flex-shrink-0 group">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden shadow-md group-hover:shadow-lg transition-shadow">
                                    @if($request->toDesigner->avatar)
                                        <img src="{{ asset('storage/' . $request->toDesigner->avatar) }}" alt="{{ $request->toDesigner->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-white text-lg font-bold">
                                            {{ strtoupper(substr($request->toDesigner->name, 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                            </a>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between mb-1">
                                    <div>
                                        <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $request->toDesigner->id]) }}"
                                           class="font-bold text-gray-900 hover:text-blue-600 transition-colors text-base sm:text-lg">
                                            {{ $request->toDesigner->name }}
                                        </a>
                                        <p class="text-sm text-gray-500">{{ $request->toDesigner->sub_sector ?? $request->toDesigner->sector ?? __('Designer') }}</p>
                                    </div>
                                    <span class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0 ml-2 bg-gray-100 px-2 py-1 rounded-lg">
                                        {{ $request->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                {{-- Status Badge --}}
                                <div class="mt-3">
                                    @if($request->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium bg-amber-100 text-amber-800 rounded-xl">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ __('Pending') }}
                                    </span>
                                    @elseif($request->status === 'accepted')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium bg-green-100 text-green-800 rounded-xl">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ __('Accepted') }}
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium bg-red-100 text-red-800 rounded-xl">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ __('Declined') }}
                                    </span>
                                    @endif
                                </div>

                                {{-- Message --}}
                                <div class="bg-gray-50 rounded-xl p-4 mt-4">
                                    <p class="text-sm text-gray-700 whitespace-pre-wrap break-words leading-relaxed">{{ $request->message }}</p>
                                </div>

                                {{-- Action Button for Accepted Requests --}}
                                @if($request->status === 'accepted')
                                <div class="mt-4">
                                    <a href="{{ route('messages.chat', ['locale' => app()->getLocale(), 'designerId' => $request->toDesigner->id]) }}"
                                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        {{ __('Open Chat') }}
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    window.switchTab = function(tab) {
        const receivedTab = document.getElementById('receivedTab');
        const sentTab = document.getElementById('sentTab');
        const receivedContent = document.getElementById('receivedContent');
        const sentContent = document.getElementById('sentContent');

        if (tab === 'received') {
            receivedTab.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-green-500', 'text-white', 'shadow-md');
            receivedTab.classList.remove('text-gray-600', 'hover:bg-gray-100');
            sentTab.classList.remove('bg-gradient-to-r', 'from-blue-600', 'to-green-500', 'text-white', 'shadow-md');
            sentTab.classList.add('text-gray-600', 'hover:bg-gray-100');
            receivedContent.classList.remove('hidden');
            sentContent.classList.add('hidden');
        } else {
            sentTab.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-green-500', 'text-white', 'shadow-md');
            sentTab.classList.remove('text-gray-600', 'hover:bg-gray-100');
            receivedTab.classList.remove('bg-gradient-to-r', 'from-blue-600', 'to-green-500', 'text-white', 'shadow-md');
            receivedTab.classList.add('text-gray-600', 'hover:bg-gray-100');
            sentContent.classList.remove('hidden');
            receivedContent.classList.add('hidden');
        }
    };

    // Accept request
    window.acceptRequest = async function(requestId) {
        const requestCard = document.querySelector(`[data-request-id="${requestId}"]`);
        const acceptBtn = requestCard.querySelector('.accept-btn');
        const declineBtn = requestCard.querySelector('.decline-btn');

        // Disable buttons and show loading
        acceptBtn.disabled = true;
        declineBtn.disabled = true;
        acceptBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ __('Accepting...') }}</span>
        `;

        try {
            const response = await fetch(`{{ url(app()->getLocale()) }}/messages/requests/${requestId}/accept`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Show success and redirect
                requestCard.innerHTML = `
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-lg font-semibold text-gray-900 mb-2">{{ __('Request Accepted!') }}</p>
                        <p class="text-gray-500 mb-4">{{ __('Redirecting to chat...') }}</p>
                    </div>
                `;
                setTimeout(() => window.location.href = data.redirect, 1000);
            } else {
                alert(data.message || '{{ __("An error occurred") }}');
                location.reload();
            }
        } catch (error) {
            console.error('Error:', error);
            alert('{{ __("An error occurred. Please try again.") }}');
            location.reload();
        }
    };

    // Decline request
    window.declineRequest = async function(requestId) {
        if (!confirm('{{ __("Are you sure you want to decline this message request?") }}')) {
            return;
        }

        const requestCard = document.querySelector(`[data-request-id="${requestId}"]`);
        const acceptBtn = requestCard.querySelector('.accept-btn');
        const declineBtn = requestCard.querySelector('.decline-btn');

        // Disable buttons and show loading
        acceptBtn.disabled = true;
        declineBtn.disabled = true;
        declineBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ __('Declining...') }}</span>
        `;

        try {
            const response = await fetch(`{{ url(app()->getLocale()) }}/messages/requests/${requestId}/decline`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Animate removal
                requestCard.style.transition = 'all 0.3s ease-out';
                requestCard.style.opacity = '0';
                requestCard.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    requestCard.remove();
                    // Check if there are no more requests
                    const remainingRequests = document.querySelectorAll('[data-request-id]');
                    if (remainingRequests.length === 0) {
                        location.reload();
                    }
                }, 300);
            } else {
                alert(data.message || '{{ __("An error occurred") }}');
                location.reload();
            }
        } catch (error) {
            console.error('Error:', error);
            alert('{{ __("An error occurred. Please try again.") }}');
            location.reload();
        }
    };
});
</script>
@endpush
@endsection
