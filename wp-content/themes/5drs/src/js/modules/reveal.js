/**
 * Reveal-on-scroll.
 *
 * Progressive enhancement: the hiding styles are scoped to a class this module
 * adds, so with JavaScript off - or if IntersectionObserver is missing - every
 * section is simply visible. Motion is also skipped outright when the visitor
 * has asked for reduced motion, rather than relying on the CSS media query
 * alone.
 */

export default function initReveal() {
	const targets = document.querySelectorAll( '[data-reveal]' );

	if ( ! targets.length || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	document.documentElement.classList.add( 'fivedrs-reveal-ready' );

	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					// Cards in a row carry an index so they arrive one after
					// another rather than all at once.
					const index = Number( entry.target.dataset.revealIndex || 0 );

					if ( index > 0 ) {
						entry.target.style.transitionDelay = `${ Math.min( index, 7 ) * 90 }ms`;
					}

					entry.target.classList.add( 'is-revealed' );
					observer.unobserve( entry.target );
				}
			} );
		},
		{ rootMargin: '0px 0px -10% 0px', threshold: 0.05 }
	);

	targets.forEach( ( target ) => observer.observe( target ) );
}
