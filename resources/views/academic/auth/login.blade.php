<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Academic Login - Palestine Creative Hub') }}</title>

    <!-- Hide page until Tailwind CSS is ready -->
    <style>body { opacity: 0; } body.ready { opacity: 1; transition: opacity 0.15s; }</style>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('ready'));</script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @if(app()->getLocale() === 'ar')
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>body { font-family: 'Noto Sans Arabic', system-ui, sans-serif; }</style>
    @endif
</head>
<body class="min-h-screen bg-gradient-to-br from-green-600 via-blue-600 to-green-700 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 mx-auto mb-4 bg-white rounded-2xl shadow-xl flex items-center justify-center">
                <i class="fas fa-university text-4xl text-green-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white">{{ __('Academic Portal') }}</h1>
            <p class="text-white/80 mt-2">{{ __('Palestine Creative Hub') }}</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">{{ __('Institution Login') }}</h2>

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('academic.login.submit', ['locale' => app()->getLocale()]) }}" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email Address') }}</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                               placeholder="{{ __('institution@example.com') }}">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Password') }}</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                               placeholder="{{ __('Enter your password') }}">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-green-600 to-blue-600 text-white font-semibold rounded-xl hover:from-green-700 hover:to-blue-700 focus:ring-4 focus:ring-green-500/50 transition-all">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    {{ __('Sign In') }}
                </button>
            </form>

            <!-- Back to Main Site -->
            <div class="mt-6 text-center">
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
                   class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>
                    {{ __('Back to Main Site') }}
                </a>
            </div>
        </div>

        <!-- Help Text -->
        <div class="mt-6 text-center text-white/70 text-sm">
            <p>{{ __('Need an account? Contact the administrator.') }}</p>
        </div>
    </div>
</body>
</html>
