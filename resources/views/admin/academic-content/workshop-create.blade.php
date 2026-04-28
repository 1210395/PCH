@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">{{ __('Create Workshop (as admin)') }}</h1>
        <a href="{{ route('admin.academic-content.workshops', ['locale' => app()->getLocale()]) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; {{ __('Back') }}</a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.academic-content.workshops.store', ['locale' => app()->getLocale()]) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf

        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg px-4 py-2 text-sm">
            <i class="fas fa-info-circle mr-1"></i>
            {{ __('Publishing as') }} <strong>Palestine Creative Hub</strong>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Title') }} *</label>
            <input type="text" name="title" required maxlength="255" value="{{ old('title') }}" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Short description') }}</label>
            <input type="text" name="short_description" maxlength="500" value="{{ old('short_description') }}" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
            <textarea name="description" rows="4" maxlength="5000" class="w-full px-3 py-2 border rounded-lg">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Objectives') }}</label>
            <textarea name="objectives" rows="3" maxlength="5000" class="w-full px-3 py-2 border rounded-lg">{{ old('objectives') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }}</label>
                <input type="text" name="category" maxlength="100" value="{{ old('category') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Instructor') }}</label>
                <input type="text" name="instructor" maxlength="255" value="{{ old('instructor') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Workshop date') }} *</label>
                <input type="date" name="workshop_date" required value="{{ old('workshop_date') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Start time') }}</label>
                <input type="time" name="start_time" value="{{ old('start_time') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('End time') }}</label>
                <input type="time" name="end_time" value="{{ old('end_time') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Location type') }}</label>
                <select name="location_type" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">—</option>
                    <option value="online"    {{ old('location_type')==='online'?'selected':'' }}>Online</option>
                    <option value="in-person" {{ old('location_type')==='in-person'?'selected':'' }}>In-person</option>
                    <option value="hybrid"    {{ old('location_type')==='hybrid'?'selected':'' }}>Hybrid</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Location') }}</label>
                <input type="text" name="location" maxlength="255" value="{{ old('location') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Max participants') }}</label>
                <input type="number" name="max_participants" min="1" value="{{ old('max_participants') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Price') }}</label>
                <input type="text" name="price" maxlength="100" value="{{ old('price') }}" placeholder="{{ __('Free, $50, ...') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Registration link') }}</label>
                <input type="url" name="registration_link" maxlength="500" value="{{ old('registration_link') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>

        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2"><input type="checkbox" name="is_online"        value="1" {{ old('is_online')?'checked':'' }}>{{ __('Online') }}</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_free"          value="1" {{ old('is_free')?'checked':'' }}>{{ __('Free') }}</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="has_certificate"  value="1" {{ old('has_certificate')?'checked':'' }}>{{ __('Certificate') }}</label>
        </div>

        <div class="flex items-center justify-end gap-2 pt-4 border-t">
            <a href="{{ route('admin.academic-content.workshops', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600">{{ __('Cancel') }}</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">{{ __('Create and publish') }}</button>
        </div>
    </form>
</div>
@endsection
