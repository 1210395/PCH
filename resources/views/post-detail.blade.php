@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - Post Detail</title>
<meta name="description" content="View post details, comments, and connect with creators">
@endsection

@section('content')
<!-- START: Laravel Integration Point -->
<!-- Expected data: $post (object) -->
<!-- Properties: id, title, category, badges[], author (object with name, avatar, verified, followers, projects), image, description, tags[], stats (object with likes, views, comments, bookmarks), posted_date, comments[] -->
<!-- END: Laravel Integration Point -->

<div class="min-h-screen bg-gray-50">
    <!-- Back Button -->
    <div class="bg-white border-b">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4">
            <a href="{{ url('/marketplace') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-700 transition-colors group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-medium">Back to Marketplace</span>
            </a>
        </div>
    </div>

    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-12">
        <div class="grid lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Main Content - Left & Center -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Post Image Banner -->
                <div class="bg-white rounded-xl overflow-hidden shadow-sm">
                    <div class="aspect-[16/9] bg-gray-100">
                        <img
                            src="https://images.unsplash.com/photo-1633533447057-56ccf997f4fe?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxicmFuZCUyMGlkZW50aXR5JTIwZGVzaWdufGVufDF8fHx8MTc2MjI0NTk0MXww&ixlib=rb-4.1.0&q=80&w=1080"
                            alt="Post image"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                        <!-- Laravel: src="{{ $post->image }}" alt="{{ $post->title }}" -->
                    </div>
                </div>

                <!-- Post Info -->
                <div class="bg-white rounded-xl p-8 shadow-sm">
                    <!-- Category Badges -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="inline-block px-4 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white text-sm rounded-full font-medium">
                            Services
                        </span>
                        <span class="inline-block px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-500 text-white text-sm rounded-full font-medium">
                            Open for Work
                        </span>
                        <!-- Laravel: Loop through $post->badges -->
                        <!-- @foreach($post->badges as $badge)
                        <span class="inline-block px-4 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white text-sm rounded-full font-medium">
                            {{ $badge }}
                        </span>
                        @endforeach -->
                    </div>

                    <!-- Post Title -->
                    <h1 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900">
                        Offering Brand Identity Design Services
                        <!-- Laravel: {{ $post->title }} -->
                    </h1>

                    <!-- Author Info -->
                    <div class="flex items-center gap-4 pb-6 border-b mb-6">
                        <img
                            src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxwcm9mZXNzaW9uYWwlMjB3b21hbiUyMHBvcnRyYWl0fGVufDF8fHx8MTc2MjI0NTk0MXww&ixlib=rb-4.1.0&q=80&w=1080"
                            alt="Author avatar"
                            class="w-14 h-14 rounded-full object-cover ring-4 ring-blue-100"
                            loading="lazy"
                        >
                        <!-- Laravel: src="{{ $post->author->avatar }}" -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-lg">Layla Ibrahim</h3>
                                <!-- Laravel: {{ $post->author->name }} -->
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    2.4K Followers
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    87 Projects
                                </span>
                                <span class="text-gray-400">5 days ago</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h2 class="text-xl font-bold mb-3">Description</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Professional brand identity design for MSMEs and startups. I specialize in creating memorable logos,
                            comprehensive brand guidelines, and visual identity systems that help businesses stand out in their market.
                            With over 5 years of experience working with companies across various industries, I bring strategic thinking
                            and creative excellence to every project.
                            <!-- Laravel: {{ $post->description }} -->
                        </p>
                        <p class="text-gray-700 leading-relaxed mt-4">
                            My services include logo design, brand color palette development, typography selection, business card design,
                            social media templates, and complete brand style guides. I work closely with clients to understand their vision
                            and deliver designs that truly represent their brand values.
                        </p>
                    </div>

                    <!-- Tags -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Tags</h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm cursor-pointer transition-colors duration-300">
                                Branding
                            </span>
                            <span class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm cursor-pointer transition-colors duration-300">
                                Logo Design
                            </span>
                            <span class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm cursor-pointer transition-colors duration-300">
                                Visual Identity
                            </span>
                            <!-- Laravel: Loop through $post->tags -->
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="flex items-center gap-6 text-sm text-gray-600 pt-6 border-t">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            128 Likes
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            2,341 Views
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            34 Comments
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                            67 Bookmarks
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <button
                            onclick="toggleLike(this)"
                            class="flex items-center justify-center gap-2 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-300 like-button"
                        >
                            <svg class="w-5 h-5 like-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span class="like-text">Like</span>
                        </button>
                        <button
                            onclick="toggleBookmark(this)"
                            class="flex items-center justify-center gap-2 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-300 bookmark-button"
                        >
                            <svg class="w-5 h-5 bookmark-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                            <span class="bookmark-text">Save</span>
                        </button>
                        <button class="flex items-center justify-center gap-2 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                            <span>{{ __('Share') }}</span>
                        </button>
                        <button class="flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-800 hover:to-cyan-700 text-white rounded-lg transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>Contact</span>
                        </button>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="bg-white rounded-xl p-8 shadow-sm">
                    <h2 class="text-2xl font-bold mb-6">Comments (34)</h2>

                    <!-- Comment Input -->
                    <div class="mb-8">
                        <form id="commentForm" class="space-y-4">
                            <!-- @csrf -->
                            <textarea
                                id="commentInput"
                                rows="3"
                                placeholder="{{ __('Write a comment...') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition-all duration-300"
                            ></textarea>
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="px-6 py-2 bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-800 hover:to-cyan-700 text-white rounded-lg transition-all duration-300 font-medium"
                                >
                                    Post Comment
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Comments List -->
                    <div class="space-y-6" id="commentsList">
                        <!-- Laravel: Loop through $post->comments -->

                        <!-- Comment 1 -->
                        <div class="flex gap-4 pb-6 border-b">
                            <img
                                src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxwcm9mZXNzaW9uYWwlMjBtYW4lMjBwb3J0cmFpdHxlbnwxfHx8fDE3NjIyNDU5NDF8MA&ixlib=rb-4.1.0&q=80&w=1080"
                                alt="Commenter avatar"
                                class="w-10 h-10 rounded-full object-cover flex-shrink-0"
                                loading="lazy"
                            >
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-semibold">Ahmed Hassan</h4>
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm text-gray-500">2 days ago</span>
                                </div>
                                <p class="text-gray-700 mb-2">
                                    Amazing work! Your portfolio is really impressive. Would love to collaborate on a project.
                                </p>
                                <button class="text-sm text-blue-600 hover:text-blue-700 font-medium">Reply</button>
                            </div>
                        </div>

                        <!-- Comment 2 -->
                        <div class="flex gap-4 pb-6 border-b">
                            <img
                                src="https://images.unsplash.com/photo-1580489944761-15a19d654956?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxwcm9mZXNzaW9uYWwlMjB3b21hbiUyMGF2YXRhcnxlbnwxfHx8fDE3NjIyNDU5NDF8MA&ixlib=rb-4.1.0&q=80&w=1080"
                                alt="Commenter avatar"
                                class="w-10 h-10 rounded-full object-cover flex-shrink-0"
                                loading="lazy"
                            >
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-semibold">Sarah Al-Khateeb</h4>
                                    <span class="text-sm text-gray-500">3 days ago</span>
                                </div>
                                <p class="text-gray-700 mb-2">
                                    Great design skills! Do you also offer package design services?
                                </p>
                                <button class="text-sm text-blue-600 hover:text-blue-700 font-medium">Reply</button>
                            </div>
                        </div>

                        <!-- Comment 3 -->
                        <div class="flex gap-4">
                            <img
                                src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwyfHxwcm9mZXNzaW9uYWwlMjBtYW4lMjBwb3J0cmFpdHxlbnwxfHx8fDE3NjIyNDU5NDF8MA&ixlib=rb-4.1.0&q=80&w=1080"
                                alt="Commenter avatar"
                                class="w-10 h-10 rounded-full object-cover flex-shrink-0"
                                loading="lazy"
                            >
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-semibold">Omar Zaki</h4>
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm text-gray-500">1 week ago</span>
                                </div>
                                <p class="text-gray-700 mb-2">
                                    Very professional approach to branding. I'm interested in discussing rates for a startup project.
                                </p>
                                <button class="text-sm text-blue-600 hover:text-blue-700 font-medium">Reply</button>
                            </div>
                        </div>
                    </div>

                    <!-- Load More Comments -->
                    <div class="text-center mt-6">
                        <button class="text-blue-600 hover:text-blue-700 font-medium">
                            Load More Comments
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-8 space-y-6">
                    <!-- Author Profile Card -->
                    <div class="bg-gradient-to-br from-blue-50 via-cyan-50 to-sky-50 rounded-xl p-6 shadow-sm">
                        <div class="text-center mb-4">
                            <img
                                src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxwcm9mZXNzaW9uYWwlMjB3b21hbiUyMHBvcnRyYWl0fGVufDF8fHx8MTc2MjI0NTk0MXww&ixlib=rb-4.1.0&q=80&w=1080"
                                alt="Author"
                                class="w-24 h-24 rounded-full object-cover mx-auto mb-3 ring-4 ring-white shadow-lg"
                                loading="lazy"
                            >
                            <h3 class="text-xl font-bold mb-1">Layla Ibrahim</h3>
                            <p class="text-sm text-gray-600 mb-4">Brand Designer</p>

                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">2.4K</div>
                                    <div class="text-xs text-gray-600">Followers</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-500 bg-clip-text text-transparent">87</div>
                                    <div class="text-xs text-gray-600">Projects</div>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button class="flex-1 bg-gradient-to-r from-blue-600 to-green-500 hover:from-blue-800 hover:to-cyan-700 text-white px-4 py-2 rounded-lg transition-all duration-300">
                                    Follow
                                </button>
                                <button class="flex-1 border border-gray-300 hover:bg-white px-4 py-2 rounded-lg transition-all duration-300">
                                    Message
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Related Posts -->
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-bold mb-4">Related Posts</h3>
                        <div class="space-y-4">
                            <!-- Laravel: Loop through $relatedPosts -->

                            <!-- Related Post 1 -->
                            <a href="#" class="block group">
                                <div class="flex gap-3">
                                    <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                                        <img
                                            src="https://images.unsplash.com/photo-1626785774573-4b799315345d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxsb2dvJTIwZGVzaWduJTIwcG9ydGZvbGlvfGVufDF8fHx8MTc2MjI0NTk0MXww&ixlib=rb-4.1.0&q=80&w=1080"
                                            alt="Related post"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                            loading="lazy"
                                        >
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-sm mb-1 line-clamp-2 group-hover:text-blue-700 transition-colors">
                                            Logo Design for Tech Startup
                                        </h4>
                                        <p class="text-xs text-gray-500">156 likes</p>
                                    </div>
                                </div>
                            </a>

                            <!-- Related Post 2 -->
                            <a href="#" class="block group">
                                <div class="flex gap-3">
                                    <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                                        <img
                                            src="https://images.unsplash.com/photo-1634942537034-2531766767d1?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxicmFuZCUyMGd1aWRlbGluZXN8ZW58MXx8fHwxNzYyMjQ1OTQxfDA&ixlib=rb-4.1.0&q=80&w=1080"
                                            alt="Related post"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                            loading="lazy"
                                        >
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-sm mb-1 line-clamp-2 group-hover:text-blue-700 transition-colors">
                                            Brand Guidelines Package
                                        </h4>
                                        <p class="text-xs text-gray-500">203 likes</p>
                                    </div>
                                </div>
                            </a>

                            <!-- Related Post 3 -->
                            <a href="#" class="block group">
                                <div class="flex gap-3">
                                    <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                                        <img
                                            src="https://images.unsplash.com/photo-1572044162444-ad60f128bdea?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxidXNpbmVzcyUyMGNhcmQlMjBkZXNpZ258ZW58MXx8fHwxNzYyMjQ1OTQxfDA&ixlib=rb-4.1.0&q=80&w=1080"
                                            alt="Related post"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                            loading="lazy"
                                        >
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-sm mb-1 line-clamp-2 group-hover:text-blue-700 transition-colors">
                                            Business Card Design
                                        </h4>
                                        <p class="text-xs text-gray-500">89 likes</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-bold mb-4">Popular Tags</h3>
                        <div class="flex flex-wrap gap-2">
                            <a href="#" class="px-3 py-1 bg-gray-100 hover:bg-blue-100 hover:text-blue-700 text-gray-700 rounded-full text-sm transition-all duration-300">
                                Branding
                            </a>
                            <a href="#" class="px-3 py-1 bg-gray-100 hover:bg-blue-100 hover:text-blue-700 text-gray-700 rounded-full text-sm transition-all duration-300">
                                Services
                            </a>
                            <a href="#" class="px-3 py-1 bg-gray-100 hover:bg-blue-100 hover:text-blue-700 text-gray-700 rounded-full text-sm transition-all duration-300">
                                Freelance
                            </a>
                            <a href="#" class="px-3 py-1 bg-gray-100 hover:bg-blue-100 hover:text-blue-700 text-gray-700 rounded-full text-sm transition-all duration-300">
                                Design
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Like Button Toggle
function toggleLike(button) {
    const icon = button.querySelector('.like-icon');
    const text = button.querySelector('.like-text');

    if (button.classList.contains('liked')) {
        button.classList.remove('liked', 'text-red-500', 'border-red-500', 'bg-red-50');
        icon.setAttribute('fill', 'none');
        text.textContent = 'Like';

        // Laravel: Send unlike request
        // fetch('/api/posts/{{ $post->id }}/unlike', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
    } else {
        button.classList.add('liked', 'text-red-500', 'border-red-500', 'bg-red-50');
        icon.setAttribute('fill', 'currentColor');
        text.textContent = 'Liked';

        // Laravel: Send like request
        // fetch('/api/posts/{{ $post->id }}/like', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
    }
}

// Bookmark Button Toggle
function toggleBookmark(button) {
    const icon = button.querySelector('.bookmark-icon');
    const text = button.querySelector('.bookmark-text');

    if (button.classList.contains('bookmarked')) {
        button.classList.remove('bookmarked', 'text-blue-700', 'border-blue-700', 'bg-blue-50');
        icon.setAttribute('fill', 'none');
        text.textContent = 'Save';
    } else {
        button.classList.add('bookmarked', 'text-blue-700', 'border-blue-700', 'bg-blue-50');
        icon.setAttribute('fill', 'currentColor');
        text.textContent = 'Saved';
    }
}

// Comment Form Submission
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const commentInput = document.getElementById('commentInput');
    const comment = commentInput.value.trim();

    if (comment) {
        // Laravel: Send comment to backend
        // fetch('/api/posts/{{ $post->id }}/comments', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        //     },
        //     body: JSON.stringify({ comment: comment })
        // })
        // .then(response => response.json())
        // .then(data => {
        //     // Add new comment to the list
        //     // Reload or prepend the new comment
        // });

        commentInput.value = '';
        alert('Comment posted! (This will be handled by Laravel backend)');
    }
});
</script>
@endsection
