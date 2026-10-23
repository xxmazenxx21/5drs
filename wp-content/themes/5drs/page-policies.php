<?php
/**
 * Template Name: Policies hub
 *
 * Lists the policy pages that sit beneath this one. The listing is generated
 * from the page tree rather than hardcoded, so adding a policy page in
 * wp-admin is enough to surface it here.
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
		array( 'title' => get_the_title() )
	);

	$fivedrs_children = get_pages(
		array(
			'parent'      => get_the_ID(),
			'sort_column' => 'menu_order,post_title',
			'post_status' => 'publish',
		)
	);
	?>

	<main id="main" class="site-main">
		<div class="fivedrs-container fivedrs-section">

			<?php if ( get_the_content() ) : ?>
				<div class="entry-content fivedrs-container--prose fivedrs-section__head">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<?php if ( $fivedrs_children ) : ?>
				<div class="fivedrs-grid fivedrs-grid--2">
					<?php foreach ( $fivedrs_children as $fivedrs_child ) : ?>
						<a class="fivedrs-card" href="<?php echo esc_url( get_permalink( $fivedrs_child ) ); ?>">
							<h2 class="fivedrs-card__title"><?php echo esc_html( get_the_title( $fivedrs_child ) ); ?></h2>
							<span class="fivedrs-card__more">
								<?php esc_html_e( 'Read more', '5drs' ); ?>
								<?php echo wp_kses( fivedrs_icon( 'arrow' ), fivedrs_kses_svg() ); ?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<?php
				get_template_part(
					'template-parts/section-placeholder',
					null,
					array(
						'label'  => __( 'Policy pages - add child pages under Policies', '5drs' ),
						'height' => '10rem',
					)
				);
				?>
			<?php endif; ?>

		</div>
	</main>

	<?php
endwhile;

get_footer();
