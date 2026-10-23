<?php
/**
 * Site header.
 *
 * Both lang and dir come from language_attributes(), so Arabic pages get
 * dir="rtl" automatically and the whole header mirrors through logical CSS.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', '5drs' ); ?></a>

<?php // Scrolls out of view, which is how the header knows to raise its shadow. ?>
<div class="fivedrs-header-sentinel" data-header-sentinel aria-hidden="true"></div>

<header id="masthead" class="fivedrs-header" data-header>
	<div class="fivedrs-container fivedrs-header__inner">

		<?php fivedrs_logo(); ?>

		<nav class="fivedrs-nav" aria-label="<?php esc_attr_e( 'Primary', '5drs' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'depth'          => 2,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>

		<div class="fivedrs-header__actions">
			<?php
			echo wp_kses_post( fivedrs_language_switcher() );
			fivedrs_cart_button();
			?>

			<button
				type="button"
				class="fivedrs-burger"
				data-offcanvas-open
				aria-controls="fivedrs-offcanvas"
				aria-expanded="false"
			>
				<?php echo wp_kses( fivedrs_icon( 'menu' ), fivedrs_kses_svg() ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Open menu', '5drs' ); ?></span>
			</button>
		</div>

	</div>
</header>

<?php get_template_part( 'template-parts/nav/offcanvas' ); ?>

<div id="content" class="site-content">
