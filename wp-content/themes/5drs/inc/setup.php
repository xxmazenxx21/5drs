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
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 100,
			'width'       => 300,
			'flex-height' => true,
			'flex-width'  => true,
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
			'primary' => __( 'Primary Menu', '5drs' ),
			'footer'  => __( 'Footer Menu', '5drs' ),
		)
	);

	// Content width placeholder - adjust when the design lands.
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
