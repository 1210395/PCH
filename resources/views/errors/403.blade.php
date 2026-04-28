@extends('layout.main')

@section('head')
    <title>{{ config('app.name') }} - {{ __('Forbidden') }}</title>
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
        <div class="max-w-lg w-full text-center">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-gray-700 to-gray-900 rounded-full flex items-center justify-center mb-6 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            <p class="text-sm font-semibold text-gray-700 mb-2">{{ __('403') }}</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">{{ __('Forbidden') }}</h1>
            <p class="text-gray-600 mb-8">{{ __("You don't have permission to access this page.") }}</p>

            <a href="{{ url(app()->getLocale()) }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-green-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                {{ __('Go to Home') }}
            </a>
        </div>
    </div>
@endsection
