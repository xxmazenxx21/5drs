<?php
/**
 * Seed the Arabic translations for the catalogue and the Our Products page.
 *
 * As with home-content.php, the English side is never written here: the page
 * strings are read back out of fivedrs_content_config() and the product copy
 * out of the products themselves, so every dictionary key is guaranteed to be
 * the exact string the page renders.
 *
 * Run with: make product-content
 *
 * @package 5drs
 */

require_once __DIR__ . '/lib-trp.php';

$language = 'ar';
$pairs    = array();

// --- Our Products page hero + the Home category cards -----------------------

$fields = array(
	'products.eyebrow'       => 'تشكيلة 5Doctors',
	'products.heading'       => 'عناية تناسب كل ابتسامة',
	'products.text'          => 'اكتشف فرش كلاسيك وبريميوم والفحم والأطفال، كل واحدة منها مصممة لراحتك ولتنظيف يومي فعّال.',
	'products.button_label'  => 'تصفّح المنتجات',
	'categories.card_2_text' => 'تصميم متطور وشعيرات ذكية لتنظيف احترافي.',
	'categories.card_3_text' => 'شعيرات ناعمة مشبعة بالفحم لتنظيف لطيف ومنعش.',
);

$config = fivedrs_content_config();

foreach ( $fields as $path => $translation ) {
	list( $section, $field ) = explode( '.', $path, 2 );

	if ( ! isset( $config[ $section ]['fields'][ $field ]['default'] ) ) {
		WP_CLI::warning( "no default for {$path} - skipped" );
		continue;
	}

	$pairs[ $config[ $section ]['fields'][ $field ]['default'] ] = $translation;
}

// --- product copy, keyed on what the products actually store -----------------

$products = array(
	'5DRS-CLS'   => array(
		'name'  => 'فرشاة كلاسيك',
		'short' => 'فرشاة الأسنان الأساسية لروتينك اليومي، متوفرة بشعيرات ناعمة ومتوسطة وصلبة لتناسب لثتك واحتياجك في التنظيف.',
		'desc'  => 'صُممت فرشاة كلاسيك من 5Doctors لتمنحك تنظيفًا مريحًا وفعّالًا كل يوم. يمنحك مقبضها المريح قبضة ثابتة وتحكمًا كاملًا، بينما يصل رأس الفرشاة إلى كل زاوية في فمك بسهولة. اختر نوع الشعيرات الذي يناسبك: ناعمة للثة الحساسة، أو متوسطة لتنظيف يومي متوازن، أو صلبة لتنظيف أعمق وأقوى.',
	),
	'5DRS-PRM'   => array(
		'name'  => 'فرشاة بريميوم',
		'short' => 'فرشاتنا المتطورة بتصميم راقٍ وشعيرات ذكية لتنظيف احترافي، متوفرة بشعيرات ناعمة ومتوسطة وصلبة.',
		'desc'  => 'ترتقي فرشاة بريميوم من 5Doctors بروتينك اليومي إلى مستوى جديد. يصل تصميمها المتطور إلى أدق الزوايا بعناية فائقة، وتمنحك شعيراتها الذكية تجربة تنظيف احترافية في منزلك. اختر الناعمة لعناية فائقة اللطف، أو المتوسطة لتنظيف احترافي يومي، أو الصلبة لمواجهة التصبغات العنيدة.',
	),
	'5DRS-CHR-S' => array(
		'name'  => 'فرشاة الفحم الناعمة',
		'short' => 'شعيرات ناعمة مشبعة بالفحم النشط لتنظيف لطيف يساعد على امتصاص التصبغات.',
		'desc'  => 'تجمع فرشاة الفحم من 5Doctors بين تصميم جريء باللونين الأسود والبنفسجي وشعيرات ناعمة مشبعة بالفحم النشط. تنظف أسنانك بلطف وتساعد على امتصاص التصبغات لتمنح فمك إحساسًا بالانتعاش، مع راحة الفرشاة الناعمة اللطيفة على اللثة.',
	),
	'5DRS-KID'   => array(
		'name'  => 'فرشاة الأطفال',
		'short' => 'فرشة الأطفال المصممة بعناية برأس صغير وشعيرات فائقة النعومة ومقبض مرح لحماية أسنان أطفالك.',
		'desc'  => 'اجعل تنظيف الأسنان أجمل وقت في يوم أطفالك. تتميز فرشاة الأطفال من 5Doctors بتصميم مرح مستوحى من كوز الذرة وقاعدة شفّاطة تثبت على الحوض، لتبقى نظيفة وفي متناول أيديهم الصغيرة دائمًا. رأسها الصغير وشعيراتها فائقة النعومة لطيفة على أسنان الأطفال ولثتهم، ومقبضها السميك سهل الإمساك للأيدي الصغيرة.',
	),
);

foreach ( $products as $sku => $translations ) {
	$product_id = wc_get_product_id_by_sku( $sku );

	if ( ! $product_id ) {
		WP_CLI::warning( "no product for SKU {$sku}" );
		continue;
	}

	$product = wc_get_product( $product_id );

	$pairs[ $product->get_name() ]              = $translations['name'];
	$pairs[ $product->get_short_description() ] = $translations['short'];
	$pairs[ $product->get_description() ]       = $translations['desc'];
}

// --- variation descriptions ---------------------------------------------------

$variations = array(
	'5DRS-CLS-S' => 'فرشاة كلاسيك ناعمة، مصممة خصيصًا لتنظيف لطيف يحمي اللثة الحساسة ويحافظ على مينا الأسنان.',
	'5DRS-CLS-M' => 'فرشاة كلاسيك متوسطة الخشونة، تجمع بين إزالة فعّالة للبلاك ونعومة مريحة للاستخدام اليومي.',
	'5DRS-CLS-H' => 'فرشاة كلاسيك صلبة، مثالية لإزالة البقع الصعبة ومنح الأسنان نظافة عميقة وقوية.',
	'5DRS-PRM-S' => 'فرشاة بريميوم الناعمة بتصميم متطور يصل لأدق الزوايا بعناية فائقة ونعومة فائقة للثة.',
	'5DRS-PRM-M' => 'فرشاة بريميوم المتوسطة الفاخرة، بشعيرات ذكية لتنظيف احترافي يضاهي عيادات الأسنان.',
	'5DRS-PRM-H' => 'فرشاة بريميوم الصلبة، أداء فائق ومقاومة عالية لتحدي التصبغات ومنح فمك انتعاشًا مطلقًا.',
);

foreach ( $variations as $sku => $translation ) {
	$variation_id = wc_get_product_id_by_sku( $sku );

	if ( ! $variation_id ) {
		WP_CLI::warning( "no variation for SKU {$sku}" );
		continue;
	}

	$variation = wc_get_product( $variation_id );
	$original  = $variation->get_description();

	if ( '' !== $original ) {
		$pairs[ $original ] = $translation;
	}
}

// --- taxonomy ------------------------------------------------------------------

$pairs['Bristle Type'] = 'نوع الشعيرات';
$pairs['Soft']         = 'ناعمة';
$pairs['Medium']       = 'متوسطة';
$pairs['Hard']         = 'صلبة';

$pairs['Classic']  = 'كلاسيك';
$pairs['Premium']  = 'بريميوم';
$pairs['Charcoal'] = 'الفحم';
$pairs['Kids']     = 'للأطفال';

// Category descriptions, shown in the compact archive hero.
$pairs['Everyday toothbrushes for adults, in soft, medium and hard bristles.']   = 'فرش أسنان يومية للكبار، بشعيرات ناعمة ومتوسطة وصلبة.';
$pairs['Advanced design and smart bristles for a professional-grade clean.']     = 'تصميم متطور وشعيرات ذكية لتنظيف احترافي.';
$pairs['Soft charcoal-infused bristles for a gentle, fresh clean.']              = 'شعيرات ناعمة مشبعة بالفحم لتنظيف لطيف ومنعش.';
$pairs["Fun, easy-to-hold brushes designed for children's teeth and gums."]      = 'فرش مرحة سهلة الإمساك مصممة لأسنان الأطفال ولثتهم.';

// --- image alt text --------------------------------------------------------------

$pairs['The 5Doctors toothbrush collection arranged on a bright marble bathroom counter with mint leaves'] = 'تشكيلة فرش أسنان 5Doctors معروضة على رف حمام رخامي مضيء مع أوراق نعناع';
$pairs['Four 5Doctors Classic toothbrushes with yellow grips standing on a bathroom counter']              = 'أربع فرش أسنان كلاسيك من 5Doctors بقبضات صفراء فوق رف حمام';
$pairs['Four 5Doctors Premium toothbrushes with green grips standing on a bathroom counter']               = 'أربع فرش أسنان بريميوم من 5Doctors بقبضات خضراء فوق رف حمام';
$pairs['Four 5Doctors Charcoal toothbrushes in black and lilac, two of them in travel cases']              = 'أربع فرش أسنان بالفحم من 5Doctors باللونين الأسود والبنفسجي، اثنتان منها داخل علبتي سفر';
$pairs["Four 5Doctors kids' toothbrushes with corn-shaped handles, two of them in travel cases"]           = 'أربع فرش أسنان للأطفال من 5Doctors بمقابض على شكل كوز الذرة، اثنتان منها داخل علبتي سفر';

$written = fivedrs_seed_dictionary( $pairs, $language );

WP_CLI::success( "Arabic catalogue content seeded ({$written} strings)." );
