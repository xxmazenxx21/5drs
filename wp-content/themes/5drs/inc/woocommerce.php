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
 * Hook customisations.
 *
 * Everything that changes WooCommerce output lives below this line.
 */

/**
 * Replace WooCommerce's content wrapper with the theme's container.
 *
 * WooCommerce's default wrapper emits `#primary .content-area`, which does not
 * exist in this theme. Swapping it here keeps every store page inside the same
 * container as the rest of the site without overriding a single template.
 *
 * @return void
 */
function fivedrs_wc_wrapper_start() {
	// On archives the page title is rendered as a brand hero instead, so that
	// the shop header matches every other page on the site.
	if ( is_shop() || is_product_taxonomy() ) {
		get_template_part(
			'template-parts/page-hero',
			null,
			array(
				'title' => woocommerce_page_title( false ),
			)
		);
	}

	echo '<div class="fivedrs-container"><main id="main" class="site-main fivedrs-main">';
}

/**
 * Close the theme's content wrapper.
 *
 * @return void
 */
function fivedrs_wc_wrapper_end() {
	echo '</main></div>';
}

/**
 * Swap WooCommerce's wrappers for the theme's and hide the duplicated title.
 *
 * @return void
 */
function fivedrs_wc_layout_hooks() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

	add_action( 'woocommerce_before_main_content', 'fivedrs_wc_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'fivedrs_wc_wrapper_end', 10 );

	// The hero above already prints it.
	add_filter( 'woocommerce_show_page_title', '__return_false' );
}
add_action( 'template_redirect', 'fivedrs_wc_layout_hooks' );

/**
 * Keep the header cart badge in sync after an AJAX add-to-cart.
 *
 * WooCommerce's cart-fragments script replaces every element matching a key in
 * this array with the markup it maps to, so the badge updates for guests and
 * signed-in customers alike without any custom request.
 *
 * @param array $fragments Cart fragments.
 * @return array
 */
function fivedrs_cart_fragment( $fragments ) {
	$fragments['.fivedrs-cart__count'] = fivedrs_cart_badge();

	// The screen-reader label travels with it, so the announced count matches.
	$count = fivedrs_cart_count();

	$fragments['[data-cart-label]'] = sprintf(
		'<span class="screen-reader-text" data-cart-label>%s</span>',
		esc_html(
			sprintf(
				/* translators: %s: number of items in the cart. */
				_n( '%s item in cart', '%s items in cart', $count, '5drs' ),
				number_format_i18n( $count )
			)
		)
	);

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'fivedrs_cart_fragment' );

/**
 * Products per row in the shop grid.
 *
 * The CSS grid is responsive on its own; this only keeps WooCommerce's own
 * column classes consistent with it.
 *
 * @return int
 */
function fivedrs_loop_columns() {
	return 3;
}
add_filter( 'loop_shop_columns', 'fivedrs_loop_columns' );

/**
 * Products per page on the shop archive.
 *
 * @return int
 */
function fivedrs_products_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'fivedrs_products_per_page' );

/*
 * Further customisations belong here: remove_action / add_action against
 * woocommerce_* hooks. Keep each change small and commented with why.
 *
 * Reference hooks:
 *   woocommerce_before_shop_loop_item / woocommerce_after_shop_loop_item
 *   woocommerce_single_product_summary
 *   woocommerce_before_cart / woocommerce_before_checkout_form
 */

/**
 * Force lazy-loading on images inside the Home page's featured loop.
 *
 * Added and removed around that loop only - see
 * template-parts/home/featured.php.
 *
 * @param array $attr Image attributes.
 * @return array
 */
function fivedrs_featured_lazy_attr( $attr ) {
	$attr['loading']  = 'lazy';
	$attr['decoding'] = 'async';

	return $attr;
}

/**
 * Render a WooCommerce loop product title as an h3.
 *
 * WooCommerce emits an h2, which on the Home page would sit at the same level
 * as the "Featured Products" heading above it. Swapped in around that loop
 * only, so the shop archive keeps WooCommerce's own markup.
 *
 * @return void
 */
function fivedrs_featured_loop_title() {
	printf(
		'<h3 class="woocommerce-loop-product__title">%s</h3>',
		esc_html( get_the_title() )
	);
}
