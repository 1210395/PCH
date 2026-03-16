@extends('academic.layouts.app')

@section('title', __('Edit Announcement'))

@section('breadcrumb')
    <a href="{{ route('academic.announcements.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Announcements') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ __('Edit') }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    @if($announcement->approval_status === 'rejected' && $announcement->rejection_reason)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <div class="flex gap-3">
                <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                <div>
                    <p class="text-sm text-red-800 font-medium">{{ __('This announcement was rejected') }}</p>
                    <p class="text-sm text-red-700">{{ $announcement->rejection_reason }}</p>
                    <p class="text-sm text-red-600 mt-2">{{ __('Please make the necessary changes and save to resubmit for approval.') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">{{ __('Edit Announcement') }}</h2>

        <form action="{{ route('academic.announcements.update', ['locale' => app()->getLocale(), 'id' => $announcement->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Title') }} <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $announcement->title) }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('title') border-red-500 @enderror"
                       placeholder="{{ __('Enter announcement title') }}">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Content') }} <span class="text-red-500">*</span></label>
                <textarea id="content" name="content" rows="6" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('content') border-red-500 @enderror"
                          placeholder="{{ __('Write your announcement content...') }}">{{ old('content', $announcement->content) }}</textarea>
                @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category & Priority -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Category') }}</label>
                    <select id="category" name="category"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="general" {{ old('category', $announcement->category) === 'general' ? 'selected' : '' }}>{{ __('General') }}</option>
                        <option value="admission" {{ old('category', $announcement->category) === 'admission' ? 'selected' : '' }}>{{ __('Admission') }}</option>
                        <option value="event" {{ old('category', $announcement->category) === 'event' ? 'selected' : '' }}>{{ __('Event') }}</option>
                        <option value="scholarship" {{ old('category', $announcement->category) === 'scholarship' ? 'selected' : '' }}>{{ __('Scholarship') }}</option>
                        <option value="job" {{ old('category', $announcement->category) === 'job' ? 'selected' : '' }}>{{ __('Job') }}</option>
                        <option value="other" {{ old('category', $announcement->category) === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                    </select>
                </div>
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Priority') }}</label>
                    <select id="priority" name="priority"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="normal" {{ old('priority', $announcement->priority) === 'normal' ? 'selected' : '' }}>{{ __('Normal') }}</option>
                        <option value="important" {{ old('priority', $announcement->priority) === 'important' ? 'selected' : '' }}>{{ __('Important') }}</option>
                        <option value="urgent" {{ old('priority', $announcement->priority) === 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                    </select>
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="publish_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Publish Date') }} <span class="text-red-500">*</span></label>
                    <input type="date" id="publish_date" name="publish_date" value="{{ old('publish_date', $announcement->publish_date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('publish_date') border-red-500 @enderror">
                    @error('publish_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Expiry Date') }}</label>
                    <input type="date" id="expiry_date" name="expiry_date" value="{{ old('expiry_date', $announcement->expiry_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('expiry_date') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">{{ __('Leave empty for no expiration') }}</p>
                    @error('expiry_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- External Link -->
            <div>
                <label for="external_link" class="block text-sm font-medium text-gray-700 mb-2">{{ __('External Link') }}</label>
                <input type="url" id="external_link" name="external_link" value="{{ old('external_link', $announcement->external_link) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="https://example.com/more-info">
                <p class="mt-1 text-xs text-gray-500">{{ __('Optional link for more details') }}</p>
            </div>

            <!-- Current Image -->
            @if($announcement->image)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Current Image') }}</label>
                    <div class="flex items-center gap-4">
                        <img src="{{ url('media/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="w-32 h-24 object-cover rounded-lg">
                        <p class="text-sm text-gray-500">{{ __('Upload a new image below to replace it') }}</p>
                    </div>
                </div>
            @endif

            <!-- Image -->
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">{{ $announcement->image ? __('Replace Image') : __('Announcement Image') }}</label>
                <input type="file" id="image" name="image" accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('Recommended: 800x600px, max 2MB') }}</p>
            </div>

            <!-- Notice -->
            @if($announcement->approval_status === 'rejected')
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex gap-3">
                        <i class="fas fa-redo text-orange-600 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-orange-800 font-medium">{{ __('Resubmission') }}</p>
                            <p class="text-sm text-orange-700">{{ __('Saving changes will resubmit this announcement for approval.') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t">
                <a href="{{ route('academic.announcements.index', ['locale' => app()->getLocale()]) }}"
                   class="px-6 py-2 text-gray-600 hover:text-gray-800">{{ __('Cancel') }}</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-green-600 to-blue-600 text-white rounded-lg hover:shadow-lg transition-all">
                    <i class="fas fa-save mr-2"></i>{{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
