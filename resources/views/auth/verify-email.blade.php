@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Verify Email') }}</title>
<meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center mb-6 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-700 to-green-500 bg-clip-text text-transparent mb-2">
                {{ __('Check Your Email') }}
            </h1>
            <p class="text-gray-600">{{ __('We\'ve sent a verification link to your email address') }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100 text-center">
            @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                {{ session('status') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <p class="text-gray-600 mb-6">
                {{ __('Please click the verification link in the email we sent you. If you didn\'t receive the email, you can request a new one.') }}
            </p>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-amber-800">
                    {{ __('The verification link will expire in 24 hours. Don\'t forget to check your spam/junk folder.') }}
                </p>
            </div>

            @php
                $userEmail = Auth::guard('designer')->check()
                    ? Auth::guard('designer')->user()->email
                    : (request('email') ?? session('verification_email') ?? old('email') ?? '');
            @endphp

            @if($userEmail)
                {{-- Email known - show resend button --}}
                <form method="POST" action="{{ route('verification.send', ['locale' => app()->getLocale()]) }}" x-data="{ sending: false, sent: false, cooldown: 0 }">
                    @csrf
                    <input type="hidden" name="email" value="{{ $userEmail }}">
                    <p class="text-sm text-gray-500 mb-4">{{ __('Sending to') }}: <strong>{{ $userEmail }}</strong></p>
                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-300 shadow-lg disabled:opacity-50"
                        :disabled="sending || cooldown > 0"
                        @click="sending = true; setTimeout(() => { sending = false; sent = true; cooldown = 60; let iv = setInterval(() => { cooldown--; if(cooldown <= 0) clearInterval(iv); }, 1000); }, 100)"
                    >
                        <span x-show="!sending && !sent && cooldown <= 0">{{ __('Resend Verification Email') }}</span>
                        <span x-show="sending">{{ __('Sending...') }}</span>
                        <span x-show="!sending && cooldown > 0">{{ __('Resend available in') }} <span x-text="cooldown"></span>s</span>
                    </button>
                </form>
            @else
                {{-- Email unknown - show email input form --}}
                <form method="POST" action="{{ route('verification.send', ['locale' => app()->getLocale()]) }}" x-data="{ sending: false, cooldown: 0 }">
                    @csrf
                    <div class="mb-4">
                        <label for="resend_email" class="block text-sm font-medium text-gray-700 mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('Email Address') }}</label>
                        <input id="resend_email" type="email" name="email" value="{{ old('email') }}" required maxlength="255"
                               aria-label="{{ __('Email Address') }}"
                               placeholder="you@example.com"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-300 shadow-lg disabled:opacity-50"
                        :disabled="sending || cooldown > 0"
                        @click="sending = true; setTimeout(() => { sending = false; cooldown = 60; let iv = setInterval(() => { cooldown--; if(cooldown <= 0) clearInterval(iv); }, 1000); }, 100)"
                    >
                        <span x-show="!sending && cooldown <= 0">{{ __('Resend Verification Email') }}</span>
                        <span x-show="sending">{{ __('Sending...') }}</span>
                        <span x-show="!sending && cooldown > 0">{{ __('Resend available in') }} <span x-text="cooldown"></span>s</span>
                    </button>
                </form>
            @endif
        </div>

        <p class="mt-6 text-center text-gray-600">
            <a href="{{ route('login', ['locale' => app()->getLocale()]) }}" class="text-blue-600 font-medium hover:underline">{{ __('Back to Login') }}</a>
        </p>
    </div>
</div>
@endsection
