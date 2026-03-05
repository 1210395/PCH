@props(['fabLab'])

@php
    $imageUrl = $fabLab->image ? asset('storage/' . $fabLab->image) : null;
    $equipment = is_array($fabLab->equipment) ? $fabLab->equipment : [];
    $typeClass = match($fabLab->type) {
        'university' => 'bg-blue-600',
        'community' => 'bg-green-600',
        'private' => 'bg-purple-600',
        'government' => 'bg-indigo-600',
        default => 'bg-gray-600',
    };
@endphp

<a href="{{ route('fab-lab.detail', ['locale' => app()->getLocale(), 'id' => $fabLab->id]) }}"
   class="group bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer flex flex-col h-auto sm:h-[520px]">

    {{-- Lab Image --}}
    <div class="relative h-48 overflow-hidden bg-gray-100">
        @if($imageUrl)
            <img
                src="{{ $imageUrl }}"
                alt="{{ $fabLab->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                onerror="this.style.display='none'; this.parentElement.classList.add('bg-gradient-to-br', 'from-blue-500', 'to-green-400');"
            >
        @else
            <div class="w-full h-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center">
                <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
        @endif

        {{-- Type and Verified Badges --}}
        <div class="absolute top-3 left-3 right-3 flex items-start justify-between">
            <span class="{{ $typeClass }} text-white text-xs font-medium px-3 py-1 rounded-full">
                {{ ucfirst($fabLab->type) }}
            </span>
            @if($fabLab->verified)
                <span class="bg-green-600 text-white text-xs font-medium px-3 py-1 rounded-full">
                    {{ __('Verified') }}
                </span>
            @endif
        </div>
    </div>

    {{-- Lab Content --}}
    <div class="p-5 flex-1 flex flex-col">
        {{-- Rating --}}
        <div class="flex items-center gap-2 mb-3">
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 fill-yellow-400 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="text-sm font-semibold">{{ number_format($fabLab->rating, 1) }}</span>
            </div>
            <span class="text-sm text-gray-500">({{ $fabLab->reviews_count }} {{ __('reviews') }})</span>
        </div>

        {{-- Name --}}
        <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-1 group-hover:text-blue-600 transition-colors">
            {{ $fabLab->name }}
        </h3>

        {{-- Location --}}
        <div class="flex items-start gap-2 text-sm text-gray-600 mb-3">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="line-clamp-1">{{ $fabLab->location }}</span>
        </div>

        {{-- Short Description --}}
        <p class="text-sm text-gray-600 line-clamp-2 mb-4 min-h-[2.5rem]">
            {{ $fabLab->short_description }}
        </p>

        {{-- Stats --}}
        <div class="flex items-center gap-4 mb-4 pb-4 border-b text-sm">
            <div class="flex items-center gap-1 text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>{{ $fabLab->members }}</span>
            </div>
            <div class="flex items-center gap-1 text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                <span>{{ count($equipment) }} {{ __('tools') }}</span>
            </div>
        </div>

        {{-- Equipment Preview --}}
        <div class="flex flex-wrap gap-2 mb-4 min-h-[1.75rem]">
            @foreach(array_slice($equipment, 0, 3) as $item)
                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-md">
                    {{ $item }}
                </span>
            @endforeach
            @if(count($equipment) > 3)
                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-md">
                    +{{ count($equipment) - 3 }}
                </span>
            @endif
        </div>

        {{-- Opening Hours --}}
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="line-clamp-1">{{ $fabLab->opening_hours }}</span>
        </div>

        {{-- View Details Button --}}
        <button class="mt-auto w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2 group/btn">
            {{ __('View Details') }}
            <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</a>
