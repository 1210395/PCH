@extends('layout.main')

@section('title', __('Send Message to') . ' ' . $recipient->name . ' | ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-gray-50 py-4 sm:py-8">
    <div class="max-w-3xl mx-auto px-3 sm:px-4 md:px-6">
        {{-- Header --}}
        <div class="mb-4 sm:mb-6">
            <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $recipient->id]) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors mb-3 sm:mb-4">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="text-sm sm:text-base">{{ __('Back to Profile') }}</span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('Send Message') }}</h1>
        </div>

        {{-- Recipient Info Card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex-shrink-0">
                    @if($recipient->avatar)
                        <img src="{{ url('media/' . $recipient->avatar) }}" alt="{{ $recipient->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-white text-lg sm:text-xl font-bold">
                            {{ strtoupper(substr($recipient->name, 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 truncate">{{ $recipient->name }}</h2>
                    <p class="text-sm sm:text-base text-gray-600 truncate">{{ $recipient->sub_sector ?? $recipient->sector ?? __('Designer') }}</p>
                </div>
            </div>
        </div>

        {{-- Existing Request Notice --}}
        @if($existingRequest)
        <div class="bg-blue-50 border-l-4 border-blue-600 p-3 sm:p-4 rounded-lg mb-4 sm:mb-6">
            <div class="flex items-start gap-2 sm:gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-blue-900 text-sm sm:text-base">{{ __('Message Request Pending') }}</p>
                    <p class="text-blue-800 text-xs sm:text-sm mt-1">{{ __('You have already sent a message request to this designer. They will see your message once they accept.') }}</p>
                    <div class="mt-3 bg-white border border-blue-200 rounded-lg p-2 sm:p-3">
                        <p class="text-xs sm:text-sm text-gray-700 italic break-words">"{{ $existingRequest->message }}"</p>
                        <p class="text-xs text-gray-500 mt-2">{{ __('Sent') }} {{ $existingRequest->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Message Form --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <form id="messageForm" class="space-y-4">
                @csrf
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Your Message') }} *
                    </label>
                    <textarea
                        id="message"
                        name="message"
                        rows="6"
                        required
                        maxlength="2000"
                        class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        placeholder="{{ __('Write your message here...') }}"
                        {{ $existingRequest ? 'disabled' : '' }}
                    ></textarea>
                    <div class="flex justify-between items-center mt-2">
                        <p class="text-xs sm:text-sm text-gray-500">
                            <span id="charCount">0</span> / 2000 {{ __('characters') }}
                        </p>
                    </div>
                </div>

                @if(!$existingRequest)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 sm:p-4">
                    <div class="flex items-start gap-2 sm:gap-3">
                        <svg class="w-5 h-5 text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-xs sm:text-sm text-gray-700">
                            <p class="font-medium mb-1">{{ __('About Message Requests') }}</p>
                            <p>{{ __('Since you haven\'t chatted with this designer before, your message will be sent as a request. They can accept or decline your request.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $recipient->id]) }}" class="w-full sm:w-auto text-center px-4 sm:px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        {{ __('Cancel') }}
                    </a>
                    <button
                        type="submit"
                        id="submitBtn"
                        class="w-full sm:w-auto px-4 sm:px-6 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg font-semibold hover:shadow-lg transition-all"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span class="text-sm sm:text-base">{{ __('Send Message Request') }}</span>
                        </span>
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    const form = document.getElementById('messageForm');
    const submitBtn = document.getElementById('submitBtn');

    // Character counter
    if (messageTextarea) {
        messageTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Form submission
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const message = messageTextarea.value.trim();
            if (!message) {
                alert('{{ __("Please enter a message") }}');
                return;
            }

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="flex items-center gap-2"><svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>{{ __("Sending...") }}</span>';

            try {
                const response = await fetch('{{ route('messages.send', ['locale' => app()->getLocale(), 'designerId' => $recipient->id]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message })
                });

                const data = await response.json();

                if (data.success) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        // Success without redirect — re-enable the button so
                        // the user isn't stuck staring at a permanent
                        // "Sending..." state. (bugs.md H-12)
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<span class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>{{ __("Send Message Request") }}</span>';
                    }
                } else {
                    alert(data.message || '{{ __("An error occurred") }}');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>{{ __("Send Message Request") }}</span>';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('{{ __("An error occurred. Please try again.") }}');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>{{ __("Send Message Request") }}</span>';
            }
        });
    }
});
</script>
@endpush
@endsection
