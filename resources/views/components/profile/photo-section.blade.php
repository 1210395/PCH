@props(['designer', 'assetPaths' => []])

{{-- Cover & Profile Photos --}}
<div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
    <div class="p-4 sm:p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold">{{ __('Profile Photos') }}</h2>
        <p class="text-sm text-gray-600 mt-1">{{ __('Update your profile and cover photos') }}</p>
    </div>
    <div class="p-4 sm:p-6 space-y-6">
        {{-- Cover Photo --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Cover Photo') }}</label>
            <div class="relative group">
                <div class="h-32 sm:h-48 w-full bg-gradient-to-br from-blue-600 to-green-500 rounded-lg overflow-hidden">
                    @if($designer->cover_image)
                        @php
                            $coverUrl = asset('storage/' . $designer->cover_image);
                        @endphp
                        <img :src="form.coverPreview || '{{ $coverUrl }}'" alt="Cover" class="w-full h-full object-cover">
                    @else
                        <img :src="form.coverPreview" alt="Cover" class="w-full h-full object-cover" x-show="form.coverPreview">
                    @endif
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center">
                        <input type="file" x-ref="coverInput" accept="image/*" @change="handleCoverUpload($event)" class="hidden">
                        <button type="button" @click="$refs.coverInput.click()" class="px-4 py-2 bg-white/90 hover:bg-white rounded-lg text-sm font-medium flex items-center gap-2 transform hover:scale-105 transition-transform duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-text="form.coverUploading ? '{{ __('Uploading...') }}' : '{{ __('Change Cover') }}'"></span>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{ __('Recommended: 1200x400px or similar ratio') }}</p>
            </div>
        </div>

        {{-- Profile Photo --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Profile Photo') }}</label>
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <div class="relative group cursor-pointer" @click="$refs.avatarInput.click()">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white text-2xl font-semibold overflow-hidden transition-transform duration-300 group-hover:scale-105">
                        @if($designer->avatar)
                            @php
                                $avatarUrl = asset('storage/' . $designer->avatar);
                            @endphp
                            <img :src="form.avatarPreview || '{{ $avatarUrl }}'" alt="{{ $designer->name ?? 'Avatar' }}" class="w-full h-full object-cover">
                        @else
                            <img :src="form.avatarPreview" alt="Avatar" class="w-full h-full object-cover" x-show="form.avatarPreview">
                            <span x-show="!form.avatarPreview">{{ strtoupper(substr($designer->name ?? 'DS', 0, 2)) }}</span>
                        @endif
                    </div>
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <input type="file" x-ref="avatarInput" accept="image/*" @change="handleAvatarUpload($event)" class="hidden">
                    <p class="text-sm text-gray-600 mb-2">{{ __('Recommended: Square image, at least 400x400px') }}</p>
                    <button type="button" @click="$refs.avatarInput.click()" class="px-4 py-2 border border-gray-300 hover:border-gray-400 bg-white text-gray-700 rounded-lg text-sm font-medium transition-all duration-200 hover:shadow-md">
                        <span x-text="form.avatarUploading ? '{{ __('Uploading...') }}' : '{{ __('Upload Photo') }}'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
