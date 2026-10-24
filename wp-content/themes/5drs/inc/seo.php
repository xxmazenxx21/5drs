<?php
/**
 * Structured data for the Home page.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

/**
 * The Home page FAQ entries that have both a question and an answer.
 *
 * @return array<int,array{question:string,answer:string}>
 */
function fivedrs_faq_entries() {
	$entries = array();

	for ( $i = 1; $i <= 4; $i++ ) {
		$question = fivedrs_content( 'faq', 'question_' . $i );
		$answer   = fivedrs_content( 'faq', 'answer_' . $i );

		if ( '' === $question || '' === $answer ) {
			continue;
		}

		$entries[] = array(
			'question' => $question,
			'answer'   => $answer,
		);
	}

	return $entries;
}

/**
 * Output FAQPage structured data for the Home page.
 *
 * Printed in the footer rather than the head so it runs after the FAQ markup
 * has been built from the same source, and translated through
 * fivedrs_translate() because TranslatePress never reads <script> contents.
 *
 * @return void
 */
function fivedrs_faq_schema() {
	if ( ! is_front_page() ) {
		return;
	}

	$entries = fivedrs_faq_entries();

	if ( empty( $entries ) ) {
		return;
	}

	$items = array();

	foreach ( $entries as $entry ) {
		$items[] = array(
			'@type'          => 'Question',
			'name'           => fivedrs_translate( $entry['question'] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => fivedrs_translate( $entry['answer'] ),
			),
		);
	}

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'inLanguage' => fivedrs_is_arabic() ? 'ar' : 'en',
		'mainEntity' => $items,
	);

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode() produces the escaping JSON-LD requires; esc_html() would corrupt it.
		wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_footer', 'fivedrs_faq_schema', 20 );

/**
 * Keep Rank Math from emitting a second, conflicting FAQPage.
 *
 * Rank Math only produces FAQ schema from its own FAQ block, which this theme
 * does not use - so today this is a no-op. It stays as a guard: if someone adds
 * that block to the Home page later, the page would otherwise carry two
 * FAQPage entities and search engines would pick one arbitrarily.
 *
 * @param array $data Rank Math JSON-LD entities.
 * @return array
 */
function fivedrs_dedupe_faq_schema( $data ) {
	if ( ! is_array( $data ) || ! is_front_page() || empty( fivedrs_faq_entries() ) ) {
		return $data;
	}

	foreach ( $data as $key => $entity ) {
		if ( is_array( $entity ) && isset( $entity['@type'] ) && 'FAQPage' === $entity['@type'] ) {
			unset( $data[ $key ] );
		}
	}

	return $data;
}
add_filter( 'rank_math/json_ld', 'fivedrs_dedupe_faq_schema', 20 );

/*
 * Arabic SEO title and description.
 *
 * TranslatePress's free tier does not translate <title> or
 * <meta name="description"> - that is its premium SEO Pack. Its "title"
 * node accessor matches the HTML title="" attribute, not the document title,
 * so Arabic pages would otherwise ship the English metadata.
 *
 * Rather than depend on a paid add-on, the Arabic pair is stored as post meta
 * beside Rank Math's own fields and swapped in on Arabic requests. Seeded by
 * scripts/seed/home-content.php; works for any post or page that has it.
 */

/**
 * Arabic SEO value for the current queried object.
 *
 * @param string $which Either 'title' or 'description'.
 * @return string Empty string when there is nothing to swap in.
 */
function fivedrs_arabic_seo( $which ) {
	if ( ! fivedrs_is_arabic() || is_admin() ) {
		return '';
	}

	$object_id = 0;

	if ( is_front_page() ) {
		$object_id = (int) get_option( 'page_on_front' );
	} elseif ( is_singular() ) {
		$object_id = get_queried_object_id();
	}

	if ( ! $object_id ) {
		return '';
	}

	return (string) get_post_meta( $object_id, 'fivedrs_seo_' . $which . '_ar', true );
}

/**
 * Swap the document title on Arabic pages.
 *
 * @param string $title Title from Rank Math.
 * @return string
 */
function fivedrs_filter_seo_title( $title ) {
	$arabic = fivedrs_arabic_seo( 'title' );

	return '' !== $arabic ? $arabic : $title;
}
add_filter( 'rank_math/frontend/title', 'fivedrs_filter_seo_title' );
add_filter( 'rank_math/opengraph/facebook/og_title', 'fivedrs_filter_seo_title' );
add_filter( 'rank_math/opengraph/twitter/twitter_title', 'fivedrs_filter_seo_title' );

/**
 * Swap the meta description on Arabic pages.
 *
 * @param string $description Description from Rank Math.
 * @return string
 */
function fivedrs_filter_seo_description( $description ) {
	$arabic = fivedrs_arabic_seo( 'description' );

	return '' !== $arabic ? $arabic : $description;
}
add_filter( 'rank_math/frontend/description', 'fivedrs_filter_seo_description' );
add_filter( 'rank_math/opengraph/facebook/og_description', 'fivedrs_filter_seo_description' );
add_filter( 'rank_math/opengraph/twitter/twitter_description', 'fivedrs_filter_seo_description' );

/*
 * Arabic product metadata.
 *
 * TranslatePress swaps text nodes, which covers the visible product copy. Three
 * things escape it, all handled here:
 *
 *   - the WooCommerce breadcrumb, whose last crumb shares a text node with the
 *     separator, so it never matches a dictionary entry;
 *   - og:title and twitter:title, which Rank Math writes into attributes;
 *   - the Product JSON-LD, which lives inside a <script>.
 */

/**
 * Translate the final WooCommerce breadcrumb crumb.
 *
 * @param array $crumbs Breadcrumb trail, each entry [ label, url ].
 * @return array
 */
function fivedrs_translate_breadcrumb( $crumbs ) {
	if ( ! fivedrs_is_arabic() || ! is_array( $crumbs ) ) {
		return $crumbs;
	}

	foreach ( $crumbs as $index => $crumb ) {
		if ( isset( $crumb[0] ) && is_string( $crumb[0] ) ) {
			$crumbs[ $index ][0] = fivedrs_translate( $crumb[0] );
		}
	}

	return $crumbs;
}
add_filter( 'woocommerce_get_breadcrumb', 'fivedrs_translate_breadcrumb' );

/**
 * The current singular object's title, translated when one exists.
 *
 * @return array{original:string,translated:string} Empty strings when not applicable.
 */
function fivedrs_arabic_post_title() {
	$empty = array(
		'original'   => '',
		'translated' => '',
	);

	if ( ! fivedrs_is_arabic() || is_admin() || ! is_singular() ) {
		return $empty;
	}

	$original = get_the_title( get_queried_object_id() );

	if ( '' === $original ) {
		return $empty;
	}

	$translated = fivedrs_translate( $original );

	return $translated === $original ? $empty : array(
		'original'   => $original,
		'translated' => $translated,
	);
}

/**
 * Swap the product name inside Rank Math's composed titles.
 *
 * Rank Math builds "%title% %sep% %sitename%", so the whole string never
 * matches a dictionary entry - only the title portion is replaced.
 *
 * @param string $title Composed title.
 * @return string
 */
function fivedrs_translate_composed_title( $title ) {
	$name = fivedrs_arabic_post_title();

	if ( '' === $name['original'] || ! is_string( $title ) ) {
		return $title;
	}

	return str_replace( $name['original'], $name['translated'], $title );
}
add_filter( 'rank_math/frontend/title', 'fivedrs_translate_composed_title', 20 );
add_filter( 'rank_math/opengraph/facebook/og_title', 'fivedrs_translate_composed_title', 20 );
add_filter( 'rank_math/opengraph/twitter/twitter_title', 'fivedrs_translate_composed_title', 20 );

/**
 * Translate the Product entity in Rank Math's JSON-LD.
 *
 * @param array $data Rank Math JSON-LD entities.
 * @return array
 */
function fivedrs_translate_product_schema( $data ) {
	if ( ! fivedrs_is_arabic() || ! is_array( $data ) ) {
		return $data;
	}

	foreach ( $data as $key => $entity ) {
		if ( ! is_array( $entity ) || ! isset( $entity['@type'] ) || 'Product' !== $entity['@type'] ) {
			continue;
		}

		foreach ( array( 'name', 'description' ) as $field ) {
			if ( ! empty( $entity[ $field ] ) && is_string( $entity[ $field ] ) ) {
				// Rank Math composes these from templates, so translate the
				// product name inside them rather than the whole string.
				$data[ $key ][ $field ] = fivedrs_translate_composed_title(
					fivedrs_translate( $entity[ $field ] )
				);
			}
		}
	}

	return $data;
}
add_filter( 'rank_math/json_ld', 'fivedrs_translate_product_schema', 20 );
