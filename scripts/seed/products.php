<?php
/**
 * Create the four real products.
 *
 * Idempotent: products are matched by SKU, so re-running updates them rather
 * than creating duplicates.
 *
 * Prices here are PLACEHOLDERS agreed with the client - see the TODO in
 * CLAUDE.md. Stock management is off for now.
 *
 * Run with: make products
 *
 * @package 5drs
 */

$bristle_tax = wc_attribute_taxonomy_name( 'bristle' );

/**
 * Attachment ID for a media slug.
 *
 * @param string $slug Attachment slug.
 * @return int
 */
function fivedrs_product_image_id( $slug ) {
	$found = get_posts(
		array(
			'name'        => $slug,
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);

	return $found ? (int) $found[0] : 0;
}

/**
 * Build the Bristle Type attribute object.
 *
 * @param array $slugs     Term slugs to offer.
 * @param bool  $variation Whether the attribute drives variations.
 * @return WC_Product_Attribute
 */
function fivedrs_bristle_attribute( array $slugs, $variation ) {
	$taxonomy = wc_attribute_taxonomy_name( 'bristle' );
	$term_ids = array();

	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( $term ) {
			$term_ids[] = (int) $term->term_id;
		}
	}

	$attribute = new WC_Product_Attribute();
	$attribute->set_id( wc_attribute_taxonomy_id_by_name( 'bristle' ) );
	$attribute->set_name( $taxonomy );
	$attribute->set_options( $term_ids );
	$attribute->set_position( 0 );
	$attribute->set_visible( true );
	$attribute->set_variation( $variation );

	return $attribute;
}

/**
 * Category term ID by slug.
 *
 * @param string $slug Category slug.
 * @return int[]
 */
function fivedrs_product_category_ids( $slug ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );

	return $term ? array( (int) $term->term_id ) : array();
}

$products = array(
	array(
		'type'       => 'variable',
		'sku'        => '5DRS-CLS',
		'name'       => 'Classic Toothbrush',
		'slug'       => 'classic-toothbrush',
		'category'   => 'classic',
		'image'      => '5drs-product-classic',
		'price'      => '45',
		'short'      => 'Our everyday essential toothbrush, available in Soft, Medium, and Hard bristles to match your gums and your cleaning needs.',
		'desc'       => 'The 5Doctors Classic toothbrush is designed for a comfortable, effective clean every day. Its ergonomic handle gives you a secure grip and full control, while the brush head reaches every corner of your mouth with ease. Choose the bristle type that suits you best: Soft for sensitive gums, Medium for balanced daily cleaning, or Hard for a deeper, stronger clean.',
		'variations' => array(
			'soft'   => array( 'sku' => '5DRS-CLS-S', 'desc' => 'Designed for an ultra-gentle clean that protects sensitive gums.' ),
			'medium' => array( 'sku' => '5DRS-CLS-M', 'desc' => 'Combines effective plaque removal with comfortable daily cleaning.' ),
			'hard'   => array( 'sku' => '5DRS-CLS-H', 'desc' => 'Ideal for powerful stain removal and a deep, strong clean.' ),
		),
	),
	array(
		'type'       => 'variable',
		'sku'        => '5DRS-PRM',
		'name'       => 'Premium Toothbrush',
		'slug'       => 'premium-toothbrush',
		'category'   => 'premium',
		'image'      => '5drs-product-premium',
		'price'      => '75',
		'short'      => 'Our advanced toothbrush with a refined design and smart bristles for a professional-grade clean - in Soft, Medium, or Hard.',
		'desc'       => 'The 5Doctors Premium toothbrush takes your daily routine to the next level. Its advanced design reaches tight spaces with care, and its smart bristles deliver a professional-grade cleaning experience at home. Choose Soft for superior gentle care, Medium for a professional everyday clean, or Hard to tackle stubborn stains.',
		'variations' => array(
			'soft'   => array( 'sku' => '5DRS-PRM-S', 'desc' => 'Advanced design reaching tight spaces with superior gentle care.' ),
			'medium' => array( 'sku' => '5DRS-PRM-M', 'desc' => 'Smart bristles for a professional-grade cleaning experience.' ),
			'hard'   => array( 'sku' => '5DRS-PRM-H', 'desc' => 'Superior performance and durability to tackle stubborn stains.' ),
		),
	),
	array(
		'type'     => 'simple',
		'sku'      => '5DRS-CHR-S',
		'name'     => 'Charcoal Soft Toothbrush',
		'slug'     => 'charcoal-soft-toothbrush',
		'category' => 'charcoal',
		'image'    => '5drs-product-charcoal',
		'price'    => '60',
		'short'    => 'Soft bristles infused with charcoal for a gentle clean that helps absorb stains.',
		'desc'     => "The 5Doctors Charcoal toothbrush combines a bold black-and-purple design with soft, charcoal-infused bristles. It gently cleans your teeth while helping absorb stains, leaving your mouth feeling fresh - all with the comfort of a soft brush that's kind to your gums.",
		'bristle'  => 'soft',
	),
	array(
		'type'     => 'simple',
		'sku'      => '5DRS-KID',
		'name'     => 'Kids Fun Toothbrush',
		'slug'     => 'kids-fun-toothbrush',
		'category' => 'kids',
		'image'    => '5drs-product-kids',
		'price'    => '40',
		'short'    => "Carefully designed with a small head, ultra-soft bristles, and a fun handle to protect your children's teeth.",
		'desc'     => "Make brushing the best part of their day. The 5Doctors kids' toothbrush features a fun corn-inspired design and a suction base that stands firmly on the sink, so it stays clean and always within reach of little hands. Its small head and ultra-soft bristles are gentle on young teeth and gums, and the chunky handle is easy for small hands to hold.",
		'bristle'  => 'soft',
	),
);

foreach ( $products as $spec ) {
	$existing_id = wc_get_product_id_by_sku( $spec['sku'] );

	if ( 'variable' === $spec['type'] ) {
		$product = $existing_id ? new WC_Product_Variable( $existing_id ) : new WC_Product_Variable();
	} else {
		$product = $existing_id ? new WC_Product_Simple( $existing_id ) : new WC_Product_Simple();
	}

	$product->set_name( $spec['name'] );
	$product->set_slug( $spec['slug'] );
	$product->set_sku( $spec['sku'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_featured( true );
	$product->set_short_description( $spec['short'] );
	$product->set_description( $spec['desc'] );
	$product->set_category_ids( fivedrs_product_category_ids( $spec['category'] ) );
	$product->set_manage_stock( false );
	$product->set_stock_status( 'instock' );

	$image_id = fivedrs_product_image_id( $spec['image'] );
	if ( $image_id ) {
		$product->set_image_id( $image_id );
	}

	if ( 'variable' === $spec['type'] ) {
		$product->set_attributes( array( fivedrs_bristle_attribute( array_keys( $spec['variations'] ), true ) ) );
		// No default attribute on purpose: the customer must choose a bristle
		// type before Add to Cart becomes available.
		$product->set_default_attributes( array() );
	} else {
		$product->set_attributes( array( fivedrs_bristle_attribute( array( $spec['bristle'] ), false ) ) );
		$product->set_regular_price( $spec['price'] );
	}

	$product_id = $product->save();

	if ( 'variable' === $spec['type'] ) {
		foreach ( $spec['variations'] as $slug => $variation_spec ) {
			$variation_id = wc_get_product_id_by_sku( $variation_spec['sku'] );
			$variation    = $variation_id ? new WC_Product_Variation( $variation_id ) : new WC_Product_Variation();

			$variation->set_parent_id( $product_id );
			$variation->set_attributes( array( $bristle_tax => $slug ) );
			$variation->set_sku( $variation_spec['sku'] );
			$variation->set_regular_price( $spec['price'] );
			$variation->set_description( $variation_spec['desc'] );
			$variation->set_status( 'publish' );
			$variation->set_manage_stock( false );
			$variation->set_stock_status( 'instock' );
			$variation->save();
		}

		WC_Product_Variable::sync( $product_id );
	}

	WP_CLI::log(
		sprintf(
			'  %-9s %-26s #%-4d %s',
			$existing_id ? 'updated' : 'created',
			$spec['sku'],
			$product_id,
			'variable' === $spec['type'] ? '(' . count( $spec['variations'] ) . ' variations)' : '(simple)'
		)
	);
}

wc_delete_product_transients();

WP_CLI::success( 'Products created.' );
