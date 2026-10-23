<?php
/**
 * 404 template.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'title' => __( 'Page not found', '5drs' ),
		'lead'  => __( 'The page you are looking for does not exist or has moved.', '5drs' ),
	)
);
?>

<main id="main" class="site-main">
	<div class="fivedrs-container fivedrs-container--prose fivedrs-section">
		<?php get_search_form(); ?>

		<p>
			<a class="fivedrs-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to home', '5drs' ); ?>
				<?php echo wp_kses( fivedrs_icon( 'arrow' ), fivedrs_kses_svg() ); ?>
			</a>
		</p>
	</div>
</main>

<?php
get_footer();
