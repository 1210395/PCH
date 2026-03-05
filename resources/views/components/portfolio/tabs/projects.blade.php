@props(['designer', 'projects', 'isOwner'])

<div id="work-tab" class="tab-content">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @if($projects->count() > 0)
        @foreach($projects as $project)
        @php
            $isRejected = $project->approval_status === 'rejected';
            $isPending = $project->approval_status === 'pending';
            // Only show rejected/pending items to owner
            if (!$isOwner && ($isRejected || $isPending)) {
                continue;
            }
        @endphp
        <!-- Project Card -->
        <a href="{{ route('project.detail', ['locale' => app()->getLocale(), 'id' => $project->id]) }}" class="group block bg-white rounded-xl border {{ $isRejected ? 'border-red-300' : ($isPending ? 'border-yellow-300' : 'border-gray-200') }} overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 relative">
            <!-- Project Image -->
            <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                @php
                    try {
                        $primaryImage = $project->images && $project->images->count() > 0 ? $project->images->where('is_primary', 1)->first() : null;
                        $imageUrl = $primaryImage ? asset('storage/' . $primaryImage->image_path) : asset('images/placeholder.jpg');
                    } catch (\Exception $e) {
                        $imageUrl = asset('images/placeholder.jpg');
                        \Log::error('Error loading project image', ['error' => $e->getMessage(), 'project_id' => $project->id]);
                    }
                @endphp
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $project->title }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                    onerror="this.style.display='none'; this.parentElement.classList.add('bg-gradient-to-br', 'from-blue-600', 'to-green-500');"
                />
                {{-- Hover overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            </div>

            {{-- Status Badge (Owner Only) --}}
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

            <!-- Project Info -->
            <div class="p-4 sm:p-5">
                @php
                    // Handle both relationship and string category
                    $categoryName = null;
                    try {
                        // First check if category is a string (from registration)
                        if (is_string($project->category) && !empty($project->category)) {
                            $categoryName = $project->category;
                        }
                        // Otherwise try to get from relationship if category_id exists
                        elseif ($project->category_id && method_exists($project, 'category')) {
                            $categoryRelation = $project->category;
                            if ($categoryRelation && isset($categoryRelation->name)) {
                                $categoryName = $categoryRelation->name;
                            }
                        }
                    } catch (\Exception $e) {
                        // Silently handle any errors
                        \Log::debug('Category access error for project ' . $project->id, ['error' => $e->getMessage()]);
                    }
                @endphp
                @if($categoryName)
                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 mb-2">
                    {{ $categoryName }}
                </span>
                @endif
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 line-clamp-1">{{ $project->title }}</h3>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full overflow-hidden bg-gray-200">
                        @if($designer->avatar)
                            <img src="{{ asset('storage/' . $designer->avatar) }}" alt="{{ $designer->name }}" class="w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.parentElement.classList.add('bg-gradient-to-br', 'from-blue-600', 'to-green-500');"/>
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white text-xs font-bold">
                                {{ strtoupper(substr($designer->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <span class="text-xs sm:text-sm text-gray-600 truncate">{{ $designer->name }}</span>
                </div>

                {{-- Rejection Reason (Owner Only) --}}
                @if($isOwner && $isRejected && $project->rejection_reason)
                    <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-xs text-red-700">
                            <strong>{{ __('Reason:') }}</strong> {{ $project->rejection_reason }}
                        </p>
                        <span class="text-xs text-red-600 hover:text-red-800 underline mt-1 inline-block">
                            {{ __('Edit & Resubmit') }}
                        </span>
                    </div>
                @endif
            </div>
        </a>
        @endforeach
        @endif

        <!-- Add New Project Card (Owner Only) -->
        @if($isOwner)
        <a href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}#projects" class="group bg-white rounded-xl border-2 border-dashed border-gray-300 hover:border-blue-500 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer flex items-center justify-center min-h-[200px] sm:min-h-[300px]">
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-blue-600 to-green-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 group-hover:text-blue-600 transition-colors">{{ __('Add New Project') }}</h3>
                <p class="text-sm text-gray-500 mt-2">{{ __('Click to create a new project') }}</p>
            </div>
        </a>
        @endif
    </div>
</div>
