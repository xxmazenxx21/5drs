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
