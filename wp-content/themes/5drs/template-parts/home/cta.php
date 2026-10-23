<?php
/**
 * Home: closing call to action.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_heading = fivedrs_home( 'cta', 'heading' );
$fivedrs_text    = fivedrs_home( 'cta', 'text' );
$fivedrs_label   = fivedrs_home( 'cta', 'button_label' );
$fivedrs_url     = fivedrs_home_page_url( 'cta', 'button_page' );

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

		<?php if ( '' !== $fivedrs_label && '' !== $fivedrs_url ) : ?>
			<p>
				<a class="fivedrs-btn fivedrs-btn--accent fivedrs-btn--lg" href="<?php echo esc_url( $fivedrs_url ); ?>">
					<?php echo esc_html( $fivedrs_label ); ?>
					<?php echo wp_kses( fivedrs_icon( 'arrow' ), fivedrs_kses_svg() ); ?>
				</a>
			</p>
		<?php endif; ?>

	</div>
</section>
