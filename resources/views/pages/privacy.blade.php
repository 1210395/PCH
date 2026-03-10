@extends('pages._layout')

@section('page-content')
@php
    $isAr = app()->getLocale() === 'ar';
@endphp

@if(!empty($content['sections']))
<div class="space-y-8">
    @foreach($content['sections'] as $index => $section)
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 shadow-sm">
        <h2 class="text-xl font-bold text-gray-800 mb-3 flex items-center gap-3">
            <i class="fas fa-shield-alt text-teal-500"></i>
            {{ $isAr && !empty($section['title_ar']) ? $section['title_ar'] : ($section['title'] ?? '') }}
        </h2>
        <div class="text-gray-600 leading-relaxed page-content">
            {!! nl2br(e($isAr && !empty($section['content_ar']) ? $section['content_ar'] : ($section['content'] ?? ''))) !!}
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
