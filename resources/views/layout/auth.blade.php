<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Prevent favicon.ico 404 errors --}}
    <link rel="icon" href="data:;base64,=" />

    @yield("head")

    {{-- Hide page until Tailwind CSS is ready --}}
    <style>body { opacity: 0; } body.ready { opacity: 1; transition: opacity 0.15s; }</style>

    {{-- Tailwind CSS CDN for standalone auth pages --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('ready'));</script>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @if(app()->getLocale() === 'ar')
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Noto Sans Arabic', system-ui, sans-serif;
        }
        html[dir="rtl"] .text-left { text-align: right; }
        html[dir="rtl"] .text-right { text-align: left; }
    </style>
    @else
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
    </style>
    @endif
</head>
<body class="auth-page">

    <!-- Auth Page Content (No Navbar/Footer) -->
    <main class="auth-content">
        @yield("content")
    </main>

    @yield("footer_js")
</body>
</html>
