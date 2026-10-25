<?php
/**
 * Sidebar - deliberately empty.
 *
 * The design has no sidebar, and inc/setup.php registers no widget areas (see
 * the note there). This file still has to exist: WordPress and WooCommerce
 * both call get_sidebar(), and a theme without sidebar.php logs a
 * "Theme without sidebar.php is deprecated" notice on every request.
 *
 * WooCommerce's own call is unhooked in inc/woocommerce.php, so store pages
 * never reach this file at all.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;
