@extends('pages._layout')

@section('page-content')
@php
    $isAr = app()->getLocale() === 'ar';
@endphp

@if($siteLinks)
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @foreach($siteLinks as $category => $links)
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-folder text-blue-500"></i>
            {{ $category }}
        </h2>
        <ul class="space-y-2">
            @foreach($links as $link)
            <li>
                <a href="{{ $link['url'] }}" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-2 text-sm py-1">
                    <i class="fas fa-chevron-{{ $isAr ? 'left' : 'right' }} text-xs text-gray-400"></i>
                    {{ $link['title'] }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    @endforeach
</div>
@endif

<!-- Additional Content -->
@if(!empty($content['additional_content']) || !empty($content['additional_content_ar']))
<div class="mt-12 text-gray-600 leading-relaxed page-content">
    {!! nl2br(e($isAr && !empty($content['additional_content_ar']) ? $content['additional_content_ar'] : ($content['additional_content'] ?? ''))) !!}
</div>
@endif
@endsection
