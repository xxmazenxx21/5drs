<?php
/**
 * Home: trust bar.
 *
 * Four short reassurances. Any item left empty simply drops out, and the whole
 * strip disappears when all four are empty.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_items = array(
	array(
		'icon' => 'shield-check',
		'text' => fivedrs_content( 'trust', 'item_1' ),
	),
	array(
		'icon' => 'leaf',
		'text' => fivedrs_content( 'trust', 'item_2' ),
	),
	array(
		'icon' => 'family',
		'text' => fivedrs_content( 'trust', 'item_3' ),
	),
	array(
		'icon' => 'lock',
		'text' => fivedrs_content( 'trust', 'item_4' ),
	),
);

$fivedrs_items = array_filter(
	$fivedrs_items,
	static function ( $item ) {
		return '' !== $item['text'];
	}
);

if ( empty( $fivedrs_items ) ) {
	return;
}
?>
<section class="fivedrs-section fivedrs-section--tight fivedrs-trust" data-reveal>
	<div class="fivedrs-container">
		<ul class="fivedrs-trust__list">
			<?php foreach ( $fivedrs_items as $fivedrs_item ) : ?>
				<li class="fivedrs-trust__item">
					<span class="fivedrs-trust__icon">
						<?php echo wp_kses( fivedrs_icon( $fivedrs_item['icon'] ), fivedrs_kses_svg() ); ?>
					</span>
					<span class="fivedrs-trust__label"><?php echo esc_html( $fivedrs_item['text'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
