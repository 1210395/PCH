{{-- JavaScript Files --}}

{{-- AlpineJS Plugins (must load before Alpine) --}}
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

{{-- AlpineJS for interactive components --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

{{-- Optimized Image Lazy Loading --}}
<script src="{{ asset('js/lazy-images.js') }}" defer></script>

{{-- Additional Scripts --}}
@stack('scripts')

{{-- Mobile Menu Toggle Script --}}
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
