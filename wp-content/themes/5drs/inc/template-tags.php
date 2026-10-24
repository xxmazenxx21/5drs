<?php
/**
 * Template tags: logo, icons, cart button, contact and social output.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tags allowed when printing an inline SVG icon.
 *
 * @return array<string,array<string,bool>>
 */
function fivedrs_kses_svg() {
	return array(
		'svg'      => array(
			'class'           => true,
			'xmlns'           => true,
			'viewbox'         => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'aria-hidden'     => true,
			'focusable'       => true,
			'role'            => true,
			'width'           => true,
			'height'          => true,
		),
		'path'     => array(
			'd'               => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
		),
		'circle'   => array(
			'cx'           => true,
			'cy'           => true,
			'r'            => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'rect'     => array(
			'x'            => true,
			'y'            => true,
			'width'        => true,
			'height'       => true,
			'rx'           => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'line'     => array(
			'x1'             => true,
			'y1'             => true,
			'x2'             => true,
			'y2'             => true,
			'stroke'         => true,
			'stroke-width'   => true,
			'stroke-linecap' => true,
		),
		'polyline' => array(
			'points' => true,
			'fill'   => true,
			'stroke' => true,
		),
	);
}

/**
 * Return an inline SVG icon.
 *
 * Icons are inline rather than a sprite sheet so they inherit currentColor and
 * cost no extra request. Decorative by default - pass a label when the icon is
 * the only content of a control.
 *
 * @param string $name Icon name.
 * @return string SVG markup, or an empty string for an unknown icon.
 */
function fivedrs_icon( $name ) {
	$stroke = 'xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';
	$solid  = 'xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"';

	switch ( $name ) {
		case 'cart':
			return '<svg ' . $stroke . '><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3h2.2l2.1 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L20.5 7H6"/></svg>';

		case 'menu':
			return '<svg ' . $stroke . '><line x1="3.5" y1="7" x2="20.5" y2="7"/><line x1="3.5" y1="12" x2="20.5" y2="12"/><line x1="3.5" y1="17" x2="20.5" y2="17"/></svg>';

		case 'close':
			return '<svg ' . $stroke . '><line x1="5.5" y1="5.5" x2="18.5" y2="18.5"/><line x1="18.5" y1="5.5" x2="5.5" y2="18.5"/></svg>';

		case 'phone':
			return '<svg ' . $stroke . '><path d="M21 16.5v2.6a1.9 1.9 0 0 1-2.1 1.9 18.6 18.6 0 0 1-8.1-2.9 18.3 18.3 0 0 1-5.6-5.6A18.6 18.6 0 0 1 2.3 4.3 1.9 1.9 0 0 1 4.2 2.2h2.6a1.9 1.9 0 0 1 1.9 1.6c.1 1 .3 1.9.7 2.8a1.9 1.9 0 0 1-.4 2L7.9 9.7a15 15 0 0 0 5.6 5.6l1.1-1.1a1.9 1.9 0 0 1 2-.4c.9.3 1.8.6 2.8.7a1.9 1.9 0 0 1 1.6 2z"/></svg>';

		case 'mail':
			return '<svg ' . $stroke . '><rect x="2.5" y="4.5" width="19" height="15" rx="2.5"/><path d="m3 7 8.1 5.6a1.6 1.6 0 0 0 1.8 0L21 7"/></svg>';

		case 'pin':
			return '<svg ' . $stroke . '><path d="M20 10.5c0 5.4-8 11.3-8 11.3s-8-5.9-8-11.3a8 8 0 1 1 16 0z"/><circle cx="12" cy="10.3" r="2.8"/></svg>';

		case 'whatsapp':
			return '<svg ' . $solid . '><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.9L2 22l5.25-1.37A9.9 9.9 0 1 0 12.04 2zm0 1.8a8.1 8.1 0 1 1-4.1 15.08l-.3-.17-3.12.81.83-3.04-.19-.31A8.1 8.1 0 0 1 12.04 3.8zm4.66 10.3c-.25-.13-1.47-.73-1.7-.81-.23-.09-.4-.13-.56.12s-.64.8-.79.97c-.14.17-.29.19-.54.06a6.6 6.6 0 0 1-3.3-2.88c-.25-.43.25-.4.71-1.32.08-.17.04-.31-.02-.44s-.56-1.35-.77-1.84c-.2-.48-.4-.42-.56-.43h-.48a.92.92 0 0 0-.67.31 2.8 2.8 0 0 0-.87 2.08 4.86 4.86 0 0 0 1.02 2.58 11.1 11.1 0 0 0 4.265 3.76c1.6.69 2.22.75 3.02.63.49-.07 1.47-.6 1.68-1.19.2-.58.2-1.08.14-1.18-.06-.11-.23-.17-.48-.29z"/></svg>';

		case 'facebook':
			return '<svg ' . $solid . '><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06C2 17.08 5.66 21.25 10.44 22v-7.03H7.9v-2.91h2.54V9.85c0-2.52 1.49-3.91 3.77-3.91 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.89h2.78l-.45 2.91h-2.33V22C18.34 21.25 22 17.08 22 12.06z"/></svg>';

		case 'instagram':
			return '<svg ' . $stroke . '><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none"/></svg>';

		case 'tiktok':
			return '<svg ' . $solid . '><path d="M16.6 2h-2.9v14.1a2.5 2.5 0 1 1-2.5-2.5c.26 0 .5.04.74.11v-2.96a5.5 5.5 0 1 0 4.66 5.43V8.9a6.5 6.5 0 0 0 3.9 1.28V7.23A3.6 3.6 0 0 1 16.6 2z"/></svg>';

		case 'shield-check':
			return '<svg ' . $stroke . '><path d="M12 2.8 5 5.6v5.5c0 4.3 3 8.3 7 9.6 4-1.3 7-5.3 7-9.6V5.6z"/><polyline points="9,11.8 11.2,14 15,10"/></svg>';

		case 'leaf':
			return '<svg ' . $stroke . '><path d="M4 20c0-7.2 4.6-12 14-12 0 7.2-4.2 12-14 12z"/><line x1="4" y1="20" x2="12" y2="12"/></svg>';

		case 'family':
			return '<svg ' . $stroke . '><circle cx="8" cy="8" r="3"/><circle cx="17" cy="10" r="2.2"/><path d="M2.8 20a5.2 5.2 0 0 1 10.4 0"/><path d="M15 20a4 4 0 0 1 6.2-3.3"/></svg>';

		case 'lock':
			return '<svg ' . $stroke . '><rect x="4.5" y="10.5" width="15" height="10" rx="2.5"/><path d="M8 10.5V7.8a4 4 0 0 1 8 0v2.7"/></svg>';

		case 'hand':
			return '<svg ' . $stroke . '><path d="M9 11V4.8a1.6 1.6 0 0 1 3.2 0V11"/><path d="M12.2 11V3.6a1.6 1.6 0 0 1 3.2 0V11"/><path d="M15.4 11.4V6.4a1.6 1.6 0 0 1 3.2 0V14a7 7 0 0 1-7 7h-.6a6 6 0 0 1-5.2-3l-2-3.4a1.6 1.6 0 0 1 2.5-2L9 15"/></svg>';

		case 'sparkle':
			return '<svg ' . $stroke . '><path d="M12 3.2 13.8 9 19.6 10.8 13.8 12.6 12 18.4 10.2 12.6 4.4 10.8 10.2 9z"/><path d="M18.5 16.5 19.2 18.6 21.3 19.3 19.2 20 18.5 22.1 17.8 20 15.7 19.3 17.8 18.6z"/></svg>';

		case 'grid':
			return '<svg ' . $stroke . '><rect x="3.5" y="3.5" width="7" height="7" rx="2"/><rect x="13.5" y="3.5" width="7" height="7" rx="2"/><rect x="3.5" y="13.5" width="7" height="7" rx="2"/><rect x="13.5" y="13.5" width="7" height="7" rx="2"/></svg>';

		case 'check':
			return '<svg ' . $stroke . '><polyline points="5,12.5 10,17.5 19,6.5"/></svg>';

		case 'chevron':
			return '<svg ' . $stroke . '><polyline points="6,9.5 12,15.5 18,9.5"/></svg>';

		case 'arrow':
			return '<svg class="fivedrs-btn__arrow" ' . $stroke . '><line x1="4" y1="12" x2="19" y2="12"/><polyline points="13,6 19,12 13,18"/></svg>';

		default:
			return '';
	}
}

/**
 * Print the site logo, linked to the home page.
 *
 * Uses the Customizer logo when the client has set one, otherwise the PNG
 * shipped with the theme.
 *
 * @param array $args Optional. 'class' => string, 'height' => int.
 * @return void
 */
function fivedrs_logo( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'class' => 'fivedrs-brand',
		)
	);

	$name = get_bloginfo( 'name' );
	$home = home_url( '/' );

	printf( '<a class="%s" href="%s" rel="home">', esc_attr( $args['class'] ), esc_url( $home ) );

	$custom_logo_id = get_theme_mod( 'custom_logo' );

	if ( $custom_logo_id ) {
		echo wp_get_attachment_image(
			$custom_logo_id,
			'full',
			false,
			array(
				'class' => 'custom-logo',
				'alt'   => esc_attr( $name ),
			)
		);
	} else {
		printf(
			'<img src="%s" width="534" height="364" alt="%s" decoding="async">',
			esc_url( FIVEDRS_URI . '/assets/images/logo-lockup.png' ),
			esc_attr( $name )
		);
	}

	echo '</a>';
}

/**
 * The brand tagline.
 *
 * Kept as real text rather than part of the logo image so that it translates
 * into Arabic and is readable by assistive technology.
 *
 * @return string
 */
function fivedrs_tagline() {
	return __( 'Our Goal Is Your Personal Comfort', '5drs' );
}

/**
 * Current number of items in the cart.
 *
 * @return int
 */
function fivedrs_cart_count() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}

	return (int) WC()->cart->get_cart_contents_count();
}

/**
 * The cart count badge.
 *
 * Rendered on its own because WooCommerce's cart-fragments script swaps this
 * exact element after an AJAX add-to-cart - see fivedrs_cart_fragment().
 *
 * @return string
 */
function fivedrs_cart_badge() {
	$count = fivedrs_cart_count();

	return sprintf(
		'<span class="fivedrs-cart__count%1$s" aria-hidden="true">%2$s</span>',
		$count ? '' : ' is-empty',
		esc_html( number_format_i18n( $count ) )
	);
}

/**
 * Print the header cart button.
 *
 * @return void
 */
function fivedrs_cart_button() {
	if ( ! function_exists( 'wc_get_cart_url' ) ) {
		return;
	}

	$count = fivedrs_cart_count();

	printf(
		'<a class="fivedrs-cart" href="%1$s">%2$s%3$s<span class="screen-reader-text" data-cart-label>%4$s</span></a>',
		esc_url( wc_get_cart_url() ),
		wp_kses( fivedrs_icon( 'cart' ), fivedrs_kses_svg() ),
		wp_kses_post( fivedrs_cart_badge() ),
		esc_html(
			sprintf(
				/* translators: %s: number of items in the cart. */
				_n( '%s item in cart', '%s items in cart', $count, '5drs' ),
				number_format_i18n( $count )
			)
		)
	);
}

/**
 * Print the footer contact list, skipping anything the client has not filled.
 *
 * @return void
 */
function fivedrs_contact_list() {
	$phone    = fivedrs_option( 'phone' );
	$email    = fivedrs_option( 'email' );
	$address  = fivedrs_option( 'address' );
	$whatsapp = fivedrs_option( 'whatsapp' );

	if ( '' === $phone && '' === $email && '' === $address && '' === $whatsapp ) {
		return;
	}

	echo '<ul class="fivedrs-footer__contact">';

	if ( '' !== $phone ) {
		printf(
			'<li>%1$s<a href="tel:%2$s" dir="ltr">%3$s</a></li>',
			wp_kses( fivedrs_icon( 'phone' ), fivedrs_kses_svg() ),
			esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ),
			esc_html( $phone )
		);
	}

	if ( '' !== $whatsapp ) {
		printf(
			'<li>%1$s<a href="https://wa.me/%2$s" dir="ltr" rel="noopener">%3$s</a></li>',
			wp_kses( fivedrs_icon( 'whatsapp' ), fivedrs_kses_svg() ),
			esc_attr( preg_replace( '/[^0-9]/', '', $whatsapp ) ),
			esc_html( $whatsapp )
		);
	}

	if ( '' !== $email ) {
		printf(
			'<li>%1$s<a href="mailto:%2$s" dir="ltr">%2$s</a></li>',
			wp_kses( fivedrs_icon( 'mail' ), fivedrs_kses_svg() ),
			esc_attr( $email )
		);
	}

	if ( '' !== $address ) {
		printf(
			'<li>%1$s<span>%2$s</span></li>',
			wp_kses( fivedrs_icon( 'pin' ), fivedrs_kses_svg() ),
			nl2br( esc_html( $address ) )
		);
	}

	echo '</ul>';
}

/**
 * Print the social icon row, skipping anything the client has not filled.
 *
 * @return void
 */
function fivedrs_social_links() {
	$networks = array(
		'facebook'  => array(
			'label' => __( 'Facebook', '5drs' ),
			'url'   => fivedrs_option( 'facebook' ),
		),
		'instagram' => array(
			'label' => __( 'Instagram', '5drs' ),
			'url'   => fivedrs_option( 'instagram' ),
		),
		'tiktok'    => array(
			'label' => __( 'TikTok', '5drs' ),
			'url'   => fivedrs_option( 'tiktok' ),
		),
	);

	$whatsapp = fivedrs_option( 'whatsapp' );
	if ( '' !== $whatsapp ) {
		$networks['whatsapp'] = array(
			'label' => __( 'WhatsApp', '5drs' ),
			'url'   => 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $whatsapp ),
		);
	}

	$networks = array_filter(
		$networks,
		static function ( $network ) {
			return '' !== $network['url'];
		}
	);

	if ( empty( $networks ) ) {
		return;
	}

	echo '<div class="fivedrs-social">';

	foreach ( $networks as $key => $network ) {
		printf(
			'<a href="%1$s" rel="noopener" target="_blank">%2$s<span class="screen-reader-text">%3$s</span></a>',
			esc_url( $network['url'] ),
			wp_kses( fivedrs_icon( $key ), fivedrs_kses_svg() ),
			esc_html( $network['label'] )
		);
	}

	echo '</div>';
}

/**
 * Print a Home page image from a Customizer field.
 *
 * The hero is the Largest Contentful Paint element, so it opts out of
 * lazy-loading and asks the browser to fetch it first. Everything else is
 * below the fold and lazy-loads. Both go through wp_get_attachment_image(), so
 * srcset and sizes come from WordPress.
 *
 * @param string $section Config section key.
 * @param string $field   Config field key.
 * @param array  $args    Optional. Keys: 'size' (registered image size,
 *                        default 'large'), 'class' (extra class names),
 *                        'priority' (true for the LCP image, default false)
 *                        and 'sizes' (explicit sizes attribute).
 * @return bool Whether an image was printed.
 */
function fivedrs_content_image( $section, $field, $args = array() ) {
	$attachment_id = (int) fivedrs_content( $section, $field );

	if ( ! $attachment_id ) {
		return false;
	}

	$args = wp_parse_args(
		$args,
		array(
			'size'     => 'large',
			'class'    => '',
			'priority' => false,
			'sizes'    => '',
		)
	);

	$attr = array(
		'class'    => trim( 'fivedrs-img ' . $args['class'] ),
		'decoding' => 'async',
	);

	if ( $args['priority'] ) {
		$attr['loading']       = 'eager';
		$attr['fetchpriority'] = 'high';
	} else {
		$attr['loading'] = 'lazy';
	}

	if ( '' !== $args['sizes'] ) {
		$attr['sizes'] = $args['sizes'];
	}

	echo wp_get_attachment_image( $attachment_id, $args['size'], false, $attr );

	return true;
}
