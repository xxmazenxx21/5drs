<?php
/**
 * Creates a throwaway WooCommerce order and fires its customer + admin emails,
 * so you can confirm mail delivery in Mailpit (http://localhost:8025).
 *
 * Run with: make mail-test
 *
 * The order is real - delete it from WooCommerce > Orders when you are done.
 */
$product_id = wc_get_product_id_by_sku( 'TEST-001' );
$order = wc_create_order();
$order->add_product( wc_get_product( $product_id ), 2 );
$address = array(
	'first_name' => 'Mailpit',
	'last_name'  => 'Test',
	'email'      => 'customer@5drs.local',
	'phone'      => '0100000000',
	'address_1'  => '1 Test Street',
	'city'       => 'Cairo',
	'state'      => 'C',
	'country'    => 'EG',
	'postcode'   => '11511',
);
$order->set_address( $address, 'billing' );
$order->set_address( $address, 'shipping' );
$order->calculate_totals();
$order->update_status( 'processing', 'Mailpit smoke test', true );

WC()->mailer();
do_action( 'woocommerce_order_status_pending_to_processing_notification', $order->get_id(), $order );

WP_CLI::success( 'Order #' . $order->get_id() . ' created, total ' . strip_tags( $order->get_formatted_order_total() ) );
