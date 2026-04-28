@extends('layout.main')

@section('head')
    <title>{{ config('app.name') }} - {{ __('Too Many Requests') }}</title>
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
        <div class="max-w-lg w-full text-center">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-amber-500 to-orange-600 rounded-full flex items-center justify-center mb-6 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <p class="text-sm font-semibold text-amber-600 mb-2">{{ __('429') }}</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">{{ __('Too Many Requests') }}</h1>
            <p class="text-gray-600 mb-2">{{ __("You're doing that a bit too often. Please slow down and try again in a minute.") }}</p>
            <p class="text-sm text-gray-500 mb-8">{{ __("If you're seeing this on a normal page, your network may be sharing an address with many other users.") }}</p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url(app()->getLocale()) }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-green-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                    {{ __('Go to Home') }}
                </a>
                <button onclick="window.location.reload()"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:border-gray-400 hover:bg-gray-50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ __('Try Again') }}
                </button>
            </div>
        </div>
    </div>
@endsection
