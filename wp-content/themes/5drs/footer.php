<?php
/**
 * Site footer.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;
?>
</div><!-- #content -->

<footer id="colophon" class="site-footer">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'footer',
			'container'      => false,
			'fallback_cb'    => false,
		)
	);
	?>

	<p class="site-copyright">
		<?php
		printf(
			/* translators: 1: current year, 2: site name. */
			esc_html__( '&copy; %1$s %2$s. All rights reserved.', '5drs' ),
			esc_html( wp_date( 'Y' ) ),
			esc_html( get_bloginfo( 'name' ) )
		);
		?>
	</p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
