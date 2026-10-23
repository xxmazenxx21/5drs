<?php
/**
 * Home: kids spotlight.
 *
 * Wide banner: image and a text panel. The text sits on the start side, so the
 * whole thing mirrors in Arabic without a direction-specific rule.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_heading = fivedrs_home( 'kids', 'heading' );
$fivedrs_image   = (int) fivedrs_home( 'kids', 'image' );

if ( '' === $fivedrs_heading && ! $fivedrs_image ) {
	return;
}

$fivedrs_bullets = array_filter(
	array(
		fivedrs_home( 'kids', 'bullet_1' ),
		fivedrs_home( 'kids', 'bullet_2' ),
		fivedrs_home( 'kids', 'bullet_3' ),
	),
	static function ( $bullet ) {
		return '' !== $bullet;
	}
);

$fivedrs_eyebrow = fivedrs_home( 'kids', 'eyebrow' );
$fivedrs_text    = fivedrs_home( 'kids', 'text' );
$fivedrs_label   = fivedrs_home( 'kids', 'button_label' );
$fivedrs_url     = fivedrs_home_category_url( 'kids', 'button_cat' );
?>
<section class="fivedrs-section fivedrs-kids" data-reveal>
	<div class="fivedrs-container">
		<div class="fivedrs-kids__panel">

			<div class="fivedrs-kids__text">
				<?php if ( '' !== $fivedrs_eyebrow ) : ?>
					<span class="fivedrs-section__eyebrow"><?php echo esc_html( $fivedrs_eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( '' !== $fivedrs_heading ) : ?>
					<h2><?php echo esc_html( $fivedrs_heading ); ?></h2>
				<?php endif; ?>

				<?php if ( '' !== $fivedrs_text ) : ?>
					<p class="fivedrs-section__lead"><?php echo esc_html( $fivedrs_text ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $fivedrs_bullets ) ) : ?>
					<ul class="fivedrs-kids__bullets">
						<?php foreach ( $fivedrs_bullets as $fivedrs_bullet ) : ?>
							<li>
								<?php echo wp_kses( fivedrs_icon( 'check' ), fivedrs_kses_svg() ); ?>
								<span><?php echo esc_html( $fivedrs_bullet ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( '' !== $fivedrs_label && '' !== $fivedrs_url ) : ?>
					<p>
						<a class="fivedrs-btn fivedrs-btn--accent" href="<?php echo esc_url( $fivedrs_url ); ?>">
							<?php echo esc_html( $fivedrs_label ); ?>
							<?php echo wp_kses( fivedrs_icon( 'arrow' ), fivedrs_kses_svg() ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $fivedrs_image ) : ?>
				<div class="fivedrs-kids__media">
					<?php
					fivedrs_home_image(
						'kids',
						'image',
						array(
							'size'  => 'large',
							'class' => 'fivedrs-kids__image',
							'sizes' => '(min-width: 64rem) 32rem, calc(100vw - 2rem)',
						)
					);
					?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
