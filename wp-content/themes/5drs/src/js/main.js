/**
 * 5drs theme entry point.
 *
 * Styles are imported here so the Vite dev server can hot-reload them; the
 * production build still emits a standalone CSS file that PHP enqueues.
 */

import '../css/main.css';

import initOffcanvas from './modules/offcanvas.js';
import initHeader from './modules/header.js';
import initCart from './modules/cart.js';
import initReveal from './modules/reveal.js';
import initShop from './modules/shop.js';
import initProduct from './modules/product.js';
import initSlideshow from './modules/slideshow.js';
import initParallax from './modules/parallax.js';

/**
 * Current document direction. Prefer CSS logical properties over branching on
 * this - it exists for the rare case where JS genuinely needs to know.
 *
 * @returns {'rtl'|'ltr'} Document direction.
 */
export function direction() {
	return document.documentElement.getAttribute( 'dir' ) === 'rtl' ? 'rtl' : 'ltr';
}

function boot() {
	document.documentElement.classList.add( 'js' );
	initHeader();
	initOffcanvas();
	initCart();
	initReveal();
	initShop();
	initProduct();
	initSlideshow();
	initParallax();
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot );
} else {
	boot();
}
