@extends('layout.main')

@section('head')
    <title>{{ config('app.name') }} - {{ __('Page Not Found') }}</title>
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
        <div class="max-w-lg w-full text-center">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-blue-600 to-green-500 rounded-full flex items-center justify-center mb-6 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <p class="text-sm font-semibold text-blue-600 mb-2">{{ __('404') }}</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">{{ __('Page Not Found') }}</h1>
            <p class="text-gray-600 mb-8">{{ __("Sorry, we couldn't find the page you're looking for. It may have moved or no longer exists.") }}</p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url(app()->getLocale()) }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-green-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    {{ __('Go to Home') }}
                </a>
                <a href="javascript:history.back()"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all">
                    <svg class="w-5 h-5 {{ app()->getLocale() === 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Go Back') }}
                </a>
            </div>
        </div>
    </div>
@endsection
