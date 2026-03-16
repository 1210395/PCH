@extends('layout.main')

@section('title', __('Send Email to') . ' ' . $recipient->name)

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $recipient->id]) }}"
               class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Back to Profile') }}
            </a>
        </div>

        <!-- Email Compose Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        @if($recipient->profile_picture)
                            <img src="{{ url('media/' . $recipient->profile_picture) }}"
                                 alt="{{ $recipient->name }}"
                                 class="w-12 h-12 rounded-full border-2 border-white object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full border-2 border-white bg-white/20 flex items-center justify-center">
                                <span class="text-white text-lg font-semibold">{{ substr($recipient->name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-white">{{ __('Send Email to') }} {{ $recipient->name }}</h1>
                        <p class="text-indigo-100 text-sm">{{ $recipient->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form id="emailForm" class="p-6 space-y-6">
                @csrf

                <!-- From (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('From') }}</label>
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        @if($sender->profile_picture)
                            <img src="{{ url('media/' . $sender->profile_picture) }}"
                                 alt="{{ $sender->name }}"
                                 class="w-8 h-8 rounded-full object-cover">
                        @else
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-indigo-600 text-sm font-semibold">{{ substr($sender->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $sender->name }}</p>
                            <p class="text-xs text-gray-500">{{ $sender->email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Subject -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Subject') }} <span class="text-red-500">*</span></label>
                    <input type="text"
                           id="subject"
                           name="subject"
                           required
                           maxlength="255"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                           placeholder="{{ __('Enter email subject...') }}">
                    <p class="mt-1 text-xs text-gray-500"><span id="subjectCount">0</span>/255</p>
                </div>

                <!-- Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Message') }} <span class="text-red-500">*</span></label>
                    <textarea id="message"
                              name="message"
                              required
                              maxlength="5000"
                              rows="8"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none"
                              placeholder="{{ __('Write your message here...') }}"></textarea>
                    <p class="mt-1 text-xs text-gray-500"><span id="messageCount">0</span>/5000</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $recipient->id]) }}"
                       class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                            id="sendButton"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span>{{ __('Send Email') }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Note -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-blue-700">
                    <p class="font-medium">{{ __('Note') }}</p>
                    <p class="mt-1">{{ __('Your email address will be shared with the recipient so they can reply directly to you.') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeSuccessModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 text-center transform scale-95 opacity-0 transition-all duration-300" id="successModalContent">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('Email Sent!') }}</h3>
            <p class="text-gray-600 mb-6">{{ __('Your email has been sent successfully to') }} {{ $recipient->name }}.</p>
            <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $recipient->id]) }}"
               class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                {{ __('Back to Profile') }}
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('emailForm');
    const subjectInput = document.getElementById('subject');
    const messageInput = document.getElementById('message');
    const subjectCount = document.getElementById('subjectCount');
    const messageCount = document.getElementById('messageCount');
    const sendButton = document.getElementById('sendButton');

    // Character counters
    subjectInput.addEventListener('input', function() {
        subjectCount.textContent = this.value.length;
    });

    messageInput.addEventListener('input', function() {
        messageCount.textContent = this.value.length;
    });

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const subject = subjectInput.value.trim();
        const message = messageInput.value.trim();

        if (!subject || !message) {
            alert('{{ __("Please fill in all required fields.") }}');
            return;
        }

        // Disable button and show loading state
        sendButton.disabled = true;
        sendButton.innerHTML = `
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ __("Sending...") }}</span>
        `;

        try {
            const response = await fetch('{{ route("email.send", ["locale" => app()->getLocale(), "designerId" => $recipient->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    subject: subject,
                    message: message
                })
            });

            const data = await response.json();

            if (data.success) {
                showSuccessModal();
            } else {
                throw new Error(data.message || '{{ __("Failed to send email") }}');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || '{{ __("An error occurred. Please try again.") }}');

            // Re-enable button
            sendButton.disabled = false;
            sendButton.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span>{{ __("Send Email") }}</span>
            `;
        }
    });
});

function showSuccessModal() {
    const modal = document.getElementById('successModal');
    const content = document.getElementById('successModalContent');
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    const content = document.getElementById('successModalContent');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}
</script>
@endsection
