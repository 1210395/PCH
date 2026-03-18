@extends('layout.main')

@section('head')
    <title>{{ config('app.name') }} - {{ __('Welcome!') }}</title>
    <meta name="description" content="{{ __('Thank you for joining Palestine Creative Hub') }}">
    <meta name="robots" content="noindex, nofollow">
    <style>
        /* Blur navbar and footer */
        nav,
        footer {
            filter: blur(8px);
            pointer-events: none;
        }
    </style>
@endsection

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 flex items-center justify-center py-12 px-4"
        x-data="{ countdown: 15 }" x-init="
            const interval = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(interval);
                    window.location.href = '{{ route('login', ['locale' => app()->getLocale()]) }}';
                }
            }, 1000);
        ">

        <!-- Backdrop overlay -->
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm"></div>

        <!-- Modal -->
        <div class="relative z-10 max-w-md w-full animate-fadeInUp">
            <!-- Logo above modal -->
            <div class="text-center mb-6">
                <a href="{{ url(app()->getLocale()) }}" class="inline-block">
                    <img src="{{ url('media/images/logo.webp') }}" alt="{{ config('app.name') }}" class="h-16 mx-auto">
                </a>
            </div>

            <!-- Modal Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-100 text-center">
                <!-- Success Icon with Animation -->
                <div
                    class="mx-auto w-20 h-20 bg-gradient-to-br from-blue-600 to-green-500 rounded-full flex items-center justify-center mb-6 shadow-lg animate-scaleIn">
                    <svg class="w-10 h-10 text-white animate-checkmark" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <!-- Message -->
                <h1 class="text-2xl font-bold text-gray-800 mb-3">
                    {{ __('Registration Successful!') }}
                </h1>
                <p class="text-gray-600 text-lg leading-relaxed mb-2">
                    {{ __('Thank you for joining Palestine Creative Hub. Your account has been created successfully!') }}
                </p>

                @if(session('registration_stats'))
                    @php $stats = session('registration_stats'); @endphp
                    <div class="bg-gray-50 rounded-lg p-4 mb-4 text-left">
                        <p class="text-sm text-gray-600 mb-2 font-medium">{{ __('Items saved:') }}</p>
                        <div class="flex gap-4 justify-center text-sm">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded">{{ $stats['products'] ?? 0 }} {{ __('Products') }}</span>
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded">{{ $stats['projects'] ?? 0 }} {{ __('Projects') }}</span>
                            <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded">{{ $stats['services'] ?? 0 }} {{ __('Services') }}</span>
                        </div>
                    </div>
                @endif

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-amber-800">{{ __('Verify your email') }}</p>
                            <p class="text-sm text-amber-700 mt-1">{{ __('We\'ve sent a verification link to your email. Please check your inbox and click the link to activate your account.') }}</p>
                            <p class="text-xs text-amber-600 mt-2">{{ __('The link expires in 24 hours. Check your spam folder if you don\'t see it.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Countdown Timer -->
                <div class="mb-6">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-50 to-green-50 rounded-xl border border-blue-200">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">
                            {{ __('Redirecting to login in') }} <span x-text="countdown" class="text-blue-600 font-bold"></span> {{ __('seconds') }}...
                        </span>
                    </div>
                </div>

                <!-- Decorative element -->
                <div class="flex justify-center gap-1 mb-6">
                    <span class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                    <span class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 100ms;"></span>
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 200ms;"></span>
                    <span class="w-2 h-2 bg-green-600 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
                </div>

                <!-- Manual Navigation Links -->
                <div class="flex flex-col gap-2">
                    <a href="{{ route('login', ['locale' => app()->getLocale()]) }}"
                        class="inline-block w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                        {{ __('Login Now') }}
                    </a>
                    <a href="{{ route('verification.notice', ['locale' => app()->getLocale(), 'email' => session('verification_email')]) }}"
                        class="inline-block w-full py-3 px-4 border-2 border-amber-300 text-amber-700 font-semibold rounded-xl hover:border-amber-400 hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200">
                        <svg class="w-5 h-5 inline-block {{ app()->getLocale() === 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        {{ __('Didn\'t receive the email? Resend') }}
                    </a>
                    <a href="{{ url(app()->getLocale()) }}"
                        class="inline-block w-full py-3 px-4 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:border-gray-400 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        {{ __('Go to Home') }}
                    </a>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    {{ __('Want to register another account?') }}
                    <a href="{{ route('register', ['locale' => app()->getLocale()]) }}"
                        class="text-blue-600 hover:text-blue-700 font-semibold hover:underline">
                        {{ __('Register here') }}
                    </a>
                </p>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.5);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes checkmark {
            0% {
                stroke-dasharray: 0, 100;
            }

            100% {
                stroke-dasharray: 100, 0;
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        .animate-scaleIn {
            animation: scaleIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        .animate-checkmark {
            stroke-dasharray: 100;
            stroke-dashoffset: 0;
            animation: checkmark 0.6s ease-in-out 0.3s forwards;
        }
    </style>
@endsection