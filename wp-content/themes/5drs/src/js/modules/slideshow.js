/**
 * About page image slideshow.
 *
 * Progressive enhancement: the markup ships every slide stacked in a fixed
 * frame with the first one visible and the controls hidden, so without
 * JavaScript the page shows a single image and nothing is broken. This adds
 * the crossfade, the dots and the pause control.
 *
 * Auto-advancing content needs a way to stop it (WCAG 2.2.2), so the pause
 * button is always available, and the slideshow also pauses itself whenever
 * the viewer is unlikely to be watching: pointer over it, keyboard focus
 * inside it, scrolled off screen, or the tab in the background.
 */

const REDUCED = () => window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/**
 * Wire up one slideshow.
 *
 * @param {HTMLElement} root The [data-slideshow] element.
 */
function setup( root ) {
	const slides = Array.from( root.querySelectorAll( '[data-slide]' ) );
	const dots = Array.from( root.querySelectorAll( '[data-slideshow-dot]' ) );
	const toggle = root.querySelector( '[data-slideshow-toggle]' );
	const controls = root.querySelector( '[data-slideshow-controls]' );

	if ( slides.length < 2 ) {
		return;
	}

	// The controls are only useful once this script is running.
	if ( controls ) {
		controls.hidden = false;
	}

	const interval = parseInt( root.dataset.slideshowInterval, 10 ) || 5000;
	const reduced = REDUCED();

	let current = 0;
	let timer = null;
	let pausedByUser = reduced; // Reduced motion starts paused: no autoplay.
	let pausedByContext = false;

	/**
	 * Fetch the slide after the given one, so it is decoded before it shows.
	 *
	 * @param {number} index Index of the slide currently showing.
	 */
	const primeNext = ( index ) => {
		const next = slides[ ( index + 1 ) % slides.length ];

		if ( next && next.getAttribute( 'loading' ) === 'lazy' ) {
			next.setAttribute( 'loading', 'eager' );
		}
	};

	const show = ( index ) => {
		current = ( index + slides.length ) % slides.length;

		slides.forEach( ( slide, i ) => {
			const isActive = i === current;
			slide.classList.toggle( 'is-active', isActive );
			slide.setAttribute( 'aria-hidden', isActive ? 'false' : 'true' );
		} );

		dots.forEach( ( dot, i ) => {
			const isActive = i === current;
			dot.classList.toggle( 'is-active', isActive );
			if ( isActive ) {
				dot.setAttribute( 'aria-current', 'true' );
			} else {
				dot.removeAttribute( 'aria-current' );
			}
		} );

		primeNext( current );
	};

	const stop = () => {
		window.clearInterval( timer );
		timer = null;
	};

	const start = () => {
		stop();

		if ( pausedByUser || pausedByContext || reduced ) {
			return;
		}

		timer = window.setInterval( () => show( current + 1 ), interval );
	};

	const setContextPause = ( paused ) => {
		pausedByContext = paused;
		start();
	};

	// --- the explicit control ------------------------------------------------
	if ( toggle ) {
		const labels = {
			pause: toggle.getAttribute( 'aria-label' ) || 'Pause slideshow',
			play: toggle.dataset.playLabel || toggle.getAttribute( 'aria-label' ) || 'Play slideshow',
		};

		const reflect = () => {
			root.classList.toggle( 'is-paused', pausedByUser );
			toggle.setAttribute( 'aria-pressed', pausedByUser ? 'true' : 'false' );
			toggle.setAttribute( 'aria-label', pausedByUser ? labels.play : labels.pause );
		};

		toggle.addEventListener( 'click', () => {
			pausedByUser = ! pausedByUser;
			reflect();
			start();
		} );

		reflect();
	}

	// --- dots ------------------------------------------------------------------
	dots.forEach( ( dot, index ) => {
		dot.addEventListener( 'click', () => {
			show( index );
			start(); // Restart the clock so the chosen slide gets a full turn.
		} );
	} );

	// --- contextual pausing -------------------------------------------------------
	root.addEventListener( 'mouseenter', () => setContextPause( true ) );
	root.addEventListener( 'mouseleave', () => setContextPause( false ) );
	root.addEventListener( 'focusin', () => setContextPause( true ) );
	root.addEventListener( 'focusout', () => {
		if ( ! root.contains( document.activeElement ) ) {
			setContextPause( false );
		}
	} );

	document.addEventListener( 'visibilitychange', () => setContextPause( document.hidden ) );

	if ( 'IntersectionObserver' in window ) {
		const observer = new IntersectionObserver(
			( [ entry ] ) => setContextPause( ! entry.isIntersecting ),
			{ threshold: 0.2 }
		);
		observer.observe( root );
	}

	show( 0 );
	start();
}

export default function initSlideshow() {
	document.querySelectorAll( '[data-slideshow]' ).forEach( setup );
}
