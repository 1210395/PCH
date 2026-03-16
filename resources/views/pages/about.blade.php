@extends('pages._layout')

@section('page-content')
@php
    $isAr = app()->getLocale() === 'ar';
@endphp

<!-- Content Sections -->
@if(!empty($content['sections']))
<div class="space-y-16">
    @foreach($content['sections'] as $index => $section)
    <div class="flex flex-col {{ $index % 2 === 1 ? 'md:flex-row-reverse' : 'md:flex-row' }} gap-8 items-center">
        <div class="{{ !empty($section['image']) ? 'md:w-3/5' : 'w-full' }}">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">
                {{ $isAr && !empty($section['title_ar']) ? $section['title_ar'] : ($section['title'] ?? '') }}
            </h2>
            <div class="text-gray-600 leading-relaxed page-content">
                {!! nl2br(e($isAr && !empty($section['content_ar']) ? $section['content_ar'] : ($section['content'] ?? ''))) !!}
            </div>
        </div>
        @if(!empty($section['image']))
        <div class="md:w-2/5">
            <img src="{{ url('media/' . $section['image']) }}" alt="{{ $section['title'] ?? '' }}" class="rounded-xl shadow-lg w-full">
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

<!-- Team Section -->
@if(!empty($content['team_members']) && count($content['team_members']) > 0)
<div class="mt-16 pt-12 border-t border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">
        {{ $isAr && !empty($content['team_title_ar']) ? $content['team_title_ar'] : ($content['team_title'] ?? __('Our Team')) }}
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
        @foreach($content['team_members'] as $member)
        @if(!empty($member['name']) || !empty($member['name_ar']))
        <div class="text-center">
            @if(!empty($member['image']))
            <img src="{{ url('media/' . $member['image']) }}" alt="{{ $member['name'] ?? '' }}" class="w-24 h-24 rounded-full object-cover mx-auto mb-3 shadow-md">
            @else
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-green-500 mx-auto mb-3 flex items-center justify-center text-white text-2xl font-bold shadow-md">
                {{ mb_substr($isAr && !empty($member['name_ar']) ? $member['name_ar'] : ($member['name'] ?? '?'), 0, 1) }}
            </div>
            @endif
            <h3 class="font-semibold text-gray-800">
                {{ $isAr && !empty($member['name_ar']) ? $member['name_ar'] : ($member['name'] ?? '') }}
            </h3>
            <p class="text-sm text-gray-500">
                {{ $isAr && !empty($member['role_ar']) ? $member['role_ar'] : ($member['role'] ?? '') }}
            </p>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif
@endsection
