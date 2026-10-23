<?php
/**
 * Single post template.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part(
		'template-parts/page-hero',
		null,
		array(
			'title'   => get_the_title(),
			'eyebrow' => get_the_date(),
		)
	);
	?>

	<main id="main" class="site-main">
		<div class="fivedrs-container fivedrs-container--prose fivedrs-section">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<nav class="page-links">',
							'after'  => '</nav>',
						)
					);
					?>
				</div>
			</article>

			<?php
			the_post_navigation(
				array(
					'prev_text' => '%title',
					'next_text' => '%title',
				)
			);

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</main>

	<?php
endwhile;

get_footer();
