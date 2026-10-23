<?php
/**
 * Creates the TranslatePress database tables for every configured language.
 *
 * TranslatePress normally creates these when the languages are saved through
 * its settings screen. Because this project configures the "trp_settings"
 * option directly with WP-CLI, that routine never runs and every front-end
 * request to /ar/ logs "table wp_trp_gettext_ar doesn't exist".
 *
 * Run with: make trp-tables
 */

$trp        = TRP_Translate_Press::get_trp_instance();
$settings   = $trp->get_component( 'settings' )->get_settings();
$query      = $trp->get_component( 'query' );
$gettext    = $query->get_query_component( 'gettext_table_creation' );
$default    = $settings['default-language'];

foreach ( $settings['translation-languages'] as $language_code ) {
	if ( $default !== $language_code ) {
		$query->check_table( $default, $language_code );
	}
	$gettext->check_gettext_table( $language_code );
	WP_CLI::log( "tables checked: {$language_code}" );
}

$query->check_original_table();
$query->check_original_meta_table();
$gettext->check_gettext_original_table();
$gettext->check_gettext_original_meta_table();
$query->check_machine_translation_lock_table();

global $wpdb;
$tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}trp_%'" );
WP_CLI::success( count( $tables ) . ' TranslatePress tables present: ' . implode( ', ', $tables ) );
