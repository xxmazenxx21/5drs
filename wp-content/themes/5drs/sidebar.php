<?php
/**
 * Sidebar.
 *
 * WordPress and WooCommerce both call get_sidebar(), so this file must exist
 * even while the design has no sidebar - otherwise every request logs a
 * "Theme without sidebar.php is deprecated" notice.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_active_sidebar( 'fivedrs-shop-sidebar' ) ) {
	return;
}
?>
<aside id="secondary" class="widget-area" aria-label="<?php esc_attr_e( 'Sidebar', '5drs' ); ?>">
	<?php dynamic_sidebar( 'fivedrs-shop-sidebar' ); ?>
</aside>
