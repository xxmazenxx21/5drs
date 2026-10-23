/**
 * Adds a shadow to the sticky header once the page has scrolled.
 *
 * Uses a sentinel element plus IntersectionObserver rather than a scroll
 * listener, so nothing runs on the main thread while scrolling.
 */

export default function initHeader() {
	const header = document.querySelector( '[data-header]' );
	const sentinel = document.querySelector( '[data-header-sentinel]' );

	if ( ! header || ! sentinel || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	const observer = new IntersectionObserver(
		( [ entry ] ) => {
			header.classList.toggle( 'is-stuck', ! entry.isIntersecting );
		},
		{ rootMargin: '0px', threshold: 0 }
	);

	observer.observe( sentinel );
}
