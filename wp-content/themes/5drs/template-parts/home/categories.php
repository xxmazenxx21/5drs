<?php
/**
 * Home: shop by need.
 *
 * Each card needs a category, an image and some text. A card missing any of
 * those is skipped - which is how the Black Edition card stays hidden until a
 * clean image is supplied in the Customizer.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_heading = fivedrs_home( 'categories', 'heading' );
$fivedrs_label   = fivedrs_home( 'categories', 'link_label' );

$fivedrs_cards = array();
for ( $fivedrs_i = 1; $fivedrs_i <= 3; $fivedrs_i++ ) {
	$fivedrs_term_id = (int) fivedrs_home( 'categories', 'card_' . $fivedrs_i . '_cat' );
	$fivedrs_image   = (int) fivedrs_home( 'categories', 'card_' . $fivedrs_i . '_image' );
	$fivedrs_url     = fivedrs_home_category_url( 'categories', 'card_' . $fivedrs_i . '_cat' );

	if ( ! $fivedrs_term_id || ! $fivedrs_image || '' === $fivedrs_url ) {
		continue;
	}

	$fivedrs_term = get_term( $fivedrs_term_id, 'product_cat' );

	if ( ! $fivedrs_term || is_wp_error( $fivedrs_term ) ) {
		continue;
	}

	$fivedrs_cards[] = array(
		'index' => $fivedrs_i,
		'name'  => $fivedrs_term->name,
		'text'  => fivedrs_home( 'categories', 'card_' . $fivedrs_i . '_text' ),
		'url'   => $fivedrs_url,
	);
}

if ( empty( $fivedrs_cards ) ) {
	return;
}
?>
<section class="fivedrs-section fivedrs-section--alt fivedrs-cats" data-reveal>
	<div class="fivedrs-container">

		<?php if ( '' !== $fivedrs_heading ) : ?>
			<div class="fivedrs-section__head fivedrs-section__head--center">
				<h2><?php echo esc_html( $fivedrs_heading ); ?></h2>
			</div>
		<?php endif; ?>

		<div class="fivedrs-grid fivedrs-cats__grid">
			<?php foreach ( $fivedrs_cards as $fivedrs_card ) : ?>
				<a class="fivedrs-cat-card" href="<?php echo esc_url( $fivedrs_card['url'] ); ?>">
					<span class="fivedrs-cat-card__media">
						<?php
						fivedrs_home_image(
							'categories',
							'card_' . $fivedrs_card['index'] . '_image',
							array(
								'size'  => 'medium_large',
								'class' => 'fivedrs-cat-card__image',
								'sizes' => '(min-width: 64rem) 24rem, (min-width: 48rem) 40vw, calc(100vw - 2rem)',
							)
						);
						?>
					</span>

					<span class="fivedrs-cat-card__body">
						<span class="fivedrs-card__title"><?php echo esc_html( $fivedrs_card['name'] ); ?></span>

						<?php if ( '' !== $fivedrs_card['text'] ) : ?>
							<span class="fivedrs-card__meta"><?php echo esc_html( $fivedrs_card['text'] ); ?></span>
						<?php endif; ?>

						<?php if ( '' !== $fivedrs_label ) : ?>
							<span class="fivedrs-card__more">
								<?php echo esc_html( $fivedrs_label ); ?>
								<?php echo wp_kses( fivedrs_icon( 'arrow' ), fivedrs_kses_svg() ); ?>
							</span>
						<?php endif; ?>
					</span>
				</a>
			<?php endforeach; ?>
		</div>

	</div>
</section>
