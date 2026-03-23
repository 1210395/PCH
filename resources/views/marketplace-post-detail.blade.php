@extends('layout.main')

@php
    // UTF-8 sanitization for title
    $sanitize = [\App\Helpers\DropdownHelper::class, 'sanitizeUtf8'];
    $postTitle = $sanitize($post->title ?? '');
    $postDescription = $sanitize($post->description ?? '');
    $posterName = $sanitize($post->designer->name ?? '');

    // Check if user has liked this marketplace post
    $hasLiked = false;
    if (auth('designer')->check()) {
        $hasLiked = \App\Models\Like::where('designer_id', auth('designer')->id())
            ->where('likeable_type', 'App\Models\MarketplacePost')
            ->where('likeable_id', $post->id)
            ->exists();
    }
@endphp

@section('title', $postTitle . ' | ' . __('Marketplace') . ' | ' . config('app.name'))

@section('head')
@php
    $ogImage = $post->image ? url('media/' . $post->image) : url('media/images/logo.png');
@endphp
<meta property="og:title" content="{{ $postTitle }} | {{ __('Marketplace') }}">
<meta property="og:description" content="{{ Str::limit($postDescription, 160) }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:type" content="article">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $postTitle }}">
<meta name="twitter:description" content="{{ Str::limit($postDescription, 160) }}">
<meta name="twitter:image" content="{{ $ogImage }}">
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 py-6 sm:py-8" x-data="{
    liked: {{ $hasLiked ? 'true' : 'false' }},
    likesCount: {{ $post->likes_count ?? 0 }},
    async toggleLike() {
        @auth('designer')
        try {
            const response = await fetch('{{ route('marketplace.like', ['locale' => app()->getLocale(), 'id' => $post->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                this.liked = data.liked;
                this.likesCount = data.likes_count;
            }
        } catch (error) {
            console.error('Error toggling like:', error);
        }
        @else
        window.location.href = '{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}';
        @endauth
    }
}">
    <div class="container mx-auto px-4">
        {{-- Back Navigation --}}
        <div class="mb-6">
            <a href="{{ route('marketplace.index', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span>{{ __('Back to Marketplace') }}</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Post Image --}}
                <div class="bg-white rounded-xl overflow-hidden shadow-sm">
                    @if($post->image)
                        <div class="relative aspect-[16/9] overflow-hidden">
                            <x-optimized-image
                                :src="url('media/' . $post->image)"
                                :alt="$post->title"
                                eager="true"
                                aspect-ratio="16/9"
                                class="w-full h-full"
                            />
                        </div>
                    @else
                        <div class="aspect-[16/9] bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-24 h-24 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif

                    {{-- Engagement Actions --}}
                    <div class="p-4 border-b flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            {{-- Like Button --}}
                            <button @click="toggleLike()" class="flex items-center gap-2 px-4 py-2 rounded-lg transition-colors"
                                :class="liked ? 'bg-red-50 text-red-600' : 'hover:bg-gray-100 text-gray-600'">
                                <svg class="w-5 h-5" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span class="text-sm font-medium" x-text="likesCount"></span>
                            </button>
                            {{-- Bookmark Button --}}
                            <button class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">{{ number_format($post->bookmarks_count ?? 0) }}</span>
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            @auth('designer')
                            <button
                                @click="$dispatch('share-marketplace-post', { postId: {{ $post->id }}, postTitle: '{{ addslashes($post->title) }}' })"
                                type="button"
                                class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-1.5"
                                title="{{ __('Share with users') }}"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ __('Send') }}</span>
                            </button>
                            @endauth
                            <x-share-button
                                :url="route('marketplace.show', ['locale' => app()->getLocale(), 'id' => $post->id])"
                                :title="$post->title"
                                :description="Str::limit($post->description, 150)"
                                variant="text"
                                size="md"
                            />
                        </div>
                    </div>

                    {{-- Post Info --}}
                    <div class="p-6">
                        {{-- Type & Category Badges --}}
                        <div class="flex flex-wrap gap-2 mb-4">
                            @php
                                $typeBadges = [
                                    'service' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => __('Service')],
                                    'collaboration' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => __('Collaboration')],
                                    'showcase' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => __('Showcase')],
                                    'opportunity' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'label' => __('Opportunity')],
                                ];
                                $typeBadge = $typeBadges[$post->type] ?? $typeBadges['showcase'];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $typeBadge['bg'] }} {{ $typeBadge['text'] }}">
                                {{ $typeBadge['label'] }}
                            </span>
                            @if($post->category)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                                    {{ $post->category }}
                                </span>
                            @endif
                        </div>

                        {{-- Title --}}
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>

                        {{-- Stats --}}
                        <div class="flex items-center gap-4 text-sm text-gray-500 mb-6 pb-6 border-b">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span>{{ number_format($post->comments_count) }} {{ __('comments') }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ $post->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="prose max-w-none mb-6">
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $post->description }}</p>
                        </div>
                    </div>
                </div>

                {{-- Tags Section --}}
                @if(!empty($post->tags) && is_array($post->tags))
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Tags') }}</h3>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($post->tags as $tag)
                                <a href="{{ route('marketplace.index', ['locale' => app()->getLocale(), 'tags' => $tag]) }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                    {{ $tag }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Comments Section --}}
                <div x-data="commentsSection()" class="bg-white rounded-xl p-6 shadow-sm">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">
                        {{ __('Comments') }} (<span x-text="totalComments">{{ number_format($post->comments_count) }}</span>)
                    </h3>

                    {{-- Comment Form (only for logged in users) --}}
                    @auth('designer')
                    <div class="mb-6">
                        <form @submit.prevent="submitComment()">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0">
                                    @if(auth('designer')->user()->avatar)
                                        <img src="{{ url('media/' . auth('designer')->user()->avatar) }}"
                                             alt="{{ auth('designer')->user()->name }}"
                                             class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr(auth('designer')->user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <textarea
                                        x-model="newComment"
                                        :disabled="submitting"
                                        rows="3"
                                        maxlength="1000"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none disabled:bg-gray-50"
                                        placeholder="{{ __('Write a comment...') }}"
                                    ></textarea>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-xs text-gray-500" x-text="newComment.length + '/1000'"></span>
                                        <button
                                            type="submit"
                                            :disabled="submitting || !newComment.trim()"
                                            class="px-4 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:from-blue-700 hover:to-green-600 transition-all font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                        >
                                            <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            {{ __('Post Comment') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg text-center">
                        <p class="text-gray-600 mb-2">{{ __('You must be logged in to comment.') }}</p>
                        <a href="{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}" class="text-blue-600 hover:underline font-medium">{{ __('Login here') }}</a>
                    </div>
                    @endauth

                    {{-- Comments List --}}
                    <div class="space-y-4">
                        {{-- Loading state --}}
                        <div x-show="loading" class="text-center py-8">
                            <svg class="w-8 h-8 animate-spin mx-auto text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <p class="text-gray-500 mt-2">{{ __('Loading comments...') }}</p>
                        </div>

                        {{-- Empty state --}}
                        <div x-show="!loading && comments.length === 0" class="text-center py-8 text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p>{{ __('No comments yet. Be the first to comment!') }}</p>
                        </div>

                        {{-- Comments --}}
                        <template x-for="comment in comments" :key="comment.id">
                            <div class="border-b border-gray-100 pb-4 last:border-0">
                                {{-- Comment --}}
                                <div class="flex gap-3">
                                    <a :href="'{{ url(app()->getLocale()) }}/designer/' + comment.designer.id" class="flex-shrink-0">
                                        <template x-if="comment.designer.avatar">
                                            <img :src="comment.designer.avatar" :alt="comment.designer.name" class="w-10 h-10 rounded-full object-cover">
                                        </template>
                                        <template x-if="!comment.designer.avatar">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white font-bold text-sm" x-text="comment.designer.name.charAt(0).toUpperCase()"></div>
                                        </template>
                                    </a>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <a :href="'{{ url(app()->getLocale()) }}/designer/' + comment.designer.id" class="font-semibold text-gray-900 hover:text-blue-600" x-text="comment.designer.name"></a>
                                            <span class="text-xs text-gray-500" x-text="comment.created_at_human"></span>
                                            <span x-show="comment.is_edited" class="text-xs text-gray-400">({{ __('edited') }})</span>
                                        </div>

                                        {{-- Edit mode --}}
                                        <template x-if="editingCommentId === comment.id">
                                            <div class="mt-2">
                                                <textarea
                                                    x-model="editContent"
                                                    rows="2"
                                                    maxlength="1000"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none text-sm"
                                                ></textarea>
                                                <div class="flex gap-2 mt-2">
                                                    <button @click="updateComment(comment.id)" :disabled="editSubmitting" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:opacity-50">{{ __('Save') }}</button>
                                                    <button @click="cancelEdit()" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">{{ __('Cancel') }}</button>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Normal view --}}
                                        <template x-if="editingCommentId !== comment.id">
                                            <p class="text-gray-700 mt-1 whitespace-pre-line" x-text="comment.content"></p>
                                        </template>

                                        {{-- Actions --}}
                                        <div class="flex items-center gap-3 mt-2 text-sm" x-show="editingCommentId !== comment.id">
                                            @auth('designer')
                                            <button @click="startReply(comment.id)" class="text-gray-500 hover:text-blue-600">{{ __('Reply') }}</button>
                                            <template x-if="comment.is_owner">
                                                <button @click="startEdit(comment)" class="text-gray-500 hover:text-blue-600">{{ __('Edit') }}</button>
                                            </template>
                                            <template x-if="comment.is_owner || isPostOwner">
                                                <button @click="deleteComment(comment.id)" class="text-gray-500 hover:text-red-600">{{ __('Delete') }}</button>
                                            </template>
                                            @endauth
                                        </div>

                                        {{-- Reply Form --}}
                                        @auth('designer')
                                        <div x-show="replyingToId === comment.id" class="mt-3">
                                            <textarea
                                                x-model="replyContent"
                                                rows="2"
                                                maxlength="1000"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none text-sm"
                                                :placeholder="'{{ __('Reply to') }} ' + comment.designer.name + '...'"
                                            ></textarea>
                                            <div class="flex gap-2 mt-2">
                                                <button @click="submitReply(comment.id)" :disabled="replySubmitting || !replyContent.trim()" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:opacity-50">{{ __('Reply') }}</button>
                                                <button @click="cancelReply()" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">{{ __('Cancel') }}</button>
                                            </div>
                                        </div>
                                        @endauth

                                        {{-- Replies --}}
                                        <template x-if="comment.replies && comment.replies.length > 0">
                                            <div class="mt-3 pl-4 border-l-2 border-gray-100 space-y-3">
                                                <template x-for="reply in comment.replies" :key="reply.id">
                                                    <div class="flex gap-3">
                                                        <a :href="'{{ url(app()->getLocale()) }}/designer/' + reply.designer.id" class="flex-shrink-0">
                                                            <template x-if="reply.designer.avatar">
                                                                <img :src="reply.designer.avatar" :alt="reply.designer.name" class="w-8 h-8 rounded-full object-cover">
                                                            </template>
                                                            <template x-if="!reply.designer.avatar">
                                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white font-bold text-xs" x-text="reply.designer.name.charAt(0).toUpperCase()"></div>
                                                            </template>
                                                        </a>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center gap-2 flex-wrap">
                                                                <a :href="'{{ url(app()->getLocale()) }}/designer/' + reply.designer.id" class="font-semibold text-gray-900 hover:text-blue-600 text-sm" x-text="reply.designer.name"></a>
                                                                <span class="text-xs text-gray-500" x-text="reply.created_at_human"></span>
                                                                <span x-show="reply.is_edited" class="text-xs text-gray-400">({{ __('edited') }})</span>
                                                            </div>
                                                            <p class="text-gray-700 text-sm mt-1 whitespace-pre-line" x-text="reply.content"></p>
                                                            <div class="flex items-center gap-3 mt-1 text-xs">
                                                                @auth('designer')
                                                                <template x-if="reply.is_owner || isPostOwner">
                                                                    <button @click="deleteComment(reply.id, comment.id)" class="text-gray-500 hover:text-red-600">{{ __('Delete') }}</button>
                                                                </template>
                                                                @endauth
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Load More --}}
                        <div x-show="hasMore && !loading" class="text-center pt-4">
                            <button @click="loadMore()" :disabled="loadingMore" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50">
                                <span x-show="!loadingMore">{{ __('Load More Comments') }}</span>
                                <span x-show="loadingMore">{{ __('Loading...') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Author Card --}}
                @if($post->designer)
                    @php
                        $designer = $post->designer;
                        $designerAvatar = $designer->avatar ? url('media/' . $designer->avatar) : null;
                    @endphp
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <div class="text-center">
                            {{-- Avatar --}}
                            <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex items-center justify-center text-white text-2xl font-bold border-4 border-gray-100">
                                @if($designerAvatar)
                                    <x-optimized-image
                                        :src="$designerAvatar"
                                        :alt="$designer->name"
                                        eager="true"
                                        aspect-ratio="1/1"
                                        class="w-full h-full"
                                        object-fit="cover"
                                    />
                                @else
                                    {{ strtoupper(substr($designer->name, 0, 1)) }}
                                @endif
                            </div>

                            {{-- Name --}}
                            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $designer->name }}</h3>
                            @if($designer->bio)
                                <p class="text-sm text-gray-600 mb-4">{{ Str::limit($designer->bio, 60) }}</p>
                            @endif

                            {{-- Stats --}}
                            <div class="flex justify-center gap-6 mb-4 pb-4 border-b">
                                <div class="text-center">
                                    <div class="text-lg font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                                        {{ number_format($designer->followers_count ?? 0) }}
                                    </div>
                                    <div class="text-xs text-gray-600">{{ __('Followers') }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">
                                        {{ number_format($designer->projects_count ?? 0) }}
                                    </div>
                                    <div class="text-xs text-gray-600">{{ __('Projects') }}</div>
                                </div>
                            </div>

                            {{-- Location & Member Since --}}
                            <div class="space-y-2 mb-4 text-sm text-gray-600">
                                @if($designer->location)
                                    <div class="flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>{{ $designer->location }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>{{ __('Member since') }} {{ $designer->created_at->format('Y') }}</span>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="space-y-2">
                                <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}" class="block w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:from-blue-700 hover:to-green-600 transition-all text-center font-medium">
                                    {{ __('View Profile') }}
                                </a>
                                @auth('designer')
                                    @if(auth('designer')->id() !== $designer->id)
                                        <button class="w-full px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                                            {{ __('Connect') }}
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Post Details --}}
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Post Details') }}</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-3">
                            <svg class="w-4 h-4 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div class="flex-1">
                                <div class="text-gray-500">{{ __('Posted') }}</div>
                                <div class="font-medium text-gray-900">{{ $post->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-4 h-4 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <div class="flex-1">
                                <div class="text-gray-500">{{ __('Comments') }}</div>
                                <div class="font-medium text-gray-900">{{ number_format($post->comments_count) }} {{ __('comments') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact --}}
                @if($post->designer)
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Contact') }}</h3>
                        <div class="space-y-2">
                            {{-- Send Message Button (uses message request system) --}}
                            @auth('designer')
                                @if(auth('designer')->id() !== $post->designer->id && $post->designer->allow_messages)
                                    <button
                                        id="messageRequestBtn"
                                        onclick="sendMessageRequest({{ $post->designer->id }})"
                                        class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:from-blue-700 hover:to-green-600 transition-all font-medium"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                        {{ __('Contact') }}
                                    </button>
                                @endif
                            @else
                                <a href="{{ route('login', ['locale' => app()->getLocale(), 'redirect' => url()->current()]) }}" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:from-blue-700 hover:to-green-600 transition-all font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    {{ __('Contact') }}
                                </a>
                            @endauth

                            {{-- Website Link --}}
                            @if($post->designer->website)
                                <a href="{{ $post->designer->website }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 w-full px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    {{ __('Visit Website') }}
                                </a>
                            @endif

                            {{-- Email Link (only if designer allows showing email) --}}
                            @if($post->designer->email && $post->designer->show_email)
                                <a href="mailto:{{ $post->designer->email }}" class="flex items-center gap-2 w-full px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    {{ __('Send Email') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Related Posts --}}
                @if($relatedPosts && $relatedPosts->count() > 0)
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('More from :name', ['name' => $post->designer->name]) }}</h3>
                        <div class="space-y-3">
                            @foreach($relatedPosts as $relatedPost)
                                <a href="{{ route('marketplace.show', ['locale' => app()->getLocale(), 'id' => $relatedPost->id]) }}" class="flex gap-3 hover:bg-gray-50 rounded-lg p-2 -m-2 transition-colors">
                                    <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                        @if($relatedPost->image)
                                            <x-optimized-image
                                                :src="url('media/' . $relatedPost->image)"
                                                :alt="$relatedPost->title"
                                                eager="false"
                                                aspect-ratio="1/1"
                                                class="w-full h-full"
                                                object-fit="cover"
                                            />
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 line-clamp-2 mb-1">
                                            {{ $relatedPost->title }}
                                        </h4>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Share to Users Modal --}}
@auth('designer')
<x-modal.share-post />
@endauth

@push('scripts')
<script>
// Check for pending message request on page load
document.addEventListener('DOMContentLoaded', function() {
    @auth('designer')
    @if($post->designer && auth('designer')->id() !== $post->designer->id && $post->designer->allow_messages)
    checkPendingMessageRequest({{ $post->designer->id }});
    @endif
    @endauth
});

// Check for pending message request
async function checkPendingMessageRequest(designerId) {
    try {
        const response = await fetch('{{ url(app()->getLocale()) }}/messages/check-pending-request/' + designerId, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success && data.has_pending_request) {
            updateMessageButtonToPending();
        }
    } catch (error) {
        console.error('Error checking pending request:', error);
    }
}

// Update button to show pending state
function updateMessageButtonToPending() {
    const btn = document.getElementById('messageRequestBtn');
    if (!btn) return;

    btn.disabled = true;
    btn.onclick = null;
    btn.className = 'flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-gray-400 text-white rounded-lg cursor-not-allowed font-medium';
    btn.innerHTML = `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ __('Request Sent') }}
    `;
}

// Custom Modal Functions
function showModal(title, message, type = 'success') {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4';
    modal.style.animation = 'fadeIn 0.2s ease-out';

    const iconColors = {
        success: 'from-green-500 to-blue-500',
        error: 'from-red-500 to-pink-500',
        warning: 'from-yellow-500 to-orange-500',
        info: 'from-blue-600 to-green-500'
    };

    const icons = {
        success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
        error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
        warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
    };

    modal.innerHTML = `
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm" onclick="this.parentElement.remove()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform" style="animation: scaleIn 0.2s ease-out;">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-r ${iconColors[type]} flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${icons[type]}
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">${title}</h3>
                </div>
                <p class="text-gray-600 mb-6 ml-16">${message}</p>
                <div class="flex justify-end">
                    <button onclick="this.closest('.fixed').remove()"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                        OK
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Add CSS animations if not already present
    if (!document.getElementById('modal-animations')) {
        const style = document.createElement('style');
        style.id = 'modal-animations';
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes scaleIn {
                from { transform: scale(0.9); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
}

function showConfirm(title, message, onConfirm) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4';
    modal.style.animation = 'fadeIn 0.2s ease-out';

    modal.innerHTML = `
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform" style="animation: scaleIn 0.2s ease-out;">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">${title}</h3>
                </div>
                <p class="text-gray-600 mb-6 ml-16">${message}</p>
                <div class="flex gap-3 justify-end">
                    <button class="cancel-btn px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all">
                        Cancel
                    </button>
                    <button class="confirm-btn px-6 py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                        Send Request
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Add event listeners
    const cancelBtn = modal.querySelector('.cancel-btn');
    const confirmBtn = modal.querySelector('.confirm-btn');
    const backdrop = modal.querySelector('.absolute');

    cancelBtn.addEventListener('click', () => modal.remove());
    backdrop.addEventListener('click', () => modal.remove());
    confirmBtn.addEventListener('click', () => {
        modal.remove();
        onConfirm();
    });
}

// Send Message Request functionality
async function sendMessageRequest(designerId) {
    showConfirm(
        '{{ __("Send Message Request") }}',
        '{{ __("Send a message request to this designer? They will be able to accept or decline.") }}',
        async function() {
            try {
                const response = await fetch('{{ url(app()->getLocale()) }}/messages/send-request/' + designerId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showModal('{{ __("Success!") }}', data.message || '{{ __("Message request sent successfully!") }}', 'success');
                    // Update button to show pending state
                    updateMessageButtonToPending();
                } else {
                    showModal('{{ __("Error") }}', data.message || '{{ __("Failed to send message request.") }}', 'error');
                }
            } catch (error) {
                console.error('Error sending message request:', error);
                showModal('{{ __("Error") }}', '{{ __("An error occurred. Please try again.") }}', 'error');
            }
        }
    );
}

// Comments Section Alpine.js Component
function commentsSection() {
    return {
        comments: [],
        totalComments: {{ $post->comments_count ?? 0 }},
        loading: true,
        loadingMore: false,
        hasMore: false,
        currentPage: 1,
        isPostOwner: {{ auth('designer')->check() && auth('designer')->id() === $post->designer_id ? 'true' : 'false' }},

        // New comment form
        newComment: '',
        submitting: false,

        // Reply form
        replyingToId: null,
        replyContent: '',
        replySubmitting: false,

        // Edit form
        editingCommentId: null,
        editContent: '',
        editSubmitting: false,

        init() {
            this.loadComments();
        },

        async loadComments() {
            this.loading = true;
            try {
                const response = await fetch('{{ url(app()->getLocale()) }}/marketplace/{{ $post->id }}/comments?page=' + this.currentPage, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (data.success) {
                    this.comments = data.comments;
                    this.hasMore = data.pagination.has_more;
                    this.totalComments = data.pagination.total;
                }
            } catch (error) {
                console.error('Error loading comments:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadMore() {
            if (this.loadingMore || !this.hasMore) return;

            this.loadingMore = true;
            this.currentPage++;

            try {
                const response = await fetch('{{ url(app()->getLocale()) }}/marketplace/{{ $post->id }}/comments?page=' + this.currentPage, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (data.success) {
                    this.comments = [...this.comments, ...data.comments];
                    this.hasMore = data.pagination.has_more;
                }
            } catch (error) {
                console.error('Error loading more comments:', error);
                this.currentPage--;
            } finally {
                this.loadingMore = false;
            }
        },

        async submitComment() {
            if (!this.newComment.trim() || this.submitting) return;

            this.submitting = true;
            try {
                const response = await fetch('{{ url(app()->getLocale()) }}/marketplace/{{ $post->id }}/comments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ content: this.newComment })
                });

                const data = await response.json();

                if (data.success) {
                    this.comments.unshift(data.comment);
                    this.totalComments++;
                    this.newComment = '';
                } else {
                    alert(data.message || '{{ __("Failed to post comment.") }}');
                }
            } catch (error) {
                console.error('Error posting comment:', error);
                alert('{{ __("An error occurred. Please try again.") }}');
            } finally {
                this.submitting = false;
            }
        },

        startReply(commentId) {
            this.replyingToId = commentId;
            this.replyContent = '';
        },

        cancelReply() {
            this.replyingToId = null;
            this.replyContent = '';
        },

        async submitReply(parentId) {
            if (!this.replyContent.trim() || this.replySubmitting) return;

            this.replySubmitting = true;
            try {
                const response = await fetch('{{ url(app()->getLocale()) }}/marketplace/{{ $post->id }}/comments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content: this.replyContent,
                        parent_id: parentId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Find parent comment and add reply
                    const parentComment = this.comments.find(c => c.id === parentId);
                    if (parentComment) {
                        if (!parentComment.replies) parentComment.replies = [];
                        parentComment.replies.push(data.comment);
                    }
                    this.totalComments++;
                    this.cancelReply();
                } else {
                    alert(data.message || '{{ __("Failed to post reply.") }}');
                }
            } catch (error) {
                console.error('Error posting reply:', error);
                alert('{{ __("An error occurred. Please try again.") }}');
            } finally {
                this.replySubmitting = false;
            }
        },

        startEdit(comment) {
            this.editingCommentId = comment.id;
            this.editContent = comment.content;
        },

        cancelEdit() {
            this.editingCommentId = null;
            this.editContent = '';
        },

        async updateComment(commentId) {
            if (!this.editContent.trim() || this.editSubmitting) return;

            this.editSubmitting = true;
            try {
                const response = await fetch('{{ url(app()->getLocale()) }}/marketplace/{{ $post->id }}/comments/' + commentId, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ content: this.editContent })
                });

                const data = await response.json();

                if (data.success) {
                    // Update comment in list
                    const commentIndex = this.comments.findIndex(c => c.id === commentId);
                    if (commentIndex !== -1) {
                        this.comments[commentIndex] = data.comment;
                    }
                    this.cancelEdit();
                } else {
                    alert(data.message || '{{ __("Failed to update comment.") }}');
                }
            } catch (error) {
                console.error('Error updating comment:', error);
                alert('{{ __("An error occurred. Please try again.") }}');
            } finally {
                this.editSubmitting = false;
            }
        },

        async deleteComment(commentId, parentId = null) {
            if (!confirm('{{ __("Are you sure you want to delete this comment?") }}')) return;

            try {
                const response = await fetch('{{ url(app()->getLocale()) }}/marketplace/{{ $post->id }}/comments/' + commentId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    if (parentId) {
                        // Delete reply
                        const parentComment = this.comments.find(c => c.id === parentId);
                        if (parentComment && parentComment.replies) {
                            parentComment.replies = parentComment.replies.filter(r => r.id !== commentId);
                        }
                        this.totalComments--;
                    } else {
                        // Delete top-level comment (and count its replies)
                        const comment = this.comments.find(c => c.id === commentId);
                        const repliesCount = comment && comment.replies ? comment.replies.length : 0;
                        this.comments = this.comments.filter(c => c.id !== commentId);
                        this.totalComments -= (1 + repliesCount);
                    }
                } else {
                    alert(data.message || '{{ __("Failed to delete comment.") }}');
                }
            } catch (error) {
                console.error('Error deleting comment:', error);
                alert('{{ __("An error occurred. Please try again.") }}');
            }
        }
    };
}
</script>
@endpush
@endsection
