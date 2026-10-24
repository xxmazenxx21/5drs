<?php
/**
 * Contact form: storage, validation and submission handling.
 *
 * The form posts to admin-post.php so it works with JavaScript switched off
 * (validate, then Post/Redirect/Get back to the page). The same handler answers
 * with JSON when the front-end script asks, so the enhanced path uses exactly
 * the same validation and the same spam checks - there is no second code path
 * that could drift.
 *
 * Every accepted message is stored as a private post BEFORE the email is
 * attempted, so a mail failure can never lose a customer's message.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

const FIVEDRS_CONTACT_ACTION   = 'fivedrs_contact';
const FIVEDRS_CONTACT_CPT      = 'fivedrs_message';
const FIVEDRS_CONTACT_MIN_TIME = 3;    // Seconds a human needs, at minimum.
const FIVEDRS_CONTACT_MAX_HITS = 5;    // Submissions per IP...
const FIVEDRS_CONTACT_WINDOW   = 600;  // ...per this many seconds.

/**
 * Register the private post type that stores submitted messages.
 *
 * Deliberately not public, not queryable and not in REST: these are customer
 * messages, not content. The admin screen is read-only - there is no reason to
 * hand-write a "received" message.
 *
 * @return void
 */
function fivedrs_register_message_cpt() {
	register_post_type(
		FIVEDRS_CONTACT_CPT,
		array(
			'labels'              => array(
				'name'          => __( 'Messages', '5drs' ),
				'singular_name' => __( 'Message', '5drs' ),
				'menu_name'     => __( 'Messages', '5drs' ),
				'all_items'     => __( 'All Messages', '5drs' ),
				'search_items'  => __( 'Search Messages', '5drs' ),
				'not_found'     => __( 'No messages yet.', '5drs' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-email-alt',
			'menu_position'       => 26,
			'supports'            => array( 'title', 'editor' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
		)
	);
}
add_action( 'init', 'fivedrs_register_message_cpt' );

/**
 * Columns for the Messages list screen.
 *
 * @param array $columns Default columns.
 * @return array
 */
function fivedrs_message_columns( $columns ) {
	return array(
		'cb'              => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'           => __( 'Name', '5drs' ),
		'fivedrs_phone'   => __( 'Phone', '5drs' ),
		'fivedrs_email'   => __( 'Email', '5drs' ),
		'fivedrs_message' => __( 'Message', '5drs' ),
		'date'            => __( 'Received', '5drs' ),
	);
}
add_filter( 'manage_' . FIVEDRS_CONTACT_CPT . '_posts_columns', 'fivedrs_message_columns' );

/**
 * Render the custom Messages columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Message ID.
 * @return void
 */
function fivedrs_message_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'fivedrs_phone':
			$phone = (string) get_post_meta( $post_id, '_fivedrs_phone', true );
			if ( '' !== $phone ) {
				printf( '<bdi dir="ltr">%s</bdi>', esc_html( $phone ) );
			}
			break;

		case 'fivedrs_email':
			$email = (string) get_post_meta( $post_id, '_fivedrs_email', true );
			if ( '' !== $email ) {
				printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $email ) );
			} else {
				echo '&mdash;';
			}
			break;

		case 'fivedrs_message':
			echo esc_html( wp_trim_words( get_post_field( 'post_content', $post_id ), 18 ) );
			break;
	}
}
add_action( 'manage_' . FIVEDRS_CONTACT_CPT . '_posts_custom_column', 'fivedrs_message_column_content', 10, 2 );

/**
 * The strings the form and its script need.
 *
 * Kept in one place so the markup, the PHP validation and the JavaScript all
 * show the same wording, and so the script never carries English of its own.
 *
 * @return array<string,string>
 */
function fivedrs_contact_strings() {
	return array(
		'name'    => __( 'Please enter your name.', '5drs' ),
		'phone'   => __( 'Please enter a valid phone number.', '5drs' ),
		'email'   => __( 'Please enter a valid email address.', '5drs' ),
		'message' => __( 'Please write your message.', '5drs' ),

		/*
		 * These two travel to the browser inside a script object, so
		 * TranslatePress never sees them as text - they have to be translated
		 * here, or the Arabic page would announce success in English.
		 */
		'success' => fivedrs_translate( fivedrs_content( 'contact', 'success' ) ),
		'error'   => fivedrs_translate( fivedrs_content( 'contact', 'error' ) ),
		'sending' => __( 'Sending...', '5drs' ),
	);
}

/**
 * Validate and clean a raw submission.
 *
 * Pure on purpose: it sends nothing, stores nothing and reads no request
 * state, so the rules can be exercised directly without posting the form.
 *
 * @param array $raw Raw field values keyed name, phone, email, message.
 * @return array{errors:array<string,string>,clean:array<string,string>}
 */
function fivedrs_contact_validate( $raw ) {
	$strings = fivedrs_contact_strings();
	$errors  = array();

	$clean = array(
		'name'    => sanitize_text_field( isset( $raw['name'] ) ? $raw['name'] : '' ),
		'phone'   => sanitize_text_field( isset( $raw['phone'] ) ? $raw['phone'] : '' ),
		'email'   => sanitize_email( isset( $raw['email'] ) ? $raw['email'] : '' ),
		'message' => sanitize_textarea_field( isset( $raw['message'] ) ? $raw['message'] : '' ),
	);

	if ( '' === $clean['name'] || mb_strlen( $clean['name'] ) < 2 ) {
		$errors['name'] = $strings['name'];
	}

	/*
	 * Lenient on formatting - people write numbers all sorts of ways - but the
	 * digit count has to look like a real phone number.
	 */
	$digits = preg_replace( '/\D/', '', $clean['phone'] );

	if ( '' === $clean['phone']
		|| preg_match( '/[^0-9 +\-()]/', $clean['phone'] )
		|| strlen( $digits ) < 8
		|| strlen( $digits ) > 15
	) {
		$errors['phone'] = $strings['phone'];
	}

	// Optional, but it has to be valid if they bothered to type one.
	if ( '' !== (string) ( isset( $raw['email'] ) ? trim( $raw['email'] ) : '' ) && ! is_email( $clean['email'] ) ) {
		$errors['email'] = $strings['email'];
	}

	if ( '' === $clean['message'] || mb_strlen( $clean['message'] ) < 2 ) {
		$errors['message'] = $strings['message'];
	}

	if ( mb_strlen( $clean['message'] ) > 2000 ) {
		$clean['message'] = mb_substr( $clean['message'], 0, 2000 );
	}

	return array(
		'errors' => $errors,
		'clean'  => $clean,
	);
}

/**
 * A key identifying this visitor well enough for a short-lived transient.
 *
 * Used for the rate limit and for handing validation errors back across the
 * redirect, which is why the submitted values never travel in the URL.
 *
 * @return string
 */
function fivedrs_contact_visitor_key() {
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

	return md5( $ip . '|' . $agent );
}

/**
 * Whether this visitor has submitted too often.
 *
 * @return bool
 */
function fivedrs_contact_rate_limited() {
	$key  = 'fivedrs_contact_rl_' . fivedrs_contact_visitor_key();
	$hits = (int) get_transient( $key );
	++$hits;

	set_transient( $key, $hits, FIVEDRS_CONTACT_WINDOW );

	return $hits > FIVEDRS_CONTACT_MAX_HITS;
}

/**
 * Store a submitted message.
 *
 * Runs before the email so a delivery failure cannot lose the message.
 *
 * @param array $clean Validated field values.
 * @return int Post ID, or 0 on failure.
 */
function fivedrs_contact_store( $clean ) {
	$post_id = wp_insert_post(
		array(
			'post_type'    => FIVEDRS_CONTACT_CPT,
			'post_status'  => 'private',
			'post_title'   => $clean['name'],
			'post_content' => $clean['message'],
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return 0;
	}

	update_post_meta( $post_id, '_fivedrs_phone', $clean['phone'] );
	update_post_meta( $post_id, '_fivedrs_email', $clean['email'] );

	return (int) $post_id;
}

/**
 * Email a submitted message to the configured recipient.
 *
 * @param array $clean Validated field values.
 * @return bool Whether wp_mail() accepted it.
 */
function fivedrs_contact_notify( $clean ) {
	$to = fivedrs_option( 'form_email' );

	if ( '' === $to || ! is_email( $to ) ) {
		return false;
	}

	$lines = array(
		sprintf( '%s: %s', __( 'Name', '5drs' ), $clean['name'] ),
		sprintf( '%s: %s', __( 'Phone Number', '5drs' ), $clean['phone'] ),
		sprintf( '%s: %s', __( 'Email Address', '5drs' ), '' !== $clean['email'] ? $clean['email'] : '-' ),
		'',
		sprintf( '%s:', __( 'Message', '5drs' ) ),
		$clean['message'],
	);

	$headers = array();

	// Replying to the notification should reach the customer.
	if ( '' !== $clean['email'] ) {
		$headers[] = sprintf( 'Reply-To: %s <%s>', $clean['name'], $clean['email'] );
	}

	return wp_mail(
		$to,
		__( 'New contact message — 5drs', '5drs' ),
		implode( "\n", $lines ),
		$headers
	);
}

/**
 * Handle a contact form submission.
 *
 * @return void
 */
function fivedrs_contact_submit() {
	$is_ajax  = isset( $_POST['fivedrs_ajax'] ) && '1' === $_POST['fivedrs_ajax']; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- the nonce is checked immediately below.
	$strings  = fivedrs_contact_strings();
	$redirect = wp_get_referer();

	if ( ! $redirect ) {
		$contact  = get_page_by_path( 'contact' );
		$redirect = $contact ? get_permalink( $contact ) : home_url( '/' );
	}

	/**
	 * Finish the request the way the caller expects.
	 *
	 * @param bool   $ok      Whether the submission succeeded.
	 * @param string $message Status message.
	 * @param array  $errors  Field errors.
	 * @param array  $values  Submitted values, echoed back on failure.
	 */
	$respond = static function ( $ok, $message, $errors = array(), $values = array() ) use ( $is_ajax, $redirect ) {
		if ( $is_ajax ) {
			$payload = array(
				'message' => $message,
				'errors'  => $errors,
			);

			if ( $ok ) {
				wp_send_json_success( $payload );
			}

			wp_send_json_error( $payload );
		}

		if ( ! $ok ) {
			// Values ride in a transient, never in the URL.
			set_transient(
				'fivedrs_contact_err_' . fivedrs_contact_visitor_key(),
				array(
					'errors' => $errors,
					'values' => $values,
					'notice' => $message,
				),
				MINUTE_IN_SECONDS
			);
		}

		wp_safe_redirect(
			add_query_arg( 'fivedrs_contact', $ok ? 'sent' : 'error', $redirect ) . '#contact-form'
		);
		exit;
	};

	if ( ! isset( $_POST['fivedrs_contact_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fivedrs_contact_nonce'] ) ), FIVEDRS_CONTACT_ACTION )
	) {
		$respond( false, $strings['error'] );
	}

	// Honeypot: a real visitor never sees this field, so anything in it is a bot.
	$honeypot = isset( $_POST['fivedrs_website'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['fivedrs_website'] ) ) ) : '';

	// Signed timestamp: forms filled in implausibly fast are not being typed.
	$stamp = isset( $_POST['fivedrs_ts'] ) ? (int) $_POST['fivedrs_ts'] : 0;
	$sig   = isset( $_POST['fivedrs_tsh'] ) ? sanitize_text_field( wp_unslash( $_POST['fivedrs_tsh'] ) ) : '';
	$fresh = $stamp > 0 && hash_equals( wp_hash( (string) $stamp ), $sig );

	if ( '' !== $honeypot || ! $fresh || ( time() - $stamp ) < FIVEDRS_CONTACT_MIN_TIME ) {
		// Say as little as possible to a bot; a person retrying will succeed.
		$respond( false, $strings['error'] );
	}

	if ( fivedrs_contact_rate_limited() ) {
		$respond( false, $strings['error'] );
	}

	/*
	 * Handed over raw on purpose: fivedrs_contact_validate() is the single
	 * place every field is sanitised, so the two request paths cannot end up
	 * cleaning input differently.
	 */
	// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verified above; every field is sanitised inside fivedrs_contact_validate().
	$result = fivedrs_contact_validate(
		array(
			'name'    => isset( $_POST['fivedrs_name'] ) ? wp_unslash( $_POST['fivedrs_name'] ) : '',
			'phone'   => isset( $_POST['fivedrs_phone'] ) ? wp_unslash( $_POST['fivedrs_phone'] ) : '',
			'email'   => isset( $_POST['fivedrs_email'] ) ? wp_unslash( $_POST['fivedrs_email'] ) : '',
			'message' => isset( $_POST['fivedrs_message'] ) ? wp_unslash( $_POST['fivedrs_message'] ) : '',
		)
	);
	// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	if ( ! empty( $result['errors'] ) ) {
		$respond( false, $strings['error'], $result['errors'], $result['clean'] );
	}

	// Store first, notify second.
	$stored = fivedrs_contact_store( $result['clean'] );
	fivedrs_contact_notify( $result['clean'] );

	if ( ! $stored ) {
		$respond( false, $strings['error'], array(), $result['clean'] );
	}

	$respond( true, $strings['success'] );
}
add_action( 'admin_post_' . FIVEDRS_CONTACT_ACTION, 'fivedrs_contact_submit' );
add_action( 'admin_post_nopriv_' . FIVEDRS_CONTACT_ACTION, 'fivedrs_contact_submit' );

/**
 * Anything handed back from a failed non-JavaScript submission.
 *
 * Read once and discarded, so a refresh does not resurrect an old error.
 *
 * @return array{errors:array,values:array,notice:string}
 */
function fivedrs_contact_feedback() {
	$empty = array(
		'errors' => array(),
		'values' => array(),
		'notice' => '',
	);

	$key   = 'fivedrs_contact_err_' . fivedrs_contact_visitor_key();
	$saved = get_transient( $key );

	if ( ! is_array( $saved ) ) {
		return $empty;
	}

	delete_transient( $key );

	return wp_parse_args( $saved, $empty );
}

/**
 * Give the contact script its endpoint and its wording.
 *
 * The script carries no English of its own, so /ar/contact/ shows Arabic.
 *
 * @return void
 */
function fivedrs_localize_contact_script() {
	if ( ! wp_script_is( 'fivedrs-main', 'enqueued' ) ) {
		return;
	}

	wp_localize_script(
		'fivedrs-main',
		'fivedrsContact',
		array(
			'endpoint' => admin_url( 'admin-post.php' ),
			'action'   => FIVEDRS_CONTACT_ACTION,
			'i18n'     => fivedrs_contact_strings(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'fivedrs_localize_contact_script', 20 );
