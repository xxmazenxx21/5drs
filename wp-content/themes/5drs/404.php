<?php
/**
 * 404 template.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="site-main">
	<h1 class="page-title"><?php esc_html_e( 'Page not found', '5drs' ); ?></h1>

	<p><?php esc_html_e( 'The page you are looking for does not exist or has moved.', '5drs' ); ?></p>

	<?php get_search_form(); ?>

	<p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Back to home', '5drs' ); ?>
		</a>
	</p>
</main>

<?php
get_footer();
