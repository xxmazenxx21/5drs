<?php
/**
 * Category cards.
 *
 * Shared by the Home page and the About page. The cards themselves always come
 * from the "categories" content section, so both pages show the same four from
 * one source; only the heading and subtitle above them are overridable.
 *
 * Each card needs a category, an image and some text. A card missing any of
 * those is skipped - which is how the Black Edition card stays hidden until a
 * clean image is supplied in the Customizer.
 *
 * @param array $args Optional. Keys: 'heading' and 'subtitle' to override the
 *                    text above the cards, and 'variant' for the section
 *                    background ('alt' by default).
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_cards_args = wp_parse_args(
	isset( $args ) ? $args : array(),
	array(
		'heading'  => fivedrs_content( 'categories', 'heading' ),
		'subtitle' => '',
		'variant'  => 'alt',
	)
);

$fivedrs_heading  = $fivedrs_cards_args['heading'];
$fivedrs_subtitle = $fivedrs_cards_args['subtitle'];
$fivedrs_label    = fivedrs_content( 'categories', 'link_label' );

$fivedrs_cards = array();
for ( $fivedrs_i = 1; $fivedrs_i <= 4; $fivedrs_i++ ) {
	$fivedrs_term_id = (int) fivedrs_content( 'categories', 'card_' . $fivedrs_i . '_cat' );
	$fivedrs_image   = (int) fivedrs_content( 'categories', 'card_' . $fivedrs_i . '_image' );
	$fivedrs_url     = fivedrs_content_category_url( 'categories', 'card_' . $fivedrs_i . '_cat' );

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
		'text'  => fivedrs_content( 'categories', 'card_' . $fivedrs_i . '_text' ),
		'url'   => $fivedrs_url,
	);
}

if ( empty( $fivedrs_cards ) ) {
	return;
}
?>
<section class="fivedrs-section fivedrs-cats<?php echo '' !== $fivedrs_cards_args['variant'] ? ' fivedrs-section--' . esc_attr( $fivedrs_cards_args['variant'] ) : ''; ?>" data-reveal>
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

		<div class="fivedrs-grid fivedrs-cats__grid">
			<?php foreach ( $fivedrs_cards as $fivedrs_card ) : ?>
				<a class="fivedrs-cat-card" href="<?php echo esc_url( $fivedrs_card['url'] ); ?>">
					<span class="fivedrs-cat-card__media">
						<?php
						fivedrs_content_image(
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
