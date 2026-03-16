@extends('layout.main')

@section('title', __('Messages') . ' | ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-6xl mx-auto px-4 py-6 sm:py-8">
        {{-- Header Section --}}
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        {{ __('Messages') }}
                    </h1>
                    <p class="text-gray-500 mt-1 ml-13">{{ __('Connect and collaborate with other designers') }}</p>
                </div>
                <a href="{{ route('messages.requests', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <span>{{ __('Message Requests') }}</span>
                    @php
                        $pendingRequests = \App\Models\MessageRequest::where('to_designer_id', auth('designer')->id())->where('status', 'pending')->count();
                    @endphp
                    @if($pendingRequests > 0)
                        <span class="px-2 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full">{{ $pendingRequests }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- Search and Filter --}}
        @if(!$conversations->isEmpty() || !empty($searchTerm) || $currentFilter !== 'all')
        <div class="mb-6">
            <form method="GET" action="{{ route('messages.index', ['locale' => app()->getLocale()]) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            placeholder="{{ __('Search conversations...') }}"
                            value="{{ $searchTerm }}"
                            class="w-full pl-12 pr-4 py-3 bg-gray-50 border-0 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                        />
                    </div>
                    <div class="flex gap-2">
                        <select name="filter" onchange="this.form.submit()" class="px-4 py-3 bg-gray-50 border-0 rounded-xl focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <option value="all" {{ $currentFilter === 'all' ? 'selected' : '' }}>{{ __('All Messages') }}</option>
                            <option value="unread" {{ $currentFilter === 'unread' ? 'selected' : '' }}>{{ __('Unread Only') }}</option>
                        </select>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                        @if(!empty($searchTerm) || $currentFilter !== 'all')
                        <a href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-3 text-gray-500 hover:text-gray-700 transition-colors flex items-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        @endif

        {{-- Conversations List --}}
        @if($conversations->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            @if(!empty($searchTerm) || $currentFilter !== 'all')
                <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No Results Found') }}</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    @if(!empty($searchTerm))
                        {{ __('No conversations match') }} "<strong class="text-gray-700">{{ $searchTerm }}</strong>"
                    @else
                        {{ __('No unread messages at the moment.') }}
                    @endif
                </p>
                <a href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                    {{ __('Clear Filters') }}
                </a>
            @else
                <div class="w-24 h-24 mx-auto bg-gradient-to-br from-blue-100 to-green-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No Messages Yet') }}</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">{{ __('Start a conversation by visiting a designer\'s profile and sending them a message request.') }}</p>
                <a href="{{ route('messages.requests', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    {{ __('View Message Requests') }}
                </a>
            @endif
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="divide-y divide-gray-100">
                @foreach($conversations as $conversation)
                    @php
                        $currentDesigner = auth('designer')->user();
                        $otherDesigner = $conversation->getOtherDesigner($currentDesigner->id);
                        $unreadCount = $conversation->designer_1_id == $currentDesigner->id
                            ? $conversation->designer_1_unread_count
                            : $conversation->designer_2_unread_count;
                        $lastMessage = $conversation->lastMessage;
                    @endphp

                    <a href="{{ route('messages.chat', ['locale' => app()->getLocale(), 'designerId' => $otherDesigner->id]) }}"
                       class="flex items-center gap-4 p-4 sm:p-5 hover:bg-gradient-to-r hover:from-blue-50 hover:to-green-50 transition-all group {{ $unreadCount > 0 ? 'bg-blue-50/50' : '' }}">
                        {{-- Avatar --}}
                        <div class="relative flex-shrink-0">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden shadow-md group-hover:shadow-lg transition-shadow">
                                @if($otherDesigner->avatar)
                                    <img src="{{ url('media/' . $otherDesigner->avatar) }}" alt="{{ $otherDesigner->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-white text-lg sm:text-xl font-bold">
                                        {{ strtoupper(substr($otherDesigner->name, 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            @if($unreadCount > 0)
                            <div class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-md ring-2 ring-white">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-1">
                                <h3 class="font-bold text-gray-900 {{ $unreadCount > 0 ? 'text-blue-900' : '' }} truncate text-base sm:text-lg group-hover:text-blue-600 transition-colors">
                                    {{ $otherDesigner->name }}
                                </h3>
                                @if($lastMessage)
                                <span class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0 ml-2">
                                    {{ $lastMessage->created_at->diffForHumans(null, true) }}
                                </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mb-1.5 truncate">
                                {{ $otherDesigner->sub_sector ?? $otherDesigner->sector ?? __('Designer') }}
                            </p>
                            @if($lastMessage)
                            <div class="flex items-center gap-2">
                                @if($lastMessage->sender_id == $currentDesigner->id)
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                                <p class="text-sm {{ $unreadCount > 0 ? 'text-gray-800 font-medium' : 'text-gray-500' }} truncate">
                                    {{ Str::limit($lastMessage->message, 60) }}
                                </p>
                            </div>
                            @endif
                        </div>

                        {{-- Arrow --}}
                        <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
