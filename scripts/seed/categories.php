<?php
/**
 * Create the product categories and give each one its card image.
 *
 * Idempotent - matched by slug. Run with: make categories
 *
 * @package 5drs
 */

$categories = array(
	array(
		'name'        => 'Classic',
		'slug'        => 'classic',
		'order'       => 10,
		'image'       => '5drs-cat-classic',
		'description' => 'Everyday toothbrushes for adults.',
	),
	array(
		'name'        => 'Kids',
		'slug'        => 'kids',
		'order'       => 20,
		'image'       => '5drs-cat-kids',
		'description' => 'Fun, easy-to-hold brushes for children.',
	),
	array(
		// TODO: name and description to be confirmed by the client - the product
		// may end up being a charcoal brush. See CLAUDE.md.
		'name'        => 'Black Edition',
		'slug'        => 'black-edition',
		'order'       => 30,
		'image'       => '5drs-cat-black',
		'description' => 'A bold design with a modern edge.',
	),
);

/**
 * Attachment ID for a media slug.
 *
 * @param string $slug Attachment slug.
 * @return int
 */
function fivedrs_seed_attachment_id( $slug ) {
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

$ids = array();

foreach ( $categories as $category ) {
	$term = get_term_by( 'slug', $category['slug'], 'product_cat' );

	if ( $term ) {
		wp_update_term(
			$term->term_id,
			'product_cat',
			array(
				'name'        => $category['name'],
				'description' => $category['description'],
			)
		);
		$term_id = (int) $term->term_id;
		$verb    = 'updated';
	} else {
		$created = wp_insert_term(
			$category['name'],
			'product_cat',
			array(
				'slug'        => $category['slug'],
				'description' => $category['description'],
			)
		);

		if ( is_wp_error( $created ) ) {
			WP_CLI::error( "category {$category['slug']}: " . $created->get_error_message() );
		}

		$term_id = (int) $created['term_id'];
		$verb    = 'created';
	}

	update_term_meta( $term_id, 'order', $category['order'] );

	$attachment_id = fivedrs_seed_attachment_id( $category['image'] );
	if ( $attachment_id ) {
		update_term_meta( $term_id, 'thumbnail_id', $attachment_id );
	} else {
		WP_CLI::warning( "no attachment for {$category['image']}" );
	}

	$ids[ $category['slug'] ] = $term_id;
	WP_CLI::log( sprintf( '  %-8s %-16s #%d  thumbnail #%d', $verb, $category['slug'], $term_id, $attachment_id ) );
}

// Put the placeholder products into real categories so the home page's category
// links land on populated archives. They keep the test-data tag, so the usual
// one-command cleanup still removes them.
$assignments = array(
	'TEST-001' => 'classic',
	'TEST-002' => 'kids',
	'TEST-003' => 'black-edition',
);

foreach ( $assignments as $sku => $slug ) {
	$product_id = wc_get_product_id_by_sku( $sku );

	if ( ! $product_id || empty( $ids[ $slug ] ) ) {
		continue;
	}

	wp_set_object_terms( $product_id, array( $ids[ $slug ] ), 'product_cat' );
	WP_CLI::log( "  {$sku} -> {$slug}" );
}

WP_CLI::success( 'Product categories ready.' );
