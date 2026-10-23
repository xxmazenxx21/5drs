<?php
/**
 * Home: featured products.
 *
 * Uses products flagged "featured" in WooCommerce, falling back to the most
 * recent ones. Rendered through WooCommerce's own loop templates so the cards,
 * prices and AJAX add-to-cart behave exactly as they do in the shop - and the
 * header cart badge keeps updating.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

$fivedrs_count = (int) fivedrs_home( 'featured', 'count' );
$fivedrs_count = $fivedrs_count > 0 ? $fivedrs_count : 4;

$fivedrs_query = array(
	'status'   => 'publish',
	'limit'    => $fivedrs_count,
	'orderby'  => 'date',
	'order'    => 'DESC',
	'visible'  => true,
	'featured' => true,
);

$fivedrs_products = wc_get_products( $fivedrs_query );

if ( empty( $fivedrs_products ) ) {
	unset( $fivedrs_query['featured'] );
	$fivedrs_products = wc_get_products( $fivedrs_query );
}

if ( empty( $fivedrs_products ) ) {
	return;
}

$fivedrs_heading  = fivedrs_home( 'featured', 'heading' );
$fivedrs_subtitle = fivedrs_home( 'featured', 'subtitle' );
$fivedrs_label    = fivedrs_home( 'featured', 'link_label' );
$fivedrs_url      = fivedrs_home_page_url( 'featured', 'link_page' );
?>
<section class="fivedrs-section fivedrs-featured woocommerce" data-reveal>
	<div class="fivedrs-container">

		<?php if ( '' !== $fivedrs_heading || '' !== $fivedrs_subtitle ) : ?>
			<div class="fivedrs-section__head fivedrs-section__head--center">
				<?php if ( '' !== $fivedrs_heading ) : ?>
					<h2><?php echo esc_html( $fivedrs_heading ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $fivedrs_subtitle ) : ?>
					<p class="fivedrs-section__lead"><?php echo esc_html( $fivedrs_subtitle ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<ul class="products columns-4">
			<?php
			global $post;
			$fivedrs_original_post = $post;

			/*
			 * Scoped to this loop only:
			 *  - the section sits well below the fold, but WordPress's loading
			 *    heuristic does not know that and leaves these images eager;
			 *  - WooCommerce renders loop titles as <h2>, which would sit at the
			 *    same level as this section's own heading.
			 */
			add_filter( 'wp_get_attachment_image_attributes', 'fivedrs_featured_lazy_attr' );
			remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
			add_action( 'woocommerce_shop_loop_item_title', 'fivedrs_featured_loop_title', 10 );

			foreach ( $fivedrs_products as $fivedrs_product ) {
				$post = get_post( $fivedrs_product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- wc_get_template_part() reads the global post; restored below.
				setup_postdata( $post );
				wc_get_template_part( 'content', 'product' );
			}

			remove_filter( 'wp_get_attachment_image_attributes', 'fivedrs_featured_lazy_attr' );
			remove_action( 'woocommerce_shop_loop_item_title', 'fivedrs_featured_loop_title', 10 );
			add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );

			$post = $fivedrs_original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restoring the global we borrowed.
			wp_reset_postdata();
			?>
		</ul>

		<?php if ( '' !== $fivedrs_label && '' !== $fivedrs_url ) : ?>
			<p class="fivedrs-featured__more">
				<a class="fivedrs-btn fivedrs-btn--outline" href="<?php echo esc_url( $fivedrs_url ); ?>">
					<?php echo esc_html( $fivedrs_label ); ?>
					<?php echo wp_kses( fivedrs_icon( 'arrow' ), fivedrs_kses_svg() ); ?>
				</a>
			</p>
		<?php endif; ?>

	</div>
</section>
