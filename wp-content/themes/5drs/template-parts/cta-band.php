<?php
/**
 * Closing call-to-action band.
 *
 * Shared by the Home page and the About page. A second button is rendered only
 * when the section defines one, so Home keeps its single button.
 *
 * @param array $args Optional. 'section' - which content section to read,
 *                    default 'cta'.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_cta_args = wp_parse_args(
	isset( $args ) ? $args : array(),
	array( 'section' => 'cta' )
);

$fivedrs_cta_section = $fivedrs_cta_args['section'];

$fivedrs_heading = fivedrs_content( $fivedrs_cta_section, 'heading' );
$fivedrs_text    = fivedrs_content( $fivedrs_cta_section, 'text' );
$fivedrs_label   = fivedrs_content( $fivedrs_cta_section, 'button_label' );
$fivedrs_url     = fivedrs_content_page_url( $fivedrs_cta_section, 'button_page' );
$fivedrs_label2  = fivedrs_content( $fivedrs_cta_section, 'button_2_label' );
$fivedrs_url2    = fivedrs_content_page_url( $fivedrs_cta_section, 'button_2_page' );

if ( '' === $fivedrs_heading && '' === $fivedrs_text ) {
	return;
}
?>
<section class="fivedrs-cta" data-reveal>
	<div class="fivedrs-container fivedrs-cta__inner">

		<?php if ( '' !== $fivedrs_heading ) : ?>
			<h2 class="fivedrs-cta__title"><?php echo esc_html( $fivedrs_heading ); ?></h2>
		<?php endif; ?>

		<?php if ( '' !== $fivedrs_text ) : ?>
			<p class="fivedrs-cta__text"><?php echo esc_html( $fivedrs_text ); ?></p>
		<?php endif; ?>

		<?php if ( ( '' !== $fivedrs_label && '' !== $fivedrs_url ) || ( '' !== $fivedrs_label2 && '' !== $fivedrs_url2 ) ) : ?>
			<p class="fivedrs-cta__actions">
				<?php if ( '' !== $fivedrs_label && '' !== $fivedrs_url ) : ?>
					<a class="fivedrs-btn fivedrs-btn--accent fivedrs-btn--lg" href="<?php echo esc_url( $fivedrs_url ); ?>">
						<?php echo esc_html( $fivedrs_label ); ?>
						<?php echo wp_kses( fivedrs_icon( 'arrow' ), fivedrs_kses_svg() ); ?>
					</a>
				<?php endif; ?>

				<?php if ( '' !== $fivedrs_label2 && '' !== $fivedrs_url2 ) : ?>
					<a class="fivedrs-btn fivedrs-btn--outline fivedrs-btn--lg fivedrs-cta__secondary" href="<?php echo esc_url( $fivedrs_url2 ); ?>">
						<?php echo esc_html( $fivedrs_label2 ); ?>
					</a>
				<?php endif; ?>
			</p>
		<?php endif; ?>

	</div>
</section>
