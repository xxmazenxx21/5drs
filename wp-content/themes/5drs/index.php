<?php
/**
 * Main template - fallback for every query without a more specific template.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="site-main">

	<?php if ( have_posts() ) : ?>

		<?php if ( is_home() && ! is_front_page() ) : ?>
			<h1 class="page-title"><?php single_post_title(); ?></h1>
		<?php elseif ( is_archive() ) : ?>
			<h1 class="page-title"><?php the_archive_title(); ?></h1>
		<?php elseif ( is_search() ) : ?>
			<h1 class="page-title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Search results for: %s', '5drs' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		<?php endif; ?>

		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', get_post_type() );
		endwhile;

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

</main>

<?php
get_footer();
