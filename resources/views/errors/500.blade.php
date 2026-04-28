@extends('layout.main')

@section('head')
    <title>{{ config('app.name') }} - {{ __('Something Went Wrong') }}</title>
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
        <div class="max-w-lg w-full text-center">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center mb-6 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <p class="text-sm font-semibold text-red-600 mb-2">{{ __('500') }}</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">{{ __('Something Went Wrong') }}</h1>
            <p class="text-gray-600 mb-8">{{ __("We're sorry — an unexpected error occurred on our end. Our team has been notified. Please try again in a moment.") }}</p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url(app()->getLocale()) }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-green-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                    {{ __('Go to Home') }}
                </a>
                <button onclick="window.location.reload()"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all">
                    {{ __('Try Again') }}
                </button>
            </div>
        </div>
    </div>
@endsection
