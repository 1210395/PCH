@extends('layout.main')

@section('head')
    <title>{{ config('app.name') }} - {{ __('Page Expired') }}</title>
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

            <p class="text-sm font-semibold text-amber-600 mb-2">{{ __('419') }}</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">{{ __('Page Expired') }}</h1>
            <p class="text-gray-600 mb-8">{{ __("This form has expired for your security. Please refresh the page and try again.") }}</p>

            <button onclick="window.location.reload()"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-green-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                {{ __('Refresh Page') }}
            </button>
        </div>
    </div>
@endsection
