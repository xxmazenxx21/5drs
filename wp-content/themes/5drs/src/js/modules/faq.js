/**
 * FAQ accordion.
 *
 * The markup stays a native <details>/<summary> pair, so with JavaScript off
 * the accordion still opens, is still keyboard operable and is still exposed
 * correctly to assistive technology. All this adds is the height transition
 * the element does not do by itself:
 *
 *   opening - the panel is measured, animated from 0 to its height, then the
 *             inline height is dropped so later content changes still fit;
 *   closing - `open` is kept on until the animation finishes, because clearing
 *             it immediately would remove the panel before it could shrink.
 *
 * Reduced motion skips the module entirely and lets the browser do what it
 * normally does.
 */

const DURATION = 280;

/**
 * Wire one <details> up to an animated open and close.
 *
 * @param {HTMLDetailsElement} item One FAQ entry.
 */
function initItem( item ) {
	const panel = item.querySelector( '.fivedrs-faq__answer' );
	const summary = item.querySelector( 'summary' );

	if ( ! panel || ! summary ) {
		return;
	}

	// The panel carries its own bottom padding, which a height of 0 would
	// still render - so it is animated alongside the height.
	const padding = window.getComputedStyle( panel ).paddingBlockEnd;

	let animation = null;
	let closing = false;

	const stop = () => {
		if ( ! animation ) {
			return;
		}
		// Detach first: cancel() fires its event asynchronously, and by then
		// `animation` may already point at the replacement.
		const running = animation;
		animation = null;
		running.onfinish = null;
		running.cancel();
	};

	const run = ( from, to, opening, onDone ) => {
		stop();

		panel.style.overflow = 'hidden';

		animation = panel.animate(
			{
				height: [ `${ from }px`, `${ to }px` ],
				paddingBlockEnd: opening ? [ '0px', padding ] : [ padding, '0px' ],
			},
			{ duration: DURATION, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
		);

		animation.onfinish = () => {
			animation = null;
			panel.style.overflow = '';
			onDone();
		};
	};

	summary.addEventListener( 'click', ( event ) => {
		event.preventDefault();

		// `open` is still true while the panel is shrinking, so the flag is
		// what decides the direction - otherwise a click mid-close would
		// start a second close instead of reopening.
		if ( ! item.open || closing ) {
			// Measured before `open` is set, or a closed panel would report
			// its full height and there would be nothing to animate.
			const from = item.open ? panel.getBoundingClientRect().height : 0;

			closing = false;
			item.open = true;
			run( from, panel.scrollHeight, true, () => {} );
			return;
		}

		closing = true;
		run( panel.getBoundingClientRect().height, 0, false, () => {
			closing = false;
			item.open = false;
		} );
	} );
}

export default function initFaq() {
	const items = document.querySelectorAll( '.fivedrs-faq__item' );

	if ( ! items.length ) {
		return;
	}

	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	items.forEach( initItem );
}
