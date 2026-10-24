<?php
/**
 * Home: three steps to a healthier smile.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_heading = fivedrs_content( 'tips', 'heading' );

$fivedrs_steps = array();
for ( $fivedrs_i = 1; $fivedrs_i <= 3; $fivedrs_i++ ) {
	$fivedrs_step = fivedrs_content( 'tips', 'step_' . $fivedrs_i );

	if ( '' !== $fivedrs_step ) {
		$fivedrs_steps[ $fivedrs_i ] = $fivedrs_step;
	}
}

if ( '' === $fivedrs_heading && empty( $fivedrs_steps ) ) {
	return;
}
?>
<section class="fivedrs-section fivedrs-section--alt fivedrs-tips" data-reveal>
	<div class="fivedrs-container">

		<?php if ( '' !== $fivedrs_heading ) : ?>
			<div class="fivedrs-section__head fivedrs-section__head--center">
				<h2><?php echo esc_html( $fivedrs_heading ); ?></h2>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $fivedrs_steps ) ) : ?>
			<ol class="fivedrs-tips__list">
				<?php foreach ( $fivedrs_steps as $fivedrs_number => $fivedrs_step ) : ?>
					<li class="fivedrs-tips__item">
						<span class="fivedrs-tips__number" aria-hidden="true"><?php echo esc_html( number_format_i18n( $fivedrs_number ) ); ?></span>
						<p><?php echo esc_html( $fivedrs_step ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

	</div>
</section>
