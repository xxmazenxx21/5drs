/**
 * Product archive behaviour: the category pill indicator, the staggered grid
 * reveal, and the button states for WooCommerce's own AJAX add-to-cart.
 */

import { showCartNotice } from './cart-notice.js';

const reduced = () => window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/**
 * Slide a highlight behind the active category pill.
 *
 * Purely decorative: the pills are real links and the active one is already
 * marked with aria-current and a colour change.
 */
function initPills() {
	const nav = document.querySelector( '[data-pills]' );

	if ( ! nav ) {
		return;
	}

	const list = nav.querySelector( '.fivedrs-pills__list' );
	const active = nav.querySelector( '.fivedrs-pills__item.is-active' );

	if ( ! list || ! active || reduced() ) {
		return;
	}

	const indicator = document.createElement( 'span' );
	indicator.className = 'fivedrs-pills__indicator';
	indicator.setAttribute( 'aria-hidden', 'true' );
	list.appendChild( indicator );

	const place = ( target, animate ) => {
		const navBox = list.getBoundingClientRect();
		const box = target.getBoundingClientRect();

		indicator.style.transition = animate ? '' : 'none';
		// Measured from the list's start edge, which works in both directions
		// because getBoundingClientRect is physical and so is translate.
		indicator.style.width = `${ box.width }px`;
		indicator.style.transform = `translateX(${ box.left - navBox.left }px)`;
		indicator.style.height = `${ box.height }px`;
	};

	place( active, false );
	window.requestAnimationFrame( () => indicator.classList.add( 'is-ready' ) );

	nav.querySelectorAll( '.fivedrs-pills__item' ).forEach( ( pill ) => {
		pill.addEventListener( 'mouseenter', () => place( pill, true ) );
		pill.addEventListener( 'focus', () => place( pill, true ) );
	} );

	nav.addEventListener( 'mouseleave', () => place( active, true ) );
	window.addEventListener( 'resize', () => place( active, false ) );
}

/**
 * Reveal product cards in a short stagger as the grid scrolls into view.
 */
function initGrid() {
	const cards = document.querySelectorAll( '.woocommerce ul.products li.product' );

	if ( ! cards.length || ! ( 'IntersectionObserver' in window ) || reduced() ) {
		return;
	}

	document.documentElement.classList.add( 'fivedrs-grid-ready' );

	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( ! entry.isIntersecting ) {
					return;
				}
				const index = Number( entry.target.dataset.revealIndex || 0 );
				entry.target.style.transitionDelay = `${ Math.min( index, 7 ) * 70 }ms`;
				entry.target.classList.add( 'is-revealed' );
				observer.unobserve( entry.target );
			} );
		},
		{ rootMargin: '0px 0px -8% 0px', threshold: 0.05 }
	);

	cards.forEach( ( card, index ) => {
		card.dataset.revealIndex = String( index % 8 );
		observer.observe( card );
	} );
}

/**
 * Loading and success states for WooCommerce's archive add-to-cart buttons.
 *
 * WooCommerce already performs the request and swaps the cart fragments; this
 * only reflects it in the button and raises the shared notice.
 */
function initArchiveButtons() {
	if ( ! window.jQuery ) {
		return;
	}

	const $ = window.jQuery;

	$( document.body ).on( 'adding_to_cart', ( event, $button ) => {
		if ( $button && $button.length ) {
			$button.get( 0 ).classList.add( 'is-loading' );
		}
	} );

	$( document.body ).on( 'added_to_cart', ( event, fragments, cartHash, $button ) => {
		const i18n = ( window.fivedrsShop && window.fivedrsShop.i18n ) || {};
		showCartNotice( i18n.added || 'Added to your cart' );

		if ( ! $button || ! $button.length ) {
			return;
		}

		const button = $button.get( 0 );
		button.classList.remove( 'is-loading' );
		button.classList.add( 'is-done' );
		window.setTimeout( () => button.classList.remove( 'is-done' ), 2000 );
	} );
}

export default function initShop() {
	initPills();
	initGrid();
	initArchiveButtons();
}
