<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', __('Admin Panel')) - {{ __('Palestine Creative Hub') }}</title>
    <link rel="icon" href="data:;base64,=" />

    <!-- Preconnect hints for external resources -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    <!-- Pre-compiled Tailwind CSS + Font Awesome (inlined to bypass server static file issues) -->
    @php
        $manifest = @json_decode(@file_get_contents(public_path('build/manifest.json')), true) ?: [];
        $adminCss = $manifest['resources/css/admin.css']['file'] ?? 'assets/admin-CyMh2Lim.css';
        $adminCss = preg_replace('/[^a-zA-Z0-9\-_\.\/]/', '', $adminCss);
        $cssPath = public_path('build/' . $adminCss);
        $cssContent = '';
        if (file_exists($cssPath) && str_starts_with(realpath($cssPath), realpath(public_path('build')))) {
            $cssContent = file_get_contents($cssPath);
        }
    @endphp
    <style>{!! $cssContent !!}</style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-item.active { background: linear-gradient(to right, #2563eb, #10b981); }
        @if(app()->getLocale() === 'ar')
        body, html[dir="rtl"] body { font-family: 'Noto Sans Arabic', system-ui, sans-serif; text-align: right; }

        /* RTL: Flip sidebar to right side */
        html[dir="rtl"] aside.fixed.left-0 { left: auto; right: 0; }

        /* RTL: Margin flips */
        html[dir="rtl"] .ml-1 { margin-left: 0; margin-right: 0.25rem; }
        html[dir="rtl"] .ml-2 { margin-left: 0; margin-right: 0.5rem; }
        html[dir="rtl"] .ml-3 { margin-left: 0; margin-right: 0.75rem; }
        html[dir="rtl"] .ml-4 { margin-left: 0; margin-right: 1rem; }
        html[dir="rtl"] .mr-1 { margin-right: 0; margin-left: 0.25rem; }
        html[dir="rtl"] .mr-2 { margin-right: 0; margin-left: 0.5rem; }
        html[dir="rtl"] .mr-3 { margin-right: 0; margin-left: 0.75rem; }

        /* RTL: Positioning flips */
        html[dir="rtl"] .left-0 { left: auto; right: 0; }
        html[dir="rtl"] .right-0 { right: auto; left: 0; }

        /* RTL: Text alignment flips */
        html[dir="rtl"] .text-left { text-align: right; }
        html[dir="rtl"] .text-right { text-align: left; }

        /* RTL: Padding flips */
        html[dir="rtl"] .pl-10 { padding-left: 0; padding-right: 2.5rem; }
        html[dir="rtl"] .pl-4 { padding-left: 0; padding-right: 1rem; }
        html[dir="rtl"] .pr-4 { padding-right: 0; padding-left: 1rem; }

        /* RTL: Space-x reverse */
        html[dir="rtl"] .space-x-2 > :not([hidden]) ~ :not([hidden]),
        html[dir="rtl"] .space-x-3 > :not([hidden]) ~ :not([hidden]),
        html[dir="rtl"] .space-x-4 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }

        /* RTL: Icon flips */
        html[dir="rtl"] .fa-arrow-right::before,
        html[dir="rtl"] .fa-chevron-right::before { transform: scaleX(-1); }
        html[dir="rtl"] .fa-arrow-left::before,
        html[dir="rtl"] .fa-chevron-left::before { transform: scaleX(-1); }

        /* RTL: Gradient direction flip */
        html[dir="rtl"] .sidebar-item.active { background: linear-gradient(to left, #2563eb, #10b981); }
        html[dir="rtl"] .bg-gradient-to-r { background-image: linear-gradient(to left, var(--tw-gradient-stops)); }

        /* RTL: Form inputs text direction */
        html[dir="rtl"] input[type="text"],
        html[dir="rtl"] input[type="search"],
        html[dir="rtl"] input[type="email"],
        html[dir="rtl"] input[type="password"],
        html[dir="rtl"] textarea,
        html[dir="rtl"] select {
            text-align: right;
        }
        @endif
    </style>
    @if(app()->getLocale() === 'ar')
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet" />
    @endif

    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: true, showToast: false, toastMessage: '', toastType: 'success' }">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        @include('admin.partials.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col transition-all duration-300" :class="{ '{{ app()->getLocale() === 'ar' ? 'mr-64' : 'ml-64' }}': sidebarOpen, '{{ app()->getLocale() === 'ar' ? 'mr-20' : 'ml-20' }}': !sidebarOpen }">
            <!-- Top Navigation -->
            @include('admin.partials.topnav')

            <!-- Page Content -->
            <main class="flex-1 p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 px-6">
                <p class="text-center text-gray-500 text-sm">
                    {{ __('Palestine Creative Hub') }} {{ __('Admin Panel') }}
                </p>
            </footer>
        </div>
    </div>

    <!-- Toast Notification -->
    @include('admin.partials.toast')

    <!-- Global Scripts -->
    <script>
        // CSRF token for AJAX requests
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Helper function for AJAX requests
        async function adminFetch(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                },
            };

            const response = await fetch(url, { ...defaultOptions, ...options });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'An error occurred');
            }

            return data;
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const event = new CustomEvent('show-toast', {
                detail: { message, type }
            });
            window.dispatchEvent(event);
        }
    </script>

    @stack('scripts')
</body>
</html>
