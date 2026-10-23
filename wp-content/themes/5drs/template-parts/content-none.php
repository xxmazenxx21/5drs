<?php
/**
 * Shown when a loop returns no results.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="no-results">
	<h2><?php esc_html_e( 'Nothing found', '5drs' ); ?></h2>
	<p><?php esc_html_e( 'No content matched your request.', '5drs' ); ?></p>
	<?php get_search_form(); ?>
</section>
