@props(['project', 'eager' => false])

@php
    // Handle images as relationship collection or JSON/array
    if (is_object($project->images) && method_exists($project->images, 'first')) {
        // It's a relationship collection
        $firstImageModel = $project->images->first();
        $firstImage = $firstImageModel && $firstImageModel->image_path ? asset('storage/' . $firstImageModel->image_path) : null;
    } else {
        // It's JSON string or array (legacy)
        $images = is_string($project->images) ? json_decode($project->images, true) : (is_array($project->images) ? $project->images : []);
        $firstImage = !empty($images) ? asset('storage/' . $images[0]) : null;
    }

    $designer = $project->designer;
    $designerAvatar = $designer && $designer->avatar ? asset('storage/' . $designer->avatar) : null;
@endphp

<a href="{{ route('project.detail', ['locale' => app()->getLocale(), 'id' => $project->id]) }}"
   class="group bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer flex flex-col">

    {{-- Project Image with Optimization --}}
    @if($firstImage)
        <x-optimized-image
            :src="$firstImage"
            :alt="$project->title"
            :eager="$eager"
            aspect-ratio="4/3"
            class="h-40 sm:h-48 group-hover:scale-110 transition-transform duration-500"
            fallback="background: linear-gradient(135deg, #1e40af 0%, #0891b2 100%);"
        />
    @else
        <div class="h-40 sm:h-48 overflow-hidden bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center flex-shrink-0 text-white text-xl font-semibold">
            {{ strtoupper(substr($project->title ?? '', 0, 2)) }}
        </div>
    @endif

    {{-- Project Info --}}
    <div class="p-3 sm:p-4 flex flex-col flex-grow">
        {{-- Role Badge --}}
        @if($project->role)
            <span class="inline-block px-2 sm:px-3 py-0.5 sm:py-1 bg-gray-100 text-gray-700 text-[10px] sm:text-xs font-medium rounded-full mb-2 w-fit">
                {{ $project->role }}
            </span>
        @endif

        {{-- Project Title --}}
        <h3 class="text-sm sm:text-base font-semibold text-gray-900 mb-1 sm:mb-2 line-clamp-2">{{ $project->title }}</h3>

        {{-- Project Description --}}
        @if($project->description)
            <p class="text-xs sm:text-sm text-gray-600 line-clamp-2 mb-3">{{ $project->description }}</p>
        @endif

        {{-- Designer Info --}}
        <div class="mt-auto pt-2 border-t border-gray-100">
            @if($designer)
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">
                        @if($designerAvatar)
                            <x-optimized-image
                                :src="$designerAvatar"
                                :alt="$designer->name"
                                eager="true"
                                aspect-ratio="1/1"
                                class="w-full h-full"
                                object-fit="cover"
                                fallback="display: none;"
                            >
                                <div class="absolute inset-0 flex items-center justify-center text-white text-xs font-bold" style="display: none;">
                                    {{ strtoupper(substr($designer->name, 0, 1)) }}
                                </div>
                            </x-optimized-image>
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
