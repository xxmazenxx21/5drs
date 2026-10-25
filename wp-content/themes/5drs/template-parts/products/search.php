<?php
/**
 * Product search form.
 *
 * A plain GET form, so it works with JavaScript off and every result set has
 * its own URL. The hidden post_type is what keeps it a *product* search:
 * without it WordPress searches posts and pages too and WooCommerce never
 * renders the result with the shop layout.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_search_id = 'fivedrs-shop-search-' . wp_unique_id();
?>
<form
	role="search"
	method="get"
	class="fivedrs-shop-search"
	action="<?php echo esc_url( home_url( '/' ) ); ?>"
>
	<label class="screen-reader-text" for="<?php echo esc_attr( $fivedrs_search_id ); ?>">
		<?php esc_html_e( 'Search products', '5drs' ); ?>
	</label>

	<input
		type="search"
		id="<?php echo esc_attr( $fivedrs_search_id ); ?>"
		class="fivedrs-shop-search__field"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Search products…', '5drs' ); ?>"
		autocomplete="off"
	/>

	<input type="hidden" name="post_type" value="product" />

	<button type="submit" class="fivedrs-shop-search__submit">
		<span class="screen-reader-text"><?php esc_html_e( 'Search', '5drs' ); ?></span>
		<?php echo fivedrs_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG from a fixed list. ?>
	</button>
</form>
