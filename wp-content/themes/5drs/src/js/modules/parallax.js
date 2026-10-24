/**
 * Gentle parallax on the full-bleed hero image.
 *
 * The translate is applied to the media wrapper, not the image, because the
 * image already carries the Ken Burns scale animation and the two transforms
 * would otherwise fight. Desktop pointers only, and never under reduced
 * motion.
 */

const MAX_SHIFT = 60; // px

export default function initParallax() {
	const media = document.querySelector( '[data-hero] .fivedrs-phero__media' );

	if ( ! media ) {
		return;
	}

	const fine = window.matchMedia( '(min-width: 64rem) and (hover: hover) and (pointer: fine)' );
	const reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	if ( ! fine.matches || reduced.matches ) {
		return;
	}

	const hero = media.closest( '[data-hero]' );
	let ticking = false;

	const update = () => {
		ticking = false;

		const rect = hero.getBoundingClientRect();

		// Only while the hero is on screen.
		if ( rect.bottom < 0 || rect.top > window.innerHeight ) {
			return;
		}

		const progress = Math.min( Math.max( -rect.top / rect.height, 0 ), 1 );
		media.style.transform = `translate3d(0, ${ ( progress * MAX_SHIFT ).toFixed( 2 ) }px, 0)`;
	};

	const onScroll = () => {
		if ( ticking ) {
			return;
		}
		ticking = true;
		window.requestAnimationFrame( update );
	};

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll, { passive: true } );
	update();
}
