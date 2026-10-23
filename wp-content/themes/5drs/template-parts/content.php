<?php
/**
 * Default content part used in loops.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'fivedrs-card' ); ?>>
	<h2 class="fivedrs-card__title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h2>

	<p class="fivedrs-card__meta">
		<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
			<?php echo esc_html( get_the_date() ); ?>
		</time>
	</p>

	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div>
</article>
