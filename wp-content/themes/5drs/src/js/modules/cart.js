/**
 * Cart badge.
 *
 * The count itself is swapped by WooCommerce's cart-fragments script, which
 * replaces the element matching the fragment key registered in
 * inc/woocommerce.php - so no markup is built here. This only adds a short
 * bump animation when the count changes, as feedback for an AJAX add-to-cart.
 */

export default function initCart() {
	const bump = () => {
		const count = document.querySelector( '.fivedrs-cart__count' );
		if ( ! count ) {
			return;
		}
		count.classList.remove( 'is-bumped' );
		// Force a reflow so the animation restarts on repeated adds.
		void count.offsetWidth;
		count.classList.add( 'is-bumped' );
	};

	// WooCommerce fires these on jQuery; guard in case jQuery is absent.
	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'added_to_cart wc_fragments_refreshed', bump );
	}
}
