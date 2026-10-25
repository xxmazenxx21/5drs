<?php
/**
 * Shop toolbar: category pills, product search and the sort control.
 *
 * One row so the three controls read as a set. WooCommerce's own sorting
 * control is printed from here rather than from woocommerce_before_shop_loop
 * (unhooked in inc/woocommerce.php) so it sits beside the search field
 * instead of below the pills.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="fivedrs-shop-toolbar" id="products">
	<?php get_template_part( 'template-parts/products/filter' ); ?>

	<div class="fivedrs-shop-toolbar__tools">
		<?php
		get_template_part( 'template-parts/products/search' );

		if ( function_exists( 'woocommerce_catalog_ordering' ) && woocommerce_product_loop() ) {
			woocommerce_catalog_ordering();
		}
		?>
	</div>
</div>
