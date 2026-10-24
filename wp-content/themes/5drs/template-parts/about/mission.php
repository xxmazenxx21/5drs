<?php
/**
 * About: mission and vision.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_cards = array();

foreach ( array(
	'mission' => 'target',
	'vision'  => 'eye',
) as $fivedrs_key => $fivedrs_icon ) {
	$fivedrs_title = fivedrs_content( 'about_mission', $fivedrs_key . '_title' );
	$fivedrs_text  = fivedrs_content( 'about_mission', $fivedrs_key . '_text' );

	if ( '' === $fivedrs_title && '' === $fivedrs_text ) {
		continue;
	}

	$fivedrs_cards[] = array(
		'icon'  => $fivedrs_icon,
		'title' => $fivedrs_title,
		'text'  => $fivedrs_text,
	);
}

if ( empty( $fivedrs_cards ) ) {
	return;
}
?>
<section class="fivedrs-section fivedrs-section--alt fivedrs-mission">
	<div class="fivedrs-container">
		<div class="fivedrs-grid fivedrs-grid--2 fivedrs-mission__grid">
			<?php foreach ( $fivedrs_cards as $fivedrs_index => $fivedrs_card ) : ?>
				<article class="fivedrs-card fivedrs-mission__card" data-reveal data-reveal-index="<?php echo esc_attr( (string) $fivedrs_index ); ?>">
					<span class="fivedrs-mission__icon">
						<?php echo wp_kses( fivedrs_icon( $fivedrs_card['icon'] ), fivedrs_kses_svg() ); ?>
					</span>

					<?php if ( '' !== $fivedrs_card['title'] ) : ?>
						<h2 class="fivedrs-card__title"><?php echo esc_html( $fivedrs_card['title'] ); ?></h2>
					<?php endif; ?>

					<?php if ( '' !== $fivedrs_card['text'] ) : ?>
						<p class="fivedrs-card__meta"><?php echo esc_html( $fivedrs_card['text'] ); ?></p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
