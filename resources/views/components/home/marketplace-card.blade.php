@props(['post', 'eager' => false, 'viewMode' => 'grid'])

@php
    $designer = $post->designer ?? null;
    $designerAvatar = $designer && $designer->avatar ? url('media/' . $designer->avatar) : null;
    $postImage = $post->image ? url('media/' . $post->image) : null;

    // Type badge colors
    $typeBadges = [
        'service' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => __('Service')],
        'collaboration' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => __('Collaboration')],
        'showcase' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => __('Showcase')],
        'opportunity' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'label' => __('Opportunity')],
    ];

    $typeBadge = $typeBadges[$post->type] ?? $typeBadges['showcase'];

    // Check if current user owns this post (to show pending/rejected status)
    $currentDesignerId = auth('designer')->id();
    $isOwner = $currentDesignerId && $designer && $currentDesignerId == $designer->id;
    $isPending = $post->approval_status === 'pending';
    $isRejected = $post->approval_status === 'rejected';
@endphp

@if($viewMode === 'grid')
    {{-- Grid View Card --}}
    <div class="group bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full {{ $isOwner && $isRejected ? 'ring-2 ring-red-300' : ($isOwner && $isPending ? 'ring-2 ring-yellow-300' : '') }}">
        {{-- Post Image --}}
        <a href="{{ route('marketplace.show', ['locale' => app()->getLocale(), 'id' => $post->id]) }}" class="relative block h-48 overflow-hidden flex-shrink-0">
            @if($postImage)
                <x-optimized-image
                    :src="$postImage"
                    :alt="$post->title"
                    :eager="$eager"
                    aspect-ratio="4/3"
                    class="group-hover:scale-105 transition-transform duration-300"
                    fallback="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
                />
            @else
                <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                    <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif

            {{-- Type Badge --}}
            <div class="absolute top-3 left-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $typeBadge['bg'] }} {{ $typeBadge['text'] }} backdrop-blur-sm">
                    {{ $typeBadge['label'] }}
                </span>
            </div>

            {{-- Pending/Rejected Status Badge (Owner Only) --}}
            @if($isOwner && ($isPending || $isRejected))
                <div class="absolute top-3 right-3">
                    @if($isRejected)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-600 text-white">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            {{ __('Rejected') }}
                        </span>
                    @elseif($isPending)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-500 text-white">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('Pending') }}
                        </span>
                    @endif
                </div>
            @endif
        </a>

        {{-- Card Content --}}
        <div class="p-4 flex flex-col flex-grow">
            {{-- Author Info --}}
            <div class="min-h-[2.5rem] mb-3">
                @if($designer)
                    <div class="flex items-center gap-2">
                        <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                @if($designerAvatar)
                                    <x-optimized-image
                                        :src="$designerAvatar"
                                        :alt="$designer->name"
                                        eager="true"
                                        aspect-ratio="1/1"
                                        class="w-full h-full"
                                        object-fit="cover"
                                        fallback="display: none;"
                                    />
                                @else
                                    {{ strtoupper(substr($designer->name, 0, 1)) }}
                                @endif
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ $designer->name }}</span>
                        </a>
                    </div>
                @endif
            </div>

            {{-- Title & Description --}}
            <a href="{{ route('marketplace.show', ['locale' => app()->getLocale(), 'id' => $post->id]) }}" class="block mb-2">
                <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-1 group-hover:text-blue-600 transition-colors">
                    {{ $post->title }}
                </h3>
                <p class="text-sm text-gray-600 line-clamp-2 min-h-[2.5rem]">
                    {{ $post->description }}
                </p>
            </a>

            {{-- Tags --}}
            <div class="flex flex-wrap gap-1.5 mt-3 mb-3 min-h-[2rem]">
                @if(!empty($post->tags) && is_array($post->tags))
                    @foreach(array_slice($post->tags, 0, 3) as $tag)
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $tag }}
                        </span>
                    @endforeach
                    @if(count($post->tags) > 3)
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-500">
                            +{{ count($post->tags) - 3 }}
                        </span>
                    @endif
                @endif
            </div>

            {{-- Stats --}}
            <div class="flex items-center gap-4 text-sm text-gray-500 mt-auto pt-3 border-t">
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>{{ number_format($post->comments_count) }}</span>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- List View Card --}}
    <div class="group bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col sm:flex-row {{ $isOwner && $isRejected ? 'ring-2 ring-red-300' : ($isOwner && $isPending ? 'ring-2 ring-yellow-300' : '') }}">
        {{-- Post Image --}}
        <a href="{{ route('marketplace.show', ['locale' => app()->getLocale(), 'id' => $post->id]) }}" class="relative block w-full sm:w-64 aspect-[16/9] sm:aspect-[4/3] overflow-hidden flex-shrink-0">
            @if($postImage)
                <x-optimized-image
                    :src="$postImage"
                    :alt="$post->title"
                    :eager="$eager"
                    aspect-ratio="4/3"
                    class="group-hover:scale-105 transition-transform duration-300"
                    fallback="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
                />
            @else
                <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                    <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif

            {{-- Type Badge --}}
            <div class="absolute top-3 left-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $typeBadge['bg'] }} {{ $typeBadge['text'] }} backdrop-blur-sm">
                    {{ $typeBadge['label'] }}
                </span>
            </div>

            {{-- Pending/Rejected Status Badge (Owner Only) --}}
            @if($isOwner && ($isPending || $isRejected))
                <div class="absolute top-3 right-3">
                    @if($isRejected)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-600 text-white">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            {{ __('Rejected') }}
                        </span>
                    @elseif($isPending)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-500 text-white">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('Pending') }}
                        </span>
                    @endif
                </div>
            @endif
        </a>

        {{-- Card Content --}}
        <div class="p-4 flex flex-col flex-grow">
            {{-- Author Info --}}
            @if($designer)
                <div class="flex items-center gap-2 mb-3">
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            @if($designerAvatar)
                                <x-optimized-image
                                    :src="$designerAvatar"
                                    :alt="$designer->name"
                                    eager="true"
                                    aspect-ratio="1/1"
                                    class="w-full h-full"
                                    object-fit="cover"
                                    fallback="display: none;"
                                />
                            @else
                                {{ strtoupper(substr($designer->name, 0, 1)) }}
                            @endif
                        </div>
                        <span class="text-sm font-medium text-gray-700">{{ $designer->name }}</span>
                    </a>
                </div>
            @endif

            {{-- Title & Description --}}
            <a href="{{ route('marketplace.show', ['locale' => app()->getLocale(), 'id' => $post->id]) }}" class="block mb-2">
                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                    {{ $post->title }}
                </h3>
                <p class="text-sm text-gray-600 line-clamp-3">
                    {{ $post->description }}
                </p>
            </a>

            {{-- Tags --}}
            @if(!empty($post->tags) && is_array($post->tags))
                <div class="flex flex-wrap gap-1.5 mt-3 mb-3">
                    @foreach(array_slice($post->tags, 0, 5) as $tag)
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $tag }}
                        </span>
                    @endforeach
                    @if(count($post->tags) > 5)
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-500">
                            +{{ count($post->tags) - 5 }}
                        </span>
                    @endif
                </div>
            @endif

            {{-- Stats --}}
            <div class="flex items-center gap-4 text-sm text-gray-500 mt-auto pt-3 border-t">
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>{{ number_format($post->comments_count) }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span>{{ number_format($post->bookmarks_count) }}</span>
                </div>
            </div>
        </div>
    </div>
@endif
