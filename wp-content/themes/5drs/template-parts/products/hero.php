<?php
/**
 * Our Products hero.
 *
 * Full-bleed on the shop page, compact on category archives. The image is the
 * LCP element, so it is eager with fetchpriority high and goes through
 * wp_get_attachment_image() for srcset.
 *
 * @param array $args {
 *     @type bool $compact Render the short category-archive variant.
 * }
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_hero = wp_parse_args(
	isset( $args ) ? $args : array(),
	array( 'compact' => false )
);

if ( $fivedrs_hero['compact'] ) {
	$fivedrs_term = get_queried_object();

	$fivedrs_eyebrow  = '';
	$fivedrs_heading  = ( $fivedrs_term && isset( $fivedrs_term->name ) ) ? $fivedrs_term->name : '';
	$fivedrs_text     = ( $fivedrs_term && isset( $fivedrs_term->description ) ) ? $fivedrs_term->description : '';
	$fivedrs_label    = '';
	$fivedrs_image_id = 0;
} else {
	$fivedrs_eyebrow  = fivedrs_content( 'products', 'eyebrow' );
	$fivedrs_heading  = fivedrs_content( 'products', 'heading' );
	$fivedrs_text     = fivedrs_content( 'products', 'text' );
	$fivedrs_label    = fivedrs_content( 'products', 'button_label' );
	$fivedrs_image_id = (int) fivedrs_content( 'products', 'image' );
}

if ( '' === $fivedrs_heading && '' === $fivedrs_text && ! $fivedrs_image_id ) {
	return;
}

$fivedrs_classes = 'fivedrs-phero';
if ( $fivedrs_hero['compact'] ) {
	$fivedrs_classes .= ' fivedrs-phero--compact';
}
if ( ! $fivedrs_image_id ) {
	// No image: the gradient alone carries the section.
	$fivedrs_classes .= ' fivedrs-phero--plain';
}
?>
<section class="<?php echo esc_attr( $fivedrs_classes ); ?>">

	<?php if ( $fivedrs_image_id ) : ?>
		<div class="fivedrs-phero__media" aria-hidden="true">
			<?php
			fivedrs_content_image(
				'products',
				'image',
				array(
					'size'     => 'full',
					'class'    => 'fivedrs-phero__image',
					'priority' => true,
					'sizes'    => '100vw',
				)
			);
			?>
		</div>
	<?php endif; ?>

	<div class="fivedrs-phero__veil" aria-hidden="true"></div>

	<div class="fivedrs-container fivedrs-phero__inner">
		<?php if ( '' !== $fivedrs_eyebrow ) : ?>
			<span class="fivedrs-phero__eyebrow" data-phero-item><?php echo esc_html( $fivedrs_eyebrow ); ?></span>
		<?php endif; ?>

		<?php if ( '' !== $fivedrs_heading ) : ?>
			<h1 class="fivedrs-phero__title" data-phero-item><?php echo esc_html( $fivedrs_heading ); ?></h1>
		<?php endif; ?>

		<?php if ( '' !== $fivedrs_text ) : ?>
			<p class="fivedrs-phero__text" data-phero-item><?php echo esc_html( wp_strip_all_tags( $fivedrs_text ) ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $fivedrs_label ) : ?>
			<p class="fivedrs-phero__actions" data-phero-item>
				<a class="fivedrs-btn fivedrs-btn--lg" href="#products">
					<?php echo esc_html( $fivedrs_label ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>

</section>
