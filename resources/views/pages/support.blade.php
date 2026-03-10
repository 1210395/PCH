@extends('pages._layout')

@section('page-content')
@php
    $isAr = app()->getLocale() === 'ar';
@endphp

<!-- Intro Content -->
@if(!empty($content['content']) || !empty($content['content_ar']))
<div class="text-gray-600 leading-relaxed page-content mb-12">
    {!! nl2br(e($isAr && !empty($content['content_ar']) ? $content['content_ar'] : ($content['content'] ?? ''))) !!}
</div>
@endif

<!-- Contact Info Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
    @if(!empty($content['contact_email']))
    <div class="bg-blue-50 rounded-xl p-6 border border-blue-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-envelope text-blue-600 text-xl"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">{{ __('Email Support') }}</h3>
                <a href="mailto:{{ $content['contact_email'] }}" class="text-blue-600 hover:underline">{{ $content['contact_email'] }}</a>
            </div>
        </div>
    </div>
    @endif
    @if(!empty($content['contact_phone']))
    <div class="bg-green-50 rounded-xl p-6 border border-green-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-phone text-green-600 text-xl"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">{{ __('Phone Support') }}</h3>
                <a href="tel:{{ preg_replace('/\s+/', '', $content['contact_phone']) }}" class="text-green-600 hover:underline" dir="ltr">{{ $content['contact_phone'] }}</a>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- FAQ Section -->
@if(!empty($content['faq_items']) && count($content['faq_items']) > 0)
<div>
    <h2 class="text-2xl font-bold text-gray-800 mb-6">{{ __('Frequently Asked Questions') }}</h2>
    <div class="space-y-4" x-data="{ openFaq: null }">
        @foreach($content['faq_items'] as $index => $faq)
        @if(!empty($faq['question']) || !empty($faq['question_ar']))
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <button @click="openFaq = openFaq === {{ $index }} ? null : {{ $index }}"
                class="w-full flex items-center justify-between px-6 py-4 text-{{ $isAr ? 'right' : 'left' }} hover:bg-gray-50 transition-colors">
                <span class="font-medium text-gray-800">
                    {{ $isAr && !empty($faq['question_ar']) ? $faq['question_ar'] : ($faq['question'] ?? '') }}
                </span>
                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': openFaq === {{ $index }} }"></i>
            </button>
            <div x-show="openFaq === {{ $index }}" x-collapse>
                <div class="px-6 pb-4 text-gray-600 leading-relaxed border-t border-gray-100 pt-4">
                    {!! nl2br(e($isAr && !empty($faq['answer_ar']) ? $faq['answer_ar'] : ($faq['answer'] ?? ''))) !!}
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif
@endsection
