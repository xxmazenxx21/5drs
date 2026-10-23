<?php
/**
 * 5drs theme bootstrap.
 *
 * PHP identifiers cannot start with a digit, so every function, class, hook
 * and constant in this theme uses the "fivedrs_" / "FIVEDRS_" prefix.
 * The text domain stays "5drs".
 *
 * This file only defines constants and requires inc/ files - no logic here.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

define( 'FIVEDRS_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'FIVEDRS_DIR', get_template_directory() );
define( 'FIVEDRS_URI', get_template_directory_uri() );
define( 'FIVEDRS_TEXTDOMAIN', '5drs' );

require_once FIVEDRS_DIR . '/inc/setup.php';
require_once FIVEDRS_DIR . '/inc/i18n.php';
require_once FIVEDRS_DIR . '/inc/assets.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once FIVEDRS_DIR . '/inc/woocommerce.php';
}
