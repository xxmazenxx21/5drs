<?php
/**
 * Create the site's page tree, assign templates and build the menus.
 *
 * Idempotent: pages are matched by slug, so re-running updates rather than
 * duplicates. Run with:
 *
 *     make pages
 *
 * @package 5drs
 */

/**
 * Find a page by slug, or create it.
 *
 * @param array $spec Page specification.
 * @return int Page ID.
 */
function fivedrs_seed_page( array $spec ) {
	$existing = get_page_by_path( $spec['slug'], OBJECT, 'page' );

	$data = array(
		'post_title'   => $spec['title'],
		'post_name'    => $spec['slug'],
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_content' => isset( $spec['content'] ) ? $spec['content'] : '',
		'post_parent'  => isset( $spec['parent'] ) ? (int) $spec['parent'] : 0,
		'menu_order'   => isset( $spec['order'] ) ? (int) $spec['order'] : 0,
	);

	if ( $existing ) {
		$data['ID'] = $existing->ID;
		$id         = wp_update_post( $data, true );
		$verb       = 'updated';
	} else {
		$id   = wp_insert_post( $data, true );
		$verb = 'created';
	}

	if ( is_wp_error( $id ) ) {
		WP_CLI::error( "page {$spec['slug']}: " . $id->get_error_message() );
	}

	if ( ! empty( $spec['template'] ) ) {
		update_post_meta( $id, '_wp_page_template', $spec['template'] );
	} else {
		delete_post_meta( $id, '_wp_page_template' );
	}

	WP_CLI::log( sprintf( '  %-9s %-28s #%d', $verb, $spec['slug'], $id ) );

	return (int) $id;
}

WP_CLI::log( 'pages' );

$home = fivedrs_seed_page(
	array(
		'title' => 'Home',
		'slug'  => 'home',
		'order' => 0,
	)
);

$about = fivedrs_seed_page(
	array(
		'title'    => 'About Us',
		'slug'     => 'about',
		'template' => 'page-about.php',
		'order'    => 20,
	)
);

$contact = fivedrs_seed_page(
	array(
		'title'    => 'Contact Us',
		'slug'     => 'contact',
		'template' => 'page-contact.php',
		'order'    => 30,
	)
);

$policies = fivedrs_seed_page(
	array(
		'title'    => 'Policies',
		'slug'     => 'policies',
		'template' => 'page-policies.php',
		'order'    => 40,
	)
);

$policy_pages = array();
$policy_specs = array(
	array( 'title' => 'Privacy Policy',      'slug' => 'privacy-policy',     'order' => 10 ),
	array( 'title' => 'Shipping & Delivery', 'slug' => 'shipping-delivery',  'order' => 20 ),
	array( 'title' => 'Returns & Refunds',   'slug' => 'returns-refunds',    'order' => 30 ),
	array( 'title' => 'Terms & Conditions',  'slug' => 'terms-conditions',   'order' => 40 ),
);

foreach ( $policy_specs as $spec ) {
	$spec['parent']              = $policies;
	$policy_pages[ $spec['slug'] ] = fivedrs_seed_page( $spec );
}

// --- the WooCommerce shop page becomes "Our Products" -----------------------

$shop_id = (int) get_option( 'woocommerce_shop_page_id' );

if ( $shop_id ) {
	wp_update_post(
		array(
			'ID'         => $shop_id,
			'post_title' => 'Our Products',
			'post_name'  => 'products',
			'menu_order' => 10,
		)
	);
	// Unchanged, but assert it so a future edit cannot silently detach the shop.
	update_option( 'woocommerce_shop_page_id', $shop_id );
	WP_CLI::log( sprintf( '  %-9s %-28s #%d (woocommerce shop page)', 'renamed', 'products', $shop_id ) );
} else {
	WP_CLI::warning( 'no WooCommerce shop page found' );
}

// --- front page -------------------------------------------------------------

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
update_option( 'page_for_posts', 0 );
WP_CLI::log( "  front page set to #{$home}" );

// --- policy page options ------------------------------------------------------

update_option( 'wp_page_for_privacy_policy', $policy_pages['privacy-policy'] );
update_option( 'woocommerce_terms_page_id', $policy_pages['terms-conditions'] );
WP_CLI::log( '  privacy + terms options wired to their policy pages' );

// WooCommerce ships a draft "Refund and Returns Policy" placeholder; the real
// page is now policies/returns-refunds.
$stale = get_page_by_path( 'refund_returns', OBJECT, 'page' );
if ( $stale ) {
	wp_delete_post( $stale->ID, true );
	WP_CLI::log( '  removed the stale "Refund and Returns Policy" draft' );
}

// --- menus ---------------------------------------------------------------------

/**
 * Build a menu from a list of page IDs and assign it to a location.
 *
 * @param string $name     Menu name.
 * @param string $location Theme location slug.
 * @param array  $page_ids Page IDs in order.
 * @return void
 */
function fivedrs_seed_menu( $name, $location, array $page_ids ) {
	$menu = wp_get_nav_menu_object( $name );

	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			WP_CLI::error( "menu {$name}: " . $menu_id->get_error_message() );
		}
	} else {
		$menu_id = (int) $menu->term_id;
		// Clear it so re-running does not stack duplicate items.
		foreach ( wp_get_nav_menu_items( $menu_id ) as $item ) {
			wp_delete_post( $item->ID, true );
		}
	}

	$position = 0;
	foreach ( $page_ids as $page_id ) {
		$position++;
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object-id' => $page_id,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position,
			)
		);
	}

	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	WP_CLI::log( sprintf( '  %-28s -> %s (%d items)', $name, $location, count( $page_ids ) ) );
}

WP_CLI::log( 'menus' );

fivedrs_seed_menu(
	'Primary',
	'primary',
	array_filter( array( $home, $shop_id, $about, $contact, $policies ) )
);

fivedrs_seed_menu(
	'Footer - Quick Links',
	'footer-quick',
	array_filter( array( $home, $shop_id, $about, $contact ) )
);

fivedrs_seed_menu(
	'Footer - Policies',
	'footer-policies',
	array_values( $policy_pages )
);

WP_CLI::success( 'Pages, templates and menus are in place.' );
