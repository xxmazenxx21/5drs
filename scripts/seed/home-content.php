<?php
/**
 * Seed the Arabic Home page content and the Home SEO fields.
 *
 * The English side is never written here - it already lives as the defaults in
 * inc/customizer-home.php. This script reads those defaults and pairs each one
 * with its Arabic translation, so the dictionary key is guaranteed to be the
 * exact string the page renders. That matters: TranslatePress keys on the
 * English text, so a typo here would silently produce an untranslated string.
 *
 * Run with: make home-content
 *
 * @package 5drs
 */

require_once __DIR__ . '/lib-trp.php';

$language = 'ar';

// section.field => Arabic translation of that field's English default.
$fields = array(
	'hero.eyebrow'             => 'هدفنا راحتك الشخصية',
	'hero.heading'             => 'ابتسامة أصح، تنظيف أعمق، وعناية متكاملة لجميع أفراد العائلة',
	'hero.text'                => 'اكتشف تشكيلتنا المبتكرة من فرش الأسنان المصممة بأعلى معايير الجودة الطبية لترتقي بروتينك اليومي وصحة فمك.',
	'hero.primary_label'       => 'تسوّق الآن',
	'hero.secondary_label'     => 'تعرّف على 5Doctors',

	'trust.item_1'             => 'مصممة لصحة الفم',
	'trust.item_2'             => 'لطيفة على اللثة',
	'trust.item_3'             => 'لجميع الأعمار',
	'trust.item_4'             => 'طلب سهل وآمن',

	'why.heading'              => 'لماذا تختار منتجاتنا؟',
	'why.subtitle'             => 'كل تفصيلة في فرشنا مصممة لتجعل تنظيف أسنانك أسهل وأكثر راحة وفاعلية.',
	'why.card_1_title'         => 'تصميم مريح وأنيق',
	'why.card_1_text'          => 'مقابض مريحة لليد تضمن تحكمًا كاملًا أثناء التنظيف، مع قبضة ثابتة تساعدك على الوصول إلى كل زاوية في فمك بسهولة.',
	'why.card_2_title'         => 'شعيرات عالية الجودة',
	'why.card_2_text'          => 'مصنوعة من ألياف آمنة وناعمة تزيل البلاك بفاعلية وتحمي اللثة، لتشعر بالانتعاش والنظافة دون إزعاج.',
	'why.card_3_title'         => 'حلول لكل الاحتياجات',
	'why.card_3_text'          => 'تشكيلة متكاملة تناسب جميع الأعمار وأنواع اللثة والأسنان، من الفرش الكلاسيكية للكبار إلى فرش مرحة يحبها الأطفال.',

	'categories.heading'       => 'تسوّق حسب احتياجك',
	'categories.link_label'    => 'تسوّق الآن',
	'categories.card_1_text'   => 'الاختيار اليومي المثالي لتنظيف فعّال ومريح.',
	'categories.card_2_text'   => 'فرشة مرحة بقاعدة شفّاطة تحوّل تنظيف الأسنان إلى وقت ممتع.',
	'categories.card_3_text'   => 'تصميم جريء بلمسة عصرية مميزة.',

	'featured.heading'         => 'منتجات مميزة',
	'featured.subtitle'        => 'اختر الفرشة المناسبة لك ولعائلتك.',
	'featured.link_label'      => 'عرض كل المنتجات',

	'kids.eyebrow'             => 'للأطفال',
	'kids.heading'             => 'اجعل تنظيف الأسنان أجمل وقت في يومهم',
	'kids.text'                => 'فرشة الأطفال من 5Doctors بتصميم مرح مستوحى من كوز الذرة، مع قاعدة شفّاطة تثبت على الحوض، لتبقى نظيفة وفي متناول أيديهم الصغيرة دائمًا.',
	'kids.bullet_1'            => 'تصميم مرح يشجعهم على التنظيف',
	'kids.bullet_2'            => 'قاعدة شفّاطة تثبت على أي سطح أملس',
	'kids.bullet_3'            => 'مقبض سميك سهل الإمساك للأيدي الصغيرة',
	'kids.button_label'        => 'تسوّق فرش الأطفال',

	'tips.heading'             => '3 خطوات لابتسامة أكثر صحة',
	'tips.step_1'              => 'نظّف أسنانك مرتين يوميًا لمدة دقيقتين كاملتين.',
	'tips.step_2'              => 'استخدم حركات دائرية لطيفة مع إمالة الفرشة بزاوية 45 درجة نحو خط اللثة.',
	'tips.step_3'              => 'استبدل فرشتك كل 3 أشهر، أو قبل ذلك إذا تفرّقت الشعيرات.',

	'faq.heading'              => 'الأسئلة الشائعة',
	'faq.question_1'           => 'كل كم يجب أن أغيّر فرشة أسناني؟',
	'faq.answer_1'             => 'كل 3 إلى 4 أشهر، أو قبل ذلك إذا لاحظت تفرّق الشعيرات أو بعد التعافي من المرض.',
	'faq.question_2'           => 'هل أختار شعيرات ناعمة أم متوسطة؟',
	'faq.answer_2'             => 'الشعيرات الناعمة والمتوسطة تناسب معظم الناس. إذا كانت لثتك حساسة فاختر الأنعم، واستشر طبيب أسنانك إذا لم تكن متأكدًا.',
	'faq.question_3'           => 'كيف أحافظ على نظافة فرشتي؟',
	'faq.answer_3'             => 'اشطفها جيدًا بعد كل استخدام، واتركها في وضع رأسي لتجف في الهواء، وتجنّب وضعها في علبة مغلقة وهي مبللة.',
	'faq.question_4'           => 'كيف أطلب منتجًا؟',
	'faq.answer_4'             => 'اختر المنتج، وأضفه إلى السلة، وأكمل الطلب في خطوات بسيطة دون الحاجة إلى إنشاء حساب.',

	'cta.heading'              => 'جاهز لابتسامة أكثر صحة؟',
	'cta.text'                 => 'اكتشف تشكيلة 5Doctors واختر الفرشة المناسبة لك.',
	'cta.button_label'         => 'تسوّق الآن',
);

$config = fivedrs_home_config();
$pairs  = array();

foreach ( $fields as $path => $translation ) {
	list( $section, $field ) = explode( '.', $path, 2 );

	if ( ! isset( $config[ $section ]['fields'][ $field ]['default'] ) ) {
		WP_CLI::warning( "no default for {$path} - skipped" );
		continue;
	}

	$pairs[ $config[ $section ]['fields'][ $field ]['default'] ] = $translation;
}

// Image alt text. TranslatePress translates alt attributes, so these are
// ordinary dictionary entries keyed on the English alt set by home-media.php.
$pairs['Three 5Doctors toothbrushes standing on marble with a water splash and fresh mint leaves'] = 'ثلاث فرش أسنان من 5Doctors فوق سطح رخامي مع رذاذ ماء وأوراق نعناع طازجة';
$pairs['Two classic 5Doctors toothbrushes with yellow and green grips on a soft green background'] = 'فرشتا أسنان كلاسيكيتان من 5Doctors بقبضتين صفراء وخضراء على خلفية خضراء فاتحة';
$pairs["Kids' 5Doctors toothbrush shaped like a corn cob, standing upright on its suction base"]   = 'فرشة أسنان للأطفال من 5Doctors بتصميم كوز الذرة، واقفة على قاعدتها الشفّاطة';
$pairs['Black and lilac 5Doctors toothbrush lit against a dark background']                        = 'فرشة أسنان من 5Doctors باللونين الأسود والبنفسجي على خلفية داكنة';
$pairs["Kids' corn-shaped 5Doctors toothbrush standing on a bright bathroom counter beside a rubber duck and a cup"] = 'فرشة أسنان للأطفال من 5Doctors بشكل كوز الذرة فوق رف حمام مضيء بجوار بطة مطاطية وكوب';

// Product category names.
$pairs['Classic']       = 'كلاسيك';
$pairs['Kids']          = 'للأطفال';
$pairs['Black Edition'] = 'بلاك إديشن';

// --- SEO -------------------------------------------------------------------

$front_id = (int) get_option( 'page_on_front' );

$seo = array(
	'title'       => array(
		'en' => '5Doctors | Toothbrushes for a Healthier Smile',
		'ar' => '5Doctors | فرش أسنان لابتسامة أكثر صحة',
	),
	'description' => array(
		'en' => 'Discover 5Doctors toothbrushes: comfortable design, high-quality soft bristles, and options for the whole family — adults and kids.',
		'ar' => 'اكتشف فرش أسنان 5Doctors: تصميم مريح، شعيرات ناعمة عالية الجودة، وخيارات لكل أفراد العائلة من الكبار إلى الأطفال.',
	),
);

if ( $front_id ) {
	update_post_meta( $front_id, 'rank_math_title', $seo['title']['en'] );
	update_post_meta( $front_id, 'rank_math_description', $seo['description']['en'] );

	// TranslatePress free does not translate <title> or the meta description,
	// so the Arabic pair is stored beside Rank Math's fields and swapped in by
	// inc/seo.php on Arabic requests.
	update_post_meta( $front_id, 'fivedrs_seo_title_ar', $seo['title']['ar'] );
	update_post_meta( $front_id, 'fivedrs_seo_description_ar', $seo['description']['ar'] );

	WP_CLI::log( "SEO: Rank Math title and description set on the front page (#{$front_id}), Arabic pair stored" );
} else {
	WP_CLI::warning( 'no static front page set - SEO fields skipped' );
}

$pairs[ $seo['title']['en'] ]       = $seo['title']['ar'];
$pairs[ $seo['description']['en'] ] = $seo['description']['ar'];

$written = fivedrs_seed_dictionary( $pairs, $language );

WP_CLI::success( "Home page Arabic content seeded ({$written} strings)." );
