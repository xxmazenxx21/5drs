<?php
/**
 * About: our promise.
 *
 * The statement reveals a word at a time. fivedrs_the_words() splits on
 * whitespace only, so Arabic words stay whole and their letters stay joined.
 *
 * The statement is translated BEFORE it is split, and the heading carries
 * data-no-translation: once the text is a run of one-word spans,
 * TranslatePress would otherwise try to translate "Our", "Goal" and "Is"
 * individually and find nothing.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_statement = fivedrs_translate( fivedrs_content( 'about_promise', 'statement' ) );
$fivedrs_text      = fivedrs_content( 'about_promise', 'text' );

if ( '' === $fivedrs_statement && '' === $fivedrs_text ) {
	return;
}
?>
<section class="fivedrs-section fivedrs-promise" data-reveal>
	<div class="fivedrs-container fivedrs-container--prose fivedrs-promise__inner">

		<?php if ( '' !== $fivedrs_statement ) : ?>
			<h2 class="fivedrs-promise__statement" data-no-translation><?php fivedrs_the_words( $fivedrs_statement ); ?></h2>
		<?php endif; ?>

		<span class="fivedrs-promise__line" aria-hidden="true"></span>

		<?php if ( '' !== $fivedrs_text ) : ?>
			<p class="fivedrs-promise__text"><?php echo esc_html( $fivedrs_text ); ?></p>
		<?php endif; ?>

	</div>
</section>
