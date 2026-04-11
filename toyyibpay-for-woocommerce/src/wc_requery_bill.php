<?php
/**
 * Bill Inquiry/Requery Handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function bill_inquiry($billCode, $OrderId) {

    $order = wc_get_order($OrderId);

    if (!$order) {
        return;
    }

    $order_id = $order->get_id();

    // Only proceed if order is still pending or was auto-cancelled
    if (!in_array($order->get_status(), array('pending', 'cancelled'), true)) {
        return;
    }

    $settings = get_option('woocommerce_toyyibpay_settings');

    $is_sandbox = $settings['enabledev'];
    if ($is_sandbox == "no") {
        $requery = 'https://toyyibpay.com/index.php/api/getBillTransactions';
    } else {
        $requery = 'https://dev.toyyibpay.com/index.php/api/getBillTransactions';
    }

    $post_check = array(
        'body' => array(
            'billCode' 			=> $billCode,
            'billpaymentStatus' => '1'
        )
    );

    $request = wp_remote_post($requery, $post_check);

    if (is_wp_error($request)) {
        $order->add_order_note('toyyibPay requery failed: API is unreachable. Please check payment status manually in your toyyibPay account.<br>Bill Code: ' . sanitize_text_field($billCode));
        return;
    }

    $response = wp_remote_retrieve_body($request);
    $arr      = json_decode($response, true);

    $billpaymentStatus = isset($arr[0]['billpaymentStatus']) ? $arr[0]['billpaymentStatus'] : '';
    $invoiceNo         = isset($arr[0]['billpaymentInvoiceNo']) ? sanitize_text_field($arr[0]['billpaymentInvoiceNo']) : '';

    if ($billpaymentStatus == "1") {
        $order->payment_complete();
        $order->add_order_note('Payment successfully made via toyyibPay.<br>
        Ref. No: ' . esc_html($invoiceNo) . '
        <br>Bill Code: ' . esc_html($billCode) . '
        <br>Order ID: ' . $order_id);
    }
}
add_action('bill_inquiry', 'bill_inquiry', 0, 2);
