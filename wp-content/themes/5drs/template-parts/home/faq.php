<?php
/**
 * Home: FAQ accordion.
 *
 * Built on <details>/<summary>, which is keyboard accessible and works without
 * JavaScript. The matching FAQPage schema is emitted by fivedrs_faq_schema()
 * from the same source strings, so the two cannot disagree.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_heading = fivedrs_home( 'faq', 'heading' );
$fivedrs_entries = fivedrs_faq_entries();

if ( empty( $fivedrs_entries ) ) {
	return;
}
?>
<section class="fivedrs-section fivedrs-faq" data-reveal>
	<div class="fivedrs-container fivedrs-container--prose">

		<?php if ( '' !== $fivedrs_heading ) : ?>
			<div class="fivedrs-section__head fivedrs-section__head--center">
				<h2><?php echo esc_html( $fivedrs_heading ); ?></h2>
			</div>
		<?php endif; ?>

		<div class="fivedrs-faq__list">
			<?php foreach ( $fivedrs_entries as $fivedrs_entry ) : ?>
				<details class="fivedrs-faq__item">
					<summary class="fivedrs-faq__question">
						<span><?php echo esc_html( $fivedrs_entry['question'] ); ?></span>
						<?php echo wp_kses( fivedrs_icon( 'chevron' ), fivedrs_kses_svg() ); ?>
					</summary>
					<div class="fivedrs-faq__answer">
						<p><?php echo esc_html( $fivedrs_entry['answer'] ); ?></p>
					</div>
				</details>
			<?php endforeach; ?>
		</div>

	</div>
</section>
