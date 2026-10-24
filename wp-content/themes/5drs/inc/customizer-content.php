<?php
/**
 * Editable page content, driven from the Customizer.
 *
 * Every string, button, link and image on the Home page and the Our Products
 * page is defined once in fivedrs_content_config(). That single array drives
 * the Customizer panels, controls, sanitisers, defaults and the front-end
 * output, so they cannot drift apart.
 *
 * Each section declares which panel it belongs to ('home' or 'products').
 *
 * The English copy here is the default. The Arabic lives in TranslatePress's
 * dictionary, keyed by the English string - see scripts/seed/home-content.php
 * and the warning in CLAUDE.md about editing English text.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field and section definitions for the Home page.
 *
 * Field types: text, textarea, image (attachment ID), page (page ID),
 * category (product_cat term ID), number.
 *
 * @return array<string,array{title:string,fields:array<string,array>}>
 */
function fivedrs_content_config() {
	return array(
		'hero'       => array(
			'title'  => __( 'Home - Hero', '5drs' ),
			'panel'  => 'home',
			'fields' => array(
				'eyebrow'         => array(
					'type'    => 'text',
					'label'   => __( 'Eyebrow', '5drs' ),
					'default' => 'Our Goal Is Your Personal Comfort',
				),
				'heading'         => array(
					'type'    => 'textarea',
					'label'   => __( 'Heading', '5drs' ),
					'default' => 'A Healthier Smile, a Deeper Clean, and Complete Care for the Whole Family',
				),
				'text'            => array(
					'type'    => 'textarea',
					'label'   => __( 'Text', '5drs' ),
					'default' => 'Discover our innovative range of toothbrushes, designed to the highest medical-grade quality standards to elevate your daily routine and your oral health.',
				),
				'primary_label'   => array(
					'type'    => 'text',
					'label'   => __( 'Primary button label', '5drs' ),
					'default' => 'Shop Now',
				),
				'primary_page'    => array(
					'type'         => 'page',
					'label'        => __( 'Primary button links to', '5drs' ),
					'default_slug' => 'products',
				),
				'secondary_label' => array(
					'type'    => 'text',
					'label'   => __( 'Secondary button label', '5drs' ),
					'default' => 'About 5Doctors',
				),
				'secondary_page'  => array(
					'type'         => 'page',
					'label'        => __( 'Secondary button links to', '5drs' ),
					'default_slug' => 'about',
				),
				'image'           => array(
					'type'         => 'image',
					'label'        => __( 'Hero image', '5drs' ),
					'default_slug' => '5drs-hero',
				),
			),
		),
		'trust'      => array(
			'title'  => __( 'Home - Trust bar', '5drs' ),
			'panel'  => 'home',
			'fields' => array(
				'item_1' => array(
					'type'    => 'text',
					'label'   => __( 'Item 1', '5drs' ),
					'default' => 'Designed for Oral Health',
				),
				'item_2' => array(
					'type'    => 'text',
					'label'   => __( 'Item 2', '5drs' ),
					'default' => 'Gentle on Gums',
				),
				'item_3' => array(
					'type'    => 'text',
					'label'   => __( 'Item 3', '5drs' ),
					'default' => 'For Every Age',
				),
				'item_4' => array(
					'type'    => 'text',
					'label'   => __( 'Item 4', '5drs' ),
					'default' => 'Easy, Secure Ordering',
				),
			),
		),
		'why'        => array(
			'title'  => __( 'Home - Why choose us', '5drs' ),
			'panel'  => 'home',
			'fields' => array(
				'heading'      => array(
					'type'    => 'text',
					'label'   => __( 'Heading', '5drs' ),
					'default' => 'Why Choose Our Products?',
				),
				'subtitle'     => array(
					'type'    => 'textarea',
					'label'   => __( 'Subtitle', '5drs' ),
					'default' => 'Every detail of our brushes is designed to make brushing easier, more comfortable, and more effective.',
				),
				'card_1_title' => array(
					'type'    => 'text',
					'label'   => __( 'Card 1 title', '5drs' ),
					'default' => 'Comfortable, Elegant Design',
				),
				'card_1_text'  => array(
					'type'    => 'textarea',
					'label'   => __( 'Card 1 text', '5drs' ),
					'default' => 'Ergonomic handles give you full control while brushing, with a secure grip that helps you reach every corner of your mouth with ease.',
				),
				'card_2_title' => array(
					'type'    => 'text',
					'label'   => __( 'Card 2 title', '5drs' ),
					'default' => 'High-Quality Bristles',
				),
				'card_2_text'  => array(
					'type'    => 'textarea',
					'label'   => __( 'Card 2 text', '5drs' ),
					'default' => 'Made from safe, soft fibers that remove plaque effectively while protecting your gums - a fresh, clean feeling without irritation.',
				),
				'card_3_title' => array(
					'type'    => 'text',
					'label'   => __( 'Card 3 title', '5drs' ),
					'default' => 'Solutions for Every Need',
				),
				'card_3_text'  => array(
					'type'    => 'textarea',
					'label'   => __( 'Card 3 text', '5drs' ),
					'default' => 'A complete range for all ages and every type of gums and teeth - from classic brushes for adults to fun brushes kids actually love to use.',
				),
			),
		),
		'categories' => array(
			'title'  => __( 'Home - Shop by need', '5drs' ),
			'panel'  => 'home',
			'fields' => array(
				'heading'      => array(
					'type'    => 'text',
					'label'   => __( 'Heading', '5drs' ),
					'default' => 'Shop by Need',
				),
				'link_label'   => array(
					'type'    => 'text',
					'label'   => __( 'Card link label', '5drs' ),
					'default' => 'Shop now',
				),
				'card_1_cat'   => array(
					'type'         => 'category',
					'label'        => __( 'Card 1 category', '5drs' ),
					'default_slug' => 'classic',
				),
				'card_1_text'  => array(
					'type'    => 'textarea',
					'label'   => __( 'Card 1 text', '5drs' ),
					'default' => 'The everyday essential for an effective, comfortable clean.',
				),
				'card_1_image' => array(
					'type'         => 'image',
					'label'        => __( 'Card 1 image', '5drs' ),
					'default_slug' => '5drs-cat-classic',
				),
				'card_2_cat'   => array(
					'type'         => 'category',
					'label'        => __( 'Card 2 category', '5drs' ),
					'default_slug' => 'premium',
				),
				'card_2_text'  => array(
					'type'    => 'textarea',
					'label'   => __( 'Card 2 text', '5drs' ),
					'default' => 'Advanced design and smart bristles for a professional-grade clean.',
				),
				'card_2_image' => array(
					'type'         => 'image',
					'label'        => __( 'Card 2 image', '5drs' ),
					'default_slug' => '5drs-product-premium',
				),
				'card_3_cat'   => array(
					'type'         => 'category',
					'label'        => __( 'Card 3 category', '5drs' ),
					'default_slug' => 'charcoal',
				),
				'card_3_text'  => array(
					'type'    => 'textarea',
					'label'   => __( 'Card 3 text', '5drs' ),
					'default' => 'Soft charcoal-infused bristles for a gentle, fresh clean.',
				),
				'card_3_image' => array(
					'type'         => 'image',
					'label'        => __( 'Card 3 image', '5drs' ),
					'default_slug' => '5drs-cat-black',
				),
				'card_4_cat'   => array(
					'type'         => 'category',
					'label'        => __( 'Card 4 category', '5drs' ),
					'default_slug' => 'kids',
				),
				'card_4_text'  => array(
					'type'    => 'textarea',
					'label'   => __( 'Card 4 text', '5drs' ),
					'default' => 'A playful brush with a suction base that turns brushing into fun.',
				),
				'card_4_image' => array(
					'type'         => 'image',
					'label'        => __( 'Card 4 image', '5drs' ),
					'default_slug' => '5drs-cat-kids',
				),
			),
		),
		'featured'   => array(
			'title'  => __( 'Home - Featured products', '5drs' ),
			'panel'  => 'home',
			'fields' => array(
				'heading'    => array(
					'type'    => 'text',
					'label'   => __( 'Heading', '5drs' ),
					'default' => 'Featured Products',
				),
				'subtitle'   => array(
					'type'    => 'textarea',
					'label'   => __( 'Subtitle', '5drs' ),
					'default' => 'Find the right brush for you and your family.',
				),
				'count'      => array(
					'type'    => 'number',
					'label'   => __( 'How many products', '5drs' ),
					'default' => 4,
				),
				'link_label' => array(
					'type'    => 'text',
					'label'   => __( 'Link label', '5drs' ),
					'default' => 'View all products',
				),
				'link_page'  => array(
					'type'         => 'page',
					'label'        => __( 'Link goes to', '5drs' ),
					'default_slug' => 'products',
				),
			),
		),
		'kids'       => array(
			'title'  => __( 'Home - Kids spotlight', '5drs' ),
			'panel'  => 'home',
			'fields' => array(
				'eyebrow'      => array(
					'type'    => 'text',
					'label'   => __( 'Eyebrow', '5drs' ),
					'default' => 'For Kids',
				),
				'heading'      => array(
					'type'    => 'textarea',
					'label'   => __( 'Heading', '5drs' ),
					'default' => 'Make Brushing the Best Part of Their Day',
				),
				'text'         => array(
					'type'    => 'textarea',
					'label'   => __( 'Text', '5drs' ),
					'default' => "Our kids' brush comes in a fun corn-inspired design with a suction base that stands firmly on the sink - so it stays clean and always within reach of little hands.",
				),
				'bullet_1'     => array(
					'type'    => 'text',
					'label'   => __( 'Bullet 1', '5drs' ),
					'default' => 'A fun design that motivates them to brush',
				),
				'bullet_2'     => array(
					'type'    => 'text',
					'label'   => __( 'Bullet 2', '5drs' ),
					'default' => 'Suction base that stands on any smooth surface',
				),
				'bullet_3'     => array(
					'type'    => 'text',
					'label'   => __( 'Bullet 3', '5drs' ),
					'default' => "A chunky handle that's easy for small hands to hold",
				),
				'button_label' => array(
					'type'    => 'text',
					'label'   => __( 'Button label', '5drs' ),
					'default' => "Shop Kids' Brushes",
				),
				'button_cat'   => array(
					'type'         => 'category',
					'label'        => __( 'Button links to category', '5drs' ),
					'default_slug' => 'kids',
				),
				'image'        => array(
					'type'         => 'image',
					'label'        => __( 'Banner image', '5drs' ),
					'default_slug' => '5drs-kids-spotlight',
				),
			),
		),
		'tips'       => array(
			'title'  => __( 'Home - Healthy smile tips', '5drs' ),
			'panel'  => 'home',
			'fields' => array(
				'heading' => array(
					'type'    => 'text',
					'label'   => __( 'Heading', '5drs' ),
					'default' => '3 Steps to a Healthier Smile',
				),
				'step_1'  => array(
					'type'    => 'textarea',
					'label'   => __( 'Step 1', '5drs' ),
					'default' => 'Brush twice a day for two full minutes.',
				),
				'step_2'  => array(
					'type'    => 'textarea',
					'label'   => __( 'Step 2', '5drs' ),
					'default' => 'Use gentle circular motions, angling the brush 45 degrees toward the gum line.',
				),
				'step_3'  => array(
					'type'    => 'textarea',
					'label'   => __( 'Step 3', '5drs' ),
					'default' => 'Replace your brush every 3 months, or sooner if the bristles fray.',
				),
			),
		),
		'faq'        => array(
			'title'  => __( 'Home - FAQ', '5drs' ),
			'panel'  => 'home',
			'fields' => array(
				'heading'    => array(
					'type'    => 'text',
					'label'   => __( 'Heading', '5drs' ),
					'default' => 'Frequently Asked Questions',
				),
				'question_1' => array(
					'type'    => 'text',
					'label'   => __( 'Question 1', '5drs' ),
					'default' => 'How often should I replace my toothbrush?',
				),
				'answer_1'   => array(
					'type'    => 'textarea',
					'label'   => __( 'Answer 1', '5drs' ),
					'default' => "Every 3-4 months, or sooner if the bristles look frayed or after you've been sick.",
				),
				'question_2' => array(
					'type'    => 'text',
					'label'   => __( 'Question 2', '5drs' ),
					'default' => 'Should I choose soft or medium bristles?',
				),
				'answer_2'   => array(
					'type'    => 'textarea',
					'label'   => __( 'Answer 2', '5drs' ),
					'default' => "Soft and medium bristles suit most people. If your gums are sensitive, go for the softer option, and ask your dentist if you're unsure.",
				),
				'question_3' => array(
					'type'    => 'text',
					'label'   => __( 'Question 3', '5drs' ),
					'default' => 'How do I keep my toothbrush clean?',
				),
				'answer_3'   => array(
					'type'    => 'textarea',
					'label'   => __( 'Answer 3', '5drs' ),
					'default' => 'Rinse it well after each use, store it upright to air-dry, and avoid closed cases while it is still wet.',
				),
				'question_4' => array(
					'type'    => 'text',
					'label'   => __( 'Question 4', '5drs' ),
					'default' => 'How do I place an order?',
				),
				'answer_4'   => array(
					'type'    => 'textarea',
					'label'   => __( 'Answer 4', '5drs' ),
					'default' => 'Choose your product, add it to your cart, and complete checkout in a few simple steps - no account needed.',
				),
			),
		),
		'products'   => array(
			'title'  => __( 'Products page - Hero', '5drs' ),
			'panel'  => 'products',
			'fields' => array(
				'eyebrow'      => array(
					'type'    => 'text',
					'label'   => __( 'Eyebrow', '5drs' ),
					'default' => 'The 5Doctors Collection',
				),
				'heading'      => array(
					'type'    => 'textarea',
					'label'   => __( 'Heading', '5drs' ),
					'default' => 'Care That Fits Every Smile',
				),
				'text'         => array(
					'type'    => 'textarea',
					'label'   => __( 'Text', '5drs' ),
					'default' => 'Explore our Classic, Premium, Charcoal, and Kids toothbrushes - each designed for comfort and an effective daily clean.',
				),
				'button_label' => array(
					'type'        => 'text',
					'label'       => __( 'Button label', '5drs' ),
					'default'     => 'Browse Products',
					'description' => __( 'Scrolls down to the product grid.', '5drs' ),
				),
				'image'        => array(
					'type'         => 'image',
					'label'        => __( 'Hero background image', '5drs' ),
					'default_slug' => '5drs-hero-products',
					'description'  => __( 'Shown full width behind the hero text. Leave empty for a brand gradient.', '5drs' ),
				),
			),
		),

		'cta'        => array(
			'title'  => __( 'Home - Closing call to action', '5drs' ),
			'panel'  => 'home',
			'fields' => array(
				'heading'      => array(
					'type'    => 'text',
					'label'   => __( 'Heading', '5drs' ),
					'default' => 'Ready for a Healthier Smile?',
				),
				'text'         => array(
					'type'    => 'textarea',
					'label'   => __( 'Text', '5drs' ),
					'default' => "Explore the 5Doctors range and find the brush that's right for you.",
				),
				'button_label' => array(
					'type'    => 'text',
					'label'   => __( 'Button label', '5drs' ),
					'default' => 'Shop Now',
				),
				'button_page'  => array(
					'type'         => 'page',
					'label'        => __( 'Button links to', '5drs' ),
					'default_slug' => 'products',
				),
			),
		),
	);
}

/**
 * Sanitiser callback for a field type.
 *
 * @param string $type Field type.
 * @return string
 */
function fivedrs_content_sanitiser( $type ) {
	switch ( $type ) {
		case 'textarea':
			return 'sanitize_textarea_field';
		case 'image':
		case 'page':
		case 'category':
		case 'number':
			return 'absint';
		default:
			return 'sanitize_text_field';
	}
}

/**
 * The setting key for a Home field.
 *
 * @param string $section Section key.
 * @param string $field   Field key.
 * @return string
 */
function fivedrs_content_key( $section, $field ) {
	return 'fivedrs_content_' . $section . '_' . $field;
}

/**
 * Raw value of a Home field, falling back to the configured default.
 *
 * Image, page and category fields resolve their default by slug rather than a
 * hardcoded ID, so the defaults survive a fresh install or a re-import.
 *
 * @param string $section Section key.
 * @param string $field   Field key.
 * @return string|int Empty string or 0 when unset and undefaulted.
 */
function fivedrs_content( $section, $field ) {
	static $config = null;
	if ( null === $config ) {
		$config = fivedrs_content_config();
	}

	if ( ! isset( $config[ $section ]['fields'][ $field ] ) ) {
		return '';
	}

	$spec = $config[ $section ]['fields'][ $field ];
	$type = $spec['type'];
	$key  = fivedrs_content_key( $section, $field );

	$stored = get_theme_mod( $key, null );

	if ( null !== $stored && '' !== $stored ) {
		return in_array( $type, array( 'image', 'page', 'category', 'number' ), true ) ? (int) $stored : (string) $stored;
	}

	// An explicitly emptied text field must stay empty so the section can hide.
	if ( '' === $stored && ! in_array( $type, array( 'image', 'page', 'category', 'number' ), true ) ) {
		return '';
	}

	if ( isset( $spec['default'] ) ) {
		return $spec['default'];
	}

	if ( isset( $spec['default_slug'] ) ) {
		switch ( $type ) {
			case 'image':
				return fivedrs_attachment_id_by_slug( $spec['default_slug'] );
			case 'page':
				$page = get_page_by_path( $spec['default_slug'] );
				return $page ? (int) $page->ID : 0;
			case 'category':
				$term = get_term_by( 'slug', $spec['default_slug'], 'product_cat' );
				return $term ? (int) $term->term_id : 0;
		}
	}

	return in_array( $type, array( 'image', 'page', 'category', 'number' ), true ) ? 0 : '';
}

/**
 * Attachment ID for a media slug, memoised per request.
 *
 * @param string $slug Attachment slug.
 * @return int
 */
function fivedrs_attachment_id_by_slug( $slug ) {
	static $cache = array();

	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}

	$found = get_posts(
		array(
			'name'                   => $slug,
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'numberposts'            => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);

	$cache[ $slug ] = $found ? (int) $found[0] : 0;

	return $cache[ $slug ];
}

/**
 * Permalink for a page field.
 *
 * @param string $section Section key.
 * @param string $field   Field key.
 * @return string Empty string when the page is unset or gone.
 */
function fivedrs_content_page_url( $section, $field ) {
	$page_id = (int) fivedrs_content( $section, $field );

	if ( ! $page_id ) {
		return '';
	}

	$url = get_permalink( $page_id );

	return $url ? $url : '';
}

/**
 * Archive URL for a product category field.
 *
 * @param string $section Section key.
 * @param string $field   Field key.
 * @return string
 */
function fivedrs_content_category_url( $section, $field ) {
	$term_id = (int) fivedrs_content( $section, $field );

	if ( ! $term_id ) {
		return '';
	}

	$url = get_term_link( $term_id, 'product_cat' );

	return is_wp_error( $url ) ? '' : $url;
}

/**
 * Register the Home page panel, its sections and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 * @return void
 */
function fivedrs_customize_register_content( $wp_customize ) {
	$panels = array(
		'home'     => array(
			'title'       => __( '5drs - Home Page', '5drs' ),
			'description' => __( 'All Home page content. Clear a section\'s text to hide that section entirely.', '5drs' ),
			'priority'    => 30,
		),
		'products' => array(
			'title'       => __( '5drs - Products Page', '5drs' ),
			'description' => __( 'The hero on the Our Products page. Clear the text to hide it.', '5drs' ),
			'priority'    => 31,
		),
	);

	foreach ( $panels as $panel_key => $panel_args ) {
		$wp_customize->add_panel( 'fivedrs_' . $panel_key, $panel_args );
	}

	$categories = array( 0 => __( '- none -', '5drs' ) );
	foreach ( get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		)
	) as $term ) {
		if ( ! is_wp_error( $term ) && isset( $term->term_id ) ) {
			$categories[ $term->term_id ] = $term->name;
		}
	}

	foreach ( fivedrs_content_config() as $section => $spec ) {
		$section_id = 'fivedrs_content_' . $section;
		$panel      = isset( $spec['panel'] ) ? $spec['panel'] : 'home';

		$wp_customize->add_section(
			$section_id,
			array(
				'title' => $spec['title'],
				'panel' => 'fivedrs_' . $panel,
			)
		);

		foreach ( $spec['fields'] as $field => $field_spec ) {
			$key     = fivedrs_content_key( $section, $field );
			$default = fivedrs_content( $section, $field );

			$wp_customize->add_setting(
				$key,
				array(
					'default'           => $default,
					'transport'         => 'refresh',
					'sanitize_callback' => fivedrs_content_sanitiser( $field_spec['type'] ),
				)
			);

			$args = array(
				'label'       => $field_spec['label'],
				'description' => isset( $field_spec['description'] ) ? $field_spec['description'] : '',
				'section'     => $section_id,
			);

			switch ( $field_spec['type'] ) {
				case 'image':
					$wp_customize->add_control(
						new WP_Customize_Media_Control(
							$wp_customize,
							$key,
							array_merge( $args, array( 'mime_type' => 'image' ) )
						)
					);
					break;

				case 'page':
					$wp_customize->add_control( $key, array_merge( $args, array( 'type' => 'dropdown-pages' ) ) );
					break;

				case 'category':
					$wp_customize->add_control(
						$key,
						array_merge(
							$args,
							array(
								'type'    => 'select',
								'choices' => $categories,
							)
						)
					);
					break;

				case 'number':
					$wp_customize->add_control(
						$key,
						array_merge(
							$args,
							array(
								'type'        => 'number',
								'input_attrs' => array(
									'min' => 1,
									'max' => 12,
								),
							)
						)
					);
					break;

				case 'textarea':
					$wp_customize->add_control( $key, array_merge( $args, array( 'type' => 'textarea' ) ) );
					break;

				default:
					$wp_customize->add_control( $key, array_merge( $args, array( 'type' => 'text' ) ) );
			}
		}
	}
}
add_action( 'customize_register', 'fivedrs_customize_register_content' );
