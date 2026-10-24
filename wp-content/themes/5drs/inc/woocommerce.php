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
	/*
	 * The products hero is deliberately printed before the container so it can
	 * run edge to edge. Category archives get the compact variant, built from
	 * the term's own name and description.
	 */
	if ( is_shop() ) {
		get_template_part( 'template-parts/products/hero' );
	} elseif ( is_product_taxonomy() ) {
		get_template_part( 'template-parts/products/hero', null, array( 'compact' => true ) );
	}

	echo '<div class="fivedrs-container"><main id="main" class="site-main fivedrs-main">';

	if ( is_shop() || is_product_taxonomy() ) {
		get_template_part( 'template-parts/products/filter' );
	}
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

	// "Showing 1-4 of 4 results" above a four-item grid is noise; the sorting
	// control stays.
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

	// The loop price is replaced so variable products can say "From ..." only
	// when their variations actually differ in price.
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	add_action( 'woocommerce_after_shop_loop_item_title', 'fivedrs_loop_price', 10 );

	// Category label above the product name.
	add_action( 'woocommerce_shop_loop_item_title', 'fivedrs_loop_category_label', 5 );

	// --- single product -----------------------------------------------------
	// Category label above the product title, matching the cards.
	add_action( 'woocommerce_single_product_summary', 'fivedrs_single_category_label', 4 );

	// Simple products carry Bristle Type for information only, so they show a
	// badge where a variable product shows its selector.
	add_action( 'woocommerce_single_product_summary', 'fivedrs_single_bristle_badge', 25 );

	// The hint that appears while no variation has been chosen.
	add_action( 'woocommerce_before_add_to_cart_button', 'fivedrs_variation_hint' );

	// Quantity stepper controls around WooCommerce's own number input.
	add_action( 'woocommerce_before_quantity_input_field', 'fivedrs_quantity_minus' );
	add_action( 'woocommerce_after_quantity_input_field', 'fivedrs_quantity_plus' );
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

/**
 * Price shown on a product card.
 *
 * WooCommerce renders a range for variable products, which reads oddly when
 * every variation costs the same. This says "From X" only when the variations
 * genuinely differ, and otherwise falls back to the normal price.
 *
 * @return void
 */
function fivedrs_loop_price() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	if ( $product->is_type( 'variable' ) ) {
		$min = $product->get_variation_price( 'min', true );
		$max = $product->get_variation_price( 'max', true );

		if ( $min !== $max ) {
			printf(
				'<span class="price">%s</span>',
				wp_kses_post(
					sprintf(
						/* translators: %s: the lowest price among a product's variations. */
						__( 'From %s', '5drs' ),
						wc_price( $min )
					)
				)
			);
			return;
		}
	}

	printf( '<span class="price">%s</span>', wp_kses_post( $product->get_price_html() ) );
}

/**
 * Category label printed above a product card's name.
 *
 * @return void
 */
function fivedrs_loop_category_label() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$terms = get_the_terms( $product->get_id(), 'product_cat' );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}

	$term = reset( $terms );

	printf( '<span class="fivedrs-loop__cat">%s</span>', esc_html( $term->name ) );
}

/**
 * Button label for a variable product in the loop.
 *
 * WooCommerce says "Select options"; the brief asks for "Choose options", and
 * it needs to be translatable through this theme's text domain.
 *
 * @param string     $text    Button text.
 * @param WC_Product $product Product.
 * @return string
 */
function fivedrs_loop_add_to_cart_text( $text, $product ) {
	if ( $product instanceof WC_Product && $product->is_type( 'variable' ) ) {
		return __( 'Choose options', '5drs' );
	}

	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'fivedrs_loop_add_to_cart_text', 10, 2 );

/**
 * Translate variation descriptions for Arabic pages.
 *
 * WooCommerce ships variation data to the browser inside the
 * data-product_variations JSON attribute. TranslatePress works on rendered
 * HTML and never looks inside an attribute's JSON, so without this the
 * description under the bristle selector stays English on /ar/.
 *
 * @param array $data Variation data passed to the front end.
 * @return array
 */
function fivedrs_translate_variation( $data ) {
	if ( ! empty( $data['variation_description'] ) ) {
		$data['variation_description'] = fivedrs_translate( $data['variation_description'] );
	}

	return $data;
}
add_filter( 'woocommerce_available_variation', 'fivedrs_translate_variation' );

/**
 * Category label above the single product title.
 *
 * @return void
 */
function fivedrs_single_category_label() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$terms = get_the_terms( $product->get_id(), 'product_cat' );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}

	$term = reset( $terms );

	printf(
		'<a class="fivedrs-single__cat" href="%s">%s</a>',
		esc_url( (string) get_term_link( $term ) ),
		esc_html( $term->name )
	);
}

/**
 * Informational bristle badge for simple products.
 *
 * Charcoal and Kids carry the Bristle Type attribute for display only - there
 * is nothing to choose - so they show a badge rather than an empty selector.
 *
 * @return void
 */
function fivedrs_single_bristle_badge() {
	global $product;

	if ( ! $product instanceof WC_Product || $product->is_type( 'variable' ) ) {
		return;
	}

	$value = $product->get_attribute( wc_attribute_taxonomy_name( 'bristle' ) );

	if ( '' === $value ) {
		return;
	}

	printf(
		'<p class="fivedrs-bristle-badge">%s<span>%s</span></p>',
		wp_kses( fivedrs_icon( 'leaf' ), fivedrs_kses_svg() ),
		esc_html(
			sprintf(
				/* translators: %s: bristle type, e.g. "Soft". */
				__( '%s bristles', '5drs' ),
				$value
			)
		)
	);
}

/**
 * Hint shown while no bristle type has been chosen.
 *
 * WooCommerce puts woocommerce-variation-add-to-cart-disabled on the wrapper
 * until a full variation is selected, so CSS alone decides when this shows -
 * no JavaScript involved in keeping it in sync.
 *
 * @return void
 */
function fivedrs_variation_hint() {
	global $product;

	if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) ) {
		return;
	}

	printf(
		'<p class="fivedrs-variation-hint" role="status">%s</p>',
		esc_html__( 'Please choose a bristle type', '5drs' )
	);
}

/**
 * Minus control for the quantity stepper.
 *
 * Printed server-side around WooCommerce's own input so the field still works
 * when JavaScript is unavailable; src/js/modules/product.js wires the buttons.
 *
 * @return void
 */
function fivedrs_quantity_minus() {
	printf(
		'<button type="button" class="fivedrs-qty__btn" data-qty="down" aria-label="%s">&minus;</button>',
		esc_attr__( 'Decrease quantity', '5drs' )
	);
}

/**
 * Plus control for the quantity stepper.
 *
 * @return void
 */
function fivedrs_quantity_plus() {
	printf(
		'<button type="button" class="fivedrs-qty__btn" data-qty="up" aria-label="%s">+</button>',
		esc_attr__( 'Increase quantity', '5drs' )
	);
}

/**
 * Expose what the front-end scripts need for AJAX add-to-cart.
 *
 * WooCommerce only wires AJAX add-to-cart on archives; the single product page
 * posts to the same endpoint, so the script needs the base URL and the strings.
 *
 * @return void
 */
function fivedrs_localize_product_script() {
	if ( ! wp_script_is( 'fivedrs-main', 'enqueued' ) ) {
		return;
	}

	wp_localize_script(
		'fivedrs-main',
		'fivedrsShop',
		array(
			'ajaxUrl' => function_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( '%%endpoint%%' ) : '',
			'cartUrl' => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
			'i18n'    => array(
				'added'         => __( 'Added to your cart', '5drs' ),
				'viewCart'      => __( 'View cart', '5drs' ),
				'error'         => __( 'Sorry, that could not be added to your cart.', '5drs' ),
				'dismiss'       => __( 'Dismiss', '5drs' ),
				'chooseBristle' => __( 'Please choose a bristle type', '5drs' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'fivedrs_localize_product_script', 20 );

/**
 * Keep variation attributes out of the variation's title.
 *
 * By default WooCommerce renders a cart line as "Classic Toothbrush - Medium"
 * and then suppresses the attribute list to avoid repeating itself. The
 * labelled form is clearer and, unlike the appended title, both halves of it
 * translate: Arabic shows "نوع الشعيرات: متوسطة" rather than a bare term.
 *
 * Applies wherever a variation is named - cart, checkout, order emails and the
 * order screens.
 *
 * @return bool
 */
function fivedrs_variation_title_without_attributes() {
	return false;
}
add_filter( 'woocommerce_product_variation_title_include_attributes', 'fivedrs_variation_title_without_attributes' );

/**
 * Translate attribute labels on Arabic pages.
 *
 * WooCommerce prints the label with a trailing colon - "Bristle Type:" - so
 * the rendered text node never matches the plain dictionary entry and
 * TranslatePress leaves it in English. Translating at the source fixes it
 * everywhere the label appears: cart, checkout, the Additional information
 * table and order emails.
 *
 * @param string $label Attribute label.
 * @return string
 */
function fivedrs_translate_attribute_label( $label ) {
	if ( ! is_string( $label ) || '' === $label ) {
		return $label;
	}

	return fivedrs_translate( $label );
}
add_filter( 'woocommerce_attribute_label', 'fivedrs_translate_attribute_label' );
