@props(['designer'])

@php
    $avatar = $designer->avatar ? url('media/' . $designer->avatar) : null;
    $coverImage = $designer->cover_image ? url('media/' . $designer->cover_image) : null;
    $skills = is_string($designer->skills) ? json_decode($designer->skills, true) : (is_array($designer->skills) ? $designer->skills : []);

    // For manufacturers, showrooms, and vendors (suppliers), show products count; for others, show projects count
    $isVendor = ($designer->sector && stripos($designer->sector, 'supplier') !== false)
             || ($designer->sub_sector && stripos($designer->sub_sector, 'supplier') !== false);
    $isManufacturerOrShowroom = in_array($designer->sector, ['manufacturer', 'showroom']) || $isVendor;
    if ($isManufacturerOrShowroom) {
        $itemCount = $designer->products_count ?? $designer->products()->count();
        $itemLabel = __('Products');
    } else {
        $itemCount = $designer->projects_count ?? $designer->projects()->count();
        $itemLabel = __('Projects');
    }
@endphp

<a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}"
    class="flex flex-col bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer h-full">
    {{-- Cover Image --}}
    <div class="relative h-32 bg-gradient-to-br from-blue-500 to-green-400 overflow-hidden rounded-t-xl flex-shrink-0">
        @if($coverImage)
            <img src="{{ $coverImage }}" alt="{{ $designer->name }}" class="w-full h-full object-cover opacity-80"
                onerror="this.style.display='none'">
        @endif
    </div>

    {{-- Content --}}
    <div class="p-6 pt-0 relative flex flex-col flex-grow">
        {{-- Avatar --}}
        <div class="flex justify-center -mt-12 mb-4">
            <div
                class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex items-center justify-center text-white text-2xl font-bold relative z-10">
                @if($avatar)
                    <img src="{{ $avatar }}" alt="{{ $designer->name }}" class="w-full h-full object-cover"
                        onerror="this.style.display='none'; this.parentElement.innerHTML='{{ strtoupper(substr($designer->name, 0, 2)) }}';">
                @else
                    {{ strtoupper(substr($designer->name, 0, 2)) }}
                @endif
            </div>
        </div>

        {{-- Name & Role --}}
        <div class="text-center mb-4">
            <div class="flex items-center justify-center gap-2 mb-1">
                <h3 class="text-xl text-gray-900 line-clamp-1">{{ $designer->name }}</h3>
                @if($designer->email_verified_at)
                    <svg class="w-5 h-5 text-blue-600 fill-blue-600 flex-shrink-0" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            </div>
            <p class="text-sm text-gray-600 line-clamp-1">{{ $designer->title ?? __('Creative Professional') }}</p>
        </div>

        {{-- Stats --}}
        <div class="flex items-center justify-center gap-6 mb-4 pb-4 border-b border-gray-200">
            <div class="text-center">
                <div class="text-lg text-gray-900">{{ number_format($itemCount) }}</div>
                <div class="text-xs text-gray-600">{{ $itemLabel }}</div>
            </div>
        </div>

        {{-- Skills --}}
        <div class="flex flex-wrap gap-2 mb-4 flex-grow">
            @if($skills && count($skills) > 0)
                @foreach(array_slice($skills, 0, 3) as $skill)
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full h-fit">
                        {{ Str::limit($skill, 15) }}
                    </span>
                @endforeach
            @endif
        </div>

        {{-- View Profile Button --}}
        <div class="mt-auto">
            <button
                class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-200 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ __('View Profile') }}
            </button>
        </div>
    </div>
</a>