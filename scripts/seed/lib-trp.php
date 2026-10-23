<?php
/**
 * Shared helper for writing translations into TranslatePress's dictionary.
 *
 * Included by the seeder scripts; not a standalone script.
 *
 * @package 5drs
 */

/**
 * Write English => Arabic pairs into the TranslatePress dictionary.
 *
 * Uses TranslatePress's own TRP_Query API rather than touching its tables, and
 * marks every row human-reviewed so the plugin treats them as final.
 *
 * @param array  $pairs    Map of original string => translation.
 * @param string $language Target language code. Default 'ar'.
 * @return int Number of strings written.
 */
function fivedrs_seed_dictionary( array $pairs, $language = 'ar' ) {
	$pairs = array_filter(
		$pairs,
		static function ( $translation, $original ) {
			return '' !== trim( (string) $original ) && '' !== trim( (string) $translation );
		},
		ARRAY_FILTER_USE_BOTH
	);

	if ( empty( $pairs ) ) {
		return 0;
	}

	/*
	 * TranslatePress keys on the string exactly as it appears in the rendered
	 * HTML, and WordPress escapes the same character differently depending on
	 * the path it took:
	 *
	 *   esc_html()                 '  ->  &#039;      &  ->  &amp;
	 *   the_title() (convert_chars) &  ->  &#038;
	 *
	 * Seeding only the raw string leaves "Shop Kids' Brushes" and
	 * "Shipping & Delivery" permanently untranslated. Every spelling a string
	 * can legitimately render as is written, so whichever one the page
	 * produces finds a match.
	 */
	foreach ( $pairs as $original => $translation ) {
		$escaped  = esc_html( $original );
		$variants = array(
			$escaped,
			str_replace( '&amp;', '&#038;', $escaped ),
			wptexturize( $original ),
			str_replace( '&amp;', '&#038;', esc_html( wptexturize( $original ) ) ),
		);

		foreach ( array_unique( $variants ) as $variant ) {
			if ( $variant !== $original && ! isset( $pairs[ $variant ] ) ) {
				$pairs[ $variant ] = $translation;
			}
		}
	}

	$trp   = TRP_Translate_Press::get_trp_instance();
	$query = $trp->get_component( 'query' );

	$originals = array_keys( $pairs );
	$existing  = $query->get_string_ids( $originals, $language );

	$new = array();
	foreach ( $originals as $original ) {
		if ( ! isset( $existing[ $original ] ) ) {
			$new[] = $original;
		}
	}

	if ( $new ) {
		$query->insert_strings( $new, $language );
	}

	$ids     = $query->get_string_ids( $originals, $language );
	$updates = array();

	foreach ( $pairs as $original => $translation ) {
		if ( ! isset( $ids[ $original ] ) ) {
			WP_CLI::warning( 'no dictionary row for: ' . substr( $original, 0, 60 ) );
			continue;
		}

		$updates[] = array(
			'id'         => (int) $ids[ $original ]->id,
			'original'   => $original,
			'translated' => $translation,
			'status'     => $query->get_constant_human_reviewed(),
			'block_type' => $query->get_constant_block_type_regular_string(),
		);
	}

	if ( $updates ) {
		$query->update_strings(
			$updates,
			$language,
			array( 'id', 'original', 'translated', 'status', 'block_type' )
		);
	}

	return count( $updates );
}
