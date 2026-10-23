<?php
/**
 * Main template - fallback for every query without a more specific template.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

get_header();

$fivedrs_title = '';
if ( is_home() && ! is_front_page() ) {
	$fivedrs_title = single_post_title( '', false );
} elseif ( is_archive() ) {
	$fivedrs_title = wp_strip_all_tags( get_the_archive_title() );
} elseif ( is_search() ) {
	$fivedrs_title = sprintf(
		/* translators: %s: search query. */
		__( 'Search results for: %s', '5drs' ),
		get_search_query()
	);
}

if ( '' !== $fivedrs_title ) {
	get_template_part( 'template-parts/page-hero', null, array( 'title' => $fivedrs_title ) );
}
?>

<main id="main" class="site-main">
	<div class="fivedrs-container fivedrs-section">

		<?php if ( have_posts() ) : ?>

			<div class="fivedrs-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', get_post_type() );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'prev_text' => esc_html__( 'Previous', '5drs' ),
					'next_text' => esc_html__( 'Next', '5drs' ),
				)
			);
			?>

		<?php else : ?>

			<?php get_template_part( 'template-parts/content', 'none' ); ?>

		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
