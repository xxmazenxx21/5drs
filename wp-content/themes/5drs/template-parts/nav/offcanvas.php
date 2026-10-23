<?php
/**
 * Mobile off-canvas menu.
 *
 * Behaviour (focus trap, Esc, scroll lock, inert) lives in
 * src/js/modules/offcanvas.js. The panel slides in from the start edge, which
 * CSS flips for Arabic.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="fivedrs-offcanvas__backdrop" data-offcanvas-backdrop></div>

<div
	id="fivedrs-offcanvas"
	class="fivedrs-offcanvas"
	data-offcanvas
	role="dialog"
	aria-modal="true"
	aria-label="<?php esc_attr_e( 'Site menu', '5drs' ); ?>"
	tabindex="-1"
>
	<div class="fivedrs-offcanvas__head">
		<?php fivedrs_logo( array( 'class' => 'fivedrs-offcanvas__brand' ) ); ?>

		<button type="button" class="fivedrs-offcanvas__close" data-offcanvas-close>
			<?php echo wp_kses( fivedrs_icon( 'close' ), fivedrs_kses_svg() ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Close menu', '5drs' ); ?></span>
		</button>
	</div>

	<div class="fivedrs-offcanvas__body">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'menu',
				'depth'          => 2,
				'fallback_cb'    => false,
			)
		);
		?>
	</div>

	<div class="fivedrs-offcanvas__foot">
		<?php echo wp_kses_post( fivedrs_language_switcher() ); ?>
	</div>
</div>
