<?php
/**
 * Contact form.
 *
 * Posts to admin-post.php so it works with JavaScript off; the front-end
 * script intercepts the same form and posts it with fetch. Both paths run the
 * identical handler, so validation and spam checks cannot diverge.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

$fivedrs_heading = fivedrs_content( 'contact', 'form_heading' );
$fivedrs_submit  = fivedrs_content( 'contact', 'submit_label' );

if ( '' === $fivedrs_heading && '' === $fivedrs_submit ) {
	return;
}

// Anything handed back from a failed submission without JavaScript.
$fivedrs_feedback = fivedrs_contact_feedback();
$fivedrs_errors   = $fivedrs_feedback['errors'];
$fivedrs_values   = $fivedrs_feedback['values'];

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only flag; it changes nothing.
$fivedrs_status = isset( $_GET['fivedrs_contact'] ) ? sanitize_key( wp_unslash( $_GET['fivedrs_contact'] ) ) : '';

$fivedrs_notice = '';
$fivedrs_tone   = '';

if ( 'sent' === $fivedrs_status ) {
	$fivedrs_notice = fivedrs_content( 'contact', 'success' );
	$fivedrs_tone   = 'success';
} elseif ( 'error' === $fivedrs_status ) {
	$fivedrs_notice = '' !== $fivedrs_feedback['notice'] ? $fivedrs_feedback['notice'] : fivedrs_content( 'contact', 'error' );
	$fivedrs_tone   = 'error';
}

/**
 * Previously submitted value for a field, if the form is being re-shown.
 *
 * @param string $key Field key.
 * @return string
 */
$fivedrs_value = static function ( $key ) use ( $fivedrs_values ) {
	return isset( $fivedrs_values[ $key ] ) ? (string) $fivedrs_values[ $key ] : '';
};

$fivedrs_stamp = time();
?>
<section class="fivedrs-contact__form-col" data-reveal="start">
	<?php if ( '' !== $fivedrs_heading ) : ?>
		<h2 class="fivedrs-contact__heading"><?php echo esc_html( $fivedrs_heading ); ?></h2>
	<?php endif; ?>

	<div
		class="fivedrs-contact__status<?php echo '' !== $fivedrs_tone ? ' is-' . esc_attr( $fivedrs_tone ) : ''; ?>"
		data-contact-status
		role="status"
		aria-live="polite"
		tabindex="-1"
	><?php echo esc_html( $fivedrs_notice ); ?></div>

	<form
		class="fivedrs-form"
		id="contact-form"
		method="post"
		action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
		data-contact-form
		novalidate
	>
		<input type="hidden" name="action" value="<?php echo esc_attr( FIVEDRS_CONTACT_ACTION ); ?>">
		<?php wp_nonce_field( FIVEDRS_CONTACT_ACTION, 'fivedrs_contact_nonce' ); ?>

		<?php // Signed so the minimum-time check cannot simply be back-dated. ?>
		<input type="hidden" name="fivedrs_ts" value="<?php echo esc_attr( (string) $fivedrs_stamp ); ?>">
		<input type="hidden" name="fivedrs_tsh" value="<?php echo esc_attr( wp_hash( (string) $fivedrs_stamp ) ); ?>">

		<?php // Honeypot: hidden from people, irresistible to bots. ?>
		<div class="fivedrs-form__trap" aria-hidden="true">
			<label for="fivedrs-website"><?php esc_html_e( 'Website', '5drs' ); ?></label>
			<input type="text" id="fivedrs-website" name="fivedrs_website" tabindex="-1" autocomplete="off" value="">
		</div>

		<div class="fivedrs-field">
			<label class="fivedrs-label" for="fivedrs-name">
				<?php esc_html_e( 'Name', '5drs' ); ?>
				<span class="fivedrs-field__required" aria-hidden="true">*</span>
			</label>
			<input
				type="text"
				id="fivedrs-name"
				name="fivedrs_name"
				autocomplete="name"
				required
				value="<?php echo esc_attr( $fivedrs_value( 'name' ) ); ?>"
				<?php echo isset( $fivedrs_errors['name'] ) ? 'aria-invalid="true"' : ''; ?>
				aria-describedby="fivedrs-name-error"
			>
			<p class="fivedrs-field__error" id="fivedrs-name-error" data-error-for="name">
				<?php echo isset( $fivedrs_errors['name'] ) ? esc_html( $fivedrs_errors['name'] ) : ''; ?>
			</p>
		</div>

		<div class="fivedrs-field">
			<label class="fivedrs-label" for="fivedrs-phone">
				<?php esc_html_e( 'Phone Number', '5drs' ); ?>
				<span class="fivedrs-field__required" aria-hidden="true">*</span>
			</label>
			<?php // Always left-to-right: a phone number is not mirrored text. ?>
			<input
				type="tel"
				id="fivedrs-phone"
				name="fivedrs_phone"
				inputmode="tel"
				autocomplete="tel"
				dir="ltr"
				required
				value="<?php echo esc_attr( $fivedrs_value( 'phone' ) ); ?>"
				<?php echo isset( $fivedrs_errors['phone'] ) ? 'aria-invalid="true"' : ''; ?>
				aria-describedby="fivedrs-phone-error"
			>
			<p class="fivedrs-field__error" id="fivedrs-phone-error" data-error-for="phone">
				<?php echo isset( $fivedrs_errors['phone'] ) ? esc_html( $fivedrs_errors['phone'] ) : ''; ?>
			</p>
		</div>

		<div class="fivedrs-field">
			<label class="fivedrs-label" for="fivedrs-email">
				<?php esc_html_e( 'Email Address', '5drs' ); ?>
			</label>
			<input
				type="email"
				id="fivedrs-email"
				name="fivedrs_email"
				inputmode="email"
				autocomplete="email"
				dir="ltr"
				value="<?php echo esc_attr( $fivedrs_value( 'email' ) ); ?>"
				<?php echo isset( $fivedrs_errors['email'] ) ? 'aria-invalid="true"' : ''; ?>
				aria-describedby="fivedrs-email-error"
			>
			<p class="fivedrs-field__error" id="fivedrs-email-error" data-error-for="email">
				<?php echo isset( $fivedrs_errors['email'] ) ? esc_html( $fivedrs_errors['email'] ) : ''; ?>
			</p>
		</div>

		<div class="fivedrs-field">
			<label class="fivedrs-label" for="fivedrs-message">
				<?php esc_html_e( 'Message', '5drs' ); ?>
				<span class="fivedrs-field__required" aria-hidden="true">*</span>
			</label>
			<textarea
				id="fivedrs-message"
				name="fivedrs_message"
				rows="6"
				maxlength="2000"
				required
				<?php echo isset( $fivedrs_errors['message'] ) ? 'aria-invalid="true"' : ''; ?>
				aria-describedby="fivedrs-message-error"
			><?php echo esc_textarea( $fivedrs_value( 'message' ) ); ?></textarea>
			<p class="fivedrs-field__error" id="fivedrs-message-error" data-error-for="message">
				<?php echo isset( $fivedrs_errors['message'] ) ? esc_html( $fivedrs_errors['message'] ) : ''; ?>
			</p>
		</div>

		<?php if ( '' !== $fivedrs_submit ) : ?>
			<button type="submit" class="fivedrs-btn fivedrs-btn--lg fivedrs-form__submit">
				<?php echo esc_html( $fivedrs_submit ); ?>
			</button>
		<?php endif; ?>
	</form>
</section>
