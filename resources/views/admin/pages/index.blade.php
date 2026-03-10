@extends('admin.layouts.app')

@section('title', __('Pages Management'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Pages Management') }}</span>
@endsection

@section('content')
<div class="p-6">
    <div class="max-w-6xl">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Pages Management') }}</h1>
            <p class="text-gray-500 mt-1">{{ __('Manage static content pages like About Us, Terms of Service, Privacy Policy, etc.') }}</p>
        </div>

        <!-- Info Banner -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                <div>
                    <p class="text-sm text-blue-800">
                        {{ __('Each page supports bilingual content (English & Arabic), images, and structured sections. Changes are reflected immediately on the public site.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Pages Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pages as $slug => $page)
            <a href="{{ route('admin.pages.edit', ['locale' => app()->getLocale(), 'slug' => $slug]) }}"
               class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-{{ $page['color'] }}-100 flex items-center justify-center">
                            <i class="{{ $page['icon'] }} text-{{ $page['color'] }}-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
                                {{ $page['label'] }}
                            </h3>
                        </div>
                    </div>
                    @if($customized[$slug] ?? false)
                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium">
                        {{ __('Customized') }}
                    </span>
                    @else
                    <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-xs font-medium">
                        {{ __('Default') }}
                    </span>
                    @endif
                </div>

                <div class="mt-4 flex items-center text-sm text-blue-600 group-hover:text-blue-700">
                    <span>{{ __('Edit page content') }}</span>
                    <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }} {{ app()->getLocale() === 'ar' ? 'mr-2' : 'ml-2' }} transition-transform group-hover:translate-x-1"></i>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
