<?php
/**
 * Site footer.
 *
 * Contact details and social links come from the Customizer
 * (Appearance > Customize > Contact & Social) and hide themselves when empty,
 * so the client can fill them in later without touching code.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;
?>
</div><!-- #content -->

<footer id="colophon" class="fivedrs-footer">
	<div class="fivedrs-container">

		<div class="fivedrs-footer__top">

			<div class="fivedrs-footer__brand-col">
				<?php fivedrs_logo( array( 'class' => 'fivedrs-footer__brand' ) ); ?>
				<p class="fivedrs-footer__tagline"><?php echo esc_html( fivedrs_tagline() ); ?></p>
				<?php fivedrs_social_links(); ?>
			</div>

			<?php if ( has_nav_menu( 'footer-quick' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Quick links', '5drs' ); ?>">
					<h2 class="fivedrs-footer__title"><?php esc_html_e( 'Quick Links', '5drs' ); ?></h2>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-quick',
							'container'      => false,
							'menu_class'     => 'menu',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'footer-policies' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Policies', '5drs' ); ?>">
					<h2 class="fivedrs-footer__title"><?php esc_html_e( 'Policies', '5drs' ); ?></h2>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-policies',
							'container'      => false,
							'menu_class'     => 'menu',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<div class="fivedrs-footer__contact-col">
				<h2 class="fivedrs-footer__title"><?php esc_html_e( 'Contact Us', '5drs' ); ?></h2>
				<?php fivedrs_contact_list(); ?>
			</div>

		</div>

		<div class="fivedrs-footer__bottom">
			<p>
				<?php
				printf(
					/* translators: 1: current year, 2: site name. */
					esc_html__( '&copy; %1$s %2$s. All rights reserved.', '5drs' ),
					esc_html( wp_date( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</p>
		</div>

	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
