@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Verify Email') }}</title>
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

            <p class="text-gray-600 mb-6">
                {{ __('Please click the verification link in the email we sent you. If you didn\'t receive the email, you can request a new one.') }}
            </p>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-amber-800">
                    {{ __('The verification link will expire in 24 hours. Don\'t forget to check your spam/junk folder.') }}
                </p>
            </div>

            <form method="POST" action="{{ route('verification.send', ['locale' => app()->getLocale()]) }}" x-data="{ sending: false }">
                @csrf
                @if(Auth::guard('designer')->check())
                    <input type="hidden" name="email" value="{{ Auth::guard('designer')->user()->email }}">
                @endif
                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-300 shadow-lg disabled:opacity-50"
                    :disabled="sending"
                    @click="sending = true"
                >
                    <span x-show="!sending">{{ __('Resend Verification Email') }}</span>
                    <span x-show="sending">{{ __('Sending...') }}</span>
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-gray-600">
            <a href="{{ route('login', ['locale' => app()->getLocale()]) }}" class="text-blue-600 font-medium hover:underline">{{ __('Back to Login') }}</a>
        </p>
    </div>
</div>
@endsection
