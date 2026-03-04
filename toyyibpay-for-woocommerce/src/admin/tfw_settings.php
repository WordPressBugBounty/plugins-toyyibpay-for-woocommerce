<?php

defined('ABSPATH') || exit;

function tfw_get_settings() {
    $saved_settings = get_option('woocommerce_toyyibpay_settings');

    $settings = array();

    $settings['enabled'] = array(
        'title'   => __('Enable / Disable', 'toyyibpay-for-woocommerce'),
        'label'   => __('Enable this payment gateway', 'toyyibpay-for-woocommerce'),
        'type'    => 'checkbox',
        'default' => 'no',
		'description' => __("<span style='color:red'>toyyibPay require customer's phone number during the Checkout process. Please set 'Required' to the Phone Number field in your Checkout Page.</span>", 'toyyibpay-for-woocommerce'),
    );

    $settings['title'] = array(
        'title'    => __('Title', 'toyyibpay-for-woocommerce'),
        'type'     => 'text',
        'default'  => __('toyyibPay', 'toyyibpay-for-woocommerce'),
    );

    $settings['description']  = array(
        'title'    => __('Description', 'toyyibpay-for-woocommerce'),
        'type'     => 'textarea',
        'default'  => __('Pay securely with toyyibPay.', 'toyyibpay-for-woocommerce'),
        'css'      => 'max-width:350px;',
    );

    $settings['display_logo'] = array(
        'title' => __('Checkout Logo', 'toyyibpay-for-woocommerce'),
        'default' => 'horiz',
        'class' => 'wc-enhanced-select',
        'type' => 'select',
        'desc_tip' => false,
        'options' => array(
            'mini' => 'Minimal',
            'horiz' => 'Horizontal',
            'verti' => 'Vertical'
        ),
    );	

    $settings['secretkey_prod'] = array(
        'title'    => __('User SecretKey', 'toyyibpay-for-woocommerce'),
        'type'     => 'text',
        'desc_tip' => __('Required', 'toyyibpay-for-woocommerce'),
        'description' => __('Obtain your secret key from your toyyibPay dashboard.', 'toyyibpay-for-woocommerce'),
    ); 

    $settings['universal_category_prod'] = array(
        'title'    => __('Category Code', 'toyyibpay-for-woocommerce'),
        'type'     => 'text',
        'desc_tip' => __('Required', 'toyyibpay-for-woocommerce'),
        'description' => __('Create a category at your toyyibPay dashboard and fill in your category code here.', 'toyyibpay-for-woocommerce'),
    );

    $settings['checkout'] = array(
        'title' => __('Checkout Settings', 'toyyibpay-for-woocommerce'),
        'type' => 'title',
        'description' => '',
    );

    $settings['universal_channel']  = array(
        'title'   => __('Payment Channel', 'toyyibpay-for-woocommerce'),
        'label'   => __('Payment Channel Options', 'toyyibpay-for-woocommerce'),
        'description' => 'Choose your preferred payment channel - FPX and/or credit cards.',
        'type'    => 'select',
        'options' => array(
            '0' => 'FPX only',
            '1' => 'Credit/Debit Card only',
            '2' => 'FPX and Credit/Debit Card'
        ),
    );


    $settings['universal_charge'] = array(
        'title'   => __('Transaction Charges', 'toyyibpay-for-woocommerce'),
        'label'   => __('Transaction Charges Options', 'toyyibpay-for-woocommerce'),
        'description' => __('Choose payer for transaction charges.', 'toyyibpay-for-woocommerce'),
        'type'    => 'select',
        'options' => array(
            '0' => 'Charge included in bill amount',
            '1' => 'Charge the Online Banking transaction charge on the customer',
        ),
    );


    // eWallet settings - temporarily hidden
    // $settings['enable_ewallet'] = array(
    //     'title'       => __('Enable eWallet', 'toyyibpay-for-woocommerce'),
    //     'type'        => 'checkbox',
    //     'label'       => __('Enable eWallet', 'toyyibpay-for-woocommerce'),
    //     'description' => 'Enable this option to allow eWallet payments through toyyibPay.',
    //     'default'     => 'no',
    // );

    // $settings['ewallet_charge'] = array(
    //     'title'   => __('eWallet Charges', 'toyyibpay-for-woocommerce'),
    //     'label'   => __('eWallet Charges Options', 'toyyibpay-for-woocommerce'),
    //     'description' => __('Choose payer for transaction charges.', 'toyyibpay-for-woocommerce'),
    //     'type'    => 'select',
    //     'options' => array(
    //         '1' => 'Charge included in bill amount',
    //         '0' => 'Charge the transaction charge on the customer',
    //     ),
    // );

    $settings['enable_duitnowqr'] = array(
        'title'       => __('Enable DuitNow QR', 'toyyibpay-for-woocommerce'),
        'type'        => 'checkbox',
        'label'       => __('Enable DuitNow QR', 'toyyibpay-for-woocommerce'),
        'description' => __('<strong style="color:#d63638;">Important:</strong> Only enable this if your toyyibPay account has DuitNow QR activated. If not activated, customers will see an error during checkout. Contact toyyibPay support to activate DuitNow QR for your account.<br><br><strong style="color:#2271b1;">Note:</strong> DuitNow QR currently does not support Split Payment. If both are enabled and customer pays via DuitNow QR, the merchant will receive the full settlement amount (split will be ignored).', 'toyyibpay-for-woocommerce'),
        'default'     => 'no',
    );

    $settings['duitnowqr_charge'] = array(
        'title'   => __('DuitNow QR Charges', 'toyyibpay-for-woocommerce'),
        'label'   => __('DuitNow QR Charges Options', 'toyyibpay-for-woocommerce'),
        'description' => __('Choose who pays the DuitNow QR admin fee (RM1 or 1%, whichever is higher).', 'toyyibpay-for-woocommerce'),
        'type'    => 'select',
        'options' => array(
            '1' => 'Charge included in bill amount (Bill Owner pays)',
            '0' => 'Charge the admin fee to the customer',
        ),
    );

    $settings['content_email'] = array(
        'title'    => __('Extra e-mail content (Optional)', 'toyyibpay-for-woocommerce'),
        'type'     => 'textarea',
        'desc_tip' => false,
        'description' => 'Content of additional e-mail to be sent to your customers (Optional - leave this blank if you are not sure what to write).',
        'default'  => '',
        'css'      => 'max-width:350px;',
    );

    $settings['split'] = array(
        'title' => __('Split Payment', 'toyyibpay-for-woocommerce'),
        'type' => 'title',
        'description' => __('Enable this feature only if you wish to split the received payment amount from your customer to other toyyibPay account. Do not enable this if you are not sure or do not want to split the received amount.<br><br><strong style="color:#2271b1;">Note:</strong> Split Payment currently only works with FPX and Credit Card payments. DuitNow QR payments do not support split - if customer pays via DuitNow QR, merchant will receive full settlement amount.', 'toyyibpay-for-woocommerce'),
    );

    $settings['enablesplit'] = array(
        'title' => __('Enable/Disable ', 'toyyibpay-for-woocommerce'),
        'type' => 'checkbox',
        'label' => __('Enable Split Payment', 'toyyibpay-for-woocommerce'),
        'description' => 'By enabling Split Payment, The transaction amount will be splitted to another (1) toyyibPay account.',
        'default' => 'no',
    );

    $settings['splitmethod'] = array(
        'title'   => __('Split method', 'toyyibpay-for-woocommerce'),
        'label'   => __('Split Method Options', 'toyyibpay-for-woocommerce'),
        'description' => __('Choose to split by percentage or fix amount.', 'toyyibpay-for-woocommerce'),
        'type'    => 'select',
        'options' => array(
            '0' => 'Percentage',
            '1' => 'Fix amount'
        ),
    );

    $settings['splitusername'] = array(
        'title'    => __('Receiver Username', 'toyyibpay-for-woocommerce'),
        'description' => __('Username of the toyyibPay account (1 username only - not your account username).', 'toyyibpay-for-woocommerce'),
        'type'     => 'text',
    );

    $settings['splitpercent'] = array(
        'title'    => __('Split Percentage (%)', 'toyyibpay-for-woocommerce'),
        'description' => __('Enter the percentage to split (Numbers only between 1 to 90).', 'toyyibpay-for-woocommerce'),
        'type'     => 'number',
    );

    $settings['splitfixamount'] = array(
        'title'    => __('Split Fix Amount', 'toyyibpay-for-woocommerce'),
        'description' => __('Enter the fix amount to split (Numbers only, split will occur if this amount is less than the total checkout amount by customers).', 'toyyibpay-for-woocommerce'),
        'type'     => 'number',
    );

    $settings['develop'] = array(
        'title' => __('Development Mode', 'toyyibpay-for-woocommerce'),
        'type' => 'title',
        'description' => __('This is for testing purposes. Please create an account in <a href="https://dev.toyyibpay.com">dev.toyyibpay.com</a> if you does not have one.<br>Use these banks only for testing in sandbox<br><b>SBI Bank A for success payments.</b><br><b>SBI Bank B for fail payments.</b><br><b>SBI Bank C for random possibilities.</b><br>(Username: 1234, Password: 1234)', 'toyyibpay-for-woocommerce'),
    );

    $settings['enabledev'] = array(
        'title' => __('Enable/Disable ', 'toyyibpay-for-woocommerce'),
        'type' => 'checkbox',
        'label' => __('Enable Development Mode', 'toyyibpay-for-woocommerce'),
        'description' => 'By enabling development mode, you will redirect to dev.toyyibpay.com instead of toyyibpay.com.',
        'default' => 'no',
    );

    $settings['secretkey_dev']  = array(
        'title'    => __('User Secret Key Dev', 'toyyibpay-for-woocommerce'),
        'description' => __('Fill in your development secret key here.', 'toyyibpay-for-woocommerce'),
        'type'     => 'text',
        'desc_tip' => __('Obtain your secret key from your development acccount.', 'toyyibpay-for-woocommerce'),
    ); 
	
    $settings['universal_category_dev'] = array(
        'title'    => __('Category Code', 'toyyibpay-for-woocommerce'),
        'description' => __('Fill in your development category code here.', 'toyyibpay-for-woocommerce'),
        'type'     => 'text',
        'desc_tip' => __('Obtain your category code from your development acccount.', 'toyyibpay-for-woocommerce'),
    );

    $settings['splitusername_dev'] = array(
        'title'    => __('Split Receiver Username', 'toyyibpay-for-woocommerce'),
        'description' => __('Username of the toyyibPay sandbox account (1 username only - not your account username).', 'toyyibpay-for-woocommerce'),
        'type'     => 'text',
    );

    return $settings;
}


function tfw_get_settings_defaults() {
    $settings = tfw_get_settings();
    $defaults = array();

    foreach ($settings as $key => $value) {
        if (isset($value['default'])) {
            $defaults[$key] = $value['default'];
        } else {
            $defaults[$key] = null;
        }
    }

    return $defaults;
}
