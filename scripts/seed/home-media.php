<?php
/**
 * Import the Home page images into the Media Library.
 *
 * Idempotent: each image gets a stable slug, and re-running updates the
 * existing attachment rather than creating a duplicate. The theme looks images
 * up by slug (fivedrs_image_by_slug), so attachment IDs never need hardcoding
 * and the defaults survive a fresh install.
 *
 * Run with: make home-media
 *
 * @package 5drs
 */

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$source_dir = '/assets/home/build';

$images = array(
	array(
		'slug'  => '5drs-hero',
		'file'  => 'hero.webp',
		'title' => 'Three 5Doctors toothbrushes with water splash and mint leaves',
		'alt'   => 'Three 5Doctors toothbrushes standing on marble with a water splash and fresh mint leaves',
	),
	array(
		'slug'  => '5drs-cat-classic',
		'file'  => 'cat-classic.webp',
		'title' => 'Classic 5Doctors toothbrushes',
		'alt'   => 'Two classic 5Doctors toothbrushes with yellow and green grips on a soft green background',
	),
	array(
		'slug'  => '5drs-cat-kids',
		'file'  => 'cat-kids.webp',
		'title' => "Kids' corn-shaped 5Doctors toothbrush",
		'alt'   => "Kids' 5Doctors toothbrush shaped like a corn cob, standing upright on its suction base",
	),
	array(
		'slug'  => '5drs-cat-black',
		'file'  => 'cat-black.webp',
		'title' => 'Black Edition 5Doctors toothbrush',
		'alt'   => 'Black and lilac 5Doctors toothbrush lit against a dark background',
	),
	array(
		'slug'  => '5drs-kids-spotlight',
		'file'  => 'kids-spotlight.webp',
		'title' => "Kids' 5Doctors toothbrush on a bathroom counter",
		'alt'   => "Kids' corn-shaped 5Doctors toothbrush standing on a bright bathroom counter beside a rubber duck and a cup",
	),
);

foreach ( $images as $image ) {
	$path = $source_dir . '/' . $image['file'];

	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( "missing {$path} - run scripts/brand/prepare-home-images.py first" );
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
		update_post_meta( $id, '_wp_attachment_image_alt', $image['alt'] );
		WP_CLI::log( sprintf( '  %-9s %-22s #%d', 'updated', $image['slug'], $id ) );
		continue;
	}

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
	update_post_meta( $id, '_wp_attachment_image_alt', $image['alt'] );

	WP_CLI::log( sprintf( '  %-9s %-22s #%d', 'imported', $image['slug'], $id ) );
}

WP_CLI::success( 'Home page images are in the Media Library.' );
