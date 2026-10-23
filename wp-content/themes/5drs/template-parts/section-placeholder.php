<?php
/**
 * An empty, named section container.
 *
 * The site is a structural shell: these mark where content will go and roughly
 * how tall it will be, without inventing copy or imagery. The dashed outline is
 * visible to signed-in editors only (see fivedrs_placeholder_body_class()), so
 * visitors see nothing at all.
 *
 * Replace a call to this part with the real section markup when that section is
 * built, and delete the HTML comment with it.
 *
 * @param array $args {
 *     @type string $label   Section name, shown to editors. Required.
 *     @type string $id      Optional id attribute.
 *     @type string $variant '', 'alt' or 'surface' background.
 *     @type string $height  CSS min-height for the placeholder box.
 * }
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_section = wp_parse_args(
	isset( $args ) ? $args : array(),
	array(
		'label'   => '',
		'id'      => '',
		'variant' => '',
		'height'  => '10rem',
	)
);

$fivedrs_classes = 'fivedrs-section';
if ( '' !== $fivedrs_section['variant'] ) {
	$fivedrs_classes .= ' fivedrs-section--' . sanitize_html_class( $fivedrs_section['variant'] );
}
?>
<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal HTML comment plus an escaped label; marks the section for developers. ?>
<?php echo "\n<!-- Section: " . esc_html( $fivedrs_section['label'] ) . " -->\n"; ?>
<section
	class="<?php echo esc_attr( $fivedrs_classes ); ?>"
	<?php echo '' !== $fivedrs_section['id'] ? 'id="' . esc_attr( $fivedrs_section['id'] ) . '"' : ''; ?>
>
	<div class="fivedrs-container">
		<div class="fivedrs-placeholder" style="min-height:<?php echo esc_attr( $fivedrs_section['height'] ); ?>">
			<?php
			printf(
				/* translators: %s: name of the page section. */
				esc_html__( 'Section placeholder: %s', '5drs' ),
				esc_html( $fivedrs_section['label'] )
			);
			?>
		</div>
	</div>
</section>
