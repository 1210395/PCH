@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Log In') }}</title>
<meta name="description" content="{{ __('Log in to your Palestine Creative Hub account') }}">
<meta name="robots" content="noindex, nofollow">
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
        <div class="bg-white rounded-2xl shadow-xl p-5 sm:p-8 border border-gray-100">
            @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                {{ session('status') }}
            </div>
            @endif

            {{-- Unverified email notice with resend option --}}
            @if($errors->has('unverified'))
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg" x-data="{ sending: false, sent: false }">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm text-amber-800 font-medium">{{ __('Email not verified') }}</p>
                        <p class="text-sm text-amber-700 mt-1">{{ $errors->first('email') }}</p>
                        {{-- @click sets sending=true immediately so the button
                             disables before the form actually submits — closes
                             the 100ms double-click race the previous setTimeout
                             pattern left open. (bugs.md M-29) --}}
                        <form method="POST" action="{{ route('verification.send', ['locale' => app()->getLocale()]) }}" class="mt-3">
                            @csrf
                            <input type="hidden" name="email" value="{{ old('email') }}">
                            <button
                                type="submit"
                                class="text-sm font-semibold text-blue-600 hover:text-blue-800 underline disabled:opacity-50"
                                :disabled="sending"
                                x-show="!sent"
                                @click="sending = true; sent = true"
                            >
                                <span x-show="!sending">{{ __('Resend verification email') }}</span>
                                <span x-show="sending">{{ __('Sending...') }}</span>
                            </button>
                            <span x-show="sent" class="text-sm text-green-600 font-medium">{{ __('Verification email sent!') }}</span>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            {{-- x-data busy guard prevents a double-click from POSTing twice
                 (which would otherwise trigger the per-account login limiter
                 and lock the user out for 1 minute on a benign double-click). --}}
            <form method="POST"
                  action="{{ route('login.post', ['locale' => app()->getLocale()]) }}"
                  class="space-y-5"
                  x-data="{ busy: false }"
                  @submit="busy = true">
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
                    @if(!$errors->has('unverified'))
                        @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    @endif
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
                </div>

                <!-- Remember Me + Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <label for="remember" class="text-sm text-gray-600">{{ __('Remember me') }}</label>
                    </div>
                    <a href="{{ route('password.request', ['locale' => app()->getLocale()]) }}" class="text-sm text-blue-600 hover:underline font-medium">
                        {{ __('Forgot password?') }}
                    </a>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="busy"
                    :class="busy ? 'opacity-60 cursor-not-allowed' : ''"
                    class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-[1.02] shadow-lg hover:shadow-xl"
                >
                    <span x-show="!busy">{{ __('Log In') }}</span>
                    <span x-show="busy" x-cloak>{{ __('Logging in...') }}</span>
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
