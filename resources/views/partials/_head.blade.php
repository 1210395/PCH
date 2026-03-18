{{-- Head Meta Tags and Assets --}}
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Default Meta Tags --}}
<title>@yield('title', config('app.name', 'Palestine Creative Hub'))</title>
<meta name="description" content="@yield('description', 'Palestine Creative Hub - Empowering Palestinian creativity through technology, innovation, and collaboration.')">
<meta name="keywords" content="@yield('keywords', 'Palestine, Creative Hub, Design, Technology, Innovation')">

{{-- Open Graph Meta Tags --}}
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:title" content="@yield('og_title', config('app.name'))">
<meta property="og:description" content="@yield('og_description', 'Palestine Creative Hub - Empowering Palestinian creativity through technology, innovation, and collaboration.')">
<meta property="og:image" content="@yield('og_image', url('media/images/logo.png'))">
<meta property="og:image:width" content="@yield('og_image_width', '512')">
<meta property="og:image:height" content="@yield('og_image_height', '512')">
<meta property="og:site_name" content="{{ config('app.name', 'Palestine Creative Hub') }}">
<meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_PS' : 'en_US' }}">

{{-- Twitter Card Meta Tags --}}
<meta name="twitter:card" content="@yield('twitter_card', 'summary')">
<meta name="twitter:title" content="@yield('og_title', config('app.name'))">
<meta name="twitter:description" content="@yield('og_description', 'Palestine Creative Hub - Empowering Palestinian creativity through technology, innovation, and collaboration.')">
<meta name="twitter:image" content="@yield('og_image', url('media/images/logo.png'))">

{{-- Canonical URL (strip query params to avoid duplicate indexing) --}}
@php
    $currentUrl = url()->current();
    $currentLocale = app()->getLocale();
    $altLocale = $currentLocale === 'ar' ? 'en' : 'ar';
    $altUrl = str_replace("/{$currentLocale}/", "/{$altLocale}/", $currentUrl);
@endphp
<link rel="canonical" href="{{ $currentUrl }}" />

{{-- Hreflang tags for bilingual SEO --}}
<link rel="alternate" hreflang="en" href="{{ str_replace("/{$currentLocale}/", "/en/", $currentUrl) }}" />
<link rel="alternate" hreflang="ar" href="{{ str_replace("/{$currentLocale}/", "/ar/", $currentUrl) }}" />
<link rel="alternate" hreflang="x-default" href="{{ str_replace("/{$currentLocale}/", "/en/", $currentUrl) }}" />

{{-- Prevent favicon.ico 404 errors --}}
<link rel="icon" href="data:;base64,=" />

{{-- Fonts --}}
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
@if(app()->getLocale() === 'ar')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet" />
@endif

{{-- Hide page until Tailwind CSS is ready --}}
<style>body { opacity: 0; } body.ready { opacity: 1; transition: opacity 0.15s; }</style>

{{-- Tailwind CSS CDN --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: true,
        }
    };
    document.addEventListener('DOMContentLoaded', () => document.body.classList.add('ready'));
</script>

{{-- AlpineJS is loaded in _javascript.blade.php to avoid duplicate loading --}}

<style>
    body {
        font-family: 'Instrument Sans', sans-serif;
    }

    @if(app()->getLocale() === 'ar')
    body, html[dir="rtl"] body {
        font-family: 'Noto Sans Arabic', 'Instrument Sans', sans-serif;
    }
    @endif

    /* Alpine.js x-cloak directive - hides elements until Alpine is ready */
    [x-cloak] {
        display: none !important;
    }

    /* RTL utility overrides */
    html[dir="rtl"] .rtl\:text-right { text-align: right; }
    html[dir="rtl"] .rtl\:text-left { text-align: left; }
    html[dir="rtl"] .rtl\:mr-0 { margin-right: 0; }
    html[dir="rtl"] .rtl\:ml-0 { margin-left: 0; }
    html[dir="rtl"] .rtl\:space-x-reverse > :not([hidden]) ~ :not([hidden]) {
        --tw-space-x-reverse: 1;
    }
    html[dir="rtl"] .rtl\:rotate-180 { transform: rotate(180deg); }
    html[dir="rtl"] .rtl\:flip { transform: scaleX(-1); }

    /* Comprehensive RTL layout fixes */
    html[dir="rtl"] {
        /* Flip flex direction for horizontal layouts */
        /* text-align default */
        text-align: right;
    }

    /* Flip margins and paddings that use directional classes */
    html[dir="rtl"] .ml-1 { margin-left: 0; margin-right: 0.25rem; }
    html[dir="rtl"] .ml-2 { margin-left: 0; margin-right: 0.5rem; }
    html[dir="rtl"] .ml-3 { margin-left: 0; margin-right: 0.75rem; }
    html[dir="rtl"] .ml-4 { margin-left: 0; margin-right: 1rem; }
    html[dir="rtl"] .ml-6 { margin-left: 0; margin-right: 1.5rem; }
    html[dir="rtl"] .ml-8 { margin-left: 0; margin-right: 2rem; }
    html[dir="rtl"] .ml-13 { margin-left: 0; margin-right: 3.25rem; }
    html[dir="rtl"] .mr-1 { margin-right: 0; margin-left: 0.25rem; }
    html[dir="rtl"] .mr-2 { margin-right: 0; margin-left: 0.5rem; }
    html[dir="rtl"] .mr-3 { margin-right: 0; margin-left: 0.75rem; }
    html[dir="rtl"] .mr-4 { margin-right: 0; margin-left: 1rem; }

    html[dir="rtl"] .ml-1\.5 { margin-left: 0; margin-right: 0.375rem; }
    html[dir="rtl"] .mr-1\.5 { margin-right: 0; margin-left: 0.375rem; }

    /* Flip left/right positioning */
    html[dir="rtl"] .left-0 { left: auto !important; right: 0 !important; }
    html[dir="rtl"] .left-3 { left: auto !important; right: 0.75rem !important; }
    html[dir="rtl"] .left-4 { left: auto !important; right: 1rem !important; }
    html[dir="rtl"] .right-0 { right: auto !important; left: 0 !important; }
    html[dir="rtl"] .right-3 { right: auto !important; left: 0.75rem !important; }
    html[dir="rtl"] .right-4 { right: auto !important; left: 1rem !important; }

    /* Flip padding-left/right for search inputs */
    html[dir="rtl"] .pl-10 { padding-left: 0.5rem !important; padding-right: 2.5rem !important; }
    html[dir="rtl"] .pl-11 { padding-left: 0.5rem !important; padding-right: 2.75rem !important; }
    html[dir="rtl"] .pl-4 { padding-left: 0 !important; padding-right: 1rem !important; }
    html[dir="rtl"] .pr-4 { padding-right: 0 !important; padding-left: 1rem !important; }
    html[dir="rtl"] .pr-10 { padding-right: 0.5rem !important; padding-left: 2.5rem !important; }

    /* Flip text-align */
    html[dir="rtl"] .text-left { text-align: right; }
    html[dir="rtl"] .text-right { text-align: left; }

    /* Flip flex-row items order */
    html[dir="rtl"] .space-x-2 > :not([hidden]) ~ :not([hidden]),
    html[dir="rtl"] .space-x-3 > :not([hidden]) ~ :not([hidden]),
    html[dir="rtl"] .space-x-4 > :not([hidden]) ~ :not([hidden]),
    html[dir="rtl"] .space-x-8 > :not([hidden]) ~ :not([hidden]) {
        --tw-space-x-reverse: 1;
    }

    /* Flip border-left/right */
    html[dir="rtl"] .border-l-4 { border-left: 0; border-right-width: 4px; }
    html[dir="rtl"] .border-l-2 { border-left: 0; border-right-width: 2px; }
    html[dir="rtl"] .border-r-4 { border-right: 0; border-left-width: 4px; }

    /* Flip rounded corners */
    html[dir="rtl"] .rounded-l-lg { border-radius: 0 0.5rem 0.5rem 0; }
    html[dir="rtl"] .rounded-r-lg { border-radius: 0.5rem 0 0 0.5rem; }
    html[dir="rtl"] .rounded-t-lg { border-radius: 0.5rem 0.5rem 0 0; }

    /* Flip translate-x for RTL */
    html[dir="rtl"] .-translate-x-full { transform: translateX(100%); }
    html[dir="rtl"] .translate-x-full { transform: translateX(-100%); }

    /* Flip arrow/chevron SVGs in navigation */
    html[dir="rtl"] .fa-arrow-right::before,
    html[dir="rtl"] .fa-chevron-right::before { transform: scaleX(-1); }

    /* Input icons - search icon positioning */
    html[dir="rtl"] input[type="text"],
    html[dir="rtl"] input[type="search"],
    html[dir="rtl"] input[type="email"],
    html[dir="rtl"] input[type="password"],
    html[dir="rtl"] textarea,
    html[dir="rtl"] select {
        text-align: right;
    }

    /* Search icon: keep in same position (left side) in RTL, do NOT rotate */
    html[dir="rtl"] .search-icon-fixed {
        left: 0.75rem !important;
        right: auto !important;
        transform: translateY(-50%) !important;
    }
    /* fa-search icon: do NOT flip in RTL */
    html[dir="rtl"] .fa-search::before {
        transform: none !important;
    }

    /* Ensure dropdown menus open from the correct side */
    html[dir="rtl"] .absolute.left-0 { left: auto; right: 0; }
    html[dir="rtl"] .absolute.right-0 { right: auto; left: 0; }

    /* Fix for the sidebar in admin */
    html[dir="rtl"] aside.fixed.left-0 { left: auto; right: 0; }

    /* Flip gradient directions */
    html[dir="rtl"] .bg-gradient-to-r { background-image: linear-gradient(to left, var(--tw-gradient-stops)); }
    html[dir="rtl"] .bg-gradient-to-l { background-image: linear-gradient(to right, var(--tw-gradient-stops)); }
</style>

{{-- Additional Head Content --}}
@stack('styles')
