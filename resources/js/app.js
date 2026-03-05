import './bootstrap';

// Import jQuery
import $ from 'jquery';
window.$ = window.jQuery = $;

// Import Bootstrap JS
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Import WOW.js
import WOW from 'wow.js';

// Import Swiper
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';

// Initialize WOW.js
window.WOW = WOW;

// Initialize Swiper
window.Swiper = Swiper;
window.SwiperModules = { Navigation, Pagination, Autoplay };

// Auto-initialize WOW.js on page load
document.addEventListener('DOMContentLoaded', function() {
    new WOW().init();
});
