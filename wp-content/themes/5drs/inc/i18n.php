<?php
/**
 * Language, direction, font loading and TranslatePress helpers.
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
 * The languages available on the site, with their URL for the current page.
 *
 * Wraps TranslatePress's own helper so templates do not depend on the plugin
 * directly and degrade to an empty list when it is not active.
 *
 * @return array<int,array{code:string,name:string,slug:string,url:string,current:bool}>
 */
function fivedrs_languages() {
	if ( ! function_exists( 'trp_custom_language_switcher' ) ) {
		return array();
	}

	$current = fivedrs_current_locale();
	$out     = array();

	foreach ( (array) trp_custom_language_switcher() as $code => $language ) {
		$out[] = array(
			'code'    => (string) $code,
			'name'    => isset( $language['language_name'] ) ? (string) $language['language_name'] : (string) $code,
			'slug'    => isset( $language['short_language_name'] ) ? (string) $language['short_language_name'] : (string) $code,
			'url'     => isset( $language['current_page_url'] ) ? (string) $language['current_page_url'] : '',
			'current' => $code === $current,
		);
	}

	return $out;
}

/**
 * Render the language switcher.
 *
 * Built from TranslatePress's data rather than its [language-switcher]
 * shortcode, so the markup is ours: a simple, styleable, keyboard-accessible
 * pair of links with aria-current on the active one. Returns an empty string
 * when TranslatePress is inactive, so templates stay safe.
 *
 * @param array $args Optional. 'wrapper_class' => string, 'label' => string.
 * @return string Switcher HTML.
 */
function fivedrs_language_switcher( $args = array() ) {
	$languages = fivedrs_languages();

	if ( count( $languages ) < 2 ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'wrapper_class' => 'fivedrs-langs',
			'label'         => __( 'Change language', '5drs' ),
		)
	);

	$items = '';
	foreach ( $languages as $language ) {
		if ( '' === $language['url'] ) {
			continue;
		}

		$items .= sprintf(
			'<a class="fivedrs-langs__item%1$s" href="%2$s" lang="%3$s" hreflang="%3$s"%4$s>%5$s</a>',
			$language['current'] ? ' is-active' : '',
			esc_url( $language['url'] ),
			esc_attr( str_replace( '_', '-', $language['code'] ) ),
			$language['current'] ? ' aria-current="true"' : '',
			esc_html( $language['name'] )
		);
	}

	if ( '' === $items ) {
		return '';
	}

	return sprintf(
		'<nav class="%1$s" aria-label="%2$s">%3$s</nav>',
		esc_attr( $args['wrapper_class'] ),
		esc_attr( $args['label'] ),
		$items
	);
}

/**
 * Stylesheet handle and preload file for the current language's webfont.
 *
 * Both faces are self-hosted (no Google CDN) and built as separate Vite
 * entries, so an English visitor never downloads Tajawal and an Arabic visitor
 * never downloads Montserrat.
 *
 * @return array{entry:string,handle:string,preload:string}
 */
function fivedrs_font_for_language() {
	if ( fivedrs_is_arabic() ) {
		return array(
			'entry'   => 'src/css/fonts-ar.css',
			'handle'  => 'fivedrs-font-ar',
			// Unhashed by vite.config.js so this path is known ahead of the build.
			'preload' => 'tajawal-arabic-400-normal.woff2',
		);
	}

	return array(
		'entry'   => 'src/css/fonts-en.css',
		'handle'  => 'fivedrs-font-en',
		'preload' => 'montserrat-latin-400-normal.woff2',
	);
}

/**
 * Enqueue the webfont for the current language.
 *
 * Runs before fivedrs_enqueue_assets() so the font sheet is ahead of the main
 * stylesheet in the document.
 *
 * @return void
 */
function fivedrs_enqueue_fonts() {
	$font = fivedrs_font_for_language();
	$url  = fivedrs_asset_url( $font['entry'] );

	if ( '' === $url ) {
		return;
	}

	wp_enqueue_style( $font['handle'], $url, array(), fivedrs_asset_version( $font['entry'] ) );
}
add_action( 'wp_enqueue_scripts', 'fivedrs_enqueue_fonts', 5 );

/**
 * Preload the body weight of the current language's font.
 *
 * Only the 400 weight is preloaded; the rest are fetched on demand. Skipped
 * while the Vite dev server is serving assets, where the path does not exist.
 *
 * @return void
 */
function fivedrs_preload_font() {
	if ( fivedrs_dev_server() ) {
		return;
	}

	$font = fivedrs_font_for_language();
	$path = FIVEDRS_DIR . '/dist/fonts/' . $font['preload'];

	if ( ! file_exists( $path ) ) {
		return;
	}

	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( FIVEDRS_URI . '/dist/fonts/' . $font['preload'] )
	);
}
add_action( 'wp_head', 'fivedrs_preload_font', 1 );
