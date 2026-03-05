@extends('academic.layouts.app')

@section('title', __('Edit Training'))

@section('breadcrumb')
    <a href="{{ route('academic.trainings.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Trainings') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ __('Edit') }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    @if($training->approval_status === 'rejected' && $training->rejection_reason)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <div class="flex gap-3">
                <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                <div>
                    <p class="text-sm text-red-800 font-medium">{{ __('This training was rejected') }}</p>
                    <p class="text-sm text-red-700">{{ $training->rejection_reason }}</p>
                    <p class="text-sm text-red-600 mt-2">{{ __('Please make the necessary changes and save to resubmit for approval.') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">{{ __('Edit Training') }}</h2>

        <form action="{{ route('academic.trainings.update', ['locale' => app()->getLocale(), 'id' => $training->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Title') }} <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $training->title) }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('title') border-red-500 @enderror"
                       placeholder="{{ __('Enter training title') }}">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Short Description -->
            <div>
                <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Short Description') }}</label>
                <input type="text" id="short_description" name="short_description" value="{{ old('short_description', $training->short_description) }}" maxlength="500"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="{{ __('Brief description for cards (max 500 characters)') }}">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }} <span class="text-red-500">*</span></label>
                <textarea id="description" name="description" rows="5" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('description') border-red-500 @enderror"
                          placeholder="{{ __('Describe the training program...') }}">{{ old('description', $training->description) }}</textarea>
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
                        <option value="technology" {{ old('category', $training->category) === 'technology' ? 'selected' : '' }}>{{ __('Technology') }}</option>
                        <option value="business" {{ old('category', $training->category) === 'business' ? 'selected' : '' }}>{{ __('Business') }}</option>
                        <option value="design" {{ old('category', $training->category) === 'design' ? 'selected' : '' }}>{{ __('Design') }}</option>
                        <option value="marketing" {{ old('category', $training->category) === 'marketing' ? 'selected' : '' }}>{{ __('Marketing') }}</option>
                        <option value="language" {{ old('category', $training->category) === 'language' ? 'selected' : '' }}>{{ __('Language') }}</option>
                        <option value="professional" {{ old('category', $training->category) === 'professional' ? 'selected' : '' }}>{{ __('Professional Development') }}</option>
                        <option value="other" {{ old('category', $training->category) === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                    </select>
                </div>
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Level') }}</label>
                    <select id="level" name="level"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="beginner" {{ old('level', $training->level) === 'beginner' ? 'selected' : '' }}>{{ __('Beginner') }}</option>
                        <option value="intermediate" {{ old('level', $training->level) === 'intermediate' ? 'selected' : '' }}>{{ __('Intermediate') }}</option>
                        <option value="advanced" {{ old('level', $training->level) === 'advanced' ? 'selected' : '' }}>{{ __('Advanced') }}</option>
                    </select>
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Start Date') }} <span class="text-red-500">*</span></label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $training->start_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('start_date') border-red-500 @enderror">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('End Date') }}</label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $training->end_date?->format('Y-m-d')) }}"
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
                        <option value="online" {{ old('location_type', $training->location_type) === 'online' ? 'selected' : '' }}>{{ __('Online') }}</option>
                        <option value="in-person" {{ old('location_type', $training->location_type) === 'in-person' ? 'selected' : '' }}>{{ __('In-Person') }}</option>
                        <option value="hybrid" {{ old('location_type', $training->location_type ?? 'hybrid') === 'hybrid' ? 'selected' : '' }}>{{ __('Hybrid') }}</option>
                    </select>
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Location') }}</label>
                    <input type="text" id="location" name="location" value="{{ old('location', $training->location) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="{{ __('Enter location') }}">
                </div>
            </div>

            <!-- Max Participants & Certificate -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="max_participants" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Max Participants') }}</label>
                    <input type="number" id="max_participants" name="max_participants" value="{{ old('max_participants', $training->max_participants) }}" min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="{{ __('Leave empty for unlimited') }}">
                </div>
                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="has_certificate" value="1" {{ old('has_certificate', $training->has_certificate) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm font-medium text-gray-700">{{ __('Offers Certificate') }}</span>
                    </label>
                </div>
            </div>

            <!-- Price -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_free" value="1" {{ old('is_free', $training->is_free) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500"
                               onchange="document.getElementById('price').disabled = this.checked">
                        <span class="text-sm font-medium text-gray-700">{{ __('This training is free') }}</span>
                    </label>
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Price (USD)') }}</label>
                    <input type="number" id="price" name="price" value="{{ old('price', $training->price) }}" min="0" step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="0.00" {{ old('is_free', $training->is_free) ? 'disabled' : '' }}>
                </div>
            </div>

            <!-- Requirements -->
            <div>
                <label for="requirements" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Requirements') }} <span class="text-gray-400 text-xs">({{ __('one per line') }})</span></label>
                <textarea id="requirements" name="requirements" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                          placeholder="{{ __('List any prerequisites or requirements...') }}">{{ old('requirements', is_array($training->requirements) ? implode("\n", $training->requirements) : $training->requirements) }}</textarea>
            </div>

            <!-- Registration Link -->
            <div>
                <label for="registration_link" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Registration Link') }}</label>
                <input type="url" id="registration_link" name="registration_link" value="{{ old('registration_link', $training->registration_link) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="https://example.com/register">
            </div>

            <!-- Current Image -->
            @if($training->image)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Current Image') }}</label>
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('storage/' . $training->image) }}" alt="{{ $training->title }}" class="w-32 h-24 object-cover rounded-lg">
                        <p class="text-sm text-gray-500">{{ __('Upload a new image below to replace it') }}</p>
                    </div>
                </div>
            @endif

            <!-- Image -->
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">{{ $training->image ? __('Replace Image') : __('Training Image') }}</label>
                <input type="file" id="image" name="image" accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('Recommended: 800x600px, max 2MB') }}</p>
            </div>

            <!-- Notice -->
            @if($training->approval_status === 'rejected')
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex gap-3">
                        <i class="fas fa-redo text-orange-600 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-orange-800 font-medium">{{ __('Resubmission') }}</p>
                            <p class="text-sm text-orange-700">{{ __('Saving changes will resubmit this training for approval.') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t">
                <a href="{{ route('academic.trainings.index', ['locale' => app()->getLocale()]) }}"
                   class="px-6 py-2 text-gray-600 hover:text-gray-800">{{ __('Cancel') }}</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-green-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition-all">
                    <i class="fas fa-save mr-2"></i>{{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
