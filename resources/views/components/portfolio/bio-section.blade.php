@props(['designer', 'isOwner'])

<!-- Bio Section - NEW DESIGN -->
<div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ __('About Me') }}</h2>
        @if($isOwner)
        <button @click="$dispatch('open-edit-bio')" class="inline-flex items-center gap-1 px-3 py-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors text-sm sm:text-base font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('Edit') }}
        </button>
        @endif
    </div>
    @if($designer->bio)
    <p class="text-sm sm:text-base text-gray-700 leading-relaxed break-words whitespace-pre-wrap">
        {{ $designer->bio }}
    </p>
    @else
    <p class="text-sm sm:text-base text-gray-500 italic">
        {{ __("This designer hasn't added a bio yet.") }}
    </p>
    @endif
</div>
