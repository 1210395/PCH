{{-- Share Post Modal - Shown after marketplace post creation --}}
<div x-data="{
        show: false,
        postId: null,
        postTitle: '',
        postUrl: '',
        copied: false,
        searchQuery: '',
        searchResults: [],
        selectedUsers: [],
        searching: false,
        sharing: false,
        shared: false,
        maxUsers: 10,
        searchTimeout: null,

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
            this.sharing = false;
            this.shared = false;
        },

        closeShare() {
            this.show = false;
            location.reload();
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

        async searchUsers() {
            if (this.searchQuery.length < 2) return;
            this.searching = true;
            try {
                const response = await fetch('/{{ app()->getLocale() }}/designers/search-users?q=' + encodeURIComponent(this.searchQuery), {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }
                });
                const data = await response.json();
                if (data.success) {
                    // Filter out already selected users
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
            this.searchQuery = '';
            this.searchResults = [];
        },

        removeUser(userId) {
            this.selectedUsers = this.selectedUsers.filter(u => u.id !== userId);
        },

        async shareToSelectedUsers() {
            if (this.selectedUsers.length === 0) return;
            this.sharing = true;
            try {
                const response = await fetch('/{{ app()->getLocale() }}/marketplace-posts/' + this.postId + '/share', {
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
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        {{-- Header --}}
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900">{{ __('Share Your Post!') }}</h3>
                <p class="text-sm text-gray-500 mt-0.5" x-text="postTitle"></p>
            </div>
            <button @click="closeShare()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-5 space-y-5">
            {{-- External Share Section --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('Share on Social Media') }}</h4>
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Facebook --}}
                    <button @click="shareToFacebook()" type="button" title="Facebook"
                            class="w-10 h-10 rounded-full bg-blue-100 hover:bg-blue-200 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </button>
                    {{-- X/Twitter --}}
                    <button @click="shareToTwitter()" type="button" title="X (Twitter)"
                            class="w-10 h-10 rounded-full bg-gray-900 hover:bg-gray-700 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </button>
                    {{-- LinkedIn --}}
                    <button @click="shareToLinkedIn()" type="button" title="LinkedIn"
                            class="w-10 h-10 rounded-full bg-blue-700 hover:bg-blue-800 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </button>
                    {{-- WhatsApp --}}
                    <button @click="shareToWhatsApp()" type="button" title="WhatsApp"
                            class="w-10 h-10 rounded-full bg-green-500 hover:bg-green-600 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </button>
                    {{-- Telegram --}}
                    <button @click="shareToTelegram()" type="button" title="Telegram"
                            class="w-10 h-10 rounded-full bg-sky-500 hover:bg-sky-600 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                    </button>
                    {{-- Copy Link --}}
                    <button @click="copyLink()" type="button" title="Copy Link"
                            class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                            :class="copied ? 'bg-green-100 text-green-600' : 'bg-gray-100 hover:bg-gray-200 text-gray-600'">
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <svg x-show="copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="border-t border-gray-100"></div>

            {{-- Internal Share Section --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('Share with Users on the Platform') }}</h4>

                {{-- Success state --}}
                <template x-if="shared">
                    <div class="text-center py-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-green-700">{{ __('Shared successfully!') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Users will be notified about your post.') }}</p>
                    </div>
                </template>

                {{-- Search & select UI --}}
                <template x-if="!shared">
                    <div>
                        {{-- Selected users chips --}}
                        <div x-show="selectedUsers.length > 0" class="flex flex-wrap gap-1.5 mb-3">
                            <template x-for="user in selectedUsers" :key="user.id">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                    <img :src="user.avatar || '/images/default-avatar.png'" class="w-4 h-4 rounded-full object-cover" :alt="user.name">
                                    <span x-text="user.name"></span>
                                    <button @click="removeUser(user.id)" type="button" class="ml-0.5 text-blue-400 hover:text-blue-600">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>

                        {{-- Search input --}}
                        <div class="relative" x-show="selectedUsers.length < maxUsers">
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="{{ __('Search users by name...') }}"
                                   class="w-full px-4 py-2.5 pr-10 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                <svg x-show="!searching" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <svg x-show="searching" class="w-4 h-4 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>

                            {{-- Search results dropdown --}}
                            <div x-show="searchResults.length > 0"
                                 x-transition
                                 class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden z-10 max-h-48 overflow-y-auto">
                                <template x-for="user in searchResults" :key="user.id">
                                    <button @click="selectUser(user)" type="button"
                                            class="w-full px-3 py-2.5 text-left hover:bg-gray-50 flex items-center gap-3 transition-colors">
                                        <img :src="user.avatar || '/images/default-avatar.png'" class="w-8 h-8 rounded-full object-cover flex-shrink-0" :alt="user.name">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate" x-text="user.name"></p>
                                            <p class="text-xs text-gray-500 truncate" x-text="user.city || ''"></p>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <p x-show="selectedUsers.length >= maxUsers" class="text-xs text-amber-600 mt-1">{{ __('Maximum of 10 users reached.') }}</p>
                        <p x-show="searchQuery.length >= 2 && searchResults.length === 0 && !searching" class="text-xs text-gray-500 mt-2">{{ __('No users found.') }}</p>

                        {{-- Share button --}}
                        <button x-show="selectedUsers.length > 0"
                                @click="shareToSelectedUsers()"
                                :disabled="sharing"
                                type="button"
                                class="mt-3 w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm font-medium rounded-xl transition-colors flex items-center justify-center gap-2">
                            <svg x-show="sharing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="sharing ? '{{ __('Sharing...') }}' : '{{ __('Share with') }} ' + selectedUsers.length + ' {{ __('user(s)') }}'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 pb-5">
            <button @click="closeShare()" type="button" class="w-full text-center text-sm text-gray-500 hover:text-gray-700 py-2 transition-colors">
                {{ __('Skip — I\'ll share later') }}
            </button>
        </div>
    </div>
</div>
