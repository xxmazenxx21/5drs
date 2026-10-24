<?php
/**
 * Page hero.
 *
 * The standard page header across the site: title, optional eyebrow and lead.
 * WooCommerce archives use it too, via fivedrs_wc_wrapper_start().
 *
 * @param array $args {
 *     @type string $title   Heading text. Defaults to the current post title.
 *     @type string $eyebrow Small label above the heading.
 *     @type string $lead    Short paragraph under the heading.
 *     @type bool   $center  Centre the block. Default true.
 *     @type bool   $crumbs  Show the parent-page breadcrumb. Default false.
 *     @type string $variant '' for the soft tint, 'gradient' for the brand
 *                           navy gradient with inverted text.
 * }
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_hero = wp_parse_args(
	isset( $args ) ? $args : array(),
	array(
		'title'   => get_the_title(),
		'eyebrow' => '',
		'lead'    => '',
		'center'  => true,
		'crumbs'  => false,
		'variant' => '',
	)
);
?>
<section class="fivedrs-hero<?php echo $fivedrs_hero['center'] ? ' fivedrs-hero--center' : ''; ?><?php echo 'gradient' === $fivedrs_hero['variant'] ? ' fivedrs-hero--gradient' : ''; ?>" data-reveal>
	<div class="fivedrs-container">
		<div class="fivedrs-hero__inner">

			<?php if ( $fivedrs_hero['crumbs'] ) : ?>
				<?php
				$fivedrs_parent = wp_get_post_parent_id( get_the_ID() );
				if ( $fivedrs_parent ) :
					?>
					<p class="fivedrs-breadcrumb">
						<a href="<?php echo esc_url( get_permalink( $fivedrs_parent ) ); ?>">
							<?php echo esc_html( get_the_title( $fivedrs_parent ) ); ?>
						</a>
					</p>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( '' !== $fivedrs_hero['eyebrow'] ) : ?>
				<span class="fivedrs-section__eyebrow"><?php echo esc_html( $fivedrs_hero['eyebrow'] ); ?></span>
			<?php endif; ?>

			<h1 class="fivedrs-hero__title"><?php echo esc_html( $fivedrs_hero['title'] ); ?></h1>

			<?php if ( '' !== $fivedrs_hero['lead'] ) : ?>
				<p class="fivedrs-hero__lead"><?php echo esc_html( $fivedrs_hero['lead'] ); ?></p>
			<?php endif; ?>

		</div>
	</div>
</section>
