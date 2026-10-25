<?php
/**
 * Clean out the placeholder data and set up the real catalogue taxonomy.
 *
 * Idempotent. Run with: make store-setup
 *
 * @package 5drs
 */

// --- 1. remove the placeholder products, their images and the test order ----

$test_ids = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- one-off seeder.
			array(
				'taxonomy' => 'product_tag',
				'field'    => 'slug',
				'terms'    => 'test-data',
			),
		),
	)
);

foreach ( $test_ids as $test_id ) {
	foreach ( get_attached_media( 'image', $test_id ) as $attachment ) {
		wp_delete_attachment( $attachment->ID, true );
	}

	$thumbnail_id = (int) get_post_thumbnail_id( $test_id );
	if ( $thumbnail_id ) {
		wp_delete_attachment( $thumbnail_id, true );
	}

	wp_delete_post( $test_id, true );
	WP_CLI::log( "  deleted placeholder product #{$test_id} and its images" );
}

foreach ( wc_get_orders( array( 'limit' => -1 ) ) as $order ) {
	$order_id = $order->get_id();
	$order->delete( true );
	WP_CLI::log( "  deleted test order #{$order_id}" );
}

// The placeholder category and tag are now empty.
foreach ( array( 'product_cat' => 'test', 'product_tag' => 'test-data' ) as $taxonomy => $slug ) {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	if ( $term ) {
		wp_delete_term( $term->term_id, $taxonomy );
		WP_CLI::log( "  deleted empty {$taxonomy} '{$slug}'" );
	}
}

// --- 2. product categories ---------------------------------------------------

/**
 * Create or update a product category.
 *
 * @param array $spec Category specification.
 * @return int Term ID.
 */
function fivedrs_setup_category( array $spec ) {
	/*
	 * Match on the old slug first so a rename finds the existing term, then
	 * fall back to the new slug - otherwise a second run would try to create
	 * a category that already exists.
	 */
	$term = false;

	if ( isset( $spec['rename_from'] ) ) {
		$term = get_term_by( 'slug', $spec['rename_from'], 'product_cat' );
	}

	if ( ! $term ) {
		$term = get_term_by( 'slug', $spec['slug'], 'product_cat' );
	}

	if ( $term ) {
		wp_update_term(
			$term->term_id,
			'product_cat',
			array(
				'name'        => $spec['name'],
				'slug'        => $spec['slug'],
				'description' => $spec['description'],
			)
		);
		$term_id = (int) $term->term_id;
		$verb    = ( isset( $spec['rename_from'] ) && $spec['slug'] !== $term->slug ) ? 'renamed' : 'updated';
	} else {
		$created = wp_insert_term(
			$spec['name'],
			'product_cat',
			array(
				'slug'        => $spec['slug'],
				'description' => $spec['description'],
			)
		);

		if ( is_wp_error( $created ) ) {
			WP_CLI::error( "category {$spec['slug']}: " . $created->get_error_message() );
		}

		$term_id = (int) $created['term_id'];
		$verb    = 'created';
	}

	update_term_meta( $term_id, 'order', $spec['order'] );
	WP_CLI::log( sprintf( '  %-8s %-14s #%d', $verb, $spec['slug'], $term_id ) );

	return $term_id;
}

$categories = array(
	array(
		'name'        => 'Classic',
		'slug'        => 'classic',
		'order'       => 10,
		'description' => 'Everyday toothbrushes for adults, in soft, medium and hard bristles.',
	),
	array(
		'name'        => 'Premium',
		'slug'        => 'premium',
		'order'       => 20,
		'description' => 'Advanced design and smart bristles for a professional-grade clean.',
	),
	array(
		'name'        => 'Charcoal',
		'slug'        => 'charcoal',
		'order'       => 30,
		'description' => 'Soft charcoal-infused bristles for a gentle, fresh clean.',
		// Was "Black Edition" until the client confirmed the product.
		'rename_from' => 'black-edition',
	),
	array(
		'name'        => 'Kids',
		'slug'        => 'kids',
		'order'       => 40,
		'description' => "Fun, easy-to-hold brushes designed for children's teeth and gums.",
	),
);

foreach ( $categories as $category ) {
	fivedrs_setup_category( $category );
}

// --- 3. the Bristle Type global attribute --------------------------------------

$taxonomy = wc_attribute_taxonomy_name( 'bristle' );

if ( ! wc_attribute_taxonomy_id_by_name( 'bristle' ) ) {
	$created = wc_create_attribute(
		array(
			'name'         => 'Bristle Type',
			'slug'         => 'bristle',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		)
	);

	if ( is_wp_error( $created ) ) {
		WP_CLI::error( 'attribute: ' . $created->get_error_message() );
	}

	WP_CLI::log( '  created attribute Bristle Type (' . $taxonomy . ')' );
}

/*
 * WooCommerce registers attribute taxonomies on init, so one created in this
 * same request does not exist yet. Register it by hand before adding terms.
 */
if ( ! taxonomy_exists( $taxonomy ) ) {
	register_taxonomy(
		$taxonomy,
		'product',
		array(
			'hierarchical' => false,
			'show_ui'      => false,
			'query_var'    => true,
			'rewrite'      => false,
		)
	);
}

$position = 0;
foreach ( array( 'Soft' => 'soft', 'Medium' => 'medium', 'Hard' => 'hard' ) as $label => $slug ) {
	$position++;
	$term = get_term_by( 'slug', $slug, $taxonomy );

	if ( ! $term ) {
		$created = wp_insert_term( $label, $taxonomy, array( 'slug' => $slug ) );
		if ( is_wp_error( $created ) ) {
			WP_CLI::error( "bristle term {$slug}: " . $created->get_error_message() );
		}
		$term_id = (int) $created['term_id'];
		WP_CLI::log( "  added bristle term: {$label}" );
	} else {
		$term_id = (int) $term->term_id;
	}

	/*
	 * The attribute sorts by menu_order, and wc_terms_clauses() reads that
	 * from the plain "order" term meta - not "order_{taxonomy}", which is the
	 * key older guides suggest. Without it the options come out alphabetically
	 * - Hard, Medium, Soft - instead of in the order a customer thinks about.
	 */
	update_term_meta( $term_id, 'order', $position );
}

delete_transient( 'wc_attribute_taxonomies' );

// --- 4. clear the widgets WordPress left behind -----------------------------

/*
 * A fresh install creates Search, Recent Posts and Recent Comments widgets.
 * As soon as a theme registers a sidebar, retrieve_widgets() moves them into
 * it, and they render on every shop and category page. The theme registers no
 * widget areas any more (see inc/setup.php), but the rows an earlier install
 * wrote are still in the database, so clear them here too - re-running this
 * script is how a site that already has them gets clean.
 */
$sidebars = (array) get_option( 'sidebars_widgets', array() );
$orphans  = array();

foreach ( $sidebars as $slug => $widgets ) {
	if ( 'array_version' === $slug || ! is_array( $widgets ) ) {
		continue;
	}
	if ( 'wp_inactive_widgets' === $slug ) {
		$orphans = array_merge( $orphans, $widgets );
		continue;
	}
	$orphans            = array_merge( $orphans, $widgets );
	$sidebars[ $slug ] = array();
}

$sidebars['wp_inactive_widgets'] = array();
update_option( 'sidebars_widgets', $sidebars );

// Drop the instances as well, so nothing can be re-assigned by hand later.
foreach ( array_unique( $orphans ) as $widget_id ) {
	if ( ! preg_match( '/^(.+)-(\d+)$/', (string) $widget_id, $m ) ) {
		continue;
	}
	$option    = 'widget_' . $m[1];
	$instances = get_option( $option );
	if ( is_array( $instances ) && isset( $instances[ (int) $m[2] ] ) ) {
		unset( $instances[ (int) $m[2] ] );
		update_option( $option, $instances );
	}
}

WP_CLI::log( sprintf( '  cleared %d leftover widget(s)', count( array_unique( $orphans ) ) ) );

WP_CLI::success( 'Store taxonomy ready.' );
