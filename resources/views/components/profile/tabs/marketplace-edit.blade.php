@props(['items' => []])

<div x-show="activeTab === 'marketplace'" x-cloak class="space-y-6">
    {{-- Header with Add Button --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-gradient-to-r from-purple-600 to-pink-500 rounded-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('Marketplace Posts') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Share your work with the community') }}</p>
            </div>
        </div>
        <button @click="openMarketplaceModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 text-white font-medium rounded-lg hover:from-purple-700 hover:to-pink-600 transition-all duration-200 shadow-md hover:shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>{{ __('Create Post') }}</span>
        </button>
    </div>

    {{-- Marketplace Posts Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="(post, index) in marketplacePosts" :key="post.id || index">
            <div class="group relative bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-all duration-200">
                {{-- Status Badge --}}
                <div class="absolute top-2 right-2 z-10">
                    <span x-show="post.approval_status === 'pending'"
                          class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        {{ __('Pending') }}
                    </span>
                    <span x-show="post.approval_status === 'rejected'"
                          class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ __('Rejected') }}
                    </span>
                    <span x-show="post.approval_status === 'approved'"
                          class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ __('Approved') }}
                    </span>
                </div>

                {{-- Post Image --}}
                <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                    <template x-if="post.image">
                        <img :src="post.image" :alt="post.title" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!post.image">
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-100 to-pink-100">
                            <svg class="w-12 h-12 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                    </template>
                </div>

                {{-- Post Info --}}
                <div class="p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span x-show="post.type" class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700" x-text="post.type"></span>
                        <span x-show="post.category" class="px-2 py-0.5 rounded-full text-xs font-medium bg-pink-50 text-pink-700" x-text="post.category"></span>
                    </div>
                    <h3 class="font-semibold text-gray-900 line-clamp-1" x-text="post.title"></h3>
                    <p class="text-sm text-gray-600 line-clamp-2 mt-1" x-text="post.description"></p>

                    {{-- Tags --}}
                    <div x-show="post.tags && post.tags.length > 0" class="flex flex-wrap gap-1 mt-2">
                        <template x-for="tag in (post.tags || []).slice(0, 3)" :key="tag">
                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600" x-text="'#' + tag"></span>
                        </template>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="px-4 pb-4 pt-2 flex gap-2 border-t border-gray-100">
                    <button @click="editMarketplacePost(post)"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-purple-700 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('Edit') }}
                    </button>
                    <button @click="deleteMarketplacePost(post.id, index)"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        {{-- Empty State --}}
        <template x-if="marketplacePosts.length === 0">
            <div class="col-span-full bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('No marketplace posts yet') }}</h3>
                <p class="text-gray-600 mb-4">{{ __('Create your first post to share your work with the community') }}</p>
                <button @click="openMarketplaceModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-500 text-white font-medium rounded-lg hover:from-purple-700 hover:to-pink-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Create Post') }}
                </button>
            </div>
        </template>
    </div>
</div>
