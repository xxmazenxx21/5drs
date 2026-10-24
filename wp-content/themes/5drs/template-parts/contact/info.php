<?php
/**
 * Contact information card.
 *
 * Reads the same Customizer settings the footer does, so the address, email
 * and phone have one source. Rows with nothing in them are skipped.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_heading = fivedrs_content( 'contact', 'info_heading' );
$fivedrs_address = fivedrs_option( 'address' );
$fivedrs_email   = fivedrs_option( 'email' );
$fivedrs_phone   = fivedrs_option( 'phone' );
$fivedrs_social  = fivedrs_content( 'contact', 'label_social' );

if ( '' === $fivedrs_address && '' === $fivedrs_email && '' === $fivedrs_phone ) {
	return;
}

$fivedrs_index = 0;
?>
<aside class="fivedrs-contact__info" data-reveal="end">
	<div class="fivedrs-info-card">
		<?php if ( '' !== $fivedrs_heading ) : ?>
			<h2 class="fivedrs-info-card__title"><?php echo esc_html( $fivedrs_heading ); ?></h2>
		<?php endif; ?>

		<ul class="fivedrs-info-card__list">
			<?php if ( '' !== $fivedrs_address ) : ?>
				<li class="fivedrs-info-row" data-reveal data-reveal-index="<?php echo esc_attr( (string) $fivedrs_index++ ); ?>">
					<span class="fivedrs-info-row__icon"><?php echo wp_kses( fivedrs_icon( 'pin' ), fivedrs_kses_svg() ); ?></span>
					<span class="fivedrs-info-row__body">
						<span class="fivedrs-info-row__label"><?php echo esc_html( fivedrs_content( 'contact', 'label_address' ) ); ?></span>
						<span class="fivedrs-info-row__value"><?php echo esc_html( $fivedrs_address ); ?></span>
					</span>
				</li>
			<?php endif; ?>

			<?php if ( '' !== $fivedrs_email ) : ?>
				<li class="fivedrs-info-row" data-reveal data-reveal-index="<?php echo esc_attr( (string) $fivedrs_index++ ); ?>">
					<span class="fivedrs-info-row__icon"><?php echo wp_kses( fivedrs_icon( 'mail' ), fivedrs_kses_svg() ); ?></span>
					<span class="fivedrs-info-row__body">
						<span class="fivedrs-info-row__label"><?php echo esc_html( fivedrs_content( 'contact', 'label_email' ) ); ?></span>
						<a class="fivedrs-info-row__value" href="mailto:<?php echo esc_attr( $fivedrs_email ); ?>" dir="ltr">
							<?php echo esc_html( $fivedrs_email ); ?>
						</a>
					</span>
				</li>
			<?php endif; ?>

			<?php if ( '' !== $fivedrs_phone ) : ?>
				<li class="fivedrs-info-row" data-reveal data-reveal-index="<?php echo esc_attr( (string) $fivedrs_index++ ); ?>">
					<span class="fivedrs-info-row__icon"><?php echo wp_kses( fivedrs_icon( 'phone' ), fivedrs_kses_svg() ); ?></span>
					<span class="fivedrs-info-row__body">
						<span class="fivedrs-info-row__label"><?php echo esc_html( fivedrs_content( 'contact', 'label_phone' ) ); ?></span>
						<?php
						/*
						 * <bdi dir="ltr"> keeps the leading "+" at the front of
						 * the number on Arabic pages, where the surrounding
						 * paragraph direction would otherwise move it.
						 */
						?>
						<a class="fivedrs-info-row__value" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $fivedrs_phone ) ); ?>">
							<bdi dir="ltr"><?php echo esc_html( $fivedrs_phone ); ?></bdi>
						</a>
					</span>
				</li>
			<?php endif; ?>
		</ul>

		<?php if ( '' !== $fivedrs_social ) : ?>
			<div class="fivedrs-info-card__social" data-reveal data-reveal-index="<?php echo esc_attr( (string) $fivedrs_index++ ); ?>">
				<span class="fivedrs-info-row__label"><?php echo esc_html( $fivedrs_social ); ?></span>
				<?php
				/*
				 * Always shown here, unlike the footer. Until the Customizer
				 * has the URLs these render as labelled marks rather than
				 * links, because a link that goes nowhere is worse than none.
				 */
				fivedrs_social_links(
					array(
						'only'   => array( 'facebook', 'instagram' ),
						'always' => true,
						'class'  => 'fivedrs-social fivedrs-social--contact',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</aside>
