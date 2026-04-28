<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    @include("partials._head")

    {{-- RTL alignment shim — flips Tailwind's text-left/text-right when the
         document direction is RTL so legacy class names still align correctly
         in Arabic. Already in layout/auth.blade.php and layout/chat.blade.php;
         duplicating here means main-layout pages no longer stay LTR-aligned
         in Arabic. (bugs.md H-20) --}}
    <style>
        html[dir="rtl"] .text-left { text-align: right; }
        html[dir="rtl"] .text-right { text-align: left; }
    </style>

    @yield("head")
</head>
<body class="{{ request()->segment(2) ? 'internal-page ' . request()->segment(2) : 'home-page' }}">

    @include("partials._navbar")

    @include("partials._subheader")

    <!-- Page Content -->
    <main class="main-content">
        @yield("content")
    </main>

    @include("partials._footer")

    @include("partials._cookie_consent")

    @include("partials._javascript")

    @yield("footer_js")
</body>
</html>
