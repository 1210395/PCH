@extends('academic.layouts.app')

@section('title', __('Edit Workshop'))

@section('breadcrumb')
    <a href="{{ route('academic.workshops.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Workshops') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ __('Edit') }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    @if($workshop->approval_status === 'rejected' && $workshop->rejection_reason)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <div class="flex gap-3">
                <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                <div>
                    <p class="text-sm text-red-800 font-medium">{{ __('This workshop was rejected') }}</p>
                    <p class="text-sm text-red-700">{{ $workshop->rejection_reason }}</p>
                    <p class="text-sm text-red-600 mt-2">{{ __('Please make the necessary changes and save to resubmit for approval.') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">{{ __('Edit Workshop') }}</h2>

        <form action="{{ route('academic.workshops.update', ['locale' => app()->getLocale(), 'id' => $workshop->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Title') }} <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $workshop->title) }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('title') border-red-500 @enderror"
                       placeholder="{{ __('Enter workshop title') }}">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }} <span class="text-red-500">*</span></label>
                <textarea id="description" name="description" rows="5" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('description') border-red-500 @enderror"
                          placeholder="{{ __('Describe the workshop...') }}">{{ old('description', $workshop->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Objectives -->
            <div>
                <label for="objectives" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Objectives') }}</label>
                <textarea id="objectives" name="objectives" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                          placeholder="{{ __('What will participants learn?') }}">{{ old('objectives', $workshop->objectives) }}</textarea>
            </div>

            <!-- Date & Time -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="workshop_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Date') }} <span class="text-red-500">*</span></label>
                    <input type="date" id="workshop_date" name="workshop_date" value="{{ old('workshop_date', $workshop->workshop_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('workshop_date') border-red-500 @enderror">
                    @error('workshop_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Start Time') }}</label>
                    <input type="time" id="start_time" name="start_time" value="{{ old('start_time', $workshop->start_time) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">{{ __('End Time') }}</label>
                    <input type="time" id="end_time" name="end_time" value="{{ old('end_time', $workshop->end_time) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>

            <!-- Location -->
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Location') }}</label>
                <input type="text" id="location" name="location" value="{{ old('location', $workshop->location) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="{{ __('Enter location or venue') }}">
            </div>

            <!-- Online Toggle & Instructor -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_online" value="1" {{ old('is_online', $workshop->is_online) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm font-medium text-gray-700">{{ __('This is an online workshop') }}</span>
                    </label>
                </div>
                <div>
                    <label for="instructor" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Instructor') }}</label>
                    <input type="text" id="instructor" name="instructor" value="{{ old('instructor', $workshop->instructor) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="{{ __('Instructor name') }}">
                </div>
            </div>

            <!-- Max Participants -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="max_participants" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Max Participants') }}</label>
                    <input type="number" id="max_participants" name="max_participants" value="{{ old('max_participants', $workshop->max_participants) }}" min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="{{ __('Leave empty for unlimited') }}">
                </div>
            </div>

            <!-- Price -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_free" value="1" {{ old('is_free', $workshop->is_free) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500"
                               onchange="document.getElementById('price').disabled = this.checked">
                        <span class="text-sm font-medium text-gray-700">{{ __('This workshop is free') }}</span>
                    </label>
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Price (USD)') }}</label>
                    <input type="number" id="price" name="price" value="{{ old('price', $workshop->price) }}" min="0" step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="0.00" {{ old('is_free', $workshop->is_free) ? 'disabled' : '' }}>
                </div>
            </div>

            <!-- Requirements -->
            <div>
                <label for="requirements" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Requirements') }}</label>
                <textarea id="requirements" name="requirements" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                          placeholder="{{ __('What participants should bring or know...') }}">{{ old('requirements', $workshop->requirements) }}</textarea>
            </div>

            <!-- Registration Link -->
            <div>
                <label for="registration_link" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Registration Link') }}</label>
                <input type="url" id="registration_link" name="registration_link" value="{{ old('registration_link', $workshop->registration_link) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="https://example.com/register">
            </div>

            <!-- Current Image -->
            @if($workshop->image)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Current Image') }}</label>
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('storage/' . $workshop->image) }}" alt="{{ $workshop->title }}" class="w-32 h-24 object-cover rounded-lg">
                        <p class="text-sm text-gray-500">{{ __('Upload a new image below to replace it') }}</p>
                    </div>
                </div>
            @endif

            <!-- Image -->
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">{{ $workshop->image ? __('Replace Image') : __('Workshop Image') }}</label>
                <input type="file" id="image" name="image" accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('Recommended: 800x600px, max 2MB') }}</p>
            </div>

            <!-- Notice -->
            @if($workshop->approval_status === 'rejected')
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex gap-3">
                        <i class="fas fa-redo text-orange-600 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-orange-800 font-medium">{{ __('Resubmission') }}</p>
                            <p class="text-sm text-orange-700">{{ __('Saving changes will resubmit this workshop for approval.') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t">
                <a href="{{ route('academic.workshops.index', ['locale' => app()->getLocale()]) }}"
                   class="px-6 py-2 text-gray-600 hover:text-gray-800">{{ __('Cancel') }}</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-green-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition-all">
                    <i class="fas fa-save mr-2"></i>{{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
