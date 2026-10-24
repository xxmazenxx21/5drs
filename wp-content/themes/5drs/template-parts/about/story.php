<?php
/**
 * About: our story.
 *
 * Two columns - text on the start side, slideshow on the end - stacking with
 * the text first on narrow screens. Carries the #story anchor the hero button
 * scrolls to.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_heading = fivedrs_content( 'about_story', 'heading' );
$fivedrs_text_1  = fivedrs_content( 'about_story', 'text_1' );
$fivedrs_text_2  = fivedrs_content( 'about_story', 'text_2' );
$fivedrs_eyebrow = fivedrs_content( 'about_story', 'eyebrow' );

if ( '' === $fivedrs_heading && '' === $fivedrs_text_1 && '' === $fivedrs_text_2 ) {
	return;
}
?>
<section class="fivedrs-section fivedrs-story" id="story">
	<div class="fivedrs-container fivedrs-story__inner">

		<div class="fivedrs-story__text" data-reveal="start">
			<?php if ( '' !== $fivedrs_eyebrow ) : ?>
				<span class="fivedrs-section__eyebrow"><?php echo esc_html( $fivedrs_eyebrow ); ?></span>
			<?php endif; ?>

			<?php if ( '' !== $fivedrs_heading ) : ?>
				<h2><?php echo esc_html( $fivedrs_heading ); ?></h2>
			<?php endif; ?>

			<?php if ( '' !== $fivedrs_text_1 ) : ?>
				<p><?php echo esc_html( $fivedrs_text_1 ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $fivedrs_text_2 ) : ?>
				<p><?php echo esc_html( $fivedrs_text_2 ); ?></p>
			<?php endif; ?>
		</div>

		<div class="fivedrs-story__media" data-reveal="end">
			<?php get_template_part( 'template-parts/about/slideshow' ); ?>
		</div>

	</div>
</section>
