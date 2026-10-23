<?php
/**
 * Home page.
 *
 * Structural shell: the sections below are empty, named containers laid out in
 * the order the finished home page will use. No copy or imagery yet - each one
 * gets built in a later task.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="site-main">

	<?php
	// The home hero is bespoke rather than the shared title hero, so it is a
	// placeholder of its own.
	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'   => __( 'Hero - brand statement, product shot, primary call to action', '5drs' ),
			'id'      => 'home-hero',
			'variant' => 'alt',
			'height'  => '24rem',
		)
	);

	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'  => __( 'Featured products', '5drs' ),
			'id'     => 'home-featured',
			'height' => '20rem',
		)
	);

	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'   => __( 'Why 5drs - benefits and trust signals', '5drs' ),
			'id'      => 'home-why',
			'variant' => 'surface',
			'height'  => '16rem',
		)
	);

	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'  => __( 'Product range by category', '5drs' ),
			'id'     => 'home-categories',
			'height' => '18rem',
		)
	);

	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'   => __( 'Customer reviews', '5drs' ),
			'id'      => 'home-reviews',
			'variant' => 'alt',
			'height'  => '16rem',
		)
	);

	get_template_part(
		'template-parts/section-placeholder',
		null,
		array(
			'label'  => __( 'Closing call to action', '5drs' ),
			'id'     => 'home-cta',
			'height' => '12rem',
		)
	);
	?>

</main>

<?php
get_footer();
