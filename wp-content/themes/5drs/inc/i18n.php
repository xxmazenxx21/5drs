<?php
/**
 * Language, direction and TranslatePress helpers.
 *
 * The site is bilingual: English (default, served at /) and Arabic (served at
 * /ar/). TranslatePress swaps the locale at runtime, so always ask these
 * helpers for the current language rather than caching it.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Current locale, TranslatePress-aware.
 *
 * TranslatePress filters `locale`, so get_locale() is already correct on the
 * front end; the global is read first because TP sets it earlier in the request
 * than the filter applies in some admin/AJAX contexts.
 *
 * @return string Locale code, e.g. "en_US" or "ar".
 */
function fivedrs_current_locale() {
	if ( ! empty( $GLOBALS['TRP_LANGUAGE'] ) ) {
		return (string) $GLOBALS['TRP_LANGUAGE'];
	}

	return get_locale();
}

/**
 * Two-letter language code for the current request.
 *
 * @return string "en" or "ar".
 */
function fivedrs_current_language() {
	return strtolower( substr( fivedrs_current_locale(), 0, 2 ) );
}

/**
 * Whether the current request renders in Arabic.
 *
 * @return bool
 */
function fivedrs_is_arabic() {
	return 'ar' === fivedrs_current_language();
}

/**
 * Whether the current request renders right-to-left.
 *
 * Prefer this over hardcoding direction, and prefer CSS logical properties
 * over either - markup should rarely need to branch on direction at all.
 *
 * @return bool
 */
function fivedrs_is_rtl() {
	return is_rtl() || fivedrs_is_arabic();
}

/**
 * Add language and direction classes to <body>.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function fivedrs_body_classes( $classes ) {
	$classes[] = 'lang-' . fivedrs_current_language();
	$classes[] = fivedrs_is_rtl() ? 'is-rtl' : 'is-ltr';

	return $classes;
}
add_filter( 'body_class', 'fivedrs_body_classes' );

/**
 * Render the TranslatePress language switcher.
 *
 * Placement is deliberately left to the templates - call this from header.php
 * (or wherever the design puts it) once the header is designed. Returns an
 * empty string when TranslatePress is not active, so templates stay safe.
 *
 * @param array $args Optional. 'wrapper_class' => string.
 * @return string Switcher HTML.
 */
function fivedrs_language_switcher( $args = array() ) {
	if ( ! class_exists( 'TRP_Translate_Press' ) ) {
		return '';
	}

	$args    = wp_parse_args( $args, array( 'wrapper_class' => 'fivedrs-language-switcher' ) );
	$content = do_shortcode( '[language-switcher]' );

	if ( '' === trim( $content ) ) {
		return '';
	}

	return sprintf(
		'<div class="%1$s">%2$s</div>',
		esc_attr( $args['wrapper_class'] ),
		$content
	);
}

/**
 * Enqueue the webfonts for the current language.
 *
 * ---------------------------------------------------------------------------
 * FONT SLOT - fill this in once the Arabic and Latin fonts are chosen.
 * ---------------------------------------------------------------------------
 * Load only what the current language needs: an Arabic face is large and the
 * English pages should not pay for it (and vice versa). Self-host the files in
 * src/fonts/ and reference them from a @font-face block in src/css/, or
 * enqueue a remote stylesheet here.
 *
 * Example shape:
 *
 *   if ( fivedrs_is_arabic() ) {
 *       wp_enqueue_style( 'fivedrs-font-ar', FIVEDRS_URI . '/dist/fonts-ar.css', array(), FIVEDRS_VERSION );
 *   } else {
 *       wp_enqueue_style( 'fivedrs-font-en', FIVEDRS_URI . '/dist/fonts-en.css', array(), FIVEDRS_VERSION );
 *   }
 *
 * Whatever is chosen, expose it to CSS through the --font-sans / --font-arabic
 * custom properties declared in src/css/main.css so components never name a
 * font family directly.
 *
 * @return void
 */
function fivedrs_enqueue_fonts() {
	// Intentionally empty - see the docblock above.
}
add_action( 'wp_enqueue_scripts', 'fivedrs_enqueue_fonts', 5 );
