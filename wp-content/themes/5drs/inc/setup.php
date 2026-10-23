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

/**
 * Register widget areas.
 *
 * @return void
 */
function fivedrs_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Shop Sidebar', '5drs' ),
			'id'            => 'fivedrs-shop-sidebar',
			'description'   => __( 'Sidebar shown on WooCommerce archive pages.', '5drs' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'fivedrs_widgets_init' );

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
