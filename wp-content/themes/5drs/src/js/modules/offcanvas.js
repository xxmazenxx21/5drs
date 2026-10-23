/**
 * Accessible off-canvas menu.
 *
 * Handles focus trapping, Esc to close, aria-expanded, body scroll lock and
 * restoring focus to the trigger. The panel itself slides from the start edge,
 * which CSS flips for RTL.
 */

const FOCUSABLE = [
	'a[href]',
	'button:not([disabled])',
	'input:not([disabled]):not([type="hidden"])',
	'select:not([disabled])',
	'textarea:not([disabled])',
	'[tabindex]:not([tabindex="-1"])',
].join( ',' );

export default function initOffcanvas() {
	const panel = document.querySelector( '[data-offcanvas]' );
	const backdrop = document.querySelector( '[data-offcanvas-backdrop]' );
	const openers = document.querySelectorAll( '[data-offcanvas-open]' );
	const closers = document.querySelectorAll( '[data-offcanvas-close]' );

	if ( ! panel || ! backdrop || ! openers.length ) {
		return;
	}

	let lastFocused = null;

	const focusable = () =>
		Array.from( panel.querySelectorAll( FOCUSABLE ) ).filter(
			( el ) => el.offsetParent !== null || el === document.activeElement
		);

	const open = ( trigger ) => {
		lastFocused = trigger || document.activeElement;
		panel.classList.add( 'is-open' );
		backdrop.classList.add( 'is-open' );
		panel.removeAttribute( 'inert' );
		panel.setAttribute( 'aria-hidden', 'false' );
		document.body.classList.add( 'is-menu-open' );
		openers.forEach( ( b ) => b.setAttribute( 'aria-expanded', 'true' ) );

		// Wait for the panel to become visible before moving focus into it.
		window.requestAnimationFrame( () => {
			const first = focusable()[ 0 ];
			( first || panel ).focus();
		} );
	};

	const close = () => {
		if ( ! panel.classList.contains( 'is-open' ) ) {
			return;
		}
		panel.classList.remove( 'is-open' );
		backdrop.classList.remove( 'is-open' );
		panel.setAttribute( 'inert', '' );
		panel.setAttribute( 'aria-hidden', 'true' );
		document.body.classList.remove( 'is-menu-open' );
		openers.forEach( ( b ) => b.setAttribute( 'aria-expanded', 'false' ) );

		if ( lastFocused && document.contains( lastFocused ) ) {
			lastFocused.focus();
		}
	};

	const trapTab = ( event ) => {
		const items = focusable();
		if ( ! items.length ) {
			event.preventDefault();
			return;
		}

		const first = items[ 0 ];
		const last = items[ items.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	};

	openers.forEach( ( button ) =>
		button.addEventListener( 'click', () => open( button ) )
	);
	closers.forEach( ( button ) => button.addEventListener( 'click', close ) );
	backdrop.addEventListener( 'click', close );

	document.addEventListener( 'keydown', ( event ) => {
		if ( ! panel.classList.contains( 'is-open' ) ) {
			return;
		}
		if ( event.key === 'Escape' ) {
			event.preventDefault();
			close();
		} else if ( event.key === 'Tab' ) {
			trapTab( event );
		}
	} );

	// Returning to desktop width while the panel is open would otherwise leave
	// the body scroll-locked behind a hidden panel.
	const desktop = window.matchMedia( '(min-width: 64rem)' );
	desktop.addEventListener( 'change', ( event ) => {
		if ( event.matches ) {
			close();
		}
	} );

	panel.setAttribute( 'inert', '' );
	panel.setAttribute( 'aria-hidden', 'true' );
}
