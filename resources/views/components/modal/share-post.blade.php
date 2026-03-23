{{-- Share Post Modal - Enhanced with suggestions & better search --}}
<div x-data="{
        show: false,
        postId: null,
        postTitle: '',
        postUrl: '',
        copied: false,
        searchQuery: '',
        searchResults: [],
        suggestedUsers: [],
        selectedUsers: [],
        searching: false,
        sharing: false,
        shared: false,
        loadingSuggestions: false,
        maxUsers: 10,
        searchTimeout: null,
        activeTab: 'people',

        init() {
            this.$watch('searchQuery', (value) => {
                clearTimeout(this.searchTimeout);
                if (value.length < 2) {
                    this.searchResults = [];
                    return;
                }
                this.searchTimeout = setTimeout(() => this.searchUsers(), 300);
            });
        },

        openShare(postId, postTitle) {
            this.postId = postId;
            this.postTitle = postTitle;
            this.postUrl = window.location.origin + '/{{ app()->getLocale() }}/marketplace/' + postId;
            this.show = true;
            this.copied = false;
            this.searchQuery = '';
            this.searchResults = [];
            this.selectedUsers = [];
            this.suggestedUsers = [];
            this.sharing = false;
            this.shared = false;
            this.activeTab = 'people';
            this.loadSuggestions();
        },

        closeShare() {
            this.show = false;
        },

        async copyLink() {
            try {
                await navigator.clipboard.writeText(this.postUrl);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            } catch (err) {
                const textArea = document.createElement('textarea');
                textArea.value = this.postUrl;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            }
        },

        shareToFacebook() {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(this.postUrl), '_blank', 'width=600,height=400');
        },
        shareToTwitter() {
            window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(this.postUrl) + '&text=' + encodeURIComponent(this.postTitle), '_blank', 'width=600,height=400');
        },
        shareToLinkedIn() {
            window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(this.postUrl), '_blank', 'width=600,height=400');
        },
        shareToWhatsApp() {
            window.open('https://wa.me/?text=' + encodeURIComponent(this.postTitle + ' ' + this.postUrl), '_blank');
        },
        shareToTelegram() {
            window.open('https://t.me/share/url?url=' + encodeURIComponent(this.postUrl) + '&text=' + encodeURIComponent(this.postTitle), '_blank');
        },

        async loadSuggestions() {
            this.loadingSuggestions = true;
            try {
                const response = await fetch('{{ url(app()->getLocale()) }}/designers/suggested-users', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }
                });
                const data = await response.json();
                if (data.success) {
                    this.suggestedUsers = data.users;
                }
            } catch (error) {
                console.error('Suggestions error:', error);
            } finally {
                this.loadingSuggestions = false;
            }
        },

        async searchUsers() {
            if (this.searchQuery.length < 2) return;
            this.searching = true;
            try {
                const response = await fetch('{{ url(app()->getLocale()) }}/designers/search-users?q=' + encodeURIComponent(this.searchQuery), {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }
                });
                const data = await response.json();
                if (data.success) {
                    const selectedIds = this.selectedUsers.map(u => u.id);
                    this.searchResults = data.users.filter(u => !selectedIds.includes(u.id));
                }
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                this.searching = false;
            }
        },

        selectUser(user) {
            if (this.selectedUsers.length >= this.maxUsers) return;
            if (this.selectedUsers.find(u => u.id === user.id)) return;
            this.selectedUsers.push(user);
            this.searchResults = this.searchResults.filter(u => u.id !== user.id);
            this.suggestedUsers = this.suggestedUsers.filter(u => u.id !== user.id);
            this.searchQuery = '';
            this.searchResults = [];
        },

        removeUser(userId) {
            this.selectedUsers = this.selectedUsers.filter(u => u.id !== userId);
        },

        isSelected(userId) {
            return this.selectedUsers.some(u => u.id === userId);
        },

        getReasonLabel(reason) {
            const labels = {
                'follower': '{{ __('Follows you') }}',
                'following': '{{ __('You follow') }}',
                'same_sector': '{{ __('Same field') }}',
                'same_city': '{{ __('Same city') }}'
            };
            return labels[reason] || '';
        },

        async shareToSelectedUsers() {
            if (this.selectedUsers.length === 0) return;
            this.sharing = true;
            try {
                const response = await fetch('{{ url(app()->getLocale()) }}/marketplace-posts/' + this.postId + '/share', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content
                    },
                    body: JSON.stringify({ user_ids: this.selectedUsers.map(u => u.id) })
                });
                const data = await response.json();
                if (data.success) {
                    this.shared = true;
                }
            } catch (error) {
                console.error('Share error:', error);
            } finally {
                this.sharing = false;
            }
        }
    }"
     x-show="show"
     x-cloak
     @share-marketplace-post.window="openShare($event.detail.postId, $event.detail.postTitle)"
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
     style="display: none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeShare()"></div>

    {{-- Modal --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        {{-- Header --}}
        <div class="flex items-center justify-between p-5 border-b border-gray-100 flex-shrink-0">
            <div class="min-w-0 flex-1">
                <h3 class="text-lg font-bold text-gray-900">{{ __('Share Post') }}</h3>
                <p class="text-sm text-gray-500 mt-0.5 truncate" x-text="postTitle"></p>
            </div>
            <button @click="closeShare()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors ml-3 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-gray-100 flex-shrink-0">
            <button @click="activeTab = 'people'" type="button"
                    :class="activeTab === 'people' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex-1 px-4 py-3 text-sm font-medium border-b-2 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ __('People') }}
                <span x-show="selectedUsers.length > 0" x-text="selectedUsers.length"
                      class="bg-blue-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center"></span>
            </button>
            <button @click="activeTab = 'social'" type="button"
                    :class="activeTab === 'social' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex-1 px-4 py-3 text-sm font-medium border-b-2 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
                {{ __('Social Media') }}
            </button>
        </div>

        {{-- Scrollable Content --}}
        <div class="flex-1 overflow-y-auto">

            {{-- People Tab --}}
            <div x-show="activeTab === 'people'" class="p-5 space-y-4">

                {{-- Success state --}}
                <template x-if="shared">
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-base font-semibold text-green-700">{{ __('Shared successfully!') }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Users will be notified about your post.') }}</p>
                    </div>
                </template>

                {{-- Search & select UI --}}
                <template x-if="!shared">
                    <div class="space-y-4">

                        {{-- Search input --}}
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                <svg x-show="!searching" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <svg x-show="searching" class="w-5 h-5 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="{{ __('Search by name, sector, or city...') }}"
                                   class="w-full pl-11 pr-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white outline-none transition-all">

                            {{-- Search results dropdown --}}
                            <div x-show="searchResults.length > 0"
                                 x-transition
                                 class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden z-10 max-h-56 overflow-y-auto">
                                <template x-for="user in searchResults" :key="user.id">
                                    <button @click="selectUser(user)" type="button"
                                            class="w-full px-4 py-3 text-left hover:bg-blue-50 flex items-center gap-3 transition-colors border-b border-gray-50 last:border-0">
                                        <div class="relative flex-shrink-0">
                                            <img :src="user.avatar || '/images/default-avatar.png'" class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-100" :alt="user.name">
                                            <template x-if="user.is_following">
                                                <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center ring-2 ring-white">
                                                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-gray-900 truncate" x-text="user.name"></p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <template x-if="user.title">
                                                    <span class="text-xs text-gray-500 truncate" x-text="user.title"></span>
                                                </template>
                                                <template x-if="user.title && user.city">
                                                    <span class="text-gray-300">&middot;</span>
                                                </template>
                                                <template x-if="user.city">
                                                    <span class="text-xs text-gray-400 truncate" x-text="user.city"></span>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="text-xs text-blue-600 font-medium bg-blue-50 px-2 py-1 rounded-full">{{ __('Add') }}</span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <p x-show="searchQuery.length >= 2 && searchResults.length === 0 && !searching" class="text-sm text-gray-500 text-center py-2">{{ __('No users found. Try a different search.') }}</p>

                        {{-- Selected users --}}
                        <div x-show="selectedUsers.length > 0" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('Selected') }} (<span x-text="selectedUsers.length"></span>/<span x-text="maxUsers"></span>)</h4>
                                <button @click="selectedUsers = []; loadSuggestions()" type="button" class="text-xs text-gray-400 hover:text-red-500 transition-colors">{{ __('Clear all') }}</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="user in selectedUsers" :key="user.id">
                                    <span class="inline-flex items-center gap-1.5 pl-1 pr-2 py-1 bg-blue-50 border border-blue-100 text-blue-700 rounded-full text-sm group">
                                        <img :src="user.avatar || '/images/default-avatar.png'" class="w-6 h-6 rounded-full object-cover" :alt="user.name">
                                        <span class="font-medium truncate max-w-[120px]" x-text="user.name"></span>
                                        <button @click="removeUser(user.id)" type="button" class="w-4 h-4 rounded-full bg-blue-200 hover:bg-red-400 hover:text-white flex items-center justify-center transition-colors">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <p x-show="selectedUsers.length >= maxUsers" class="text-xs text-amber-600">{{ __('Maximum of 10 users reached.') }}</p>
                        </div>

                        {{-- Suggested users --}}
                        <div x-show="searchQuery.length < 2 && suggestedUsers.length > 0 && selectedUsers.length < maxUsers">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('Suggested') }}</h4>
                            <div class="space-y-1">
                                <template x-for="user in suggestedUsers" :key="user.id">
                                    <button @click="selectUser(user)" type="button"
                                            class="w-full px-3 py-2.5 text-left hover:bg-blue-50 rounded-xl flex items-center gap-3 transition-colors group">
                                        <div class="relative flex-shrink-0">
                                            <img :src="user.avatar || '/images/default-avatar.png'" class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-100" :alt="user.name">
                                            <template x-if="user.is_following">
                                                <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center ring-2 ring-white">
                                                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-gray-900 truncate" x-text="user.name"></p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <template x-if="user.title">
                                                    <span class="text-xs text-gray-500 truncate" x-text="user.title"></span>
                                                </template>
                                                <template x-if="!user.title && user.sector">
                                                    <span class="text-xs text-gray-500 truncate" x-text="user.sector"></span>
                                                </template>
                                                <template x-if="user.city">
                                                    <span class="text-xs text-gray-400">
                                                        <span class="text-gray-300 mx-1">&middot;</span>
                                                        <span x-text="user.city"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 flex items-center gap-2">
                                            <template x-if="user.reason">
                                                <span class="text-[10px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full hidden sm:inline" x-text="getReasonLabel(user.reason)"></span>
                                            </template>
                                            <span class="w-7 h-7 rounded-full bg-gray-100 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Loading suggestions --}}
                        <div x-show="loadingSuggestions && searchQuery.length < 2" class="flex items-center justify-center py-6">
                            <svg class="w-6 h-6 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>

                        {{-- Empty state --}}
                        <div x-show="!loadingSuggestions && suggestedUsers.length === 0 && searchQuery.length < 2 && selectedUsers.length === 0" class="text-center py-6">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500">{{ __('Search for users to share with') }}</p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Social Media Tab --}}
            <div x-show="activeTab === 'social'" class="p-5 space-y-4">

                {{-- Copy Link --}}
                <button @click="copyLink()" type="button"
                        class="w-full flex items-center gap-4 p-4 rounded-xl border-2 transition-all"
                        :class="copied ? 'border-green-200 bg-green-50' : 'border-gray-100 hover:border-blue-200 hover:bg-blue-50'">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                         :class="copied ? 'bg-green-100' : 'bg-gray-100'">
                        <svg x-show="!copied" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <svg x-show="copied" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="text-left flex-1 min-w-0">
                        <p class="text-sm font-semibold" :class="copied ? 'text-green-700' : 'text-gray-900'" x-text="copied ? '{{ __('Link copied!') }}' : '{{ __('Copy Link') }}'"></p>
                        <p class="text-xs text-gray-400 truncate" x-text="postUrl"></p>
                    </div>
                </button>

                {{-- Social buttons grid --}}
                <div class="grid grid-cols-2 gap-2">
                    <button @click="shareToWhatsApp()" type="button"
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-green-200 hover:bg-green-50 transition-all group">
                        <div class="w-9 h-9 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-green-700">WhatsApp</span>
                    </button>

                    <button @click="shareToFacebook()" type="button"
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50 transition-all group">
                        <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">Facebook</span>
                    </button>

                    <button @click="shareToTwitter()" type="button"
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-300 hover:bg-gray-50 transition-all group">
                        <div class="w-9 h-9 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">X (Twitter)</span>
                    </button>

                    <button @click="shareToLinkedIn()" type="button"
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50 transition-all group">
                        <div class="w-9 h-9 rounded-full bg-blue-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">LinkedIn</span>
                    </button>

                    <button @click="shareToTelegram()" type="button"
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-sky-200 hover:bg-sky-50 transition-all group col-span-2">
                        <div class="w-9 h-9 rounded-full bg-sky-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-sky-700">Telegram</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Footer with share button --}}
        <div class="flex-shrink-0 border-t border-gray-100 p-4 space-y-2">
            <template x-if="activeTab === 'people' && selectedUsers.length > 0 && !shared">
                <button @click="shareToSelectedUsers()"
                        :disabled="sharing"
                        type="button"
                        class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:from-blue-400 disabled:to-blue-400 text-white text-sm font-semibold rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm">
                    <svg x-show="sharing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <svg x-show="!sharing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span x-text="sharing ? '{{ __('Sending...') }}' : '{{ __('Send to') }} ' + selectedUsers.length + ' {{ __('user(s)') }}'"></span>
                </button>
            </template>
            <button @click="closeShare()" type="button" class="w-full text-center text-sm text-gray-500 hover:text-gray-700 py-2 transition-colors">
                {{ __('Close') }}
            </button>
        </div>
    </div>
</div>
