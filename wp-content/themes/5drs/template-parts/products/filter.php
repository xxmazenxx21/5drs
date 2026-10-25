<?php
/**
 * Category filter pills.
 *
 * Real links to the category archives, so filtering works with JavaScript off
 * and every filtered view has its own URL. The sliding indicator is decorative
 * and added by src/js/modules/products.js.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_shop_id = (int) wc_get_page_id( 'shop' );

$fivedrs_terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'orderby'    => 'meta_value_num',
		'meta_key'   => 'order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- four terms, ordered by the admin's own sort field.
		'order'      => 'ASC',
	)
);

if ( is_wp_error( $fivedrs_terms ) || count( $fivedrs_terms ) < 2 ) {
	return;
}

$fivedrs_current = 0;
if ( is_product_taxonomy() ) {
	$fivedrs_queried = get_queried_object();
	$fivedrs_current = ( $fivedrs_queried && isset( $fivedrs_queried->term_id ) ) ? (int) $fivedrs_queried->term_id : 0;
}
?>
<nav class="fivedrs-pills" aria-label="<?php esc_attr_e( 'Filter by category', '5drs' ); ?>" data-pills>
	<ul class="fivedrs-pills__list">
		<li>
			<a
				class="fivedrs-pills__item<?php echo $fivedrs_current ? '' : ' is-active'; ?>"
				href="<?php echo esc_url( $fivedrs_shop_id ? get_permalink( $fivedrs_shop_id ) : home_url( '/' ) ); ?>"
				<?php echo $fivedrs_current ? '' : 'aria-current="page"'; ?>
			>
				<?php esc_html_e( 'All', '5drs' ); ?>
			</a>
		</li>

		<?php foreach ( $fivedrs_terms as $fivedrs_term ) : ?>
			<?php
			$fivedrs_url = get_term_link( $fivedrs_term );
			if ( is_wp_error( $fivedrs_url ) ) {
				continue;
			}
			$fivedrs_is_active = ( $fivedrs_current === (int) $fivedrs_term->term_id );
			?>
			<li>
				<a
					class="fivedrs-pills__item<?php echo $fivedrs_is_active ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( $fivedrs_url ); ?>"
					<?php echo $fivedrs_is_active ? 'aria-current="page"' : ''; ?>
				>
					<?php echo esc_html( $fivedrs_term->name ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
