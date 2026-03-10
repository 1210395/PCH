@extends('pages._layout')

@section('page-content')
@php
    $isAr = app()->getLocale() === 'ar';
@endphp

@if(!empty($content['sections']))
<div class="space-y-8">
    @foreach($content['sections'] as $index => $section)
    <div>
        <h2 class="text-xl font-bold text-gray-800 mb-3 flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-bold flex-shrink-0">{{ $index + 1 }}</span>
            {{ $isAr && !empty($section['title_ar']) ? $section['title_ar'] : ($section['title'] ?? '') }}
        </h2>
        <div class="text-gray-600 leading-relaxed page-content {{ $isAr ? 'pr-10' : 'pl-10' }}">
            {!! nl2br(e($isAr && !empty($section['content_ar']) ? $section['content_ar'] : ($section['content'] ?? ''))) !!}
        </div>
    </div>
    @if(!$loop->last)
    <hr class="border-gray-200">
    @endif
    @endforeach
</div>
@endif
@endsection
