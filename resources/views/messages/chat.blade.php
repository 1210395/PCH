@extends('layout.chat')

@section('title', __('Chat with') . ' ' . $otherDesigner->name . ' | ' . config('app.name'))

@section('content')
<div class="h-full bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col overflow-hidden">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 shadow-sm flex-shrink-0">
        <div class="max-w-5xl mx-auto px-4 py-3 sm:py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0 flex-1">
                    {{-- Back Button --}}
                    <a href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}"
                       class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>

                    {{-- Profile Info --}}
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $otherDesigner->id]) }}"
                       class="flex items-center gap-3 hover:opacity-80 transition-opacity min-w-0 flex-1 group">
                        <div class="relative flex-shrink-0">
                            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden shadow-md">
                                @if($otherDesigner->avatar)
                                    <img src="{{ asset('storage/' . $otherDesigner->avatar) }}" alt="{{ $otherDesigner->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-white text-sm font-bold">
                                        {{ strtoupper(substr($otherDesigner->name, 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-white"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h1 class="text-base sm:text-lg font-bold text-gray-900 truncate group-hover:text-blue-600 transition-colors">
                                {{ $otherDesigner->name }}
                            </h1>
                            <p class="text-xs sm:text-sm text-gray-500 truncate">
                                {{ $otherDesigner->sub_sector ?? $otherDesigner->sector ?? __('Designer') }}
                            </p>
                        </div>
                    </a>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $otherDesigner->id]) }}"
                       class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ __('View Profile') }}
                    </a>
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $otherDesigner->id]) }}"
                       class="sm:hidden w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Messages Container --}}
    <div class="flex-1 overflow-hidden min-h-0">
        <div class="max-w-5xl mx-auto h-full flex flex-col min-h-0">
            {{-- Messages List --}}
            <div id="messagesContainer" class="flex-1 overflow-y-auto px-4 py-4 sm:py-6 space-y-4 min-h-0">
                @if($messages->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full text-center py-12">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-green-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ __('Start the conversation') }}</h3>
                        <p class="text-gray-500 text-sm">{{ __('Send a message to begin chatting with') }} {{ $otherDesigner->name }}</p>
                    </div>
                @else
                    @php $lastDate = null; @endphp
                    @foreach($messages as $message)
                        @php
                            $isSender = $message->sender_id === auth('designer')->id();
                            $messageDate = $message->created_at->format('Y-m-d');
                        @endphp

                        {{-- Date Separator --}}
                        @if($lastDate !== $messageDate)
                            <div class="flex items-center justify-center my-4">
                                <div class="px-4 py-1.5 bg-white rounded-full shadow-sm border border-gray-100 text-xs font-medium text-gray-500">
                                    @if($message->created_at->isToday())
                                        {{ __('Today') }}
                                    @elseif($message->created_at->isYesterday())
                                        {{ __('Yesterday') }}
                                    @else
                                        {{ $message->created_at->format('M d, Y') }}
                                    @endif
                                </div>
                            </div>
                            @php $lastDate = $messageDate; @endphp
                        @endif

                        <div class="message-item flex {{ $isSender ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
                            <div class="flex items-end gap-2 {{ $isSender ? 'flex-row-reverse' : 'flex-row' }} max-w-[85%] sm:max-w-[75%]">
                                @if(!$isSender)
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex-shrink-0 shadow-sm">
                                    @if($otherDesigner->avatar)
                                        <img src="{{ asset('storage/' . $otherDesigner->avatar) }}" alt="{{ $otherDesigner->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-white text-xs font-bold">
                                            {{ strtoupper(substr($otherDesigner->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                @endif
                                <div class="flex flex-col {{ $isSender ? 'items-end' : 'items-start' }}">
                                    <div class="px-4 py-2.5 rounded-2xl {{ $isSender ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-br-md' : 'bg-white border border-gray-100 text-gray-900 shadow-sm rounded-bl-md' }}">
                                        <p class="text-sm whitespace-pre-wrap break-words leading-relaxed">{{ $message->message }}</p>
                                    </div>
                                    <span class="text-xs text-gray-400 mt-1.5 px-1">
                                        {{ $message->created_at->format('g:i A') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Conversation Rating Popup --}}
            <div id="ratingSection" class="hidden relative px-4 pb-2 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 relative">
                    {{-- Close button --}}
                    <button type="button" id="ratingDismissBtn" onclick="dismissRatingPopup()" class="absolute top-2 right-2 w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="flex flex-col items-center gap-2">
                        {{-- Star icon + prompt --}}
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="text-sm font-semibold text-gray-800" id="ratingPrompt">{{ __('How was your conversation?') }}</span>
                        </div>

                        {{-- Stars --}}
                        <div id="ratingStars" class="flex gap-1.5">
                            @for($i = 1; $i <= 5; $i++)
                            <button type="button" onclick="submitConversationRating({{ $i }})" class="rating-star p-0.5 transition-transform hover:scale-125 focus:outline-none" data-rating="{{ $i }}">
                                <svg class="w-8 h-8 text-gray-300 hover:text-yellow-400 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                            @endfor
                        </div>

                        {{-- Countdown --}}
                        <div id="ratingCountdown" class="hidden text-xs text-gray-500"></div>

                        {{-- Already rated message --}}
                        <div id="ratedMessage" class="hidden flex items-center gap-2 text-sm text-green-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span id="ratedText"></span>
                        </div>

                        <p class="text-xs text-gray-400">{{ __('This rating contributes to their profile score') }}</p>
                    </div>
                </div>
            </div>

            {{-- Message Input --}}
            <div class="border-t border-gray-200 bg-white px-4 py-3 flex-shrink-0">
                <form id="messageForm" class="flex items-end gap-2 sm:gap-3">
                    @csrf
                    <div class="flex-1 relative">
                        <textarea
                            id="messageInput"
                            rows="1"
                            maxlength="2000"
                            placeholder="{{ __('Type your message...') }}"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 border-0 rounded-xl sm:rounded-2xl focus:ring-2 focus:ring-blue-500 focus:bg-white resize-none transition-all text-sm"
                            style="min-height: 44px; max-height: 100px;"
                        ></textarea>
                    </div>
                    <button
                        type="submit"
                        id="sendBtn"
                        class="w-11 h-11 sm:w-12 sm:h-12 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-105 transition-all flex-shrink-0 flex items-center justify-center disabled:opacity-50 disabled:scale-100"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
                <p class="text-xs text-gray-400 mt-1.5 text-center hidden sm:block">
                    {{ __('Press Enter to send, Shift+Enter for new line') }}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('messagesContainer');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const conversationId = {{ $conversation->id }};
    let lastMessageId = {{ $messages->last()->id ?? 0 }};
    let pollingInterval;

    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Scroll to bottom
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Initial scroll
    scrollToBottom();

    // Send message
    messageForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const message = messageInput.value.trim();
        if (!message) return;

        // Disable input
        messageInput.disabled = true;
        sendBtn.disabled = true;

        try {
            const response = await fetch('{{ route('messages.sendInChat', ['locale' => app()->getLocale(), 'conversationId' => $conversation->id]) }}', {
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
                // Clear input
                messageInput.value = '';
                messageInput.style.height = 'auto';

                // Add message to UI
                addMessageToUI(data.message, true);
                lastMessageId = data.message.id;

                // Scroll to bottom
                scrollToBottom();
            } else {
                alert(data.message || '{{ __("An error occurred") }}');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('{{ __("An error occurred. Please try again.") }}');
        } finally {
            messageInput.disabled = false;
            sendBtn.disabled = false;
            messageInput.focus();
        }
    });

    // Add message to UI
    function addMessageToUI(message, isSender) {
        // Remove empty state if exists
        const emptyState = messagesContainer.querySelector('.flex.flex-col.items-center.justify-center');
        if (emptyState) {
            emptyState.remove();
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `message-item flex ${isSender ? 'justify-end' : 'justify-start'}`;
        messageDiv.setAttribute('data-message-id', message.id);

        const avatarHtml = !isSender ? `
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex-shrink-0 shadow-sm">
                @if($otherDesigner->avatar)
                    <img src="{{ asset('storage/' . $otherDesigner->avatar) }}" alt="{{ $otherDesigner->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($otherDesigner->name, 0, 1)) }}
                    </div>
                @endif
            </div>
        ` : '';

        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

        messageDiv.innerHTML = `
            <div class="flex items-end gap-2 ${isSender ? 'flex-row-reverse' : 'flex-row'} max-w-[85%] sm:max-w-[75%]">
                ${avatarHtml}
                <div class="flex flex-col ${isSender ? 'items-end' : 'items-start'}">
                    <div class="px-4 py-2.5 rounded-2xl ${isSender ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-br-md' : 'bg-white border border-gray-100 text-gray-900 shadow-sm rounded-bl-md'}">
                        <p class="text-sm whitespace-pre-wrap break-words leading-relaxed">${escapeHtml(message.message)}</p>
                    </div>
                    <span class="text-xs text-gray-400 mt-1.5 px-1">${timeStr}</span>
                </div>
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Poll for new messages
    async function pollMessages() {
        try {
            const response = await fetch(`{{ route('messages.getMessages', ['locale' => app()->getLocale(), 'conversationId' => $conversation->id]) }}?last_message_id=${lastMessageId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success && data.messages.length > 0) {
                data.messages.forEach(message => {
                    const isSender = message.sender_id === {{ auth('designer')->id() }};
                    // Only add if not already in UI
                    if (!document.querySelector(`[data-message-id="${message.id}"]`)) {
                        addMessageToUI(message, isSender);
                    }
                    lastMessageId = message.id;
                });

                // Scroll to bottom if near bottom
                const isNearBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight < 100;
                if (isNearBottom) {
                    scrollToBottom();
                }
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }

    // Start polling every 3 seconds
    pollingInterval = setInterval(pollMessages, 3000);

    // Stop polling when page is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(pollingInterval);
        } else {
            pollingInterval = setInterval(pollMessages, 3000);
        }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        clearInterval(pollingInterval);
    });

    // Allow Enter to send (Shift+Enter for new line)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.dispatchEvent(new Event('submit'));
        }
    });

    // Conversation Rating Functionality
    const ratingSection = document.getElementById('ratingSection');
    const ratingStars = document.getElementById('ratingStars');
    const ratingPrompt = document.getElementById('ratingPrompt');
    const ratingCountdown = document.getElementById('ratingCountdown');
    const ratedMessage = document.getElementById('ratedMessage');
    const ratedText = document.getElementById('ratedText');
    const ratingDismissBtn = document.getElementById('ratingDismissBtn');
    window.ratingDismissed = false;

    // Check rating status
    async function checkRatingStatus() {
        if (window.ratingDismissed) return;

        try {
            const response = await fetch('{{ route('messages.rating.status', ['locale' => app()->getLocale(), 'conversationId' => $conversation->id]) }}', {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                if (data.can_rate) {
                    ratingSection.classList.remove('hidden');

                    if (data.has_rated) {
                        // Already rated - show confirmation
                        ratingStars.classList.remove('hidden');
                        ratingStars.classList.add('pointer-events-none', 'opacity-75');
                        ratingPrompt.classList.add('hidden');
                        ratedMessage.classList.remove('hidden');
                        ratedText.textContent = `{{ __("You rated this conversation") }} ${data.user_rating.rating} {{ __("stars") }}`;
                        ratingDismissBtn.classList.remove('hidden');
                        highlightStars(data.user_rating.rating);
                    } else {
                        // Show rating prompt with stars
                        ratingStars.classList.remove('hidden', 'pointer-events-none', 'opacity-75');
                        ratingPrompt.classList.remove('hidden');
                        ratedMessage.classList.add('hidden');
                        ratingCountdown.classList.add('hidden');
                        ratingDismissBtn.classList.remove('hidden');
                    }
                } else if (data.hours_remaining !== null && data.hours_remaining > 0) {
                    // Show countdown
                    ratingSection.classList.remove('hidden');
                    ratingStars.classList.add('hidden');
                    ratingPrompt.textContent = '{{ __("Rate this conversation") }}';
                    ratingCountdown.classList.remove('hidden');
                    ratingCountdown.textContent = `{{ __("Available in") }} ${data.hours_remaining} {{ __("hour") }}${data.hours_remaining > 1 ? 's' : ''}`;
                    ratingDismissBtn.classList.remove('hidden');
                }
            }
        } catch (error) {
            console.error('Error checking rating status:', error);
        }
    }

    // Highlight stars
    function highlightStars(rating) {
        const stars = document.querySelectorAll('.rating-star svg');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }

    // Check rating status on page load
    checkRatingStatus();

    // Check periodically (every 5 minutes)
    setInterval(checkRatingStatus, 300000);
});

// Dismiss rating popup for this session
function dismissRatingPopup() {
    const ratingSection = document.getElementById('ratingSection');
    ratingSection.classList.add('hidden');
    // Set flag so it doesn't reappear during periodic checks
    window.ratingDismissed = true;
}

// Submit conversation rating (global function for onclick)
async function submitConversationRating(rating) {
    try {
        const response = await fetch('{{ route('messages.rating.store', ['locale' => app()->getLocale(), 'conversationId' => $conversation->id]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ rating })
        });

        const data = await response.json();

        if (data.success) {
            // Update UI
            const ratingStars = document.getElementById('ratingStars');
            const ratingPrompt = document.getElementById('ratingPrompt');
            const ratedMessage = document.getElementById('ratedMessage');
            const ratedText = document.getElementById('ratedText');

            ratingPrompt.classList.add('hidden');
            ratedMessage.classList.remove('hidden');
            ratedText.textContent = `{{ __("Thank you! You rated") }} ${rating} {{ __("stars") }}`;

            // Highlight stars
            const stars = document.querySelectorAll('.rating-star svg');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-yellow-400');
                } else {
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                }
            });

            ratingStars.classList.add('pointer-events-none', 'opacity-75');
        } else {
            alert(data.message || '{{ __("Failed to submit rating") }}');
        }
    } catch (error) {
        console.error('Error submitting rating:', error);
        alert('{{ __("An error occurred. Please try again.") }}');
    }
}
</script>
@endpush
@endsection
