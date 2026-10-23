<?php
/**
 * Plugin Name:  5drs Local Mail
 * Description:  Routes all outgoing WordPress mail to Mailpit. Runs ONLY when
 *               WP_ENVIRONMENT_TYPE is "local", so it is inert in production.
 * Author:       5drs
 * Version:      1.0.0
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hard guard: anything below this line must never execute outside local dev.
 * wp_get_environment_type() reads WP_ENVIRONMENT_TYPE, which docker-compose.yml
 * defines as "local". On Hostinger the constant is absent and defaults to
 * "production", so this file short-circuits here.
 */
if ( 'local' !== wp_get_environment_type() ) {
	return;
}

defined( 'FIVEDRS_MAIL_HOST' ) || define( 'FIVEDRS_MAIL_HOST', 'mailpit' );
defined( 'FIVEDRS_MAIL_PORT' ) || define( 'FIVEDRS_MAIL_PORT', 1025 );
defined( 'FIVEDRS_MAIL_FROM' ) || define( 'FIVEDRS_MAIL_FROM', 'no-reply@5drs.local' );
defined( 'FIVEDRS_MAIL_FROM_NAME' ) || define( 'FIVEDRS_MAIL_FROM_NAME', '5drs (local)' );

/**
 * Point PHPMailer at the Mailpit SMTP service.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Mailer instance, by reference.
 * @return void
 */
function fivedrs_local_mail_phpmailer( $phpmailer ) {
	$phpmailer->isSMTP();
	$phpmailer->Host       = FIVEDRS_MAIL_HOST;
	$phpmailer->Port       = FIVEDRS_MAIL_PORT;
	$phpmailer->SMTPAuth   = false;
	$phpmailer->SMTPSecure = '';
	$phpmailer->SMTPAutoTLS = false;
}
add_action( 'phpmailer_init', 'fivedrs_local_mail_phpmailer' );

/**
 * Use a consistent local sender so Mailpit groups messages predictably.
 *
 * @return string
 */
function fivedrs_local_mail_from() {
	return FIVEDRS_MAIL_FROM;
}
add_filter( 'wp_mail_from', 'fivedrs_local_mail_from', 99 );

/**
 * Sender name for local mail.
 *
 * @return string
 */
function fivedrs_local_mail_from_name() {
	return FIVEDRS_MAIL_FROM_NAME;
}
add_filter( 'wp_mail_from_name', 'fivedrs_local_mail_from_name', 99 );
