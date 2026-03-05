@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Forgot Password') }}</title>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-700 to-green-500 bg-clip-text text-transparent mb-2">
                {{ __('Reset Password') }}
            </h1>
            <p class="text-gray-600">{{ __('Enter your email to receive a password reset link') }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <form method="POST" action="#" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email Address') }}</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                        placeholder="you@example.com"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-300 shadow-lg"
                >
                    {{ __('Send Reset Link') }}
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-gray-600">
            {{ __('Remember your password?') }}
            <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-blue-600 font-medium hover:underline">{{ __('Log In') }}</a>
        </p>
    </div>
</div>
@endsection
