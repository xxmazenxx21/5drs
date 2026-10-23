<?php
/**
 * Site header.
 *
 * Bare markup only - the header is designed in a later task.
 * language_attributes() emits both lang and dir, so Arabic pages get
 * dir="rtl" automatically.
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

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', '5drs' ); ?></a>

<header id="masthead" class="site-header">
	<p class="site-branding">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
	</p>

	<nav id="site-navigation" class="site-navigation" aria-label="<?php esc_attr_e( 'Primary', '5drs' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'fallback_cb'    => false,
			)
		);
		?>
	</nav>

	<?php
	/**
	 * Language switcher slot.
	 *
	 * Returns the TranslatePress switcher via fivedrs_language_switcher();
	 * printed unstyled for now and designed together with the header.
	 */
	echo wp_kses_post( fivedrs_language_switcher() );
	?>
</header>

<div id="content" class="site-content">
