<?php
/**
 * toyyibPay Payment Gateway Class
 *
 * @package toyyibPay_WooCommerce
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Add toyyibPay gateway to WooCommerce
 */
function ty_add_gateway($methods) {
	$methods[] = 'WC_ToyyibPay_Gateway';
	return $methods;
}
add_filter('woocommerce_payment_gateways', 'ty_add_gateway');

/**
 * WC_ToyyibPay_Gateway Class
 */
class WC_ToyyibPay_Gateway extends WC_Payment_Gateway {

	/**
	 * Logger enabled
	 * @var bool
	 */
	public static $log_enabled = false;

	/**
	 * Logger instance
	 * @var WC_Logger|false
	 */
	public static $log = false;

	/**
	 * Gateway ID
	 * @var string
	 */
	public static $gateway_id = 'toyyibpay';

	/**
	 * Settings properties
	 */
	public $display_logo = '';
	public $secretkey_prod = '';
	public $universal_category_prod = '';
	public $universal_channel = '0';
	public $universal_charge = '0';
	public $enable_duitnowqr = 'no';
	public $duitnowqr_charge = '1';
	public $content_email = '';
	public $enablesplit = 'no';
	public $splitmethod = '0';
	public $splitusername = '';
	public $splitpercent = '';
	public $splitfixamount = '';
	public $enabledev = 'no';
	public $secretkey_dev = '';
	public $universal_category_dev = '';
	public $splitusername_dev = '';
	public $checkout = '';
	public $split = '';
	public $develop = '';

	/**
	 * Error messages
	 * @var array
	 */
	private $error_messages = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = self::$gateway_id;
		$this->method_title       = __('toyyibPay', 'toyyibpay-for-woocommerce');
		$this->method_description = __('Enable your customers to make payments securely via toyyibPay.', 'toyyibpay-for-woocommerce');
		$this->title              = __('toyyibPay', 'toyyibpay-for-woocommerce');
		$this->order_button_text  = __('Pay with toyyibPay', 'toyyibpay-for-woocommerce');
		$this->has_fields         = true;
		$this->supports           = array('products');

		// Load settings
		if (is_admin()) {
			$this->init_form_fields();
		}
		$this->init_settings();

		// Set settings to properties
		foreach ($this->settings as $setting_key => $value) {
			if (property_exists($this, $setting_key)) {
				$this->$setting_key = $value;
			}
		}

		// Set checkout icon based on payment channel and logo preference
		$this->set_checkout_icon();

		// Admin hooks
		if (is_admin()) {
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'validate_duitnowqr_settings'), 20);
		}

		$this->woocommerce_add_action();
	}

	private function woocommerce_add_action()
	{
		add_action('woocommerce_api_callback', array($this, 'check_toyyibpay_callback'));
	}

	/**
	 * Set checkout icon based on payment channel and logo preference
	 */
	private function set_checkout_icon()
	{
		$assets_url = TFW_PLUGIN_URL . 'src/assets/';

		// FPX only channel (0) uses FPX logos, otherwise use all-payment logos
		if ($this->universal_channel == '0') {
			if ($this->display_logo == 'mini') {
				$this->icon = $assets_url . 'mini-fpx.png';
			} elseif ($this->display_logo == 'horiz') {
				$this->icon = $assets_url . 'hor-fpx.png';
			} else {
				$this->icon = $assets_url . 'ver-fpx.png';
			}
		} else {
			if ($this->display_logo == 'mini') {
				$this->icon = $assets_url . 'mini-all.png';
			} elseif ($this->display_logo == 'horiz') {
				$this->icon = $assets_url . 'hor-all.png';
			} else {
				$this->icon = $assets_url . 'ver-all.png';
			}
		}
	}


	# Build the administration fields for this specific Gateway
	public function init_form_fields()
	{
		$this->form_fields = apply_filters('tfw_form_fields', tfw_get_settings());
	}

	/**
	 * Validate DuitNow QR settings after save
	 * Check if merchant has DuitNow QR activated in their toyyibPay account
	 */
	public function validate_duitnowqr_settings()
	{
		$settings = get_option('woocommerce_toyyibpay_settings');

		// Only validate if DuitNow QR is being enabled
		if (!isset($settings['enable_duitnowqr']) || $settings['enable_duitnowqr'] !== 'yes') {
			return;
		}

		// Determine which secret key to use
		$secretkey = '';
		if (isset($settings['enabledev']) && $settings['enabledev'] === 'yes') {
			$secretkey = isset($settings['secretkey_dev']) ? $settings['secretkey_dev'] : '';
		} else {
			$secretkey = isset($settings['secretkey_prod']) ? $settings['secretkey_prod'] : '';
		}

		if (empty($secretkey)) {
			return;
		}

		// Determine API URL
		$url = (isset($settings['enabledev']) && $settings['enabledev'] === 'yes')
			? 'https://dev.toyyibpay.com/index.php/api/checkDuitNowQRStatus'
			: 'https://toyyibpay.com/index.php/api/checkDuitNowQRStatus';

		// Call API to check DuitNow QR status
		$response = wp_remote_post($url, array(
			'body' => array(
				'userSecretKey' => $secretkey
			),
			'timeout' => 30
		));

		if (is_wp_error($response)) {
			return; // Silently fail if API is unreachable
		}

		$body = wp_remote_retrieve_body($response);
		$result = json_decode($body, true);

		// If DuitNow QR is not activated, show warning notice
		if (isset($result['status']) && $result['status'] === 'success') {
			if (isset($result['duitnowqr_activated']) && $result['duitnowqr_activated'] === false) {
				// Store transient to show admin notice
				set_transient('tfw_duitnowqr_not_activated', true, 60);
			}
		}
	}


	# Submit payment
	public function process_payment($order_id)
	{
		# Get this order's information so that we know who to charge and how much
		$customer_order = wc_get_order($order_id);

		$settings = get_option('woocommerce_toyyibpay_settings');
		# Prepare the data to send to toyyibPay

		$billName = "Order No " . $order_id;
		$description = "Payment for Order No " .  $order_id;
		$payChannel = $settings['universal_channel'];
		$extraEmail = $settings['content_email'];
		$callbackURL = WC()->api_request_url('callback');

		$universal_charge = $settings['universal_charge'];

		if ($universal_charge == "0") {
			$billTransactionCharge = '';
		} else {
			$billTransactionCharge = '0';
		}

		// eWallet - temporarily hidden
		$CTCustomerEwallet = '1'; // Default value while eWallet is hidden

		$order_id = $customer_order->get_id();
		$amount   = $customer_order->get_total();
		$name     = $customer_order->get_billing_first_name() . ' ' . $customer_order->get_billing_last_name();
		$email    = $customer_order->get_billing_email();
		$phone    = $customer_order->get_billing_phone();
		$returnURL = add_query_arg('key', $customer_order->get_order_key(), wc_get_endpoint_url('order-received', '', wc_get_checkout_url()));

		if ($name == NULL || $phone == NULL || $email == NULL) {
			wc_add_notice('Error! Please complete your details (Name, phone, and e-mail are compulsory).', 'error');
			return;
		}


		# Create bill API from toyyibpay

		if ($settings['enabledev'] == "no") {

			$secretkey = $settings['secretkey_prod'];
			$categorycode = $settings['universal_category_prod'];
			$url = 'https://toyyibpay.com/index.php/api/createBill';
			$redirect = "https://toyyibpay.com/";
		} else {

			$secretkey = $settings['secretkey_dev'];
			$categorycode = $settings['universal_category_dev'];
			$url = 'https://dev.toyyibpay.com/index.php/api/createBill';
			$redirect = "https://dev.toyyibpay.com/";
		}

		# EWALLET - temporarily hidden
		$enableEwallet = '0'; // eWallet disabled while hidden

		# DUITNOW QR
		if (isset($settings['enable_duitnowqr']) && $settings['enable_duitnowqr'] == "yes") {
			$enableDuitNowQR = '1';
			// Charge to customer (0) or bill owner (1)
			$chargeDuitNowQR = isset($settings['duitnowqr_charge']) && $settings['duitnowqr_charge'] == '0' ? '1' : '0';
		} else {
			$enableDuitNowQR = '0';
			$chargeDuitNowQR = '';
		}

		# enable split
		if ($settings['enablesplit'] == "no") {
			$enableSplit = '0';
		} else {
			$enableSplit = '1';
		}

		if ($enableSplit == '1') {

			if ($settings['enabledev'] == "no") {
				$splitusername = $settings['splitusername'];
			} else {
				$splitusername = $settings['splitusername_dev'];
			}

			if ($settings['splitmethod'] == 0 || $settings['splitmethod'] == '0') {
				$splitAmount = ($settings['splitpercent'] / 100) * $amount;
			} else {
				$splitAmount = $settings['splitfixamount'];
			}

			$splitArgs = '[{"id":"' . $splitusername . '","amount":"' . $splitAmount * 100 . '"}]';
		} else {
			$splitArgs = '';
		}

		$post_args = array(
			'body' => array(
				'userSecretKey' 			=> $secretkey,
				'categoryCode' 				=> $categorycode,
				'billName' 					=> $billName,
				'billDescription' 			=> $description,
				'billPriceSetting'			=>	1,
				'billPayorInfo'				=>	1,
				'billAmount'				=>	$amount * 100,
				'billReturnUrl'				=>	$returnURL,
				'billCallbackUrl'			=>	$callbackURL,
				'billExternalReferenceNo' 	=>	$order_id,
				'billTo'					=>	$name,
				'billEmail'					=>	$email,
				'billPhone'					=>	$phone,
				'enableEwallet'				=>	$enableEwallet,
				'billSplitPayment'			=>	$enableSplit,
				'billSplitPaymentArgs'		=>	$splitArgs,
				'billPaymentChannel'		=>	$payChannel,
				'billDisplayMerchant'		=>	1,
				'billContentEmail'			=>	$extraEmail,
				'billChargeToCustomer'		=>	$billTransactionCharge,
				'billEwalletCTCustomer'		=>	$CTCustomerEwallet,
				'enableDuitNowQR'			=>	$enableDuitNowQR,
				'chargeDuitNowQR'			=>	$chargeDuitNowQR,
				'billASPCode'				=>  'toyyibPay-V1-WCV2.0.1'
			)
		);


		$request 	= wp_remote_post($url, $post_args);
		$response 	= wp_remote_retrieve_body($request);
		$arr 		= json_decode($response, true);
		$billCode 	= $arr[0]['BillCode'] ?? NULL;

		$order_note = wc_get_order($order_id);

		if ($billCode == NULL) {

			$arr = [json_decode($response, true)];
			$msg = $arr[0]['msg'] ?? NULL;

			if ($msg == NULL) {
				return array(
					'result'   => 'failure',
					'messages' => wc_add_notice( esc_html__( 'Error! Please check the following: ', 'toyyibpay-for-woocommerce' ) . esc_html( $response ), 'error')
				);
			} else {
				return array(
					'result'   => 'failure',
					'messages' => wc_add_notice( esc_html__( 'Error! Please check the following: ', 'toyyibpay-for-woocommerce' ) . esc_html( $msg ), 'error')
				);
			}

			return;
		} else {
			$arguments = array($billCode, $order_id);

			// Use 6 minutes for DuitNow QR (QR expires in 5 min), 3 minutes for other payments
			$requery_delay = ($enableDuitNowQR == '1') ? 6 * MINUTE_IN_SECONDS : 3 * MINUTE_IN_SECONDS;

			// Use Action Scheduler (bundled with WooCommerce) for more reliable scheduling
			if (function_exists('as_schedule_single_action')) {
				as_schedule_single_action(time() + $requery_delay, 'bill_inquiry', $arguments, 'toyyibpay');
			} else {
				wp_schedule_single_event(time() + $requery_delay, 'bill_inquiry', $arguments);
			}

			$order_note->add_order_note('Customer made a payment attempt via toyyibPay.<br>Bill Code : ' . sanitize_text_field($billCode) . '<br>You can check the payment status of this bill in toyyibPay account.');

			return array(
				'result'   => 'success',
				'redirect' => $redirect . $billCode
			);
		}
	}

	public static function get_listener_url($order_id)
	{

		$arg = array(
			'order'       => $order_id,
			'message_type' => 'toyyibpay_bill_callback'
		);
		return add_query_arg($arg, site_url('/'));
	}

	/**
	 * Handle toyyibPay return URL response
	 *
	 * Note: Nonce verification is not applicable here as this handles
	 * external payment gateway return redirects from toyyibPay.
	 */
	public function check_toyyibpay_response()
	{
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- External payment gateway return URL
		// Use $_GET only — the return URL is a browser redirect (GET).
		// Using $_REQUEST would also match callback POST data and intercept it.
		if (isset($_GET['status_id']) && isset($_GET['billcode']) && isset($_GET['order_id']) && isset($_GET['msg']) && isset($_GET['transaction_id'])) {
			$order_id_raw = absint(wp_unslash($_GET['order_id']));
			$status_id    = sanitize_text_field(wp_unslash($_GET['status_id']));
			$order_key    = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';

			$order = wc_get_order($order_id_raw);

			if ($order && $order->get_id() != 0) {

				// Validate order key to prevent spoofed return URLs
				if (!$order->key_is_valid($order_key)) {
					wc_add_notice(__('Invalid order key. Please contact the site administrator.', 'toyyibpay-for-woocommerce'), 'error');
					wp_safe_redirect(wc_get_checkout_url());
					exit;
				}

				if ($status_id == '1') {

					if (in_array($order->get_status(), array('cancelled', 'pending', 'processing'), true)) {

						wp_safe_redirect($order->get_checkout_order_received_url());
						exit;
					}
				} elseif ($status_id == '3') {
					if (in_array($order->get_status(), array('cancelled', 'pending', 'processing'), true)) {

						wc_add_notice(__('Payment was declined. Reason: Bank error / insufficient fund', 'toyyibpay-for-woocommerce'), 'error');
						wp_safe_redirect(wc_get_checkout_url());
						exit;
					}
				} else {
					if (in_array($order->get_status(), array('pending', 'processing'), true)) {

						wc_add_notice(__('Payment was declined. Reason: Payment is pending, please contact site admin to get your payment status', 'toyyibpay-for-woocommerce'), 'error');
						wp_safe_redirect(wc_get_checkout_url());
						exit;
					}
				}
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	# Validate fields, do nothing for the moment
	public function validate_fields()
	{
		return true;
	}

	# Check if we are forcing SSL on checkout pages, Custom function not required by the Gateway for now
	public function do_ssl_check()
	{
		$settings = get_option('woocommerce_toyyibpay_settings');

		if ($settings['enabled'] == "yes") {
			if (get_option('woocommerce_force_ssl_checkout') == "no") {
				echo '<div class="error"><p>';
				printf(
					/* translators: 1: Payment method title, 2: WooCommerce checkout settings URL */
					wp_kses_post(__('<strong>%1$s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href="%2$s">forcing the checkout pages to be secured.</a>', 'toyyibpay-for-woocommerce')),
					esc_html($this->method_title),
					esc_url(admin_url('admin.php?page=wc-settings&tab=checkout'))
				);
				echo '</p></div>';
			}
		}
	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 * Note: Not used for the time being
	 * @return bool
	 */
	public function is_valid_for_use()
	{
		return in_array(get_woocommerce_currency(), array('MYR'));
	}

	/**
	 * Handle toyyibPay server-to-server callback
	 *
	 * Note: Nonce verification is not applicable here as this handles
	 * external payment gateway server-to-server callbacks from toyyibPay.
	 * Authentication is done via MD5 hash verification using the secret key.
	 */
	public function check_toyyibpay_callback()
	{
		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- External payment gateway callback
		$settings = get_option('woocommerce_toyyibpay_settings');

		if ($settings['enabledev'] == "no") {
			$secretkey = $settings['secretkey_prod'];
		} else {
			$secretkey = $settings['secretkey_dev'];
		}

		if (isset($_REQUEST['status']) && isset($_REQUEST['billcode']) && isset($_REQUEST['order_id']) && isset($_REQUEST['reason']) && isset($_REQUEST['refno'])) {

			$order_id_raw   = absint(wp_unslash($_REQUEST['order_id']));
			$status         = sanitize_text_field(wp_unslash($_REQUEST['status']));
			$billcode       = sanitize_text_field(wp_unslash($_REQUEST['billcode']));
			$reason         = sanitize_text_field(wp_unslash($_REQUEST['reason']));
			$refno          = sanitize_text_field(wp_unslash($_REQUEST['refno']));
			$transaction_id = isset($_REQUEST['transaction_id']) ? sanitize_text_field(wp_unslash($_REQUEST['transaction_id'])) : '';

			$post_status   = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : '';
			$post_order_id = isset($_POST['order_id']) ? sanitize_text_field(wp_unslash($_POST['order_id'])) : '';
			$post_refno    = isset($_POST['refno']) ? sanitize_text_field(wp_unslash($_POST['refno'])) : '';
			$post_hash     = isset($_POST['hash']) ? sanitize_text_field(wp_unslash($_POST['hash'])) : '';

			$order = wc_get_order($order_id_raw);
			$order_id = $order ? $order->get_id() : 0;

			if ($order && $order_id != 0) {

				$userSecretKey  = $secretkey;
				$hashval        = md5($userSecretKey . $post_status . $post_order_id . $post_refno . "ok");

				if (hash_equals($hashval, $post_hash)) {
					if ($status == '1') {

						if (in_array($order->get_status(), array('cancelled', 'pending', 'processing'), true)) {

							if (in_array($order->get_status(), array('cancelled', 'pending'), true)) {
								$order->add_order_note('Payment is successfully made through toyyibPay!<br>
								Ref. No: ' . esc_html($refno) . '
								<br>Bill Code: ' . esc_html($billcode) . '
								<br>Order ID: ' . $order_id);
								$order->payment_complete();
							}
						}
					} elseif ($status == '3') {
						if (in_array($order->get_status(), array('cancelled', 'pending', 'processing'), true)) {

							if (in_array($order->get_status(), array('cancelled', 'pending'), true)) {
								$order->add_order_note('Payment attempt was failed.<br>
								Ref. No: ' . esc_html($transaction_id) . '
								<br>Bill Code: ' . esc_html($billcode) . '
								<br>Order ID: ' . $order_id . '
								<br>Reason: ' . esc_html($reason));
							}
						}
					} else {
						if (in_array($order->get_status(), array('cancelled', 'pending'), true)) {

							if ($settings['enabledev'] == "no") {
								$urlCheck = 'https://toyyibpay.com/index.php/api/getBillTransactions';
							} else {
								$urlCheck = 'https://dev.toyyibpay.com/index.php/api/getBillTransactions';
							}
							$post_check = array(
								'body' => array(
									'billCode' 			=> $billcode,
									'billpaymentStatus' => '1'
								)
							);

							$requestCheck = wp_remote_post($urlCheck, $post_check);

							if (is_wp_error($requestCheck)) {
								$order->add_order_note('Payment status could not be verified. toyyibPay API is unreachable. Please check manually in your toyyibPay account.');
								return;
							}

							$responseCheck = wp_remote_retrieve_body($requestCheck);
							$arrCheck = json_decode($responseCheck, true);
							$billpaymentStatus = isset($arrCheck[0]['billpaymentStatus']) ? $arrCheck[0]['billpaymentStatus'] : '';

							if ($billpaymentStatus == 1 || $billpaymentStatus == "1") {

								$order->add_order_note('Payment successfully made through toyyibPay!<br>
									Ref. No: ' . esc_html($transaction_id) . '
									<br>Bill Code: ' . esc_html($billcode) . '
									<br>Order ID: ' . $order_id);
								$order->payment_complete();
							} elseif ($billpaymentStatus == 3 || $billpaymentStatus == "3") {
								if (in_array($order->get_status(), array('cancelled', 'pending', 'processing'), true)) {

									if ($order->get_status() == 'pending') {
										$order->add_order_note('Payment attempt was failed.<br>
                                            Ref. No: ' . esc_html($transaction_id) . '
                                            <br>Bill Code: ' . esc_html($billcode) . '
                                            <br>Order ID: ' . $order_id . '
                                            <br>Reason: ' . esc_html($reason));
									}
								}
							} else {
								if (in_array($order->get_status(), array('cancelled', 'pending', 'processing'), true)) {

									if ($order->get_status() == 'pending') {
										$order->add_order_note('Payment status pending. Please check in your toyyibPay account for the latest status.<br>
                                            Ref. No: ' . esc_html($transaction_id) . '
                                            <br>Bill Code: ' . esc_html($billcode) . '
                                            <br>Order ID: ' . $order_id . '
                                            <br>Reason: ' . esc_html($reason));
									}
								}
							}
						}
					}
				} else {

					$order->add_order_note('Payment attempt was failed.<br>
							Ref. No: ' . esc_html($transaction_id) . '
							<br>Bill Code: ' . esc_html($billcode) . '
							<br>Order ID: ' . $order_id . '
							<br>Reason: Payment has failed to complete.');
				}
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
	}
}
