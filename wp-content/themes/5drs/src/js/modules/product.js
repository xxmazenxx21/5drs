/**
 * Single product page: bristle pills, quantity stepper and AJAX add-to-cart.
 *
 * Progressive enhancement throughout. The native <select> and the plain
 * quantity field are what WooCommerce renders and what the form posts; this
 * module layers a nicer control on top and only hides the original once its
 * replacement is in place. With JavaScript off the page still works.
 */

import { showCartNotice } from './cart-notice.js';

const shop = () => window.fivedrsShop || {};
const strings = () => shop().i18n || {};

/* -------------------------------------------------------------------------
 * Bristle type pills
 * ---------------------------------------------------------------------- */

/**
 * Replace a variation <select> with a radio group of pills.
 *
 * The select stays in the DOM and remains the source of truth: every pill
 * click writes to it and dispatches a bubbling change event, which is what
 * WooCommerce's own variation script listens for.
 *
 * @param {HTMLSelectElement} select The attribute select.
 */
function buildPills( select ) {
	const options = Array.from( select.options ).filter( ( option ) => option.value !== '' );

	if ( options.length < 2 ) {
		return;
	}

	const group = document.createElement( 'div' );
	group.className = 'fivedrs-bristle';
	group.setAttribute( 'role', 'radiogroup' );

	const label = select.closest( 'tr' )?.querySelector( '.label label' );
	if ( label ) {
		if ( ! label.id ) {
			label.id = `${ select.id }-label`;
		}
		group.setAttribute( 'aria-labelledby', label.id );
	}

	const pills = options.map( ( option ) => {
		const pill = document.createElement( 'button' );
		pill.type = 'button';
		pill.className = 'fivedrs-bristle__pill';
		pill.dataset.value = option.value;
		pill.textContent = option.textContent;
		pill.setAttribute( 'role', 'radio' );
		pill.setAttribute( 'aria-checked', 'false' );
		pill.tabIndex = -1;
		group.appendChild( pill );
		return pill;
	} );

	/**
	 * Reflect the select's value on the pills.
	 *
	 * Also governs the roving tabindex: an unselected group is entered on the
	 * first pill, a selected one on the chosen pill.
	 */
	const sync = () => {
		let checkedIndex = -1;

		pills.forEach( ( pill, index ) => {
			const isChecked = pill.dataset.value === select.value;
			pill.setAttribute( 'aria-checked', isChecked ? 'true' : 'false' );
			pill.classList.toggle( 'is-selected', isChecked );
			if ( isChecked ) {
				checkedIndex = index;
			}
		} );

		pills.forEach( ( pill, index ) => {
			pill.tabIndex = index === ( checkedIndex === -1 ? 0 : checkedIndex ) ? 0 : -1;
		} );
	};

	const choose = ( pill ) => {
		select.value = pill.dataset.value;
		select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		sync();
		pill.focus();
	};

	pills.forEach( ( pill, index ) => {
		pill.addEventListener( 'click', () => choose( pill ) );

		pill.addEventListener( 'keydown', ( event ) => {
			const forward = [ 'ArrowRight', 'ArrowDown' ].includes( event.key );
			const back = [ 'ArrowLeft', 'ArrowUp' ].includes( event.key );

			if ( ! forward && ! back ) {
				return;
			}

			event.preventDefault();

			// In a radio group the arrows follow reading order, so they swap
			// meaning in a right-to-left layout.
			const rtl = document.documentElement.getAttribute( 'dir' ) === 'rtl';
			const step = ( forward ? 1 : -1 ) * ( rtl ? -1 : 1 );
			const next = pills[ ( index + step + pills.length ) % pills.length ];

			choose( next );
		} );
	} );

	select.parentNode.insertBefore( group, select );

	// Keep the select for WooCommerce, but out of the tab order and away from
	// assistive technology now that the pills represent it.
	select.classList.add( 'fivedrs-visually-hidden' );
	select.setAttribute( 'aria-hidden', 'true' );
	select.tabIndex = -1;

	// WooCommerce resets the select when "Clear" is used or a combination is
	// unavailable, so mirror any external change back onto the pills.
	select.addEventListener( 'change', sync );

	sync();
}

function initBristlePills() {
	document
		.querySelectorAll( '.variations_form .variations select' )
		.forEach( ( select ) => buildPills( select ) );
}

/* -------------------------------------------------------------------------
 * Quantity stepper
 * ---------------------------------------------------------------------- */

function initQuantity() {
	document.querySelectorAll( '[data-qty]' ).forEach( ( button ) => {
		button.addEventListener( 'click', () => {
			const wrapper = button.closest( '.quantity' );
			const input = wrapper && wrapper.querySelector( 'input.qty' );

			if ( ! input ) {
				return;
			}

			const step = parseFloat( input.getAttribute( 'step' ) ) || 1;
			const min = parseFloat( input.getAttribute( 'min' ) );
			const max = parseFloat( input.getAttribute( 'max' ) );
			const floor = Number.isNaN( min ) ? 1 : min;
			const current = parseFloat( input.value ) || floor;

			let next = button.dataset.qty === 'up' ? current + step : current - step;
			next = Math.max( floor, next );

			if ( ! Number.isNaN( max ) && max > 0 ) {
				next = Math.min( max, next );
			}

			if ( next === current ) {
				return;
			}

			input.value = String( next );
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	} );
}

/* -------------------------------------------------------------------------
 * AJAX add to cart
 * ---------------------------------------------------------------------- */

/**
 * Apply the cart fragments WooCommerce returns.
 *
 * @param {Object} fragments Selector => markup.
 */
function applyFragments( fragments ) {
	if ( ! fragments ) {
		return;
	}

	Object.keys( fragments ).forEach( ( selector ) => {
		document.querySelectorAll( selector ).forEach( ( element ) => {
			element.outerHTML = fragments[ selector ];
		} );
	} );
}

/**
 * Intercept the single product form and post it to WooCommerce's AJAX
 * endpoint instead of reloading the page.
 *
 * WooCommerce only wires this up on archives. For a variable product the
 * variation id is sent as product_id - the endpoint resolves the parent and
 * the chosen attributes from it.
 *
 * @param {HTMLFormElement} form The cart form.
 */
function bindForm( form ) {
	form.addEventListener( 'submit', ( event ) => {
		const button = form.querySelector( 'button[type="submit"], .single_add_to_cart_button' );

		if ( button && button.classList.contains( 'disabled' ) ) {
			return; // Let WooCommerce explain why.
		}

		const variationInput = form.querySelector( 'input[name="variation_id"]' );
		const variationId = variationInput ? parseInt( variationInput.value, 10 ) : 0;
		const isVariable = form.classList.contains( 'variations_form' );

		if ( isVariable && ! variationId ) {
			return; // Nothing chosen yet; the native hint covers it.
		}

		const addToCart = form.querySelector( '[name="add-to-cart"]' );
		const productId = variationId || ( addToCart && addToCart.value ) || form.dataset.product_id;

		if ( ! productId || ! shop().ajaxUrl ) {
			return; // Fall back to a normal submit.
		}

		event.preventDefault();

		const quantityInput = form.querySelector( 'input.qty' );
		const quantity = quantityInput ? quantityInput.value : 1;

		const body = new FormData();
		body.append( 'product_id', productId );
		body.append( 'quantity', quantity );

		if ( button ) {
			button.classList.add( 'is-loading' );
		}

		window
			.fetch( shop().ajaxUrl.replace( '%%endpoint%%', 'add_to_cart' ), {
				method: 'POST',
				credentials: 'same-origin',
				body,
			} )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				if ( button ) {
					button.classList.remove( 'is-loading' );
				}

				if ( ! data || data.error ) {
					showCartNotice( strings().error || 'Could not add to cart', true );
					return;
				}

				applyFragments( data.fragments );

				if ( window.jQuery ) {
					// Keeps WooCommerce's own fragment cache and the cart badge
					// bump in step with what just happened.
					window.jQuery( document.body ).trigger( 'added_to_cart', [
						data.fragments,
						data.cart_hash,
						window.jQuery( button ),
					] );
				}

				showCartNotice( strings().added || 'Added to your cart' );

				if ( button ) {
					button.classList.add( 'is-done' );
					window.setTimeout( () => button.classList.remove( 'is-done' ), 2000 );
				}
			} )
			.catch( () => {
				if ( button ) {
					button.classList.remove( 'is-loading' );
				}
				showCartNotice( strings().error || 'Could not add to cart', true );
			} );
	} );
}

function initAjaxCart() {
	document
		.querySelectorAll( '.single-product form.cart' )
		.forEach( ( form ) => bindForm( form ) );
}

/* -------------------------------------------------------------------------
 * Gallery
 * ---------------------------------------------------------------------- */

/**
 * Crossfade the main gallery image when a thumbnail is chosen.
 *
 * WooCommerce swaps the image instantly; this only adds the transition. With
 * a single product image there are no thumbnails and nothing happens.
 */
function initGallery() {
	const gallery = document.querySelector( '.woocommerce-product-gallery' );

	if ( ! gallery || window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	gallery.addEventListener( 'click', ( event ) => {
		if ( ! event.target.closest( '.flex-control-thumbs li' ) ) {
			return;
		}

		const wrapper = gallery.querySelector( '.woocommerce-product-gallery__image' );

		if ( ! wrapper ) {
			return;
		}

		wrapper.classList.remove( 'is-swapping' );
		void wrapper.offsetWidth;
		wrapper.classList.add( 'is-swapping' );
	} );
}

export default function initProduct() {
	initBristlePills();
	initQuantity();
	initAjaxCart();
	initGallery();
}
