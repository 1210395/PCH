@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Log In') }}</title>
<meta name="description" content="{{ __('Log in to your TecnoPark account') }}">
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-700 to-green-500 bg-clip-text text-transparent mb-2">
                {{ __('Welcome Back') }}
            </h1>
            <p class="text-gray-600">{{ __('Log in to your account to continue') }}</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post', ['locale' => app()->getLocale()]) }}" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('email') border-red-500 @enderror"
                        placeholder="you@example.com"
                    >
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Password') }}</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('password') border-red-500 @enderror"
                        placeholder="{{ __('Enter your password') }}"
                    >
                    @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">{{ __('Contact TechnoPark if you forgot your password') }}</p>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <label for="remember" class="text-sm text-gray-600">{{ __('Remember me for 30 days') }}</label>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-[1.02] shadow-lg hover:shadow-xl"
                >
                    {{ __('Log In') }}
                </button>
            </form>
        </div>

        <!-- Register Link -->
        <p class="mt-6 text-center text-gray-600">
            {{ __("Don't have an account?") }}
<a href="{{ route('register', ['locale' => app()->getLocale()]) }}" class="text-blue-600 font-medium hover:underline">{{ __('Sign Up') }}</a>
        </p>
    </div>
</div>
@endsection
