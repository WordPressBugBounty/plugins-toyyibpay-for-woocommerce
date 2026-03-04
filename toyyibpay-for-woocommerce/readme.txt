=== toyyibPay for WooCommerce ===
Contributors: toyyibPay, zahiruliman
Tags: payment gateway, Malaysia, fpx, woocommerce, duitnow
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 2.0.0
Requires PHP: 7.0
WC requires at least: 7.0
WC tested up to: 9.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The official toyyibPay payment gateway plugin for WooCommerce — enabling Malaysian merchants to accept secure online payments with ease.

== Description ==

toyyibPay for WooCommerce is a robust payment gateway integration that allows Malaysian merchants to seamlessly accept payments on their WooCommerce store. Powered by [toyyibPay](https://toyyibpay.com), one of Malaysia's leading payment service providers, this plugin offers a straightforward setup with no hidden fees.

> Our pricing is always per transaction. No startup fees, no monthly fees, and no gateway fees. No hidden fees, period.

**Currently available to businesses registered and operating in Malaysia.**

= Supported Payment Modes =

* **FPX Online Banking** — Direct bank transfer via Financial Process Exchange (FPX), supporting all major Malaysian banks
* **Credit / Debit Card** — Visa and Mastercard payments for local and international customers
* **DuitNow QR** *(New in 2.0.0)* — Instant QR-based payments via the DuitNow network
* **Split Payment** — Automatically split payment proceeds between multiple toyyibPay accounts

= Features =

* Multiple payment modes: FPX, Credit/Debit Card, DuitNow QR, and Split Payment
* HPOS (High-Performance Order Storage) compatible
* WooCommerce Blocks checkout support
* Seamless integration with the WooCommerce payments settings
* Configurable admin fee handling for DuitNow QR
* Sandbox/development mode for testing before going live

Please go to the [signup page](https://toyyibpay.com/access/registration) to create a toyyibPay account and start receiving payments.

Contact us on our [Facebook Page](https://www.facebook.com/toyyibpay) if you have any questions or comments about this plugin.

== Installation ==

Make sure that you already have WooCommerce plugin installed and activated.

**Step 1:**

- Login to your *WordPress Dashboard*
- Go to **Plugins > Add New**
- Search for **toyyibPay for WooCommerce**
- Click **Install Now**, then **Activate**

**Step 2:**

- Go to **WooCommerce > Settings > Payments**
- Find **toyyibPay** and click **Manage**

**Step 3:**

- Fill in your **Category Code** and **Secret Key**. You can retrieve these from your [toyyibPay Admin Dashboard](https://toyyibpay.com/access/login).
- Tick **Enable this payment gateway**
- Select your preferred payment channels (FPX, Credit/Debit Card, or both)
- Optionally enable **DuitNow QR** and configure the admin fee settings
- Click **Save changes**


== Frequently Asked Questions ==

= Do I need to sign up with toyyibPay in order to use this plugin? =

Yes, we require info such as email and secret key that is only available after you sign up with toyyibPay.

= Can I use this plugin without using WooCommerce? =

No.

= What currency does it support? =

Currently toyyibPay only support Malaysian Ringgit (RM).

= Is this plugin compatible with HPOS? =

Yes, this plugin is fully compatible with WooCommerce High-Performance Order Storage (HPOS).

= Is this plugin compatible with WooCommerce Blocks? =

Yes, this plugin supports WooCommerce Blocks checkout.

= How do I enable DuitNow QR payments? =

Go to WooCommerce > Settings > Payments > toyyibPay > Manage. Enable the "Enable DuitNow QR" checkbox and configure who pays the DuitNow QR admin fee (RM1 or 1%, whichever is higher). Note: Your toyyibPay account must have DuitNow QR activated.

= What if I have some other question related to toyyibPay? =

Contact us on our [Facebook Page](https://www.facebook.com/toyyibpay) if you have any questions or comments about this plugin.

== Changelog ==

= 2.0.0 =
* [NEW] DuitNow QR payment support
* [NEW] HPOS (High-Performance Order Storage) compatibility
* [NEW] WooCommerce Blocks checkout support
* [IMPROVED] PHP 7.0+ to 8.x compatibility
* [IMPROVED] Better WordPress and WooCommerce version compatibility
* [IMPROVED] Better error handling and security improvements
* [IMPROVED] Code cleanup and removed legacy unused files
* [FIXED] Deprecated function warnings
* [FIXED] Missing exit after payment redirects

= 1.4.0 =
* [Fixed] Major bugs from WordPress latest version.

= 1.3.2 =
* [Fixed] Security update

= 1.3.1 =
* [Fixed] Security update

= 1.3.0 =
* [NEW] Split Payment feature introduced in WooCommerce.

= 1.2.3.6 =
* [FIXED] Bug fixed

= 1.2.3.5 =
* [FIXED] Redirection after payment issues

= 1.2.2 =
* [FIXED] Bug fixed

= 1.2.1.1 =
* [FIXED] Bug fixed

= 1.2.1 =
* [IMPROVED] Improve callback function for better payment status handling.

= 1.2.0.5 =
* [FIXED] Bug fixed

= 1.2.0.4 =
* [IMPROVED] Made some changes in API usage

= 1.2.0.3 =
* [FIXED] Bug fixed

= 1.2.0.2 =
* [FIXED] Bug fixed

= 1.2.0.1 =
* [FIXED] Checkout bug fixed

= 1.2.0 =
* [FIXED] Bugs fixed
* [NEW] Support multiple wordpress site per e-mail.
* [NEW] Development mode for testing purposes.
* [NEW] Support toyyibPay V1.2 updates (Transaction charge on customer & extra e-mail)

= 1.1.2 =
* [FIXED] Bugs fixed

= 1.1.1 =
* [NEW] Added new image for WooCommerce checkout options.

= 1.1.0 =
* [NEW] Now you can choose to enable Credit/Debit Card Only, FPX Only or both!

= 1.0.1 =
* [FIXED] Bugs fixed

= 1.0.0 =
* Initial release. Yay!

== Upgrade Notice ==

= 2.0.0 =
Major update: DuitNow QR payment support, HPOS compatibility, WooCommerce Blocks support, and security improvements. Recommended update for all users.
