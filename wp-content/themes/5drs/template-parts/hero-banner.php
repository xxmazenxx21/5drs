<?php
/**
 * Full-bleed page hero.
 *
 * Shared by the Our Products page and the About page, and used in a compact
 * form on product category archives. The image is the LCP element, so it is
 * eager with fetchpriority high and goes through wp_get_attachment_image()
 * for srcset.
 *
 * @param array $args    Optional. Keys: 'section' (content config section to
 *                       read, default 'products'), 'anchor' (href for the
 *                       button, default '#products'), 'compact' (render the
 *                       short category-archive variant) and 'heading' / 'text'
 *                       (override the copy, used by the search results page).
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_hero = wp_parse_args(
	isset( $args ) ? $args : array(),
	array(
		'section' => 'products',
		'anchor'  => '#products',
		'compact' => false,
		'heading' => '',
		'text'    => '',
	)
);

if ( $fivedrs_hero['compact'] ) {
	$fivedrs_term = get_queried_object();

	$fivedrs_eyebrow = '';
	$fivedrs_heading = ( $fivedrs_term && isset( $fivedrs_term->name ) ) ? $fivedrs_term->name : '';
	$fivedrs_text    = ( $fivedrs_term && isset( $fivedrs_term->description ) ) ? $fivedrs_term->description : '';
	$fivedrs_label   = '';

	/*
	 * The compact variant carries the same photograph as the Our Products
	 * hero, read from the section this part was called with (products by
	 * default), so a category archive gets the same image, veil and white
	 * text rather than the bare gradient.
	 */
	$fivedrs_image_id = (int) fivedrs_content( $fivedrs_hero['section'], 'image' );
} else {
	$fivedrs_section  = $fivedrs_hero['section'];
	$fivedrs_eyebrow  = fivedrs_content( $fivedrs_section, 'eyebrow' );
	$fivedrs_heading  = fivedrs_content( $fivedrs_section, 'heading' );
	$fivedrs_text     = fivedrs_content( $fivedrs_section, 'text' );
	$fivedrs_label    = fivedrs_content( $fivedrs_section, 'button_label' );
	$fivedrs_image_id = (int) fivedrs_content( $fivedrs_section, 'image' );
}

// An explicit heading or text wins, so a caller can build its own hero copy.
if ( '' !== $fivedrs_hero['heading'] ) {
	$fivedrs_heading = $fivedrs_hero['heading'];
}
if ( '' !== $fivedrs_hero['text'] ) {
	$fivedrs_text = $fivedrs_hero['text'];
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
<section class="<?php echo esc_attr( $fivedrs_classes ); ?>" data-hero>

	<?php if ( $fivedrs_image_id ) : ?>
		<div class="fivedrs-phero__media" aria-hidden="true">
			<?php
			fivedrs_content_image(
				$fivedrs_hero['section'],
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
				<a class="fivedrs-btn fivedrs-btn--lg" href="<?php echo esc_attr( $fivedrs_hero['anchor'] ); ?>">
					<?php echo esc_html( $fivedrs_label ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>

</section>
