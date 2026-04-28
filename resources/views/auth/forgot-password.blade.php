@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Forgot Password') }}</title>
<meta name="robots" content="noindex, nofollow">
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
            @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                {{ session('status') }}
            </div>
            @endif

            {{-- busy guard prevents double-submit which would otherwise count
                 as 2 attempts against the password-reset throttle. --}}
            <form method="POST" action="{{ route('password.email', ['locale' => app()->getLocale()]) }}" class="space-y-5"
                  x-data="{ busy: false }" @submit="busy = true">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email Address') }}</label>
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

                <button
                    type="submit"
                    :disabled="busy"
                    :class="busy ? 'opacity-60 cursor-not-allowed' : ''"
                    class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-300 shadow-lg"
                >
                    <span x-show="!busy">{{ __('Send Reset Link') }}</span>
                    <span x-show="busy" x-cloak>{{ __('Sending...') }}</span>
                </button>
            </form>

            <div class="mt-4 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                <p class="text-xs text-blue-700">
                    {{ __('The reset link will expire in 15 minutes. If you don\'t receive the email, check your spam folder.') }}
                </p>
            </div>
        </div>

        <p class="mt-6 text-center text-gray-600">
            {{ __('Remember your password?') }}
            <a href="{{ route('login', ['locale' => app()->getLocale()]) }}" class="text-blue-600 font-medium hover:underline">{{ __('Log In') }}</a>
        </p>
    </div>
</div>
@endsection
