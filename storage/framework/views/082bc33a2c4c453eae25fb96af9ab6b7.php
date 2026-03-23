<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $__env->yieldContent('title', __('Admin Panel')); ?> - <?php echo e(__('Palestine Creative Hub')); ?></title>
    <link rel="icon" href="data:;base64,=" />

    <!-- Preconnect hints for external resources -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    <!-- Pre-compiled Tailwind CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('build/assets/admin-CyMh2Lim.css')); ?>">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-item.active { background: linear-gradient(to right, #2563eb, #10b981); }
        <?php if(app()->getLocale() === 'ar'): ?>
        body, html[dir="rtl"] body { font-family: 'Noto Sans Arabic', system-ui, sans-serif; text-align: right; }

        /* RTL: Ensure sidebar stays on right side */
        html[dir="rtl"] aside.fixed { right: 0 !important; left: auto !important; }

        /* RTL: Margin flips */
        html[dir="rtl"] .ml-1 { margin-left: 0; margin-right: 0.25rem; }
        html[dir="rtl"] .ml-2 { margin-left: 0; margin-right: 0.5rem; }
        html[dir="rtl"] .ml-3 { margin-left: 0; margin-right: 0.75rem; }
        html[dir="rtl"] .ml-4 { margin-left: 0; margin-right: 1rem; }
        html[dir="rtl"] .mr-1 { margin-right: 0; margin-left: 0.25rem; }
        html[dir="rtl"] .mr-2 { margin-right: 0; margin-left: 0.5rem; }
        html[dir="rtl"] .mr-3 { margin-right: 0; margin-left: 0.75rem; }

        /* RTL: Positioning flips - exclude sidebar which handles its own RTL */
        html[dir="rtl"] .left-0:not(aside) { left: auto; right: 0; }
        html[dir="rtl"] .right-0:not(aside) { right: auto; left: 0; }

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
        <?php endif; ?>
    </style>
    <?php if(app()->getLocale() === 'ar'): ?>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: true, showToast: false, toastMessage: '', toastType: 'success' }">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col transition-all duration-300"
             :style="sidebarOpen ? '<?php echo e(app()->getLocale() === 'ar' ? 'margin-right: 16rem' : 'margin-left: 16rem'); ?>' : '<?php echo e(app()->getLocale() === 'ar' ? 'margin-right: 5rem' : 'margin-left: 5rem'); ?>'">
            <!-- Top Navigation -->
            <?php echo $__env->make('admin.partials.topnav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                <!-- Flash Messages -->
                <?php if(session('success')): ?>
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 px-6">
                <p class="text-center text-gray-500 text-sm">
                    <?php echo e(__('Palestine Creative Hub')); ?> <?php echo e(__('Admin Panel')); ?>

                </p>
            </footer>
        </div>
    </div>

    <!-- Toast Notification -->
    <?php echo $__env->make('admin.partials.toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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

    <script>
    // Shared Alpine.js helper — computes smart page-number array with "…" gaps.
    // Extracted here to avoid embedding < and > operators inside HTML attributes.
    function getPageNums(pg, pages) {
        const t = pages, c = pg;
        if (t <= 7) return Array.from({length: t}, (_, i) => i + 1);
        const s = new Set([1, t, c, Math.max(2, c - 1), Math.min(t - 1, c + 1)]);
        return [...s].sort((a, b) => a - b).reduce((acc, n, i, arr) => {
            if (i > 0 && n - arr[i - 1] > 1) acc.push('…');
            acc.push(n);
            return acc;
        }, []);
    }
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/admin/layouts/app.blade.php ENDPATH**/ ?>