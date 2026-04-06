@props(['designer', 'marketplacePosts', 'isOwner'])

<div id="marketplace-tab" class="tab-content">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @if($designer->marketplacePosts && $designer->marketplacePosts->count() > 0)
        @foreach($designer->marketplacePosts as $post)
        @php
            $isRejected = $post->approval_status === 'rejected';
            $isPending = $post->approval_status === 'pending';
            // Only show rejected/pending items to owner
            if (!$isOwner && ($isRejected || $isPending)) {
                continue;
            }
        @endphp
        <!-- Marketplace Post Card -->
        <a href="{{ route('marketplace.show', ['locale' => app()->getLocale(), 'id' => $post->id]) }}" class="group block bg-white rounded-xl border {{ $isRejected ? 'border-red-300' : ($isPending ? 'border-yellow-300' : 'border-gray-200') }} overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 relative">
            <!-- Approval Status Badge (Owner Only) -->
            @if($isOwner && ($isRejected || $isPending))
            <div class="absolute top-3 right-3 z-10">
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

            <!-- Post Image -->
            <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                @if($post->image)
                <img src="{{ url('media/' . $post->image) }}" alt="{{ $post->title ?? __('Marketplace Post') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" onerror="this.style.display='none'; this.parentElement.classList.add('bg-gradient-to-br', 'from-blue-600', 'to-green-500');"/>
                @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-100 to-pink-100">
                    <svg class="w-16 h-16 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                @endif
                {{-- Hover overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            </div>

            <!-- Post Info -->
            <div class="p-4 sm:p-5">
                <div class="flex items-center gap-2 mb-2">
                    @if($post->type)
                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700">
                        {{ ucfirst($post->type) }}
                    </span>
                    @endif
                    @if($post->category)
                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-pink-50 text-pink-700">
                        {{ $post->category }}
                    </span>
                    @endif
                </div>
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 line-clamp-1">{{ $post->title ?? __('Untitled Post') }}</h3>
                <p class="text-xs sm:text-sm text-gray-600 line-clamp-2">{{ $post->description ?? '' }}</p>

                <!-- Tags -->
                @php
                    $tags = is_array($post->tags) ? $post->tags : (is_string($post->tags) ? json_decode($post->tags, true) : []);
                @endphp
                @if($tags && count($tags) > 0)
                <div class="flex flex-wrap gap-1 mt-3">
                    @foreach(array_slice($tags, 0, 3) as $tag)
                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">#{{ $tag }}</span>
                    @endforeach
                    @if(count($tags) > 3)
                    <span class="text-xs px-2 py-0.5 text-gray-500">+{{ count($tags) - 3 }} {{ __('more') }}</span>
                    @endif
                </div>
                @endif

                {{-- Rejection Reason (Owner Only) --}}
                @if($isOwner && $isRejected && $post->rejection_reason)
                    <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-xs text-red-700">
                            <strong>{{ __('Reason:') }}</strong> {{ $post->rejection_reason }}
                        </p>
                    </div>
                @endif

                {{-- Edit/Delete Buttons (Owner Only) --}}
                @if($isOwner)
                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}#marketplace"
                       onclick="event.stopPropagation(); event.preventDefault(); window.location.href=this.href;"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('Edit') }}
                    </a>
                    <form action="{{ route('marketplace-posts.destroy', ['locale' => app()->getLocale(), 'id' => $post->id]) }}" method="POST"
                          onclick="event.stopPropagation();"
                          onsubmit="return confirm('{{ __('Are you sure you want to delete this post?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ __('Delete') }}
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </a>
        @endforeach
        @endif

        <!-- Add New Marketplace Post Card (Owner Only) -->
        @if($isOwner)
        <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}#marketplace" class="group bg-white rounded-xl border-2 border-dashed border-gray-300 hover:border-purple-500 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer flex items-center justify-center min-h-[200px] sm:min-h-[300px]">
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 group-hover:text-purple-600 transition-colors">{{ __('Create Marketplace Post') }}</h3>
                <p class="text-sm text-gray-500 mt-2">{{ __('Share your work with the community') }}</p>
            </div>
        </a>
        @endif

        <!-- Empty State (No Posts) -->
        @php
            $visiblePosts = $designer->marketplacePosts ? $designer->marketplacePosts->filter(function($p) use ($isOwner) {
                if ($isOwner) return true;
                return $p->approval_status === 'approved';
            })->count() : 0;
        @endphp
        @if(!$isOwner && $visiblePosts == 0)
        <div class="col-span-full text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-700">{{ __('No marketplace posts yet') }}</h3>
            <p class="text-sm text-gray-500 mt-2">{{ __("This designer hasn't shared any marketplace posts.") }}</p>
        </div>
        @endif
    </div>
</div>
