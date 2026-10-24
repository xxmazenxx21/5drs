<?php
/**
 * Seed the Arabic Contact page content and its SEO fields.
 *
 * Page wording comes from the content config; the address, email and phone come
 * from the shared Contact & Social settings, so every key here is read back out
 * of the same place the page renders it from.
 *
 * Form field labels and validation messages are theme strings and live in
 * languages/ar.po instead - they are interface text, not page copy.
 *
 * Run with: make contact-content
 *
 * @package 5drs
 */

require_once __DIR__ . '/lib-trp.php';

$language = 'ar';

$fields = array(
	'contact.heading'       => 'تواصل معنا',
	'contact.text'          => 'لديك سؤال عن منتجاتنا أو طلبك؟ أرسل لنا رسالة وسيتواصل معك فريقنا في أقرب وقت ممكن.',
	'contact.form_heading'  => 'أرسل لنا رسالة',
	'contact.submit_label'  => 'إرسال الرسالة',
	'contact.success'       => 'شكرًا لك! تم إرسال رسالتك، وسنتواصل معك قريبًا.',
	'contact.error'         => 'حدث خطأ ما. يرجى المحاولة مرة أخرى أو الاتصال بنا مباشرة.',
	'contact.info_heading'  => 'معلومات التواصل',
	'contact.label_address' => 'العنوان',
	'contact.label_email'   => 'راسلنا',
	'contact.label_phone'   => 'اتصل بنا',
	'contact.label_social'  => 'تابعنا',
);

$config = fivedrs_content_config();
$pairs  = array();

foreach ( $fields as $path => $translation ) {
	list( $section, $field ) = explode( '.', $path, 2 );

	if ( ! isset( $config[ $section ]['fields'][ $field ]['default'] ) ) {
		WP_CLI::warning( "no default for {$path} - skipped" );
		continue;
	}

	$pairs[ $config[ $section ]['fields'][ $field ]['default'] ] = $translation;
}

// The address is shared with the footer, so translating it once covers both.
$address = fivedrs_option( 'address' );

if ( '' !== $address ) {
	$pairs[ $address ] = 'مصر، التجمع الخامس، فيلات النرجس، الحي الثاني، فيلا 28';
}

// --- SEO -------------------------------------------------------------------

$contact = get_page_by_path( 'contact' );

$seo = array(
	'title'       => array(
		'en' => 'Contact Us | 5Doctors',
		'ar' => 'تواصل معنا | 5Doctors',
	),
	'description' => array(
		'en' => 'Questions about our toothbrushes or your order? Contact the 5Doctors team by phone, email, or through our contact form.',
		'ar' => 'لديك سؤال عن فرش أسناننا أو طلبك؟ تواصل مع فريق 5Doctors عبر الهاتف أو البريد الإلكتروني أو نموذج التواصل.',
	),
);

if ( $contact ) {
	update_post_meta( $contact->ID, 'rank_math_title', $seo['title']['en'] );
	update_post_meta( $contact->ID, 'rank_math_description', $seo['description']['en'] );
	update_post_meta( $contact->ID, 'fivedrs_seo_title_ar', $seo['title']['ar'] );
	update_post_meta( $contact->ID, 'fivedrs_seo_description_ar', $seo['description']['ar'] );
	WP_CLI::log( "SEO: title and description set on the Contact page (#{$contact->ID}), Arabic pair stored" );
} else {
	WP_CLI::warning( 'no Contact page found - SEO fields skipped' );
}

$pairs[ $seo['title']['en'] ]       = $seo['title']['ar'];
$pairs[ $seo['description']['en'] ] = $seo['description']['ar'];

$written = fivedrs_seed_dictionary( $pairs, $language );

WP_CLI::success( "Arabic Contact content seeded ({$written} strings)." );
