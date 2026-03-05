@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Create Project') }}</title>
<meta name="description" content="Share your creative project with the TecnoPark community">
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
                <a href="{{ route('projects') }}" class="hover:text-blue-600 transition-colors">{{ __('Projects') }}</a>
                <span>/</span>
                <span class="text-gray-900">{{ __('Create New Project') }}</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Share Your Work') }}</h1>
            <p class="text-gray-600">{{ __('Showcase your creative project to the TecnoPark community') }}</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-8 border border-gray-100">
            <form method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Project Title -->
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Project Title') }} *</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('title') border-red-500 @enderror"
                        placeholder="{{ __('Give your project a descriptive title') }}"
                    >
                    @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Category') }} *</label>
                    <select
                        id="category_id"
                        name="category_id"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('category_id') border-red-500 @enderror"
                    >
                        <option value="">{{ __('Select a category') }}</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Project Image -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Project Cover Image') }} *</label>
                    <div class="relative">
                        <div
                            id="dropZone"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors cursor-pointer"
                            onclick="document.getElementById('image').click()"
                        >
                            <div id="uploadPlaceholder">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-gray-600 mb-2">{{ __('Click to upload or drag and drop') }}</p>
                                <p class="text-sm text-gray-500">{{ __('PNG, JPG, GIF up to 10MB') }}</p>
                            </div>
                            <div id="imagePreview" class="hidden">
                                <img id="previewImg" src="" alt="Preview" class="max-h-64 mx-auto rounded-lg">
                                <button type="button" onclick="removeImage(event)" class="mt-4 text-sm text-red-600 hover:underline">{{ __('Remove image') }}</button>
                            </div>
                        </div>
                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept="image/*"
                            required
                            class="hidden"
                            onchange="previewImage(this)"
                        >
                    </div>
                    @error('image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Project Description') }}</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none @error('description') border-red-500 @enderror"
                        placeholder="{{ __('Tell the story behind your project. What inspired you? What challenges did you face? What tools did you use?') }}"
                    >{{ old('description') }}</textarea>
                    @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">{{ __('Optional: Add more context to help viewers understand your work') }}</p>
                </div>

                <!-- Tags -->
                <div>
                    <label for="tags" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Tags') }}</label>
                    <input
                        type="text"
                        id="tags"
                        name="tags"
                        value="{{ old('tags') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                        placeholder="{{ __('e.g. branding, logo, minimalist (comma separated)') }}"
                    >
                    <p class="mt-1 text-sm text-gray-500">{{ __('Add tags to help others discover your work') }}</p>
                </div>

                <!-- Featured Option -->
                <div class="flex items-start gap-3 p-4 bg-blue-50 rounded-lg">
                    <input
                        type="checkbox"
                        id="featured"
                        name="featured"
                        value="1"
                        class="mt-1 w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        {{ old('featured') ? 'checked' : '' }}
                    >
                    <div>
                        <label for="featured" class="block text-sm font-semibold text-gray-700">{{ __('Mark as Featured') }}</label>
                        <p class="text-sm text-gray-600">{{ __('Featured projects appear in the homepage spotlight section') }}</p>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t">
                    <a
                        href="{{ route('projects') }}"
                        class="px-6 py-3 text-gray-700 font-medium hover:text-gray-900 transition-colors"
                    >
                        {{ __('Cancel') }}
                    </a>
                    <button
                        type="submit"
                        name="action"
                        value="draft"
                        class="px-6 py-3 border border-gray-300 rounded-lg font-medium hover:bg-gray-50 transition-colors"
                    >
                        {{ __('Save as Draft') }}
                    </button>
                    <button
                        type="submit"
                        name="action"
                        value="publish"
                        class="px-8 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-[1.02] shadow-lg hover:shadow-xl"
                    >
                        {{ __('Publish Project') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Tips Card -->
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-green-50 rounded-xl p-6 border border-blue-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Tips for a Great Project') }}</h3>
            <ul class="space-y-3 text-gray-700">
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ __('Use high-quality images that showcase your work clearly') }}</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ __('Write a compelling title that captures attention') }}</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ __('Add relevant tags to increase discoverability') }}</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ __('Share the story behind your creative process') }}</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('uploadPlaceholder').classList.add('hidden');
            document.getElementById('imagePreview').classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage(e) {
    e.stopPropagation();
    document.getElementById('image').value = '';
    document.getElementById('uploadPlaceholder').classList.remove('hidden');
    document.getElementById('imagePreview').classList.add('hidden');
}

// Drag and drop functionality
const dropZone = document.getElementById('dropZone');

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('image').files = files;
        previewImage(document.getElementById('image'));
    }
});
</script>
@endsection
