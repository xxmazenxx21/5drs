<?php
/**
 * Template Name: Policies
 *
 * The Policies page carries the return and exchange policy itself. It used to
 * be a hub listing four child pages; those were replaced by this single page,
 * so there is no listing here any more - just the page's own content, written
 * in the editor as core blocks.
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
			'variant' => 'gradient',
		)
	);
	?>

	<main id="main" class="site-main fivedrs-policy">
		<div class="fivedrs-container fivedrs-section">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'fivedrs-policy__body' ); ?>>
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
		</div>
	</main>

	<?php
endwhile;

get_footer();
