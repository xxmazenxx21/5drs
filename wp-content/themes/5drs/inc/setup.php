<?php
/**
 * Theme setup: supports, menus, image sizes, text domain.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports, menus and image sizes.
 *
 * @return void
 */
function fivedrs_setup() {
	// Translations live in languages/5drs-{locale}.mo. Generate the .pot with `make pot`.
	load_theme_textdomain( FIVEDRS_TEXTDOMAIN, FIVEDRS_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	/*
	 * The client can replace the logo in Appearance > Customize; until they do,
	 * fivedrs_logo() falls back to the PNG shipped in assets/images.
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'               => 364,
			'width'                => 534,
			'flex-height'          => true,
			'flex-width'           => true,
			'unlink-homepage-logo' => false,
		)
	);

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);

	register_nav_menus(
		array(
			'primary'         => __( 'Primary Menu', '5drs' ),
			'footer-quick'    => __( 'Footer - Quick Links', '5drs' ),
			'footer-policies' => __( 'Footer - Policies', '5drs' ),
		)
	);

	add_image_size( 'fivedrs-card', 600, 600, true );
	add_image_size( 'fivedrs-hero', 1920, 900, true );
}
add_action( 'after_setup_theme', 'fivedrs_setup' );

/*
 * No widget areas, on purpose.
 *
 * The design has no sidebar on any page, and a registered-but-empty area is
 * not harmless: when a theme registers its first sidebar, WordPress moves the
 * widgets left over from installation - Search, Recent Posts, Recent Comments
 * - into it, and they then render on every shop and category page.
 *
 * Registering none is the fix: retrieve_widgets() has nowhere to put those
 * orphans, so they stay inactive however often the theme is switched.
 * scripts/seed/store-setup.php clears the rows an earlier install wrote, and
 * sidebar.php stays as a no-op stub because WordPress still calls it.
 */

/**
 * Mark the current menu item for assistive technology.
 *
 * WordPress adds current-menu-item classes but not aria-current, which is what
 * screen readers actually announce.
 *
 * @param array   $atts Anchor attributes.
 * @param WP_Post $item Menu item.
 * @return array
 */
function fivedrs_nav_aria_current( $atts, $item ) {
	if ( ! empty( $item->current ) || ! empty( $item->current_item_ancestor ) ) {
		$atts['aria-current'] = 'page';
	}

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'fivedrs_nav_aria_current', 10, 2 );

/**
 * Reveal the empty section placeholders to editors only.
 *
 * The site is a structural shell at this stage; signed-in editors should see
 * where content will go, visitors should see nothing.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function fivedrs_placeholder_body_class( $classes ) {
	if ( current_user_can( 'edit_posts' ) ) {
		$classes[] = 'fivedrs-show-placeholders';
	}

	return $classes;
}
add_filter( 'body_class', 'fivedrs_placeholder_body_class' );
