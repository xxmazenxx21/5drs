<?php
/**
 * Fill the Policies page with the return and exchange policy, and seed its
 * Arabic.
 *
 * The content goes in as core blocks so the client can edit it in the normal
 * WordPress editor rather than in a template.
 *
 * Each bold lead-in is its own <strong>, which means TranslatePress sees it as
 * a separate text node from the sentence after it - so the lead and the body
 * are seeded as separate strings. Seeding the combined sentence would never
 * match anything.
 *
 * Run with: make policies-content
 *
 * @package 5drs
 */

require_once __DIR__ . '/lib-trp.php';

$policies = get_page_by_path( 'policies', OBJECT, 'page' );

if ( ! $policies ) {
	WP_CLI::error( 'no Policies page found' );
}

$contact     = get_page_by_path( 'contact', OBJECT, 'page' );
$contact_url = $contact ? get_permalink( $contact ) : home_url( '/contact/' );

$intro = 'Out of our commitment to the health and safety of all our customers, and in line with international health standards for personal and medical care products, we have set out the following return and exchange policy with full transparency.';

$sections = array(
	array(
		'heading' => 'General Return & Exchange Conditions',
		// Rendered as a highlighted notice: it is the rule customers most need
		// to see before they buy.
		'notice'  => array(
			'lead' => 'Hygienic nature of the product:',
			'body' => 'Because toothbrushes are highly personal products directly linked to personal health, any toothbrush whose packaging has been opened or tampered with cannot be returned or exchanged under any circumstances, for health and safety reasons.',
		),
		'items'   => array(
			array(
				'lead' => 'Unopened products:',
				'body' => 'You may request an exchange or return within 14 days of receiving your order, provided the product is in its original, fully sealed packaging, unopened, and in exactly the same condition as when it was delivered.',
			),
		),
	),
	array(
		'heading' => 'Exceptions (Manufacturing Defects or Order Errors)',
		'items'   => array(
			array(
				'lead' => 'Manufacturing defects or incorrect shipments:',
				'body' => 'If you receive a damaged product, a product with a manufacturing defect, or an item different from what you ordered, we will cover all shipping and exchange costs.',
			),
			array(
				'lead' => 'Prompt reporting:',
				'body' => 'Defects or errors must be reported within 48 hours of receiving the shipment, along with photos or a video showing the issue.',
			),
		),
	),
	array(
		'heading' => 'Refund Process & Timing',
		'items'   => array(
			array(
				'lead' => 'Inspection:',
				'body' => 'Returned products are inspected as soon as they arrive at our warehouse to confirm the packaging is intact.',
			),
			array(
				'lead' => 'Refunds:',
				'body' => 'Once a return is approved, the amount will be refunded using the original payment method (or via bank transfer or store credit) within 7 to 14 business days.',
			),
			array(
				'lead' => 'Shipping fees:',
				'body' => "If the return is requested at the customer's choice and the product is in its original sealed condition, the original shipping fees and the return shipping fees will be deducted from the refund.",
			),
		),
	),
	array(
		'heading' => 'How to Request an Exchange or Return',
		'ordered' => true,
		'items'   => array(
			array(
				'lead' => 'Contact us:',
				// The link points at the contact page; no WhatsApp number has
				// been supplied, so none is invented here.
				'body' => 'Reach our customer service team by <a href="' . esc_url( $contact_url ) . '">email or WhatsApp</a>, as listed on our website.',
				'text' => 'email or WhatsApp',
			),
			array(
				'lead' => 'Order details:',
				'body' => 'Provide your order number, the product name, and the reason for the return or exchange.',
			),
			array(
				'lead' => 'Shipping & drop-off:',
				'body' => 'Our team will provide you with a return shipping waybill and direct you to the nearest branch of the shipping company.',
			),
		),
	),
);

// --- build the block markup --------------------------------------------------

$blocks = array();

$blocks[] = "<!-- wp:paragraph {\"className\":\"fivedrs-policy-intro\"} -->\n<p class=\"fivedrs-policy-intro\">" . esc_html( $intro ) . "</p>\n<!-- /wp:paragraph -->";

foreach ( $sections as $section ) {
	$blocks[] = "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( $section['heading'] ) . "</h2>\n<!-- /wp:heading -->";

	if ( isset( $section['notice'] ) ) {
		$blocks[] = "<!-- wp:paragraph {\"className\":\"fivedrs-policy-notice\"} -->\n<p class=\"fivedrs-policy-notice\"><strong>"
			. esc_html( $section['notice']['lead'] ) . '</strong> ' . esc_html( $section['notice']['body'] )
			. "</p>\n<!-- /wp:paragraph -->";
	}

	$ordered = ! empty( $section['ordered'] );
	$tag     = $ordered ? 'ol' : 'ul';
	$attrs   = $ordered ? ' {"ordered":true}' : '';
	$items   = '';

	foreach ( $section['items'] as $item ) {
		// Only the link markup is trusted here; it is built above with esc_url.
		$body    = isset( $item['text'] ) ? $item['body'] : esc_html( $item['body'] );
		$items  .= "<!-- wp:list-item -->\n<li><strong>" . esc_html( $item['lead'] ) . '</strong> ' . $body . "</li>\n<!-- /wp:list-item -->\n";
	}

	$blocks[] = "<!-- wp:list{$attrs} -->\n<{$tag} class=\"wp-block-list\">\n" . $items . "</{$tag}>\n<!-- /wp:list -->";
}

wp_update_post(
	array(
		'ID'           => $policies->ID,
		'post_content' => implode( "\n\n", $blocks ),
	)
);

WP_CLI::log( '  policy content written to page #' . $policies->ID );

// --- Arabic --------------------------------------------------------------------

$pairs = array(
	'Return & Exchange Policy' => 'سياسة الاسترجاع والاستبدال',

	$intro => 'حرصًا منا على صحة وسلامة جميع عملائنا، وتطبيقًا للمعايير الصحية الدولية الخاصة بمنتجات العناية الشخصية والطبية، تم وضع سياسة الاسترجاع والاستبدال التالية بكل شفافية.',

	'General Return & Exchange Conditions'            => 'أولًا: شروط الاسترجاع والاستبدال العامة',
	'Exceptions (Manufacturing Defects or Order Errors)' => 'ثانيًا: الحالات المستثناة (العيوب التصنيعية أو الأخطاء)',
	'Refund Process & Timing'                         => 'ثالثًا: إجراءات الاسترداد المالي ومدته',
	'How to Request an Exchange or Return'            => 'رابعًا: خطوات تقديم طلب الاستبدال أو الاسترجاع',

	'Hygienic nature of the product:' => 'طبيعة المنتج الصحية:',
	'Because toothbrushes are highly personal products directly linked to personal health, any toothbrush whose packaging has been opened or tampered with cannot be returned or exchanged under any circumstances, for health and safety reasons.' => 'نظرًا لأن فرش الأسنان تُعد من المنتجات شديدة الخصوصية والمرتبطة بالصحة الشخصية، يُمنع منعًا باتًا استرجاع أو استبدال أي فرشاة أسنان تم فتح غلافها أو العبث بعبوتها، وذلك لأسباب صحية ووقائية.',

	'Unopened products:' => 'المنتجات غير المفتوحة:',
	'You may request an exchange or return within 14 days of receiving your order, provided the product is in its original, fully sealed packaging, unopened, and in exactly the same condition as when it was delivered.' => 'يحق للعميل طلب استبدال أو استرجاع المنتج خلال 14 يومًا من تاريخ الاستلام، بشرط أن يكون المنتج في غلافه الأصلي محكم الإغلاق، وغير مفتوح، وفي حالته الأصلية تمامًا كما تم تسليمه.',

	'Manufacturing defects or incorrect shipments:' => 'عيوب التصنيع أو خطأ الشحنة:',
	'If you receive a damaged product, a product with a manufacturing defect, or an item different from what you ordered, we will cover all shipping and exchange costs.' => 'في حال استلام منتج تالف، أو منتج يحتوي على عيب تصنيعي، أو استلام صنف مختلف عن الطلب، نتحمل كامل تكاليف الشحن والاستبدال.',

	'Prompt reporting:' => 'الإبلاغ الفوري:',
	'Defects or errors must be reported within 48 hours of receiving the shipment, along with photos or a video showing the issue.' => 'يجب الإبلاغ عن العيب أو الخطأ خلال مدة لا تتجاوز 48 ساعة من تاريخ استلام الشحنة، مع إرفاق صور أو فيديو يوضح المشكلة.',

	'Inspection:' => 'فحص المرتجعات:',
	'Returned products are inspected as soon as they arrive at our warehouse to confirm the packaging is intact.' => 'يتم فحص المنتج المرتجع فور وصوله إلى مخازننا للتحقق من سلامة الغلاف.',

	'Refunds:' => 'رد المبالغ:',
	'Once a return is approved, the amount will be refunded using the original payment method (or via bank transfer or store credit) within 7 to 14 business days.' => 'في حال الموافقة على الاسترجاع، يتم رد المبلغ بنفس طريقة الدفع الأصلية (أو عبر تحويل بنكي أو رصيد في المتجر) خلال 7 إلى 14 يوم عمل.',

	'Shipping fees:' => 'رسوم الشحن:',
	"If the return is requested at the customer's choice and the product is in its original sealed condition, the original shipping fees and the return shipping fees will be deducted from the refund." => 'تُخصم رسوم الشحن الأصلية ورسوم الإرجاع في حال كان طلب الاسترجاع بناءً على رغبة العميل وكان المنتج بحالته الأصلية المغلقة.',

	'Contact us:' => 'التواصل:',
	'Reach our customer service team by' => 'تواصل مع فريق خدمة العملاء عبر',
	'email or WhatsApp'                  => 'البريد الإلكتروني أو الواتساب',
	', as listed on our website.'        => 'الموضح بالموقع.',

	'Order details:' => 'تفاصيل الطلب:',
	'Provide your order number, the product name, and the reason for the return or exchange.' => 'زوّد الفريق برقم الطلب واسم الصنف وسبب الاسترجاع أو الاستبدال.',

	'Shipping & drop-off:' => 'الشحن والتسليم:',
	'Our team will provide you with a return shipping waybill and direct you to the nearest branch of the shipping company.' => 'سيزودك فريقنا ببوليصة شحن للإرجاع ويوجهك إلى أقرب فرع لشركة الشحن.',
);

// --- SEO -----------------------------------------------------------------------

$seo = array(
	'title'       => array(
		'en' => 'Return & Exchange Policy | 5Doctors',
		'ar' => 'سياسة الاسترجاع والاستبدال | 5Doctors',
	),
	'description' => array(
		'en' => 'Learn how returns and exchanges work at 5Doctors, including conditions for unopened products, defective items, and refunds.',
		'ar' => 'تعرّف على شروط الاسترجاع والاستبدال في 5Doctors، بما في ذلك المنتجات غير المفتوحة والمنتجات التالفة واسترداد المبالغ.',
	),
);

update_post_meta( $policies->ID, 'rank_math_title', $seo['title']['en'] );
update_post_meta( $policies->ID, 'rank_math_description', $seo['description']['en'] );
update_post_meta( $policies->ID, 'fivedrs_seo_title_ar', $seo['title']['ar'] );
update_post_meta( $policies->ID, 'fivedrs_seo_description_ar', $seo['description']['ar'] );

$pairs[ $seo['title']['en'] ]       = $seo['title']['ar'];
$pairs[ $seo['description']['en'] ] = $seo['description']['ar'];

$written = fivedrs_seed_dictionary( $pairs, 'ar' );

WP_CLI::success( "Policy content and Arabic seeded ({$written} strings)." );
