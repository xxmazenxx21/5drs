<?php
/**
 * Seed the Arabic translations TranslatePress serves on the front end.
 *
 * Two separate stores are involved and both need filling:
 *
 *  1. Gettext strings (theme strings with the "5drs" text domain).
 *     TranslatePress intercepts every gettext call, caches the English source
 *     in wp_trp_gettext_ar and renders whatever that row holds - which means a
 *     .mo file alone is not enough once a string has been captured. The
 *     translations are read out of languages/ar.po so the .po stays the
 *     single place a translator edits.
 *
 *  2. Dynamic strings (page titles and menu labels) go in the regular
 *     dictionary table, through TranslatePress's own TRP_Query API.
 *
 * Idempotent - safe to re-run after editing the .po. Run with:
 *
 *     make translations
 *
 * @package 5drs
 */

$language = 'ar';
$domain   = '5drs';
$po_file  = get_template_directory() . '/languages/ar.po';

// --- 1. gettext strings, sourced from the .po ------------------------------

/**
 * Minimal .po reader: returns [ msgid => msgstr ] for singular entries.
 *
 * Plural entries are skipped - WordPress resolves those from the .mo, and
 * TranslatePress stores each plural form as its own row keyed by plural_form.
 *
 * @param string $path Path to the .po file.
 * @return array<string,string>
 */
function fivedrs_read_po( $path ) {
	if ( ! is_readable( $path ) ) {
		WP_CLI::error( "cannot read {$path}" );
	}

	$entries = array();
	$msgid   = null;
	$msgstr  = null;
	$mode    = null;
	$plural  = false;

	$flush = static function () use ( &$entries, &$msgid, &$msgstr, &$plural ) {
		if ( null !== $msgid && '' !== $msgid && ! $plural && null !== $msgstr && '' !== $msgstr ) {
			$entries[ $msgid ] = $msgstr;
		}
		$msgid  = null;
		$msgstr = null;
		$plural = false;
	};

	$unquote = static function ( $line ) {
		$line = trim( $line );
		$line = preg_replace( '/^[a-z_\[\]0-9]+\s+/', '', $line );
		$line = trim( $line );
		if ( '"' === substr( $line, 0, 1 ) ) {
			$line = substr( $line, 1, -1 );
		}
		return stripcslashes( $line );
	};

	foreach ( file( $path ) as $line ) {
		$trimmed = trim( $line );

		if ( '' === $trimmed ) {
			$flush();
			$mode = null;
			continue;
		}
		if ( '#' === substr( $trimmed, 0, 1 ) ) {
			continue;
		}

		if ( 0 === strpos( $trimmed, 'msgid_plural' ) ) {
			$plural = true;
			$mode   = null;
		} elseif ( 0 === strpos( $trimmed, 'msgid' ) ) {
			$flush();
			$msgid = $unquote( $trimmed );
			$mode  = 'id';
		} elseif ( 0 === strpos( $trimmed, 'msgstr[' ) ) {
			$plural = true;
			$mode   = null;
		} elseif ( 0 === strpos( $trimmed, 'msgstr' ) ) {
			$msgstr = $unquote( $trimmed );
			$mode   = 'str';
		} elseif ( '"' === substr( $trimmed, 0, 1 ) ) {
			// Continuation line.
			if ( 'id' === $mode ) {
				$msgid .= $unquote( $trimmed );
			} elseif ( 'str' === $mode ) {
				$msgstr .= $unquote( $trimmed );
			}
		}
	}
	$flush();

	return $entries;
}

global $wpdb;

$strings = fivedrs_read_po( $po_file );
WP_CLI::log( sprintf( 'gettext: %d translations read from %s', count( $strings ), basename( $po_file ) ) );

$table   = $wpdb->prefix . 'trp_gettext_' . strtolower( $language );
$updated = 0;
$missing = 0;

foreach ( $strings as $original => $translation ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- TranslatePress owns this table; there is no API for bulk-filling captured gettext rows.
	$rows = $wpdb->query(
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is built from $wpdb->prefix.
			"UPDATE `{$table}` SET translated = %s, status = %d WHERE original = %s AND domain = %s",
			$translation,
			2, // TRP_Query::HUMAN_REVIEWED.
			$original,
			$domain
		)
	);

	if ( $rows ) {
		$updated += (int) $rows;
	} else {
		// Either the row already held this translation (MySQL reports zero
		// affected rows when nothing changes) or TranslatePress has not
		// rendered that string yet. Neither is a problem.
		$missing++;
	}
}

WP_CLI::log( "gettext: {$updated} cached row(s) repaired, {$missing} already correct or not yet rendered" );

// --- 2. page titles and menu labels ----------------------------------------

$dynamic = array(
	'Home'                => 'الرئيسية',
	'Our Products'        => 'منتجاتنا',
	'About Us'            => 'من نحن',
	'Contact Us'          => 'تواصل معنا',
	'Policies'            => 'السياسات',
	'Privacy Policy'      => 'سياسة الخصوصية',
	'Shipping & Delivery' => 'الشحن والتوصيل',
	'Returns & Refunds'   => 'الإرجاع والاسترداد',
	'Terms & Conditions'  => 'الشروط والأحكام',
	'5drs'                => '5drs',
	'Cart'                => 'سلة التسوق',
	'Checkout'            => 'إتمام الطلب',
	'My account'          => 'حسابي',
);

require_once __DIR__ . '/lib-trp.php';

$written = fivedrs_seed_dictionary( $dynamic, $language );
WP_CLI::log( "dictionary: {$written} titles and menu labels translated" );

WP_CLI::success( 'Arabic translations seeded.' );
