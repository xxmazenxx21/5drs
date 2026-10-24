<?php
/**
 * Home: why choose our products.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_heading  = fivedrs_content( 'why', 'heading' );
$fivedrs_subtitle = fivedrs_content( 'why', 'subtitle' );

$fivedrs_cards = array();
foreach ( array(
	1 => 'hand',
	2 => 'sparkle',
	3 => 'grid',
) as $fivedrs_i => $fivedrs_icon ) {
	$fivedrs_title = fivedrs_content( 'why', 'card_' . $fivedrs_i . '_title' );
	$fivedrs_text  = fivedrs_content( 'why', 'card_' . $fivedrs_i . '_text' );

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
<section class="fivedrs-section fivedrs-why" data-reveal>
	<div class="fivedrs-container">

		<?php if ( '' !== $fivedrs_heading || '' !== $fivedrs_subtitle ) : ?>
			<div class="fivedrs-section__head fivedrs-section__head--center">
				<?php if ( '' !== $fivedrs_heading ) : ?>
					<h2><?php echo esc_html( $fivedrs_heading ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $fivedrs_subtitle ) : ?>
					<p class="fivedrs-section__lead"><?php echo esc_html( $fivedrs_subtitle ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $fivedrs_cards ) ) : ?>
			<div class="fivedrs-grid fivedrs-why__grid">
				<?php foreach ( $fivedrs_cards as $fivedrs_card ) : ?>
					<article class="fivedrs-card fivedrs-why__card">
						<span class="fivedrs-why__icon">
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
