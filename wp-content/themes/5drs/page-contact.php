<?php
/**
 * Template Name: Contact Us
 *
 * Structural shell for the Contact page.
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
	/*
	 * Two columns on desktop: the enquiry form on one side, the contact details
	 * the client fills in through the Customizer on the other.
	 *
	 * No form plugin is installed yet - that is a separate task. When one is
	 * chosen, drop its shortcode into the form placeholder below.
	 */
	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'  => __( 'Contact form - no form plugin installed yet', '5drs' ),
			'id'     => 'contact-form',
			'height' => '22rem',
		)
	);

	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'   => __( 'Contact details and opening hours', '5drs' ),
			'id'      => 'contact-details',
			'variant' => 'alt',
			'height'  => '14rem',
		)
	);

	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'  => __( 'Map', '5drs' ),
			'id'     => 'contact-map',
			'height' => '18rem',
		)
	);
	?>

</main>

<?php
get_footer();
