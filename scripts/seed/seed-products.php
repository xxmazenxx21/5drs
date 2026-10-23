<?php
/**
 * One-off seeder for 3 test products. Run with:
 *   ./scripts/wp.sh eval-file /scripts/seed/seed-products.php
 * Everything it creates is tagged "test-data" so it can be bulk-deleted later.
 */

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$seed_dir = __DIR__;

// Product category "Test".
$cat = term_exists( 'test', 'product_cat' );
if ( ! $cat ) {
	$cat = wp_insert_term( 'Test', 'product_cat', array( 'slug' => 'test' ) );
}
$cat_id = (int) $cat['term_id'];

// Product tag "test-data".
$tag = term_exists( 'test-data', 'product_tag' );
if ( ! $tag ) {
	$tag = wp_insert_term( 'test-data', 'product_tag', array( 'slug' => 'test-data' ) );
}
$tag_id = (int) $tag['term_id'];

$items = array(
	array( 'Test Product One',   'TEST-001', 149.00, null,   'test-product-1.png', 'A simple test product used to verify the shop, cart and checkout flow.' ),
	array( 'Test Product Two',   'TEST-002', 299.00, 249.00, 'test-product-2.png', 'A second test product, on sale, to verify sale price rendering.' ),
	array( 'Test Product Three', 'TEST-003', 999.50, null,   'test-product-3.png', 'A third test product with a higher price to check currency formatting.' ),
);

foreach ( $items as $item ) {
	list( $name, $sku, $price, $sale, $image, $desc ) = $item;

	$existing = wc_get_product_id_by_sku( $sku );
	if ( $existing ) {
		WP_CLI::log( "skip (exists): {$sku}" );
		continue;
	}

	$product = new WC_Product_Simple();
	$product->set_name( $name );
	$product->set_sku( $sku );
	$product->set_regular_price( (string) $price );
	if ( $sale ) {
		$product->set_sale_price( (string) $sale );
	}
	$product->set_description( $desc );
	$product->set_short_description( 'Placeholder test data - safe to delete.' );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( 25 );
	$product->set_stock_status( 'instock' );
	$product->set_weight( '1' );
	$product->set_category_ids( array( $cat_id ) );
	$product->set_tag_ids( array( $tag_id ) );
	$product_id = $product->save();

	// Attach the placeholder image.
	$src = $seed_dir . '/' . $image;
	if ( file_exists( $src ) ) {
		$upload = wp_upload_bits( $image, null, file_get_contents( $src ) );
		if ( empty( $upload['error'] ) ) {
			$attachment_id = wp_insert_attachment(
				array(
					'post_mime_type' => 'image/png',
					'post_title'     => $name,
					'post_content'   => '',
					'post_status'    => 'inherit',
				),
				$upload['file'],
				$product_id
			);
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
			set_post_thumbnail( $product_id, $attachment_id );
		}
	}

	WP_CLI::log( "created: {$sku} (#{$product_id})" );
}

WP_CLI::success( 'Test products seeded.' );
