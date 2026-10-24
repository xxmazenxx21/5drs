<?php
/**
 * About: what we stand for.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_heading = fivedrs_content( 'about_values', 'heading' );

$fivedrs_icons = array(
	1 => 'shield-check',
	2 => 'hand',
	3 => 'family',
	4 => 'sparkle',
);
$fivedrs_cards = array();

foreach ( $fivedrs_icons as $fivedrs_i => $fivedrs_icon ) {
	$fivedrs_title = fivedrs_content( 'about_values', 'value_' . $fivedrs_i . '_title' );
	$fivedrs_text  = fivedrs_content( 'about_values', 'value_' . $fivedrs_i . '_text' );

	if ( '' === $fivedrs_title && '' === $fivedrs_text ) {
		continue;
	}

	$fivedrs_cards[] = array(
		'icon'  => $fivedrs_icon,
		'title' => $fivedrs_title,
		'text'  => $fivedrs_text,
	);
}

if ( '' === $fivedrs_heading && empty( $fivedrs_cards ) ) {
	return;
}
?>
<section class="fivedrs-section fivedrs-values">
	<div class="fivedrs-container">

		<?php if ( '' !== $fivedrs_heading ) : ?>
			<div class="fivedrs-section__head fivedrs-section__head--center" data-reveal>
				<h2><?php echo esc_html( $fivedrs_heading ); ?></h2>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $fivedrs_cards ) ) : ?>
			<div class="fivedrs-grid fivedrs-values__grid">
				<?php foreach ( $fivedrs_cards as $fivedrs_index => $fivedrs_card ) : ?>
					<article class="fivedrs-card fivedrs-values__card" data-reveal data-reveal-index="<?php echo esc_attr( (string) $fivedrs_index ); ?>">
						<span class="fivedrs-values__icon">
							<?php echo wp_kses( fivedrs_icon( $fivedrs_card['icon'] ), fivedrs_kses_svg() ); ?>
						</span>

						<?php if ( '' !== $fivedrs_card['title'] ) : ?>
							<h3 class="fivedrs-card__title"><?php echo esc_html( $fivedrs_card['title'] ); ?></h3>
						<?php endif; ?>

						<?php if ( '' !== $fivedrs_card['text'] ) : ?>
							<p class="fivedrs-card__meta"><?php echo esc_html( $fivedrs_card['text'] ); ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
