<?php
/**
 * Home page.
 *
 * Each section is a template part that renders nothing when its content is
 * empty, so the client can switch a whole section off from the Customizer
 * (5drs - Home Page) by clearing its text.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="site-main fivedrs-home">
	<?php
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/trust' );
	get_template_part( 'template-parts/home/why' );
	get_template_part( 'template-parts/home/categories' );
	get_template_part( 'template-parts/home/featured' );
	get_template_part( 'template-parts/home/kids' );
	get_template_part( 'template-parts/home/tips' );
	get_template_part( 'template-parts/home/faq' );
	get_template_part( 'template-parts/home/cta' );
	?>
</main>

<?php
get_footer();
