@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">{{ __('Create Announcement (as admin)') }}</h1>
        <a href="{{ route('admin.academic-content.announcements', ['locale' => app()->getLocale()]) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; {{ __('Back') }}</a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.academic-content.announcements.store', ['locale' => app()->getLocale()]) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
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
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Content') }} *</label>
            <textarea name="content" required rows="6" maxlength="10000" class="w-full px-3 py-2 border rounded-lg">{{ old('content') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }}</label>
                <select name="category" class="w-full px-3 py-2 border rounded-lg">
                    @foreach(['general','admission','event','scholarship','job','other'] as $cat)
                        <option value="{{ $cat }}" {{ old('category')===$cat?'selected':'' }}>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Priority') }}</label>
                <select name="priority" class="w-full px-3 py-2 border rounded-lg">
                    <option value="normal"    {{ old('priority')==='normal'?'selected':'' }}>Normal</option>
                    <option value="important" {{ old('priority')==='important'?'selected':'' }}>Important</option>
                    <option value="urgent"    {{ old('priority')==='urgent'?'selected':'' }}>Urgent</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Publish date') }} *</label>
                <input type="date" name="publish_date" required value="{{ old('publish_date', now()->toDateString()) }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Expiry date') }}</label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('External link') }}</label>
            <input type="url" name="external_link" maxlength="255" value="{{ old('external_link') }}" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Cover image') }}</label>
            <input type="file" name="image" accept="image/*" class="w-full">
        </div>

        <div class="flex items-center justify-end gap-2 pt-4 border-t">
            <a href="{{ route('admin.academic-content.announcements', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600">{{ __('Cancel') }}</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">{{ __('Create and publish') }}</button>
        </div>
    </form>
</div>
@endsection
