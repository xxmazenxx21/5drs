<?php
/**
 * About: image slideshow.
 *
 * Reuses photographs already in the Media Library from the Home and Products
 * work - nothing is imported for this. All slides are in the DOM, stacked in a
 * fixed-aspect frame so switching cannot shift the layout, and only the active
 * one is visible. Without JavaScript the CSS shows the first slide and the
 * controls stay hidden; src/js/modules/slideshow.js adds the rest.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_slides = array();

for ( $fivedrs_i = 1; $fivedrs_i <= 6; $fivedrs_i++ ) {
	$fivedrs_id = (int) fivedrs_content( 'about_story', 'slide_' . $fivedrs_i );

	if ( $fivedrs_id ) {
		$fivedrs_slides[] = $fivedrs_id;
	}
}

if ( empty( $fivedrs_slides ) ) {
	return;
}

$fivedrs_count = count( $fivedrs_slides );
?>
<div
	class="fivedrs-slideshow"
	data-slideshow
	data-slideshow-interval="5000"
	role="group"
	aria-roledescription="<?php esc_attr_e( 'Slideshow', '5drs' ); ?>"
	aria-label="<?php esc_attr_e( 'Our products', '5drs' ); ?>"
>
	<div class="fivedrs-slideshow__frame">
		<?php foreach ( $fivedrs_slides as $fivedrs_index => $fivedrs_slide_id ) : ?>
			<?php
			$fivedrs_is_first = ( 0 === $fivedrs_index );

			echo wp_get_attachment_image(
				$fivedrs_slide_id,
				'large',
				false,
				array(
					'class'       => 'fivedrs-slideshow__image' . ( $fivedrs_is_first ? ' is-active' : '' ),
					// The first slide is visible immediately; the rest wait.
					'loading'     => $fivedrs_is_first ? 'eager' : 'lazy',
					'decoding'    => 'async',
					'sizes'       => '(min-width: 64rem) 32rem, calc(100vw - 2rem)',
					'data-slide'  => (string) $fivedrs_index,
					'aria-hidden' => $fivedrs_is_first ? 'false' : 'true',
				)
			);
			?>
		<?php endforeach; ?>
	</div>

	<?php if ( $fivedrs_count > 1 ) : ?>
		<div class="fivedrs-slideshow__controls" data-slideshow-controls hidden>
			<button
				type="button"
				class="fivedrs-slideshow__toggle"
				data-slideshow-toggle
				aria-label="<?php esc_attr_e( 'Pause slideshow', '5drs' ); ?>"
			>
				<span class="fivedrs-slideshow__icon" data-icon="pause"><?php echo wp_kses( fivedrs_icon( 'pause' ), fivedrs_kses_svg() ); ?></span>
				<span class="fivedrs-slideshow__icon" data-icon="play"><?php echo wp_kses( fivedrs_icon( 'play' ), fivedrs_kses_svg() ); ?></span>
			</button>

			<div class="fivedrs-slideshow__dots">
				<?php foreach ( $fivedrs_slides as $fivedrs_index => $fivedrs_slide_id ) : ?>
					<button
						type="button"
						class="fivedrs-slideshow__dot<?php echo 0 === $fivedrs_index ? ' is-active' : ''; ?>"
						data-slideshow-dot="<?php echo esc_attr( (string) $fivedrs_index ); ?>"
						<?php echo 0 === $fivedrs_index ? 'aria-current="true"' : ''; ?>
						aria-label="
						<?php
						echo esc_attr(
							sprintf(
								/* translators: 1: slide number, 2: total number of slides. */
								__( 'Show image %1$s of %2$s', '5drs' ),
								number_format_i18n( $fivedrs_index + 1 ),
								number_format_i18n( $fivedrs_count )
							)
						);
						?>
						"
					></button>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
