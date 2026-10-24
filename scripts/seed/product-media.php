<?php
/**
 * Import the product photography into the Media Library.
 *
 * Idempotent: each image has a stable slug and re-running updates rather than
 * duplicating. The theme and the Customizer resolve images by slug, never by a
 * hardcoded attachment ID.
 *
 * Run with: make product-media
 *
 * @package 5drs
 */

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$source_dir = '/assets/products/build';

$images = array(
	array(
		'slug'  => '5drs-hero-products',
		'file'  => 'hero-product.webp',
		'title' => '5Doctors toothbrush collection on a bright bathroom counter',
		'alt'   => 'The 5Doctors toothbrush collection arranged on a bright marble bathroom counter with mint leaves',
	),
	array(
		'slug'  => '5drs-product-classic',
		'file'  => 'classic.webp',
		'title' => 'Classic Toothbrush',
		'alt'   => 'Four 5Doctors Classic toothbrushes with yellow grips standing on a bathroom counter',
	),
	array(
		'slug'  => '5drs-product-premium',
		'file'  => 'premium.webp',
		'title' => 'Premium Toothbrush',
		'alt'   => 'Four 5Doctors Premium toothbrushes with green grips standing on a bathroom counter',
	),
	array(
		'slug'  => '5drs-product-charcoal',
		'file'  => 'charcoal.webp',
		'title' => 'Charcoal Soft Toothbrush',
		'alt'   => 'Four 5Doctors Charcoal toothbrushes in black and lilac, two of them in travel cases',
	),
	array(
		'slug'  => '5drs-product-kids',
		'file'  => 'kids.webp',
		'title' => 'Kids Fun Toothbrush',
		'alt'   => "Four 5Doctors kids' toothbrushes with corn-shaped handles, two of them in travel cases",
	),
);

$ids = array();

foreach ( $images as $image ) {
	$path = $source_dir . '/' . $image['file'];

	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( "missing {$path} - run scripts/brand/prepare-product-images.py first" );
		continue;
	}

	$existing = get_posts(
		array(
			'name'        => $image['slug'],
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);

	if ( $existing ) {
		$id = (int) $existing[0];
		wp_update_post(
			array(
				'ID'         => $id,
				'post_title' => $image['title'],
			)
		);
		$verb = 'updated';
	} else {
		$upload = wp_upload_bits( $image['file'], null, file_get_contents( $path ) );

		if ( ! empty( $upload['error'] ) ) {
			WP_CLI::error( "upload failed for {$image['file']}: {$upload['error']}" );
		}

		$id = wp_insert_attachment(
			array(
				'post_mime_type' => 'image/webp',
				'post_title'     => $image['title'],
				'post_name'      => $image['slug'],
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$upload['file']
		);

		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
		$verb = 'imported';
	}

	update_post_meta( $id, '_wp_attachment_image_alt', $image['alt'] );
	$ids[ $image['slug'] ] = $id;

	WP_CLI::log( sprintf( '  %-9s %-24s #%d', $verb, $image['slug'], $id ) );
}

// --- category thumbnails -----------------------------------------------------
// Classic, Charcoal and Kids keep the dedicated card shots produced for the
// Home page; Premium has no card shot of its own, so it uses its product photo.

$thumbnails = array(
	'classic'  => '5drs-cat-classic',
	'premium'  => '5drs-product-premium',
	'charcoal' => '5drs-cat-black',
	'kids'     => '5drs-cat-kids',
);

foreach ( $thumbnails as $category_slug => $image_slug ) {
	$term = get_term_by( 'slug', $category_slug, 'product_cat' );

	if ( ! $term ) {
		continue;
	}

	$attachment_id = 0;

	if ( isset( $ids[ $image_slug ] ) ) {
		$attachment_id = $ids[ $image_slug ];
	} else {
		$found = get_posts(
			array(
				'name'        => $image_slug,
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);
		$attachment_id = $found ? (int) $found[0] : 0;
	}

	if ( $attachment_id ) {
		update_term_meta( $term->term_id, 'thumbnail_id', $attachment_id );
		WP_CLI::log( "  category {$category_slug} thumbnail -> #{$attachment_id}" );
	}
}

WP_CLI::success( 'Product images are in the Media Library.' );
