@extends('admin.layouts.app')

@section('title', __('Dropdown Options Management'))

@section('breadcrumb')
    <a href="{{ route('admin.settings.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Settings') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ __('Dropdown Options') }}</span>
@endsection

@section('content')
<div class="max-w-6xl">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ __('Dropdown Options Management') }}</h1>
        <p class="text-gray-500 mt-1">{{ __('Manage all dropdown and combobox options used throughout the system') }}</p>
    </div>

    <!-- Info Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
            <div>
                <p class="text-sm text-blue-800">
                    {{ __('Changes made here will affect dropdowns across registration, profile editing, and admin pages. You can add, edit, delete, or deactivate any option.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Dropdown Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($types as $key => $type)
        <a href="{{ route('admin.dropdowns.show', ['locale' => app()->getLocale(), 'type' => $key]) }}"
           class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
                        {{ $type['label'] }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $type['description'] }}</p>
                </div>
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium ml-3 flex-shrink-0">
                    {{ $counts[$key] ?? 0 }}
                </span>
            </div>

            @if($type['has_children'] ?? false)
            <div class="mt-4 pt-3 border-t border-gray-100">
                <span class="inline-flex items-center text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">
                    <i class="fas fa-sitemap mr-1"></i>
                    {{ __('Has Sub-Options') }} ({{ $type['child_label'] ?? __('Children') }})
                </span>
            </div>
            @endif

            <div class="mt-4 flex items-center text-sm text-blue-600 group-hover:text-blue-700">
                <span>{{ __('Manage options') }}</span>
                <i class="fas fa-arrow-right ml-2 transition-transform group-hover:translate-x-1"></i>
            </div>
        </a>
        @endforeach
    </div>

    <!-- Quick Stats -->
    <div class="mt-8 bg-gray-50 rounded-xl p-6">
        <h3 class="text-sm font-medium text-gray-700 uppercase tracking-wider mb-4">{{ __('Quick Stats') }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <p class="text-2xl font-bold text-gray-800">{{ array_sum($counts) }}</p>
                <p class="text-sm text-gray-500">{{ __('Total Options') }}</p>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <p class="text-2xl font-bold text-gray-800">{{ count($types) }}</p>
                <p class="text-sm text-gray-500">{{ __('Categories') }}</p>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <p class="text-2xl font-bold text-gray-800">{{ $counts['sector'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">{{ __('Sectors') }}</p>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <p class="text-2xl font-bold text-gray-800">{{ $counts['skill'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">{{ __('Skills') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
