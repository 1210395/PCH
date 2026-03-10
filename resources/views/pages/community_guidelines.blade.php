@extends('pages._layout')

@section('page-content')
@php
    $isAr = app()->getLocale() === 'ar';
@endphp

@if(!empty($content['sections']))
<div class="space-y-10">
    @foreach($content['sections'] as $index => $section)
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0 mt-1">
                <span class="text-purple-600 font-bold">{{ $index + 1 }}</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-3">
                    {{ $isAr && !empty($section['title_ar']) ? $section['title_ar'] : ($section['title'] ?? '') }}
                </h2>
                <div class="text-gray-600 leading-relaxed page-content">
                    {!! nl2br(e($isAr && !empty($section['content_ar']) ? $section['content_ar'] : ($section['content'] ?? ''))) !!}
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
