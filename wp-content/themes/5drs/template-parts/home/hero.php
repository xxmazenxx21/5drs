<?php
/**
 * Home: hero.
 *
 * Two columns - text on the start side, image on the end - stacking to a single
 * column with the text first on narrow screens. Carries the page's only <h1>.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_heading = fivedrs_home( 'hero', 'heading' );
$fivedrs_image   = (int) fivedrs_home( 'hero', 'image' );

if ( '' === $fivedrs_heading && ! $fivedrs_image ) {
	return;
}

$fivedrs_eyebrow   = fivedrs_home( 'hero', 'eyebrow' );
$fivedrs_text      = fivedrs_home( 'hero', 'text' );
$fivedrs_primary   = fivedrs_home( 'hero', 'primary_label' );
$fivedrs_secondary = fivedrs_home( 'hero', 'secondary_label' );
$fivedrs_url_1     = fivedrs_home_page_url( 'hero', 'primary_page' );
$fivedrs_url_2     = fivedrs_home_page_url( 'hero', 'secondary_page' );
?>
<section class="fivedrs-home-hero">
	<div class="fivedrs-container fivedrs-home-hero__inner">

		<div class="fivedrs-home-hero__text">
			<?php if ( '' !== $fivedrs_eyebrow ) : ?>
				<span class="fivedrs-section__eyebrow"><?php echo esc_html( $fivedrs_eyebrow ); ?></span>
			<?php endif; ?>

			<?php if ( '' !== $fivedrs_heading ) : ?>
				<h1 class="fivedrs-home-hero__title"><?php echo esc_html( $fivedrs_heading ); ?></h1>
			<?php endif; ?>

			<?php if ( '' !== $fivedrs_text ) : ?>
				<p class="fivedrs-home-hero__lead"><?php echo esc_html( $fivedrs_text ); ?></p>
			<?php endif; ?>

			<?php if ( ( '' !== $fivedrs_primary && '' !== $fivedrs_url_1 ) || ( '' !== $fivedrs_secondary && '' !== $fivedrs_url_2 ) ) : ?>
				<p class="fivedrs-home-hero__actions">
					<?php if ( '' !== $fivedrs_primary && '' !== $fivedrs_url_1 ) : ?>
						<a class="fivedrs-btn fivedrs-btn--lg" href="<?php echo esc_url( $fivedrs_url_1 ); ?>">
							<?php echo esc_html( $fivedrs_primary ); ?>
							<?php echo wp_kses( fivedrs_icon( 'arrow' ), fivedrs_kses_svg() ); ?>
						</a>
					<?php endif; ?>

					<?php if ( '' !== $fivedrs_secondary && '' !== $fivedrs_url_2 ) : ?>
						<a class="fivedrs-btn fivedrs-btn--outline fivedrs-btn--lg" href="<?php echo esc_url( $fivedrs_url_2 ); ?>">
							<?php echo esc_html( $fivedrs_secondary ); ?>
						</a>
					<?php endif; ?>
				</p>
			<?php endif; ?>
		</div>

		<?php if ( $fivedrs_image ) : ?>
			<div class="fivedrs-home-hero__media">
				<?php
				fivedrs_home_image(
					'hero',
					'image',
					array(
						'size'     => 'full',
						'class'    => 'fivedrs-home-hero__image',
						'priority' => true,
						'sizes'    => '(min-width: 64rem) 36rem, (min-width: 48rem) 28rem, calc(100vw - 2rem)',
					)
				);
				?>
			</div>
		<?php endif; ?>

	</div>
</section>
