<?php
/**
 * Template Name: Contact Us
 *
 * The Contact page: a compact hero, then the form beside the contact details.
 * Both columns read their wording from the Customizer (5drs - Contact Page);
 * the address, email and phone come from the shared Contact & Social settings
 * the footer uses, so there is one source for them.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'template-parts/page-hero',
	null,
	array(
		'title'   => fivedrs_content( 'contact', 'heading' ),
		'lead'    => fivedrs_content( 'contact', 'text' ),
		'variant' => 'gradient',
	)
);
?>

<main id="main" class="site-main fivedrs-contact">
	<div class="fivedrs-container fivedrs-section">
		<div class="fivedrs-contact__grid">
			<?php
			get_template_part( 'template-parts/contact/form' );
			get_template_part( 'template-parts/contact/info' );
			?>
		</div>
	</div>
</main>

<?php
get_footer();
