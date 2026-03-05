@extends('academic.layouts.app')

@section('title', __('Add Training'))

@section('breadcrumb')
    <a href="{{ route('academic.trainings.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Trainings') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ __('Add New') }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">{{ __('Add New Training') }}</h2>

        <form action="{{ route('academic.trainings.store', ['locale' => app()->getLocale()]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Title') }} <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('title') border-red-500 @enderror"
                       placeholder="{{ __('Enter training title') }}">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Short Description -->
            <div>
                <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Short Description') }}</label>
                <input type="text" id="short_description" name="short_description" value="{{ old('short_description') }}" maxlength="500"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="{{ __('Brief description for cards (max 500 characters)') }}">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }} <span class="text-red-500">*</span></label>
                <textarea id="description" name="description" rows="5" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('description') border-red-500 @enderror"
                          placeholder="{{ __('Describe the training program...') }}">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category & Level -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Category') }}</label>
                    <select id="category" name="category"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">{{ __('Select category') }}</option>
                        @foreach(\App\Helpers\DropdownHelper::trainingCategories() as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Level') }}</label>
                    <select id="level" name="level"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="beginner" {{ old('level') === 'beginner' ? 'selected' : '' }}>{{ __('Beginner') }}</option>
                        <option value="intermediate" {{ old('level') === 'intermediate' ? 'selected' : '' }}>{{ __('Intermediate') }}</option>
                        <option value="advanced" {{ old('level') === 'advanced' ? 'selected' : '' }}>{{ __('Advanced') }}</option>
                    </select>
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Start Date') }} <span class="text-red-500">*</span></label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('start_date') border-red-500 @enderror">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('End Date') }}</label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('end_date') border-red-500 @enderror">
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Location Type & Location -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="location_type" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Location Type') }}</label>
                    <select id="location_type" name="location_type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="online" {{ old('location_type') === 'online' ? 'selected' : '' }}>{{ __('Online') }}</option>
                        <option value="in-person" {{ old('location_type') === 'in-person' ? 'selected' : '' }}>{{ __('In-Person') }}</option>
                        <option value="hybrid" {{ old('location_type', 'hybrid') === 'hybrid' ? 'selected' : '' }}>{{ __('Hybrid') }}</option>
                    </select>
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Location') }}</label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="{{ __('Enter location') }}">
                </div>
            </div>

            <!-- Max Participants & Certificate -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="max_participants" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Max Participants') }}</label>
                    <input type="number" id="max_participants" name="max_participants" value="{{ old('max_participants') }}" min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="{{ __('Leave empty for unlimited') }}">
                </div>
                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="has_certificate" value="1" {{ old('has_certificate', true) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm font-medium text-gray-700">{{ __('Offers Certificate') }}</span>
                    </label>
                </div>
            </div>

            <!-- Price -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_free" value="1" {{ old('is_free', true) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500"
                               onchange="document.getElementById('price').disabled = this.checked">
                        <span class="text-sm font-medium text-gray-700">{{ __('This training is free') }}</span>
                    </label>
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Price (USD)') }}</label>
                    <input type="number" id="price" name="price" value="{{ old('price') }}" min="0" step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="0.00" {{ old('is_free', true) ? 'disabled' : '' }}>
                </div>
            </div>

            <!-- Requirements -->
            <div>
                <label for="requirements" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Requirements') }}</label>
                <textarea id="requirements" name="requirements" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                          placeholder="{{ __('List any prerequisites or requirements...') }}">{{ old('requirements') }}</textarea>
            </div>

            <!-- Registration Link -->
            <div>
                <label for="registration_link" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Registration Link') }}</label>
                <input type="url" id="registration_link" name="registration_link" value="{{ old('registration_link') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="https://example.com/register">
            </div>

            <!-- Image -->
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Training Image') }}</label>
                <input type="file" id="image" name="image" accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('Recommended: 800x600px, max 2MB') }}</p>
            </div>

            <!-- Notice -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex gap-3">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                    <div>
                        <p class="text-sm text-blue-800 font-medium">{{ __('Approval Required') }}</p>
                        <p class="text-sm text-blue-700">{{ __('Your training will be reviewed by an administrator before it becomes publicly visible.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t">
                <a href="{{ route('academic.trainings.index', ['locale' => app()->getLocale()]) }}"
                   class="px-6 py-2 text-gray-600 hover:text-gray-800">{{ __('Cancel') }}</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-green-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition-all">
                    <i class="fas fa-save mr-2"></i>{{ __('Create Training') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
