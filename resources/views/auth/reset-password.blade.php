@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Set New Password') }}</title>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-700 to-green-500 bg-clip-text text-transparent mb-2">
                {{ __('Set New Password') }}
            </h1>
            <p class="text-gray-600">{{ __('Enter your new password below') }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.update', ['locale' => app()->getLocale()]) }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email Address') }}</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        required
                        readonly
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-600"
                    >
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('New Password') }}</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('password') border-red-500 @enderror"
                        placeholder="{{ __('Enter new password') }}"
                    >
                    @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">{{ __('Minimum 8 characters with uppercase, lowercase, number and symbol') }}</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Confirm New Password') }}</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                        placeholder="{{ __('Re-enter new password') }}"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-300 shadow-lg"
                >
                    {{ __('Reset Password') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
