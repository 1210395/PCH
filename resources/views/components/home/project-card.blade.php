@props(['project'])

@php
    // Handle images as relationship collection or JSON/array
    if (is_object($project->images) && method_exists($project->images, 'first')) {
        // It's a relationship collection
        $firstImageModel = $project->images->first();
        $firstImage = $firstImageModel && $firstImageModel->image_path ? url('media/' . $firstImageModel->image_path) : null;
    } else {
        // It's JSON string or array (legacy)
        $images = is_string($project->images) ? json_decode($project->images, true) : (is_array($project->images) ? $project->images : []);
        $firstImage = !empty($images) ? url('media/' . $images[0]) : null;
    }

    $designer = $project->designer;
    $designerAvatar = $designer && $designer->avatar ? url('media/' . $designer->avatar) : null;
@endphp

<a href="{{ route('project.detail', ['locale' => app()->getLocale(), 'id' => $project->id]) }}" class="group bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer flex flex-col h-full">
    {{-- Project Image --}}
    <div class="relative aspect-[4/3] overflow-hidden bg-gray-100 flex-shrink-0">
        @if($firstImage)
            <img
                src="{{ $firstImage }}"
                alt="{{ $project->title }}"
                loading="lazy"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                onerror="this.style.display='none'; this.parentElement.classList.add('bg-gradient-to-br', 'from-blue-600', 'to-green-500');"
            />
        @else
            <div class="w-full h-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white text-xl font-semibold">
                {{ strtoupper(substr($project->title ?? '', 0, 2)) }}
            </div>
        @endif
    </div>

    {{-- Project Info --}}
    <div class="p-3 sm:p-4 flex flex-col flex-grow">
        {{-- Role Badge --}}
        <div class="min-h-[1.75rem] mb-2">
            @if($project->role)
                <span class="inline-block px-2 sm:px-3 py-0.5 sm:py-1 bg-gray-100 text-gray-700 text-[10px] sm:text-xs font-medium rounded-full w-fit">
                    {{ $project->role }}
                </span>
            @endif
        </div>

        {{-- Project Title --}}
        <h3 class="text-sm sm:text-base font-semibold text-gray-900 mb-1 sm:mb-2 line-clamp-1">{{ $project->title }}</h3>

        {{-- Project Description --}}
        <p class="text-xs sm:text-sm text-gray-600 line-clamp-2 mb-3 min-h-[2.5rem]">{{ $project->description ?? '' }}</p>

        {{-- Designer Info --}}
        <div class="mt-auto pt-2 border-t border-gray-100 min-h-[2.5rem]">
            @if($designer)
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">
                        @if($designerAvatar)
                            <img src="{{ $designerAvatar }}" alt="{{ $designer->name }}" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'; this.parentElement.innerHTML='{{ strtoupper(substr($designer->name, 0, 1)) }}';">
                        @else
                            {{ strtoupper(substr($designer->name, 0, 1)) }}
                        @endif
                    </div>
                    <span class="text-xs sm:text-sm text-gray-600 truncate">{{ $designer->name }}</span>
                </div>
            @endif
        </div>
    </div>
</a>
