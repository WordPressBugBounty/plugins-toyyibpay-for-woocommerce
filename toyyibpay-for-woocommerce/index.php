<?php
/**
 * Plugin Name: toyyibPay for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/toyyibpay-for-woocommerce/#installation
 * Description: Integrate your WooCommerce site with toyyibPay Payment Gateway.
 * Version: 2.0.1
 * Author: toyyibPay
 * Author URI: https://toyyibpay.com
 * Requires at least: 6.0
 * Tested up to: 6.9
 * Requires PHP: 7.0
 * WC requires at least: 7.0
 * WC tested up to: 9.5
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: toyyibpay-for-woocommerce
 * Domain Path: /languages
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define('TFW_PLUGIN_VER', '2.0.1');
define('TFW_MIN_PHP_VER', '7.0');
define('TFW_MIN_WOOCOMMERCE_VER', '7.0');
define('TFW_PLUGIN_FILE', __FILE__);
define('TFW_PLUGIN_DIR', dirname(TFW_PLUGIN_FILE));
define('TFW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TFW_BASENAME', plugin_basename(TFW_PLUGIN_FILE));

/**
 * Declare HPOS compatibility
 */
add_action('before_woocommerce_init', function() {
	if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
	}
});

/**
 * Admin notices storage
 */
global $tfw_admin_notices;
$tfw_admin_notices = array();

/**
 * Add admin notice
 */
function tfw_add_admin_notice($slug, $class, $message) {
	global $tfw_admin_notices;
	$tfw_admin_notices[$slug] = array(
		'class' => $class,
		'message' => $message,
	);
}

/**
 * Display admin notices
 */
add_action('admin_notices', function() {
	global $tfw_admin_notices;
	if (!empty($tfw_admin_notices)) {
		foreach ($tfw_admin_notices as $slug => $notice) {
			printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				esc_attr($notice['class']),
				wp_kses_post($notice['message'])
			);
		}
	}

	// Check for DuitNow QR not activated notice
	if (get_transient('tfw_duitnowqr_not_activated')) {
		delete_transient('tfw_duitnowqr_not_activated');
		printf(
			'<div class="notice notice-warning is-dismissible"><p><strong>%s</strong> %s</p></div>',
			esc_html__('toyyibPay Warning:', 'toyyibpay-for-woocommerce'),
			esc_html__('DuitNow QR is enabled but your toyyibPay account does not have DuitNow QR activated. Customers will see an error during checkout. Please contact toyyibPay support to activate DuitNow QR for your account, or disable this option.', 'toyyibpay-for-woocommerce')
		);
	}
});

/**
 * Check environment on admin init
 */
add_action('admin_init', 'tfw_check_environment');

/**
 * Initialize plugin
 */
add_action('plugins_loaded', 'toyyibpay_init', 0);

/**
 * Register blocks support
 */
add_action('woocommerce_blocks_loaded', 'tfw_blocks_support');

/**
 * Initialize toyyibPay gateway
 */
function toyyibpay_init() {
	if (!class_exists('WooCommerce')) {
		return false;
	}

	if (is_admin()) {
		require_once TFW_PLUGIN_DIR . '/src/admin/tfw_action_links.php';
		require_once TFW_PLUGIN_DIR . '/src/admin/tfw_settings.php';
	}

	require_once TFW_PLUGIN_DIR . '/src/wc_toyyibpay_gateway.php';
	require_once TFW_PLUGIN_DIR . '/src/wc_requery_bill.php';
}

/**
 * Register WooCommerce Blocks support
 */
function tfw_blocks_support() {
	if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
		require_once TFW_PLUGIN_DIR . '/src/wc_toyyibpay_blocks_support.php';

		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function(Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
				$payment_method_registry->register(new WC_ToyyibPay_Blocks_Support());
			}
		);
	}
}

/**
 * Check environment requirements
 */
function tfw_check_environment() {
	$environment_warning = tfw_get_environment_warning();

	if ($environment_warning && is_plugin_active(TFW_BASENAME)) {
		deactivate_plugins(TFW_BASENAME);
		tfw_add_admin_notice('bad_environment', 'error', $environment_warning);
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Standard WP plugin activation flow
		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}
		return false;
	}

	$is_woocommerce_active = class_exists('WooCommerce');

	if (is_admin() && current_user_can('activate_plugins') && !$is_woocommerce_active) {
		tfw_add_admin_notice(
			'prompt_tfw_activate',
			'error',
			sprintf(
				/* translators: %s: WooCommerce URL */
				__('<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">WooCommerce</a> plugin installed and activated for toyyibPay to activate.', 'toyyibpay-for-woocommerce'),
				'https://woocommerce.com'
			)
		);
		deactivate_plugins(TFW_BASENAME);
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Standard WP plugin activation flow
		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}
		return false;
	}

	if (defined('WC_VERSION') && version_compare(WC_VERSION, TFW_MIN_WOOCOMMERCE_VER, '<')) {
		tfw_add_admin_notice(
			'prompt_woocommerce_version_update',
			'error',
			sprintf(
				/* translators: 1: WooCommerce URL, 2: Minimum WooCommerce version */
				__('<strong>Activation Error:</strong> You must have the <a href="%1$s" target="_blank">WooCommerce</a> core version %2$s+ for the toyyibPay for WooCommerce add-on to activate.', 'toyyibpay-for-woocommerce'),
				'https://woocommerce.com',
				TFW_MIN_WOOCOMMERCE_VER
			)
		);
		deactivate_plugins(TFW_BASENAME);
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Standard WP plugin activation flow
		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}
		return false;
	}

	return true;
}

/**
 * Get environment warning message
 */
function tfw_get_environment_warning($during_activation = false) {
	if (version_compare(phpversion(), TFW_MIN_PHP_VER, '<')) {
		if ($during_activation) {
			/* translators: 1: Minimum PHP version, 2: Current PHP version */
			$message = __('The plugin could not be activated. The minimum PHP version required for this plugin is %1$s. You are running %2$s. Please contact your web host to upgrade your server\'s PHP version.', 'toyyibpay-for-woocommerce');
		} else {
			/* translators: 1: Minimum PHP version, 2: Current PHP version */
			$message = __('The plugin has been deactivated. The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'toyyibpay-for-woocommerce');
		}
		return sprintf($message, TFW_MIN_PHP_VER, phpversion());
	}

	if (!class_exists('WC_Payment_Gateway')) {
		if ($during_activation) {
			return __('The plugin could not be activated. toyyibPay for WooCommerce depends on the latest version of <a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a> to work.', 'toyyibpay-for-woocommerce');
		}
		return __('The plugin has been deactivated. toyyibPay for WooCommerce depends on the latest version of <a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a> to work.', 'toyyibpay-for-woocommerce');
	}

	return false;
}

/**
 * Check toyyibPay response on init
 */
add_action('init', 'toyyibpay_check_response', 15);

function toyyibpay_check_response() {
	// If the parent WC_Payment_Gateway class doesn't exist it means WooCommerce is not installed
	if (!class_exists('WC_Payment_Gateway')) {
		return;
	}

	require_once TFW_PLUGIN_DIR . '/src/wc_toyyibpay_gateway.php';

	$toyyibpay = new WC_ToyyibPay_Gateway();
	$toyyibpay->check_toyyibpay_response();
	// Callback is handled exclusively via WC-API endpoint (/wc-api/callback/)
	// to preserve POST data. See woocommerce_api_callback action in gateway class.
}
