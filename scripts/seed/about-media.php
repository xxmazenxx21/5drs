<?php
/**
 * Import the About page hero into the Media Library.
 *
 * The slideshow on that page deliberately reuses images already imported by
 * the Home and Products tasks, so this only handles the one new photograph.
 *
 * Idempotent: matched by slug. Run with: make about-media
 *
 * @package 5drs
 */

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$images = array(
	array(
		'slug'  => '5drs-hero-about',
		'file'  => 'hero-about.webp',
		'title' => '5Doctors toothbrushes in a bathroom',
		'alt'   => 'A cup of 5Doctors toothbrushes beside a corn-shaped kids brush on a sunlit bathroom counter',
	),
);

foreach ( $images as $image ) {
	$path = '/assets/about/build/' . $image['file'];

	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( "missing {$path} - run scripts/brand/prepare-images.py about first" );
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
	WP_CLI::log( sprintf( '  %-9s %-20s #%d', $verb, $image['slug'], $id ) );
}

WP_CLI::success( 'About page image is in the Media Library.' );
