<!-- START: Laravel Integration Point -->
<!-- Expected data: Dynamic header settings from SiteSetting model -->
<!-- Properties: Navigation items loaded from database -->
<!-- START: Navbar Component - Matching Figma Header Design EXACTLY -->
@php
    $headerSettings = \App\Models\SiteSetting::get('header_settings');
    if (!$headerSettings) {
        $headerSettings = [
            'nav_links' => [
                ['title' => 'Discover', 'url' => '/', 'route' => 'home', 'highlight' => false],
                ['title' => 'Products', 'url' => '/products', 'route' => 'products', 'highlight' => false],
                ['title' => 'Projects', 'url' => '/projects', 'route' => 'projects', 'highlight' => false],
                ['title' => 'Services', 'url' => '/services', 'route' => 'services', 'highlight' => false],
                ['title' => 'Fab Labs', 'url' => '/fab-labs', 'route' => 'fab-labs', 'highlight' => false],
                ['title' => 'Academic & Workplace Learning Centers', 'url' => '/academic-tevets', 'route' => 'academic-tevets', 'highlight' => false],
                ['title' => 'MarketPlace', 'url' => '/marketplace', 'route' => 'marketplace.index', 'highlight' => true],
            ],
        ];
    }
@endphp
<header class="sticky top-0 z-50 bg-white border-b border-gray-200 animate-on-load animate-fadeInDown">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4">
        <div class="flex items-center justify-between gap-4 sm:gap-8">

            <!-- Logo & Main Navigation -->
            <div class="flex items-center gap-4 sm:gap-8 lg:gap-12">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="cursor-pointer">
                    <img src="{{ asset('images/logo.webp') }}" alt="Palestine Creative Hub" class="h-[3.6rem]">
                </a>

                <!-- Main Navigation - Desktop -->
                <nav class="hidden lg:flex items-center gap-8">
                    @foreach($headerSettings['nav_links'] ?? [] as $navLink)
                        @php
                            $navTitle = (app()->getLocale() === 'ar' && !empty($navLink['title_ar'])) ? $navLink['title_ar'] : $navLink['title'];
                        @endphp
                        @if(($navLink['type'] ?? 'link') === 'dropdown')
                            {{-- Dropdown Menu --}}
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" class="flex items-center gap-1 text-gray-700 hover:text-gray-900 transition-colors">
                                    {{ $navTitle }}
                                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50"
                                     style="display: none;">
                                    @foreach($navLink['children'] ?? [] as $childLink)
                                        <a href="{{ url(app()->getLocale() . $childLink['url']) }}"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors">
                                            {{ (app()->getLocale() === 'ar' && !empty($childLink['title_ar'])) ? $childLink['title_ar'] : $childLink['title'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @elseif(!empty($navLink['highlight']))
                            <a href="{{ url(app()->getLocale() . $navLink['url']) }}" class="px-4 py-2 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-md hover:shadow-lg">
                                {{ $navTitle }}
                            </a>
                        @else
                            <a href="{{ url(app()->getLocale() . $navLink['url']) }}" class="text-gray-700 hover:text-gray-900 transition-colors">
                                {{ $navTitle }}
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>

            <!-- Search Bar with Instant Results -->
            <div class="hidden md:flex flex-1 max-w-md" x-data="searchDropdown()" @click.away="showResults = false">
                <div class="relative w-full">
                    <form action="{{ route('search', ['locale' => app()->getLocale()]) }}" method="GET" class="relative w-full">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            name="q"
                            x-ref="searchInput"
                            x-model="query"
                            @input.debounce.300ms="search"
                            @focus="if(query.length >= 2) showResults = true"
                            @keydown.escape="showResults = false"
                            @keydown.enter="handleEnter($event)"
                            placeholder="{{ __('Search designers, projects, products...') }}"
                            class="w-full pl-10 pr-10 bg-gray-50 border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            autocomplete="off"
                            value="{{ request('q') ?? '' }}"
                        />
                        <!-- Loading Spinner -->
                        <div x-show="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <svg class="animate-spin w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <!-- Clear Button -->
                        <button type="button" x-show="query.length > 0 && !loading" @click="clearSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </form>

                    <!-- Search Results Dropdown -->
                    <div x-show="showResults && hasResults"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50 max-h-[70vh] overflow-y-auto"
                         style="display: none;">

                        <!-- Designers Results -->
                        <template x-if="results.designers && results.designers.length > 0">
                            <div class="border-b border-gray-100">
                                <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Designers') }}</div>
                                <template x-for="designer in results.designers" :key="'d-'+designer.id">
                                    <a :href="'{{ url(app()->getLocale()) }}/designer/' + designer.id"
                                       class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition-colors">
                                        <template x-if="designer.avatar">
                                            <img :src="'{{ asset('storage') }}/' + designer.avatar" class="w-10 h-10 rounded-full object-cover">
                                        </template>
                                        <template x-if="!designer.avatar">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-semibold text-sm" x-text="designer.name.charAt(0).toUpperCase()"></div>
                                        </template>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 truncate" x-text="designer.name"></p>
                                            <p class="text-xs text-gray-500 truncate" x-text="designer.sector || designer.sub_sector || 'Designer'"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <!-- Projects Results -->
                        <template x-if="results.projects && results.projects.length > 0">
                            <div class="border-b border-gray-100">
                                <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Projects') }}</div>
                                <template x-for="project in results.projects" :key="'p-'+project.id">
                                    <a :href="'{{ url(app()->getLocale()) }}/projects/' + project.id"
                                       class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition-colors">
                                        <template x-if="project.image">
                                            <img :src="'{{ asset('storage') }}/' + project.image" class="w-12 h-10 rounded-lg object-cover">
                                        </template>
                                        <template x-if="!project.image">
                                            <div class="w-12 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        </template>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 truncate" x-text="project.title"></p>
                                            <p class="text-xs text-gray-500 truncate" x-text="project.designer_name ? 'by ' + project.designer_name : ''"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <!-- Products Results -->
                        <template x-if="results.products && results.products.length > 0">
                            <div>
                                <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Products') }}</div>
                                <template x-for="product in results.products" :key="'pr-'+product.id">
                                    <a :href="'{{ url(app()->getLocale()) }}/products/' + product.id"
                                       class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition-colors">
                                        <template x-if="product.image">
                                            <img :src="'{{ asset('storage') }}/' + product.image" class="w-12 h-10 rounded-lg object-cover">
                                        </template>
                                        <template x-if="!product.image">
                                            <div class="w-12 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            </div>
                                        </template>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 truncate" x-text="product.name"></p>
                                            <p class="text-xs text-gray-500 truncate" x-text="product.designer_name ? 'by ' + product.designer_name : ''"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <!-- View All Results Link -->
                        <a :href="'{{ route('search', ['locale' => app()->getLocale()]) }}?q=' + encodeURIComponent(query)"
                           class="block px-4 py-3 text-center text-sm font-semibold text-blue-600 hover:bg-blue-50 transition-colors border-t border-gray-200">
                            {{ __('View all results for') }} "<span x-text="query"></span>"
                            <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <!-- No Results State -->
                    <div x-show="showResults && !hasResults && !loading && query.length >= 2"
                         class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50 p-6 text-center"
                         style="display: none;">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p class="text-gray-500">{{ __('No results found for') }} "<span x-text="query" class="font-medium"></span>"</p>
                        <p class="text-gray-400 text-sm mt-1">{{ __('Try different keywords or') }} <a :href="'{{ route('search', ['locale' => app()->getLocale()]) }}?q=' + encodeURIComponent(query)" class="text-blue-500 hover:underline">{{ __('view full search') }}</a></p>
                    </div>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-4">
                <!-- Language Switcher -->
                @php
                    $currentLocale = app()->getLocale();
                    $oppositeLocale = $currentLocale === 'ar' ? 'en' : 'ar';
                    $switchUrl = str_replace('/' . $currentLocale . '/', '/' . $oppositeLocale . '/', request()->fullUrl());
                    // Handle edge case where URL might end with locale
                    if ($switchUrl === request()->fullUrl()) {
                        $switchUrl = str_replace('/' . $currentLocale, '/' . $oppositeLocale, request()->fullUrl());
                    }
                @endphp
                <a href="{{ $switchUrl }}"
                   onclick="sessionStorage.setItem('pageIsRefreshing', 'true')"
                   class="hidden md:flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors"
                   title="{{ $currentLocale === 'ar' ? 'Switch to English' : 'التبديل إلى العربية' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
                    </svg>
                    <span>{{ $currentLocale === 'ar' ? 'EN' : 'عربي' }}</span>
                </a>

                @auth('designer')
                <!-- Messages Icon with Dropdown -->
                @php
                    $currentUser = Auth::guard('designer')->user();
                    $totalUnread = 0;
                    $pendingRequestsCount = 0;
                    $recentChats = collect();

                    if ($currentUser) {
                        $conversations = \App\Models\Conversation::where(function($query) use ($currentUser) {
                            $query->where('designer_1_id', $currentUser->id)
                                  ->orWhere('designer_2_id', $currentUser->id);
                        })
                        ->with(['designer1', 'designer2', 'lastMessage'])
                        ->orderBy('last_message_at', 'desc')
                        ->limit(5)
                        ->get();

                        foreach ($conversations as $conversation) {
                            if ($conversation->designer_1_id == $currentUser->id) {
                                $totalUnread += $conversation->designer_1_unread_count;
                                $otherUser = $conversation->designer2;
                                $unreadCount = $conversation->designer_1_unread_count;
                            } else {
                                $totalUnread += $conversation->designer_2_unread_count;
                                $otherUser = $conversation->designer1;
                                $unreadCount = $conversation->designer_2_unread_count;
                            }

                            $recentChats->push([
                                'id' => $conversation->id,
                                'other_user' => $otherUser,
                                'last_message' => $conversation->lastMessage,
                                'unread_count' => $unreadCount,
                                'updated_at' => $conversation->last_message_at ?? $conversation->updated_at
                            ]);
                        }

                        // Get pending message requests count
                        $pendingRequestsCount = \App\Models\MessageRequest::where('to_designer_id', $currentUser->id)
                            ->where('status', 'pending')
                            ->count();
                    }
                @endphp

                <div class="hidden md:block relative" x-data="messagesDropdown()" @click.away="closeDropdown()">
                    <button @click="toggleDropdown()" class="relative p-2 hover:bg-gray-100 rounded-full transition-colors group">
                        <svg class="w-5 h-5 text-gray-600 group-hover:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <!-- Unread messages badge -->
                        <span id="unreadBadge" class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-blue-600 to-green-500 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-lg {{ $totalUnread > 0 ? '' : 'hidden' }}">
                            <span id="unreadCount">{{ $totalUnread > 9 ? '9+' : $totalUnread }}</span>
                        </span>
                        <!-- Message requests badge -->
                        <span id="requestsBadge" class="absolute -top-1 -left-1 w-5 h-5 bg-gradient-to-r from-purple-600 to-pink-500 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-lg animate-pulse {{ $pendingRequestsCount > 0 ? '' : 'hidden' }}">
                            <span id="requestsCount">{{ $pendingRequestsCount > 9 ? '9+' : $pendingRequestsCount }}</span>
                        </span>
                    </button>

                    <!-- Messages Dropdown -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50"
                         style="display: none;">

                        <!-- Header -->
                        <div class="px-4 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold">{{ __('Messages') }}</h3>
                                <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full">{{ $totalUnread }} {{ __('unread') }}</span>
                            </div>
                            <!-- Message Requests Link -->
                            <a id="requestsLink" href="{{ route('messages.requests', ['locale' => app()->getLocale()]) }}"
                               class="mt-2 flex items-center justify-between text-xs bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-lg transition-colors {{ $pendingRequestsCount > 0 ? '' : 'hidden' }}">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <span id="requestsLinkCount">{{ $pendingRequestsCount }}</span> {{ __('Message Requests') }}
                                </span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Chat List -->
                        <div class="max-h-[320px] overflow-y-auto">
                            @forelse($recentChats as $chat)
                            <button @click="openChat({{ $chat['id'] }}, @js($chat['other_user']->name), @js($chat['other_user']->avatar ? asset('storage/' . $chat['other_user']->avatar) : null), {{ $chat['other_user']->id }})"
                                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-b-0 text-left {{ $chat['unread_count'] > 0 ? 'bg-blue-50/50' : '' }}">
                                <!-- Avatar -->
                                <div class="relative flex-shrink-0">
                                    @if($chat['other_user']->avatar)
                                    <img src="{{ asset('storage/' . $chat['other_user']->avatar) }}" alt="{{ $chat['other_user']->name }}" class="w-12 h-12 rounded-full object-cover">
                                    @else
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($chat['other_user']->name, 0, 1)) }}
                                    </div>
                                    @endif
                                    @if($chat['unread_count'] > 0)
                                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-blue-600 to-green-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                                        {{ $chat['unread_count'] > 9 ? '9+' : $chat['unread_count'] }}
                                    </span>
                                    @endif
                                </div>
                                <!-- Chat Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="font-semibold text-gray-900 truncate {{ $chat['unread_count'] > 0 ? 'text-blue-600' : '' }}">{{ $chat['other_user']->name }}</span>
                                        <span class="text-[10px] text-gray-400 flex-shrink-0 ml-2">{{ $chat['updated_at'] ? $chat['updated_at']->diffForHumans(null, true, true) : '' }}</span>
                                    </div>
                                    <p class="text-sm text-gray-500 truncate {{ $chat['unread_count'] > 0 ? 'font-medium text-gray-700' : '' }}">
                                        @if($chat['last_message'])
                                            {{ Str::limit($chat['last_message']->message, 35) }}
                                        @else
                                            {{ __('Start a conversation') }}
                                        @endif
                                    </p>
                                </div>
                            </button>
                            @empty
                            <div class="px-4 py-8 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p class="text-gray-500 text-sm">{{ __('No messages yet') }}</p>
                            </div>
                            @endforelse
                        </div>

                        <!-- View All Link -->
                        <a href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}"
                           class="block px-4 py-3 text-center text-sm font-semibold text-blue-600 hover:bg-blue-50 transition-colors border-t border-gray-200">
                            {{ __('View All Messages') }}
                            <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Notifications Dropdown -->
                <div class="hidden md:block relative" x-data="notificationsDropdown()" @click.away="closeDropdown()">
                    <button @click="toggleDropdown()" class="relative p-2 hover:bg-gray-100 rounded-full transition-colors group">
                        <svg class="w-5 h-5 text-gray-600 group-hover:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="unreadCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-blue-600 to-green-500 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-lg">
                            <span x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                        </span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50"
                         style="display: none;">

                        <!-- Header -->
                        <div class="px-4 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold">{{ __('Notifications') }}</h3>
                                <button x-show="unreadCount > 0" @click="markAllAsRead()" class="text-xs bg-white/20 px-2 py-0.5 rounded-full hover:bg-white/30 transition-colors">
                                    {{ __('Mark all read') }}
                                </button>
                            </div>
                        </div>

                        <!-- Notifications List -->
                        <div class="max-h-[320px] overflow-y-auto">
                            <template x-if="loading">
                                <div class="px-4 py-8 text-center">
                                    <svg class="animate-spin w-8 h-8 mx-auto text-blue-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </template>
                            <template x-if="!loading && notifications.length === 0">
                                <div class="px-4 py-8 text-center">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    <p class="text-gray-500 text-sm">{{ __('No notifications yet') }}</p>
                                </div>
                            </template>
                            <template x-for="notification in notifications" :key="notification.id">
                                <div @click="markAsRead(notification)"
                                     class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-b-0 cursor-pointer"
                                     :class="{ 'bg-blue-50/50': !notification.read }">
                                    <!-- Icon based on type -->
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                                         :class="{
                                            'bg-blue-100 text-blue-600': notification.type === 'profile_view',
                                            'bg-green-100 text-green-600': notification.type === 'project_view' || notification.type === 'message_request_accepted',
                                            'bg-pink-100 text-pink-600': notification.type === 'profile_like',
                                            'bg-purple-100 text-purple-600': notification.type === 'new_follower' || notification.type === 'message_request',
                                            'bg-yellow-100 text-yellow-600': notification.type === 'product_view',
                                            'bg-indigo-100 text-indigo-600': notification.type === 'new_message',
                                            'bg-gray-100 text-gray-600': !['profile_view', 'project_view', 'profile_like', 'new_follower', 'product_view', 'message_request', 'message_request_accepted', 'new_message'].includes(notification.type)
                                         }">
                                        <!-- Profile View Icon -->
                                        <template x-if="notification.type === 'profile_view'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </template>
                                        <!-- Project View Icon -->
                                        <template x-if="notification.type === 'project_view'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                            </svg>
                                        </template>
                                        <!-- Like Icon -->
                                        <template x-if="notification.type === 'profile_like'">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                            </svg>
                                        </template>
                                        <!-- Follower Icon -->
                                        <template x-if="notification.type === 'new_follower'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                            </svg>
                                        </template>
                                        <!-- Product View Icon -->
                                        <template x-if="notification.type === 'product_view'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </template>
                                        <!-- Message Request Icon -->
                                        <template x-if="notification.type === 'message_request'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                            </svg>
                                        </template>
                                        <!-- Message Request Accepted Icon -->
                                        <template x-if="notification.type === 'message_request_accepted'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </template>
                                        <!-- New Message Icon -->
                                        <template x-if="notification.type === 'new_message'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                            </svg>
                                        </template>
                                        <!-- Default Icon -->
                                        <template x-if="!['profile_view', 'project_view', 'profile_like', 'new_follower', 'product_view', 'message_request', 'message_request_accepted', 'new_message'].includes(notification.type)">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                        </template>
                                    </div>
                                    <!-- Notification Content -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900" :class="{ 'font-semibold': !notification.read }" x-text="notification.title"></p>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="notification.message"></p>
                                        <p class="text-[10px] text-gray-400 mt-1" x-text="notification.time_ago"></p>
                                    </div>
                                    <!-- Unread dot -->
                                    <div x-show="!notification.read" class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                @endauth

                <!-- Authentication Buttons -->
                @guest('designer')
                <a href="{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}" class="hidden md:block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md transition-colors">
                    {{ __('Log In') }}
                </a>

                <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors shadow-sm">
                    {{ __('Sign Up') }}
                </a>
                @else
                <div class="flex items-center gap-3">
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <!-- Profile Avatar Button -->
                        <button @click="open = !open" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                            @if(Auth::guard('designer')->user()->avatar)
                            <img src="{{ asset('storage/' . Auth::guard('designer')->user()->avatar) }}" alt="{{ Auth::guard('designer')->user()->name }}" class="w-8 h-8 rounded-full object-cover border-2 border-transparent hover:border-blue-500 transition-all">
                            @else
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white text-sm font-medium border-2 border-transparent hover:border-blue-600 transition-all">
                                {{ strtoupper(substr(Auth::guard('designer')->user()->name, 0, 1)) }}
                            </div>
                            @endif
                            <svg class="w-4 h-4 text-gray-600 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                             style="display: none;">

                            <!-- User Info Section -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">{{ Auth::guard('designer')->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Auth::guard('designer')->user()->email }}</p>
                            </div>

                            @php
                                $isGuest = Auth::guard('designer')->user()->sector === 'guest' || Auth::guard('designer')->user()->sub_sector === 'Guest';
                            @endphp

                            <!-- Menu Items -->
                            <div class="py-2">
                                @if(!$isGuest)
                                <!-- View Profile - Only for non-guests -->
                                <a href="{{ route('profile', ['locale' => app()->getLocale()]) }}"
                                   class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ __('View Profile') }}
                                </a>

                                <!-- Profile Settings - Only for non-guests -->
                                <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}"
                                   class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    {{ __('Profile Settings') }}
                                </a>
                                @endif

                                <!-- Messages - For all users -->
                                <a href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}"
                                   class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        <span>{{ __('Messages') }}</span>
                                    </div>
                                    <span id="unreadBadgeDropdown" class="px-2 py-0.5 text-xs font-bold bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-full {{ $totalUnread > 0 ? '' : 'hidden' }}">
                                        <span id="unreadCountDropdown">{{ $totalUnread > 9 ? '9+' : $totalUnread }}</span>
                                    </span>
                                </a>

                                <!-- Account Settings - For all users -->
                                <a href="{{ route('account.settings', ['locale' => app()->getLocale()]) }}"
                                   class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ __('Account Settings') }}
                                </a>

                                <!-- Divider -->
                                <div class="my-2 border-t border-gray-100"></div>

                                <!-- Logout - For all users -->
                                <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        {{ __('Logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endguest

                <!-- Mobile Menu Button -->
                <button class="lg:hidden p-2 hover:bg-gray-100 rounded-md transition-colors" onclick="toggleMobileMenu()">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>
<!-- END: Laravel Integration Point -->

<!-- Mobile Menu (Hidden by default) -->
<div id="mobileMenu" class="hidden lg:hidden border-t border-gray-100 bg-white">
    <div class="px-4 py-4 space-y-3">
        <!-- Mobile Search Bar -->
        <div class="md:hidden">
            <form action="{{ route('search', ['locale' => app()->getLocale()]) }}" method="GET" class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    name="q"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-md text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="{{ __('Search creative work...') }}"
                    value="{{ request('q') ?? '' }}"
                >
            </form>
        </div>

        <!-- Mobile Navigation Links -->
        @foreach($headerSettings['nav_links'] ?? [] as $navLink)
            @php
                $mobileNavTitle = (app()->getLocale() === 'ar' && !empty($navLink['title_ar'])) ? $navLink['title_ar'] : $navLink['title'];
            @endphp
            @if(($navLink['type'] ?? 'link') === 'dropdown')
                {{-- Mobile Dropdown --}}
                <div x-data="{ open: false }" class="border-b border-gray-100 last:border-b-0">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                        <span>{{ $mobileNavTitle }}</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="bg-gray-50">
                        @foreach($navLink['children'] ?? [] as $childLink)
                            <a href="{{ url(app()->getLocale() . $childLink['url']) }}" class="block px-8 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-blue-600">
                                {{ (app()->getLocale() === 'ar' && !empty($childLink['title_ar'])) ? $childLink['title_ar'] : $childLink['title'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @elseif(!empty($navLink['highlight']))
                <a href="{{ url(app()->getLocale() . $navLink['url']) }}" class="block px-4 py-2 text-base font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-full text-center">
                    {{ $mobileNavTitle }}
                </a>
            @else
                <a href="{{ url(app()->getLocale() . $navLink['url']) }}" class="block px-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                    {{ $mobileNavTitle }}
                </a>
            @endif
        @endforeach

        <!-- Mobile Language Switcher -->
        <div class="border-t border-gray-100 pt-3">
            <a href="{{ $switchUrl }}"
               onclick="sessionStorage.setItem('pageIsRefreshing', 'true')"
               class="flex items-center gap-3 px-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
                </svg>
                <span>{{ $currentLocale === 'ar' ? 'English' : 'العربية' }}</span>
            </a>
        </div>

        <!-- Mobile Auth Buttons -->
        @guest('designer')
        <div class="pt-4 border-t border-gray-100 space-y-2">
            <a href="{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}" class="block w-full px-4 py-2 text-center text-base font-medium text-gray-700 hover:bg-gray-50 rounded-md border border-gray-200">
                {{ __('Log In') }}
            </a>
            <a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="block w-full px-4 py-2 text-center text-base font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                {{ __('Sign Up') }}
            </a>
        </div>
        @else
        <!-- Mobile Profile Menu - Authenticated Users -->
        <div class="pt-4 border-t border-gray-100">
            <!-- User Info -->
            <div class="px-4 py-3 bg-gray-50 rounded-lg mb-3">
                <div class="flex items-center gap-3">
                    @if(Auth::guard('designer')->user()->avatar)
                    <img src="{{ asset('storage/' . Auth::guard('designer')->user()->avatar) }}" alt="{{ Auth::guard('designer')->user()->name }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium">
                        {{ strtoupper(substr(Auth::guard('designer')->user()->name, 0, 1)) }}
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::guard('designer')->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::guard('designer')->user()->email }}</p>
                    </div>
                </div>
            </div>

            @php
                $isGuest = Auth::guard('designer')->user()->sector === 'guest' || Auth::guard('designer')->user()->sub_sector === 'Guest';
            @endphp

            <!-- Profile Menu Items -->
            <div class="space-y-1">
                @if(!$isGuest)
                <!-- View Profile - Only for non-guests -->
                <a href="{{ route('profile', ['locale' => app()->getLocale()]) }}"
                   class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>{{ __('View Profile') }}</span>
                </a>

                <!-- Profile Settings - Only for non-guests -->
                <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}"
                   class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>{{ __('Profile Settings') }}</span>
                </a>
                @endif

                <!-- Messages - For all users -->
                @php
                    $mobileCurrentUser = Auth::guard('designer')->user();
                    $mobileTotalUnread = 0;
                    $mobilePendingRequests = 0;

                    if ($mobileCurrentUser) {
                        $mobileConversations = \App\Models\Conversation::where(function($query) use ($mobileCurrentUser) {
                            $query->where('designer_1_id', $mobileCurrentUser->id)
                                  ->orWhere('designer_2_id', $mobileCurrentUser->id);
                        })->get();

                        foreach ($mobileConversations as $conversation) {
                            if ($conversation->designer_1_id == $mobileCurrentUser->id) {
                                $mobileTotalUnread += $conversation->designer_1_unread_count;
                            } else {
                                $mobileTotalUnread += $conversation->designer_2_unread_count;
                            }
                        }

                        // Get pending message requests count for mobile
                        $mobilePendingRequests = \App\Models\MessageRequest::where('to_designer_id', $mobileCurrentUser->id)
                            ->where('status', 'pending')
                            ->count();
                    }
                @endphp

                <a href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}"
                   class="flex items-center justify-between px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>{{ __('Messages') }}</span>
                    </div>
                    <span id="unreadBadgeMobile" class="px-2 py-1 text-xs font-bold bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-full {{ $mobileTotalUnread > 0 ? '' : 'hidden' }}">
                        <span id="unreadCountMobile">{{ $mobileTotalUnread > 9 ? '9+' : $mobileTotalUnread }}</span>
                    </span>
                </a>

                <!-- Message Requests Link (Mobile) -->
                <a href="{{ route('messages.requests', ['locale' => app()->getLocale()]) }}"
                   id="requestsLinkMobile"
                   class="flex items-center justify-between px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors {{ $mobilePendingRequests > 0 ? '' : 'hidden' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <span>{{ __('Message Requests') }}</span>
                    </div>
                    <span id="requestsBadgeMobile" class="px-2 py-1 text-xs font-bold bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-full animate-pulse {{ $mobilePendingRequests > 0 ? '' : 'hidden' }}">
                        <span id="requestsCountMobile">{{ $mobilePendingRequests > 9 ? '9+' : $mobilePendingRequests }}</span>
                    </span>
                </a>

                <!-- Account Settings - For all users -->
                <a href="{{ route('account.settings', ['locale' => app()->getLocale()]) }}"
                   class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ __('Account Settings') }}</span>
                </a>

                <!-- Logout - For all users -->
                <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}" class="mt-2">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>{{ __('Logout') }}</span>
                    </button>
                </form>
            </div>
        </div>
        @endguest
    </div>
</div>

@auth('designer')
<!-- Slide-out Chat Panel -->
<div id="chatPanel" class="fixed bottom-0 right-4 z-[9999] hidden">
    <div class="w-[340px] bg-white rounded-t-xl shadow-2xl border border-gray-200 border-b-0 flex flex-col" style="height: 450px;">
        <!-- Chat Header -->
        <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-t-xl cursor-pointer" onclick="toggleChatMinimize()">
            <div class="flex items-center gap-3">
                <div id="chatAvatar" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-semibold"></div>
                <div>
                    <h4 id="chatUserName" class="font-semibold text-sm"></h4>
                    <span id="chatStatus" class="text-[10px] opacity-80">{{ __('Online') }}</span>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="event.stopPropagation(); toggleChatMinimize()" class="p-1.5 hover:bg-white/20 rounded-lg transition-colors" title="Minimize">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                </button>
                <button onclick="event.stopPropagation(); expandChat()" class="p-1.5 hover:bg-white/20 rounded-lg transition-colors" title="Open full page">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </button>
                <button onclick="event.stopPropagation(); closeChat()" class="p-1.5 hover:bg-white/20 rounded-lg transition-colors" title="Close">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat Body (collapsible) -->
        <div id="chatBody" class="flex-1 flex flex-col overflow-hidden">
            <!-- Messages Container -->
            <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
                <!-- Loading -->
                <div id="chatLoading" class="flex items-center justify-center h-full">
                    <div class="flex flex-col items-center gap-2">
                        <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-gray-500">{{ __('Loading messages...') }}</span>
                    </div>
                </div>
            </div>

            <!-- Message Input -->
            <div class="p-3 bg-white border-t border-gray-200">
                <form id="chatForm" onsubmit="sendMessage(event)" class="flex items-center gap-2">
                    <input type="text" id="chatInput" placeholder="{{ __('Type a message...') }}"
                           class="flex-1 px-4 py-2 bg-gray-100 border-0 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                           autocomplete="off">
                    <button type="submit" class="p-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-full hover:shadow-lg transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endauth

<!-- JavaScript for Mobile Menu Toggle and Search -->
<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
    }

    // Search Dropdown Alpine Component
    function searchDropdown() {
        return {
            query: '{{ request('q') ?? '' }}',
            showResults: false,
            loading: false,
            results: {
                designers: [],
                projects: [],
                products: []
            },

            get hasResults() {
                return (this.results.designers && this.results.designers.length > 0) ||
                       (this.results.projects && this.results.projects.length > 0) ||
                       (this.results.products && this.results.products.length > 0);
            },

            async search() {
                if (this.query.length < 2) {
                    this.showResults = false;
                    this.results = { designers: [], projects: [], products: [] };
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch('{{ route("search.instant", ["locale" => app()->getLocale()]) }}?q=' + encodeURIComponent(this.query), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.results = {
                            designers: data.designers || [],
                            projects: data.projects || [],
                            products: data.products || []
                        };
                        this.showResults = true;
                    }
                } catch (error) {
                    console.error('Search error:', error);
                } finally {
                    this.loading = false;
                }
            },

            clearSearch() {
                this.query = '';
                this.showResults = false;
                this.results = { designers: [], projects: [], products: [] };
                this.$refs.searchInput.focus();
            },

            handleEnter(event) {
                // Let the form submit normally if there are results showing
                // The form will redirect to the full search page
            }
        };
    }

    @auth('designer')
    // Messages Dropdown Alpine Component
    function messagesDropdown() {
        return {
            open: false,
            toggleDropdown() {
                this.open = !this.open;
            },
            closeDropdown() {
                this.open = false;
            },
            openChat(conversationId, userName, userAvatar, userId) {
                this.open = false;
                window.openChatPanel(conversationId, userName, userAvatar, userId);
            }
        };
    }

    // Notifications Dropdown Alpine Component
    function notificationsDropdown() {
        return {
            open: false,
            loading: false,
            notifications: [],
            unreadCount: 0,

            init() {
                // Load unread count on page load
                this.fetchUnreadCount();

                // Poll for new notifications every 90 seconds (only when tab is visible)
                let notificationInterval = setInterval(() => this.fetchUnreadCount(), 90000);

                // Pause polling when tab is hidden to reduce server load
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        clearInterval(notificationInterval);
                    } else {
                        this.fetchUnreadCount(); // Fetch immediately when tab becomes visible
                        notificationInterval = setInterval(() => this.fetchUnreadCount(), 90000);
                    }
                });
            },

            toggleDropdown() {
                this.open = !this.open;
                if (this.open) {
                    this.fetchNotifications();
                }
            },

            closeDropdown() {
                this.open = false;
            },

            async fetchUnreadCount() {
                try {
                    const response = await fetch('{{ route("notifications.unreadCount", ["locale" => app()->getLocale()]) }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.unreadCount = data.count;
                    }
                } catch (error) {
                    console.error('Error fetching notification count:', error);
                }
            },

            async fetchNotifications() {
                this.loading = true;
                try {
                    const response = await fetch('{{ route("notifications.index", ["locale" => app()->getLocale()]) }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.notifications = data.notifications;
                    }
                } catch (error) {
                    console.error('Error fetching notifications:', error);
                } finally {
                    this.loading = false;
                }
            },

            async markAsRead(notification) {
                // Mark as read first
                if (!notification.read) {
                    try {
                        await fetch('{{ url(app()->getLocale()) }}/notifications/' + notification.id + '/mark-read', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        notification.read = true;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    } catch (error) {
                        console.error('Error marking notification as read:', error);
                    }
                }

                // Navigate based on notification type
                const baseUrl = '{{ url(app()->getLocale()) }}';
                let redirectUrl = null;

                switch (notification.type) {
                    case 'message_request':
                        redirectUrl = baseUrl + '/messages/requests';
                        break;
                    case 'message_request_accepted':
                    case 'new_message':
                        redirectUrl = baseUrl + '/messages';
                        break;
                    case 'profile_view':
                    case 'project_view':
                    case 'profile_like':
                    case 'new_follower':
                    case 'product_view':
                        // No redirect for view/engagement notifications
                        break;
                    default:
                        break;
                }

                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            },

            async markAllAsRead() {
                try {
                    await fetch('{{ route("notifications.markAllAsRead", ["locale" => app()->getLocale()]) }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    this.notifications.forEach(n => n.read = true);
                    this.unreadCount = 0;
                } catch (error) {
                    console.error('Error marking all as read:', error);
                }
            }
        };
    }

    // Chat Panel State
    window.chatState = {
        conversationId: null,
        otherUserId: null,
        userName: '',
        userAvatar: null,
        isMinimized: false,
        messages: [],
        pollInterval: null
    };

    // Save chat state to localStorage
    function saveChatState() {
        const stateToSave = {
            conversationId: window.chatState.conversationId,
            otherUserId: window.chatState.otherUserId,
            userName: window.chatState.userName,
            userAvatar: window.chatState.userAvatar,
            isMinimized: window.chatState.isMinimized
        };
        localStorage.setItem('openChat', JSON.stringify(stateToSave));
    }

    // Clear chat state from localStorage
    function clearChatState() {
        localStorage.removeItem('openChat');
    }

    // Restore chat from localStorage on page load
    function restoreChatState() {
        try {
            const saved = localStorage.getItem('openChat');
            if (saved) {
                const state = JSON.parse(saved);
                if (state.conversationId) {
                    // Restore the chat panel
                    window.openChatPanel(
                        state.conversationId,
                        state.userName,
                        state.userAvatar,
                        state.otherUserId,
                        state.isMinimized // Pass minimized state
                    );
                }
            }
        } catch (e) {
            console.error('Error restoring chat state:', e);
            clearChatState();
        }
    }

    // Open Chat Panel
    window.openChatPanel = function(conversationId, userName, userAvatar, userId, startMinimized = false) {
        const panel = document.getElementById('chatPanel');
        const chatBody = document.getElementById('chatBody');
        const panelInner = panel.querySelector('.w-\\[340px\\]');

        window.chatState.conversationId = conversationId;
        window.chatState.otherUserId = userId;
        window.chatState.userName = userName;
        window.chatState.userAvatar = userAvatar;
        window.chatState.isMinimized = startMinimized;

        // Update header
        document.getElementById('chatUserName').textContent = userName;

        // Update avatar
        const avatarEl = document.getElementById('chatAvatar');
        if (userAvatar) {
            avatarEl.innerHTML = '<img src="' + userAvatar + '" class="w-8 h-8 rounded-full object-cover">';
        } else {
            avatarEl.innerHTML = userName.charAt(0).toUpperCase();
        }

        // Show panel
        panel.classList.remove('hidden');

        // Handle minimized state
        if (startMinimized) {
            chatBody.style.display = 'none';
            panelInner.style.height = 'auto';
        } else {
            chatBody.style.display = 'flex';
            panelInner.style.height = '450px';
        }

        // Load messages
        loadChatMessages(conversationId);

        // Start polling for new messages
        startMessagePolling(conversationId);

        // Mark messages as read
        markMessagesAsRead(conversationId);

        // Save state to localStorage
        saveChatState();
    };

    // Close Chat
    window.closeChat = function() {
        const panel = document.getElementById('chatPanel');
        panel.classList.add('hidden');

        // Stop polling
        if (window.chatState.pollInterval) {
            clearInterval(window.chatState.pollInterval);
            window.chatState.pollInterval = null;
        }

        window.chatState.conversationId = null;

        // Clear from localStorage
        clearChatState();
    };

    // Toggle Minimize
    window.toggleChatMinimize = function() {
        const panel = document.getElementById('chatPanel');
        const chatBody = document.getElementById('chatBody');
        const panelInner = panel.querySelector('.w-\\[340px\\]');

        window.chatState.isMinimized = !window.chatState.isMinimized;

        if (window.chatState.isMinimized) {
            chatBody.style.display = 'none';
            panelInner.style.height = 'auto';
        } else {
            chatBody.style.display = 'flex';
            panelInner.style.height = '450px';
            scrollToBottom();
        }

        // Save minimized state to localStorage
        saveChatState();
    };

    // Expand to full page
    window.expandChat = function() {
        if (window.chatState.otherUserId) {
            window.location.href = '{{ url(app()->getLocale()) }}/messages/chat/' + window.chatState.otherUserId;
        }
    };

    // Load Chat Messages
    async function loadChatMessages(conversationId) {
        const container = document.getElementById('chatMessages');
        const loading = document.getElementById('chatLoading');

        loading.style.display = 'flex';

        try {
            const response = await fetch('{{ url(app()->getLocale()) }}/messages/' + conversationId + '/fetch', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            loading.style.display = 'none';

            if (data.success) {
                window.chatState.messages = data.messages || [];
                renderMessages();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            loading.innerHTML = '<p class="text-red-500 text-sm">Failed to load messages</p>';
        }
    }

    // Render Messages
    function renderMessages() {
        const container = document.getElementById('chatMessages');
        const currentUserId = {{ Auth::guard('designer')->id() }};

        let html = '';

        window.chatState.messages.forEach(msg => {
            const isMine = msg.sender_id === currentUserId;
            const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            if (isMine) {
                html += `
                    <div class="flex justify-end">
                        <div class="max-w-[75%]">
                            <div class="bg-gradient-to-r from-blue-600 to-green-500 text-white px-4 py-2 rounded-2xl rounded-br-md text-sm">
                                ${escapeHtml(msg.message)}
                            </div>
                            <div class="text-[10px] text-gray-400 text-right mt-1">${time}</div>
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="flex justify-start">
                        <div class="max-w-[75%]">
                            <div class="bg-white text-gray-800 px-4 py-2 rounded-2xl rounded-bl-md text-sm shadow-sm">
                                ${escapeHtml(msg.message)}
                            </div>
                            <div class="text-[10px] text-gray-400 mt-1">${time}</div>
                        </div>
                    </div>
                `;
            }
        });

        if (html === '') {
            html = '<div class="text-center text-gray-400 text-sm py-8">No messages yet. Say hello!</div>';
        }

        container.innerHTML = html;
        scrollToBottom();
    }

    // Send Message
    async function sendMessage(event) {
        event.preventDefault();

        const input = document.getElementById('chatInput');
        const message = input.value.trim();

        if (!message || !window.chatState.conversationId) return;

        input.value = '';

        // Optimistic UI update
        const currentUserId = {{ Auth::guard('designer')->id() }};
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        window.chatState.messages.push({
            id: 'temp-' + Date.now(),
            sender_id: currentUserId,
            message: message,
            created_at: new Date().toISOString()
        });

        renderMessages();

        try {
            const response = await fetch('{{ url(app()->getLocale()) }}/messages/' + window.chatState.conversationId + '/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: message })
            });

            const data = await response.json();

            if (!data.success) {
                console.error('Failed to send message:', data.message);
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    // Mark Messages as Read
    async function markMessagesAsRead(conversationId) {
        try {
            await fetch('{{ url(app()->getLocale()) }}/messages/' + conversationId + '/mark-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            updateUnreadCount();
        } catch (error) {
            console.error('Error marking messages as read:', error);
        }
    }

    // Start Polling for New Messages
    function startMessagePolling(conversationId) {
        // Clear existing interval
        if (window.chatState.pollInterval) {
            clearInterval(window.chatState.pollInterval);
        }

        // Poll every 3 seconds
        window.chatState.pollInterval = setInterval(async () => {
            if (window.chatState.conversationId !== conversationId) {
                clearInterval(window.chatState.pollInterval);
                return;
            }

            try {
                const response = await fetch('{{ url(app()->getLocale()) }}/messages/' + conversationId + '/fetch?since=' + (window.chatState.messages.length > 0 ? window.chatState.messages[window.chatState.messages.length - 1].id : 0), {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (data.success && data.messages && data.messages.length > window.chatState.messages.length) {
                    window.chatState.messages = data.messages;
                    renderMessages();
                    markMessagesAsRead(conversationId);
                }
            } catch (error) {
                console.error('Error polling messages:', error);
            }
        }, 3000);
    }

    // Utility Functions
    function scrollToBottom() {
        const container = document.getElementById('chatMessages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Update unread message count dynamically
    function updateUnreadCount() {
        fetch('{{ route("messages.unreadCount", ["locale" => app()->getLocale()]) }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            const count = data.count || 0;
            const displayCount = count > 9 ? '9+' : count;

            // Update desktop icon badge
            const badge = document.getElementById('unreadBadge');
            const badgeCount = document.getElementById('unreadCount');
            if (badge && badgeCount) {
                if (count > 0) {
                    badge.classList.remove('hidden');
                    badgeCount.textContent = displayCount;
                } else {
                    badge.classList.add('hidden');
                }
            }

            // Update dropdown badge
            const badgeDropdown = document.getElementById('unreadBadgeDropdown');
            const badgeCountDropdown = document.getElementById('unreadCountDropdown');
            if (badgeDropdown && badgeCountDropdown) {
                if (count > 0) {
                    badgeDropdown.classList.remove('hidden');
                    badgeCountDropdown.textContent = displayCount;
                } else {
                    badgeDropdown.classList.add('hidden');
                }
            }

            // Update mobile badge
            const badgeMobile = document.getElementById('unreadBadgeMobile');
            const badgeCountMobile = document.getElementById('unreadCountMobile');
            if (badgeMobile && badgeCountMobile) {
                if (count > 0) {
                    badgeMobile.classList.remove('hidden');
                    badgeCountMobile.textContent = displayCount;
                } else {
                    badgeMobile.classList.add('hidden');
                }
            }
        })
        .catch(error => {
            console.error('Error fetching unread count:', error);
        });
    }

    // Update pending message requests count dynamically
    function updatePendingRequestsCount() {
        fetch('{{ route("messages.pendingRequestsCount", ["locale" => app()->getLocale()]) }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            const count = data.count || 0;
            const displayCount = count > 9 ? '9+' : count;

            // Update desktop icon badge for requests
            const badge = document.getElementById('requestsBadge');
            const badgeCount = document.getElementById('requestsCount');
            if (badge && badgeCount) {
                if (count > 0) {
                    badge.classList.remove('hidden');
                    badgeCount.textContent = displayCount;
                } else {
                    badge.classList.add('hidden');
                }
            }

            // Update requests link in dropdown
            const requestsLink = document.getElementById('requestsLink');
            const requestsLinkCount = document.getElementById('requestsLinkCount');
            if (requestsLink && requestsLinkCount) {
                if (count > 0) {
                    requestsLink.classList.remove('hidden');
                    requestsLinkCount.textContent = count;
                } else {
                    requestsLink.classList.add('hidden');
                }
            }

            // Update mobile badge for requests
            const badgeMobile = document.getElementById('requestsBadgeMobile');
            const badgeCountMobile = document.getElementById('requestsCountMobile');
            if (badgeMobile && badgeCountMobile) {
                if (count > 0) {
                    badgeMobile.classList.remove('hidden');
                    badgeCountMobile.textContent = displayCount;
                } else {
                    badgeMobile.classList.add('hidden');
                }
            }

            // Update mobile requests link visibility
            const requestsLinkMobile = document.getElementById('requestsLinkMobile');
            if (requestsLinkMobile) {
                if (count > 0) {
                    requestsLinkMobile.classList.remove('hidden');
                } else {
                    requestsLinkMobile.classList.add('hidden');
                }
            }
        })
        .catch(error => {
            console.error('Error fetching pending requests count:', error);
        });
    }

    // Update immediately on page load
    updateUnreadCount();
    updatePendingRequestsCount();

    // Poll for updates every 90 seconds (reduced from 30s to minimize server load)
    let messageInterval = setInterval(updateUnreadCount, 90000);
    let requestsInterval = setInterval(updatePendingRequestsCount, 90000);

    // Pause polling when tab is hidden to reduce server load
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Tab is hidden - stop polling
            clearInterval(messageInterval);
            clearInterval(requestsInterval);
        } else {
            // Tab is visible again - fetch immediately and restart polling
            updateUnreadCount();
            updatePendingRequestsCount();
            messageInterval = setInterval(updateUnreadCount, 90000);
            requestsInterval = setInterval(updatePendingRequestsCount, 90000);
        }
    });

    // Restore chat panel if it was open before page navigation/refresh
    document.addEventListener('DOMContentLoaded', function() {
        restoreChatState();
    });
    @endauth
</script>
<!-- END: Navbar Component -->
