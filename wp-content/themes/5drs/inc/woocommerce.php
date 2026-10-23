<?php
/**
 * WooCommerce integration.
 *
 * Rule for this theme: prefer hooks over template overrides. Only copy a
 * template into woocommerce/ when markup genuinely cannot be reached by a
 * hook - see woocommerce/README.md.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declare WooCommerce support and gallery features.
 *
 * @return void
 */
function fivedrs_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 1200,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 3,
				'min_columns'     => 1,
				'max_columns'     => 6,
			),
		)
	);

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'fivedrs_woocommerce_setup' );

/**
 * Declare compatibility with WooCommerce High Performance Order Storage.
 *
 * @return void
 */
function fivedrs_declare_wc_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'fivedrs_declare_wc_compatibility' );

/*
-------------------------------------------------------------------------
 * Hook customisations
 * -------------------------------------------------------------------------
 * Everything that changes WooCommerce output belongs here: remove_action /
 * add_action against woocommerce_* hooks, wrappers, loop columns, breadcrumb
 * tweaks, and so on. Keep each change small and commented with why.
 *
 * Intentionally empty for now - the shop is built in a later task.
 *
 * Reference hooks, for when that work starts:
 *   woocommerce_before_main_content / woocommerce_after_main_content
 *   woocommerce_before_shop_loop_item / woocommerce_after_shop_loop_item
 *   woocommerce_single_product_summary
 *   woocommerce_before_cart / woocommerce_before_checkout_form
 * ----------------------------------------------------------------------
 */
