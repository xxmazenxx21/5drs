<?php
/**
 * Template Name: About Us
 *
 * The About page. Named page-about.php so it also applies automatically to the
 * page with the slug "about"; the Template Name header means it keeps working
 * if that slug ever changes.
 *
 * Every section is a template part that renders nothing when its content is
 * empty, so the client can switch a whole section off from the Customizer
 * (5drs - About Page) by clearing its text.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'template-parts/hero-banner',
	null,
	array(
		'section' => 'about',
		'anchor'  => '#story',
	)
);
?>

<main id="main" class="site-main fivedrs-about">
	<?php
	get_template_part( 'template-parts/about/story' );
	get_template_part( 'template-parts/about/mission' );
	get_template_part( 'template-parts/about/values' );

	get_template_part(
		'template-parts/category-cards',
		null,
		array(
			'heading'  => fivedrs_content( 'about_range', 'heading' ),
			'subtitle' => fivedrs_content( 'about_range', 'subtitle' ),
			'variant'  => '',
		)
	);

	get_template_part( 'template-parts/about/promise' );
	get_template_part( 'template-parts/cta-band', null, array( 'section' => 'about_cta' ) );
	?>
</main>

<?php
get_footer();
