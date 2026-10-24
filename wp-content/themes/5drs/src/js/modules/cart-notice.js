/**
 * The on-brand "added to cart" notice.
 *
 * Shared by the product grid and the single product page. One element is
 * reused for the lifetime of the page, so repeated adds re-animate the same
 * node instead of stacking notices up the screen.
 */

const strings = () => ( window.fivedrsShop && window.fivedrsShop.i18n ) || {};

let node = null;
let hideTimer = null;

function build() {
	if ( node ) {
		return node;
	}

	const i18n = strings();

	node = document.createElement( 'div' );
	node.className = 'fivedrs-notice';
	node.setAttribute( 'role', 'status' );
	node.setAttribute( 'aria-live', 'polite' );
	node.innerHTML = `
		<span class="fivedrs-notice__icon" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
				stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><polyline points="5,12.5 10,17.5 19,6.5"/></svg>
		</span>
		<span class="fivedrs-notice__text"></span>
		<a class="fivedrs-notice__link" href="${ ( window.fivedrsShop && window.fivedrsShop.cartUrl ) || '#' }"></a>
		<button type="button" class="fivedrs-notice__close" aria-label="${ i18n.dismiss || 'Dismiss' }">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
				stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="5.5" y1="5.5" x2="18.5" y2="18.5"/><line x1="18.5" y1="5.5" x2="5.5" y2="18.5"/></svg>
		</button>`;

	node.querySelector( '.fivedrs-notice__close' ).addEventListener( 'click', hide );
	document.body.appendChild( node );

	return node;
}

function hide() {
	if ( node ) {
		node.classList.remove( 'is-visible' );
	}
	window.clearTimeout( hideTimer );
}

/**
 * Show the notice.
 *
 * @param {string} message Text to display.
 * @param {boolean} isError Style it as an error.
 */
export function showCartNotice( message, isError = false ) {
	const i18n = strings();
	const el = build();

	el.classList.toggle( 'fivedrs-notice--error', isError );
	el.querySelector( '.fivedrs-notice__text' ).textContent = message;

	const link = el.querySelector( '.fivedrs-notice__link' );
	link.textContent = isError ? '' : ( i18n.viewCart || 'View cart' );
	link.hidden = isError;

	// Restart the entrance animation even if the notice is already up.
	el.classList.remove( 'is-visible' );
	void el.offsetWidth;
	el.classList.add( 'is-visible' );

	window.clearTimeout( hideTimer );
	hideTimer = window.setTimeout( hide, 6000 );
}
