/**
 * Optimized Image Lazy Loading
 * Uses Intersection Observer API for efficient lazy loading
 * Includes fallback for older browsers
 */

(function() {
    'use strict';

    // Configuration
    const config = {
        rootMargin: '50px 0px',  // Start loading 50px before image enters viewport
        threshold: 0.01,
        enableWebP: true,
        retryAttempts: 3,
        retryDelay: 1000
    };

    // Check if browser supports Intersection Observer
    const supportsIntersectionObserver = 'IntersectionObserver' in window;

    // Check WebP support
    let supportsWebP = false;
    if (config.enableWebP) {
        const webP = new Image();
        webP.src = 'data:image/webp;base64,UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAwA0JaQAA3AA/vuUAAA=';
        webP.onload = webP.onerror = function() {
            supportsWebP = (webP.height === 1);
        };
    }

    /**
     * Load an image with retry logic
     */
    function loadImageWithRetry(img, src, attempts = 0) {
        return new Promise((resolve, reject) => {
            const tempImg = new Image();

            tempImg.onload = function() {
                img.src = src;
                img.classList.remove('lazy-image');
                img.classList.add('lazy-loaded');
                resolve();
            };

            tempImg.onerror = function() {
                if (attempts < config.retryAttempts) {
                    setTimeout(() => {
                        loadImageWithRetry(img, src, attempts + 1)
                            .then(resolve)
                            .catch(reject);
                    }, config.retryDelay * (attempts + 1));
                } else {
                    reject(new Error('Failed to load image after retries'));
                }
            };

            // Convert to WebP if supported and available
            if (supportsWebP && src.match(/\.(jpg|jpeg|png)$/i)) {
                const webpSrc = src.replace(/\.(jpg|jpeg|png)$/i, '.webp');
                // Try WebP first, fallback to original
                const webpTest = new Image();
                webpTest.onload = function() {
                    tempImg.src = webpSrc;
                };
                webpTest.onerror = function() {
                    tempImg.src = src;
                };
                webpTest.src = webpSrc;
            } else {
                tempImg.src = src;
            }
        });
    }

    /**
     * Load image
     */
    function loadImage(img) {
        const src = img.dataset.src;

        if (!src || img.dataset.loading === 'true') {
            return;
        }

        img.dataset.loading = 'true';

        loadImageWithRetry(img, src)
            .then(() => {
                // Image loaded successfully
                img.dataset.loading = 'false';
                img.dataset.loaded = 'true';

                // Trigger custom event
                const event = new CustomEvent('imageLoaded', {
                    detail: { img: img, src: src }
                });
                img.dispatchEvent(event);
            })
            .catch((error) => {
                console.warn('Failed to load image:', src, error);
                img.dataset.loading = 'false';
                img.dataset.loadError = 'true';

                // Trigger error event
                const event = new CustomEvent('imageLoadError', {
                    detail: { img: img, src: src, error: error }
                });
                img.dispatchEvent(event);

                // Trigger native onerror if exists
                if (img.onerror) {
                    img.onerror();
                }
            });
    }

    /**
     * Initialize Intersection Observer
     */
    function initIntersectionObserver() {
        const images = document.querySelectorAll('.lazy-image[data-src]');

        if (images.length === 0) {
            return;
        }

        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    loadImage(img);
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: config.rootMargin,
            threshold: config.threshold
        });

        images.forEach(img => {
            imageObserver.observe(img);
        });
    }

    /**
     * Fallback for browsers without Intersection Observer
     */
    function fallbackLazyLoad() {
        const images = document.querySelectorAll('.lazy-image[data-src]');

        function isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= -50 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) + 50 &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }

        function checkImages() {
            images.forEach(img => {
                if (img.dataset.loaded !== 'true' && isInViewport(img)) {
                    loadImage(img);
                }
            });
        }

        // Check on scroll, resize, and initial load
        let timeout;
        const handleScroll = function() {
            clearTimeout(timeout);
            timeout = setTimeout(checkImages, 100);
        };

        window.addEventListener('scroll', handleScroll, { passive: true });
        window.addEventListener('resize', handleScroll, { passive: true });
        checkImages();
    }

    /**
     * Preload critical images (above the fold)
     */
    function preloadCriticalImages() {
        const criticalImages = document.querySelectorAll('img[data-critical="true"]');

        criticalImages.forEach(img => {
            if (img.dataset.src) {
                loadImage(img);
            }
        });
    }

    /**
     * Initialize lazy loading
     */
    function init() {
        // Preload critical images first
        preloadCriticalImages();

        // Initialize lazy loading
        if (supportsIntersectionObserver) {
            initIntersectionObserver();
        } else {
            fallbackLazyLoad();
        }

        // Re-initialize when new content is added dynamically
        const mutationObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) {
                    if (supportsIntersectionObserver) {
                        initIntersectionObserver();
                    }
                }
            });
        });

        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose API for manual loading
    window.LazyImages = {
        load: loadImage,
        loadAll: function() {
            const images = document.querySelectorAll('.lazy-image[data-src]');
            images.forEach(loadImage);
        },
        reload: init
    };
})();
