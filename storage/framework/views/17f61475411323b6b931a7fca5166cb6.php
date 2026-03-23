


<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>


<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>


<script src="<?php echo e(asset('js/lazy-images.js')); ?>" defer></script>


<?php echo $__env->yieldPushContent('scripts'); ?>


<script>
    // Mobile menu toggle
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    // User dropdown toggle
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const userDropdown = document.getElementById('userDropdown');
        const userButton = event.target.closest('button[onclick="toggleUserDropdown()"]');

        if (userDropdown && !userButton && !userDropdown.contains(event.target)) {
            userDropdown.classList.add('hidden');
        }
    });

    // CSRF Token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        window.axios = window.axios || {};
        if (window.axios.defaults) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
        }
    }

    // Set CSRF token for fetch requests
    if (csrfToken) {
        window.csrfToken = csrfToken.content;
    }
</script>
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/partials/_javascript.blade.php ENDPATH**/ ?>