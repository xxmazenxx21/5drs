<?php
/**
 * Customizer settings.
 *
 * Contact details and social profiles live here rather than in templates, so
 * the client can fill them in from Appearance > Customize without touching
 * code. Every field is optional - the footer hides whatever is empty.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

/**
 * The editable contact and social fields.
 *
 * One definition drives the Customizer controls, the sanitisers and the footer
 * output, so the three cannot drift apart.
 *
 * @return array<string,array{label:string,type:string,sanitize:string,description?:string}>
 */
function fivedrs_option_fields() {
	return array(
		'phone'      => array(
			'label'       => __( 'Phone number', '5drs' ),
			'type'        => 'text',
			'sanitize'    => 'sanitize_text_field',
			'default'     => '+20 111 156 1011',
			'description' => __( 'Shown in the footer and on the contact page, and used for the call link.', '5drs' ),
		),
		'email'      => array(
			'label'    => __( 'Email address', '5drs' ),
			'type'     => 'email',
			'sanitize' => 'sanitize_email',
			'default'  => 'mbakr.gop@gmail.com',
		),
		'address'    => array(
			'label'       => __( 'Address', '5drs' ),
			'type'        => 'textarea',
			'sanitize'    => 'sanitize_textarea_field',
			'default'     => 'Fifth Settlement, Al Narges Villas, District 2, Villa 28, Egypt',
			// Kept to a single line on purpose: a line break would split this
			// into several text nodes, each needing its own translation entry.
			'description' => __( 'One line, so it stays a single translatable string.', '5drs' ),
		),
		'form_email' => array(
			'label'       => __( 'Contact form recipient', '5drs' ),
			'type'        => 'email',
			'sanitize'    => 'sanitize_email',
			'default'     => 'mbakr.gop@gmail.com',
			'description' => __( 'Where messages from the contact form are sent.', '5drs' ),
		),
		'whatsapp'   => array(
			'label'       => __( 'WhatsApp number', '5drs' ),
			'type'        => 'text',
			'sanitize'    => 'sanitize_text_field',
			'description' => __( 'International format without spaces, e.g. 201234567890.', '5drs' ),
		),
		'facebook'   => array(
			'label'    => __( 'Facebook URL', '5drs' ),
			'type'     => 'url',
			'sanitize' => 'esc_url_raw',
		),
		'instagram'  => array(
			'label'    => __( 'Instagram URL', '5drs' ),
			'type'     => 'url',
			'sanitize' => 'esc_url_raw',
		),
		'tiktok'     => array(
			'label'    => __( 'TikTok URL', '5drs' ),
			'type'     => 'url',
			'sanitize' => 'esc_url_raw',
		),
	);
}

/**
 * Read one theme option.
 *
 * @param string $key     Field key, as returned by fivedrs_option_fields().
 * @param string $default_value Value to fall back to.
 * @return string
 */
function fivedrs_option( $key, $default_value = null ) {
	if ( null === $default_value ) {
		$fields        = fivedrs_option_fields();
		$default_value = isset( $fields[ $key ]['default'] ) ? $fields[ $key ]['default'] : '';
	}

	return (string) get_theme_mod( 'fivedrs_' . $key, $default_value );
}

/**
 * Register the Customizer section and its controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 * @return void
 */
function fivedrs_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'fivedrs_contact',
		array(
			'title'       => __( 'Contact & Social', '5drs' ),
			'description' => __( 'Used in the site footer. Leave a field empty to hide it.', '5drs' ),
			'priority'    => 40,
		)
	);

	foreach ( fivedrs_option_fields() as $key => $field ) {
		$wp_customize->add_setting(
			'fivedrs_' . $key,
			array(
				'default'           => isset( $field['default'] ) ? $field['default'] : '',
				'transport'         => 'refresh',
				'sanitize_callback' => $field['sanitize'],
			)
		);

		$wp_customize->add_control(
			'fivedrs_' . $key,
			array(
				'label'       => $field['label'],
				'description' => isset( $field['description'] ) ? $field['description'] : '',
				'section'     => 'fivedrs_contact',
				'type'        => $field['type'],
			)
		);
	}
}
add_action( 'customize_register', 'fivedrs_customize_register' );
