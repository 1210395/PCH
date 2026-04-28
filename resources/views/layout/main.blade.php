<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    @include("partials._head")

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
