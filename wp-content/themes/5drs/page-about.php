<?php
/**
 * Template Name: About Us
 *
 * Structural shell for the About page. Named page-about.php so it also applies
 * automatically to the page with the slug "about"; the Template Name header
 * means it keeps working if that slug ever changes.
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
endwhile;
?>

<main id="main" class="site-main">

	<?php
	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'  => __( 'Our story', '5drs' ),
			'id'     => 'about-story',
			'height' => '18rem',
		)
	);

	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'   => __( 'Our mission and values', '5drs' ),
			'id'      => 'about-mission',
			'variant' => 'alt',
			'height'  => '16rem',
		)
	);

	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'  => __( 'What we make - product philosophy', '5drs' ),
			'id'     => 'about-products',
			'height' => '16rem',
		)
	);

	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'   => __( 'Closing call to action', '5drs' ),
			'id'      => 'about-cta',
			'variant' => 'surface',
			'height'  => '12rem',
		)
	);
	?>

</main>

<?php
get_footer();
