<?php
/**
 * Seed the Arabic About page content and its SEO fields.
 *
 * As with the other content seeders, the English side is read back out of
 * fivedrs_content_config() so every dictionary key is exactly the string the
 * page renders.
 *
 * Run with: make about-content
 *
 * @package 5drs
 */

require_once __DIR__ . '/lib-trp.php';

$language = 'ar';

// section.field => Arabic translation of that field's English default.
$fields = array(
	'about.eyebrow'                => 'عن 5Doctors',
	'about.heading'                => 'الابتسامة المشرقة تبدأ من العناية اليومية الصحيحة',
	'about.text'                   => 'نصمم فرش أسنان تجمع بين التكنولوجيا المتطورة والراحة العصرية والعناية بكل فرد في العائلة.',
	'about.button_label'           => 'قصتنا',

	'about_story.eyebrow'          => 'من نحن',
	'about_story.heading'          => 'عناية بالفم مصممة من أجلك',
	'about_story.text_1'           => 'نحن علامة تجارية متخصصة في العناية بصحة الفم والأسنان، نهدف إلى تقديم منتجات تجمع بين التكنولوجيا المتطورة والتصميم العصري المريح. نؤمن بأن الابتسامة المشرقة تبدأ من العناية اليومية الصحيحة، ولذلك حرصنا على تطوير تشكيلة متكاملة من فرش الأسنان المصممة لتلبي احتياجات كل فرد في العائلة، مع مراعاة أعلى معايير الأمان والجودة الطبية.',
	'about_story.text_2'           => 'من شكل المقبض إلى نعومة كل شعيرة، صُممت كل فرشاة من 5Doctors حول فكرة واحدة: راحتك الشخصية. سواء كنت تحتاج إلى لمسة لطيفة للثة الحساسة، أو تنظيفًا أعمق للتصبغات العنيدة، أو فرشاة مرحة تحبّب الأطفال في تنظيف أسنانهم، ستجد في 5Doctors الفرشاة المناسبة لك.',

	'about_mission.mission_title'  => 'رسالتنا',
	'about_mission.mission_text'   => 'أن نجعل العناية الفعّالة والمريحة بالفم جزءًا من الروتين اليومي لكل عائلة، من خلال منتجات مصممة بعناية تجمع بين الجودة والأمان وسهولة الاستخدام.',
	'about_mission.vision_title'   => 'رؤيتنا',
	'about_mission.vision_text'    => 'أن نصبح اسمًا موثوقًا في العناية بالفم داخل كل بيت، يُعرف بمنتجات تضع راحة الإنسان وصحته في المقام الأول.',

	'about_values.heading'         => 'ما نؤمن به',
	'about_values.value_1_title'   => 'الجودة والأمان',
	'about_values.value_1_text'    => 'نختار خامات آمنة ونهتم بأدق التفاصيل، لتنظف أسنانك بثقة كل يوم.',
	'about_values.value_2_title'   => 'الراحة في التصميم',
	'about_values.value_2_text'    => 'مقابض مريحة ورؤوس مصممة بعناية تجعل تنظيف الأسنان تجربة طبيعية وسهلة.',
	'about_values.value_3_title'   => 'عناية لكل الأعمار',
	'about_values.value_3_text'    => 'من الكبار إلى الصغار، صُممت تشكيلتنا ليجد كل فرد في العائلة الفرشاة التي تناسبه.',
	'about_values.value_4_title'   => 'الابتكار المستمر',
	'about_values.value_4_text'    => 'نواصل تطوير تصميماتنا وأنواع الشعيرات لنقدم لك تنظيفًا أفضل كل يوم.',

	'about_range.heading'          => 'عائلة واحدة من الفرش، لكل ابتسامة',
	'about_range.subtitle'         => 'كلاسيك وبريميوم والفحم والأطفال، اختر ما يناسبك.',

	'about_promise.statement'      => 'هدفنا راحتك الشخصية',
	'about_promise.text'           => 'ليست مجرد عبارة، بل السؤال الذي نطرحه قبل كل قرار في التصميم: هل سيجعل هذا تنظيف أسنانك أكثر راحة؟',

	'about_cta.heading'            => 'اعثر على فرشاتك المثالية',
	'about_cta.text'               => 'تصفّح تشكيلتنا واختر الفرشاة التي تناسب ابتسامتك.',
	'about_cta.button_label'       => 'تسوّق الآن',
	'about_cta.button_2_label'     => 'تواصل معنا',
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

// Hero image alt text.
$pairs['A cup of 5Doctors toothbrushes beside a corn-shaped kids brush on a sunlit bathroom counter'] = 'كوب به فرش أسنان من 5Doctors بجوار فرشاة أطفال على شكل كوز الذرة فوق رف حمام تغمره الشمس';

// Slideshow control strings that WordPress renders as attributes.
$pairs['Our products'] = 'منتجاتنا';

// --- SEO -------------------------------------------------------------------
// Composed from the copy supplied for this page - no new claims.

$about = get_page_by_path( 'about' );

$seo = array(
	'title'       => array(
		'en' => 'About 5Doctors | Oral Care Designed Around You',
		'ar' => 'عن 5Doctors | عناية بالفم مصممة من أجلك',
	),
	'description' => array(
		'en' => 'We design toothbrushes that bring together advanced technology, modern comfort, and care for every member of the family.',
		'ar' => 'نصمم فرش أسنان تجمع بين التكنولوجيا المتطورة والراحة العصرية والعناية بكل فرد في العائلة.',
	),
);

if ( $about ) {
	update_post_meta( $about->ID, 'rank_math_title', $seo['title']['en'] );
	update_post_meta( $about->ID, 'rank_math_description', $seo['description']['en'] );
	update_post_meta( $about->ID, 'fivedrs_seo_title_ar', $seo['title']['ar'] );
	update_post_meta( $about->ID, 'fivedrs_seo_description_ar', $seo['description']['ar'] );
	WP_CLI::log( "SEO: title and description set on the About page (#{$about->ID}), Arabic pair stored" );
} else {
	WP_CLI::warning( 'no About page found - SEO fields skipped' );
}

$pairs[ $seo['title']['en'] ]       = $seo['title']['ar'];
$pairs[ $seo['description']['en'] ] = $seo['description']['ar'];

$written = fivedrs_seed_dictionary( $pairs, $language );

WP_CLI::success( "Arabic About content seeded ({$written} strings)." );
