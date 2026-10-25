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
		// The page carries the policy itself, so its title is the policy's
		// name; the menus below keep the shorter "Policies" label.
		'title'    => 'Return & Exchange Policy',
		'slug'     => 'policies',
		'template' => 'page-policies.php',
		'order'    => 40,
	)
);

/*
 * Policies is a single page now. The four child pages it used to list were
 * removed once the client supplied one combined return and exchange policy;
 * delete any that survive from an older run so this stays idempotent.
 */
foreach ( array( 'privacy-policy', 'shipping-delivery', 'returns-refunds', 'terms-conditions' ) as $slug ) {
	// Matched by slug rather than path: these were children of Policies, so
	// get_page_by_path() would need the full "policies/<slug>" and silently
	// find nothing if the parent ever changed.
	$stale = get_posts(
		array(
			'name'        => $slug,
			'post_type'   => 'page',
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);

	if ( $stale ) {
		wp_delete_post( (int) $stale[0], true );
		WP_CLI::log( sprintf( '  %-9s %-28s #%d', 'deleted', $slug, (int) $stale[0] ) );
	}
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

/*
 * The checkout "I have read and agree to the terms" checkbox links to whatever
 * woocommerce_terms_page_id points at, so it now points at the Policies page.
 * There is no separate privacy page any more, and leaving the option set to a
 * deleted post would make WordPress render a link to nothing.
 */
update_option( 'woocommerce_terms_page_id', $policies );
update_option( 'wp_page_for_privacy_policy', 0 );
WP_CLI::log( '  terms checkbox -> Policies; privacy page unset' );

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
 * @param array  $labels   Optional. Page ID => link text, where the menu
 *                         should not simply repeat the page title.
 * @return void
 */
function fivedrs_seed_menu( $name, $location, array $page_ids, array $labels = array() ) {
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
		$item = array(
			'menu-item-object-id' => $page_id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $position,
		);

		if ( isset( $labels[ $page_id ] ) ) {
			$item['menu-item-title'] = $labels[ $page_id ];
		}

		wp_update_nav_menu_item( $menu_id, 0, $item );
	}

	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	WP_CLI::log( sprintf( '  %-28s -> %s (%d items)', $name, $location, count( $page_ids ) ) );
}

WP_CLI::log( 'menus' );

$menu_labels = array( $policies => 'Policies' );

fivedrs_seed_menu(
	'Primary',
	'primary',
	array_filter( array( $home, $shop_id, $about, $contact, $policies ) ),
	$menu_labels
);

fivedrs_seed_menu(
	'Footer - Quick Links',
	'footer-quick',
	array_filter( array( $home, $shop_id, $about, $contact, $policies ) ),
	$menu_labels
);

/*
 * The footer's policies column listed the four child pages. With a single
 * Policies page it would be a heading above one link that repeats it, so the
 * link moved into Quick Links and this menu is retired. The footer skips the
 * column when no menu is assigned to the location.
 */
$legacy = wp_get_nav_menu_object( 'Footer - Policies' );

if ( $legacy ) {
	wp_delete_nav_menu( $legacy->term_id );
	WP_CLI::log( '  removed the Footer - Policies menu' );
}

$locations = get_theme_mod( 'nav_menu_locations', array() );
unset( $locations['footer-policies'] );
set_theme_mod( 'nav_menu_locations', $locations );

WP_CLI::success( 'Pages, templates and menus are in place.' );
