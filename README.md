# Klump WooCommerce Buy Now, Pay Later Plugin

Klump WooCommerce Buy Now, Pay Later plugin allows merchants to give their customers the option of purchasing an item or service and make payment in four instalments.

## Note

This plugin is meant to be used by merchants in Nigeria.

## Suggestions / Feature Request

If you have challenges using the plugin or suggestions or a new feature request, kindly reach out via the [contact form on our website](https://useklump.com/contact) or send us an email at support@useklump.com

## Installation

### Requirements
Ensure the following plugins are already installed on your site:
*   [WooCommerce](https://wordpress.org/plugins/woocommerce/)

### Automatic Installation
*   Login to WordPress admin dashboard
*   Go to **WordPress Admin** > **Plugins** > **Add New** from the left-hand menu.
*   In the search box type **Klump WooCommerce Payment Gateway**.
*   Click on **Install Now** on **Klump WooCommerce Payment Gateway** to install the plugin on your website.
*   Confirm the installation
*   After successful installation, **Activate** the plugin.
*   Go to **WooCommerce** > **Settings** from the left-hand menu
*   Click on the **Payments** tab
*   Click on the **Pay with Klump** link from the available payment options
*   Enter your parameters accordingly and click save.

### Manual Installation
*   [Download](https://downloads.wordpress.org/plugin/klump-wc-payment-gateway.zip) the plugin zip file
*   Login to WordPress admin dashboard
*   Go to **WordPress Admin** > **Plugins** > **Add New** from the left-hand menu.
*   Click on the "Upload" option, then click "Choose File" to select the zip file you downloaded. Click "OK" and "Install Now" to complete the installation.
*   After successful installation, **Activate** the plugin.
*   Go to **WooCommerce** > **Settings** from the left-hand menu
*   Click on the **Payments** tab
*   Click on the **Pay with Klump** link from the available payment options
*   Enter your parameters accordingly and click save.

## Klump Configuration Reference
*   **Enable/Disable** - Check this checkbox to Enable "Pay with Klump" on your store's checkout page
*   **Title** - This is what users will see on the checkout page. The default is "Pay in Instalments - Klump" but you can change this to better reflect how you communicate with your customers.
*   **Description** - This controls the message that appears under the payment fields on the checkout page. You can change the default description to describe in your own words the benefit of Klump to your customers.
*   **Test Mode** - Check this to enable test mode. Test mode requires test public and secret key and it allows you to see Klump in action before going live to start receiving real payments from your customers.
*   **API Keys** - Klump requires public and private API keys for both test mode and live mode. You can obtain them from your Klump merchant dashboard.
*   **Webhook URL** - To complete the order automatically, copy and paste the webhook link here on your Klump merchant dashboard.
*   **Enable Klump Ads** - This shows your customers the breakdown of the payment stages if they want to use Klump before they start the process. It's nice to have but not required for Klump to work on your site.
*   **Disallow cancel order** - Removes the cancel order button if enabled
*   **Autocomplete order** - If enabled automatically resolves order from webhook call after payment. Note: It requires webhook to work for the most part.
*    Click on **Save Changes** to update the settings.

## Troubleshooting
If you do not find Klump on WooCommerce payments tab on settings page, please check and ensure the following:

*   **"Enable/Disable"** checkbox is checked (enabled)
*   **API Keys** are supplied and are correct

## Frequently Asked Questions

### What Do I Need To Use The Plugin?

*   A Klump merchant account—use an existing account or [create an account here](https://merchant.useklump.com/sign-up)
*   An active [WooCommerce installation](https://docs.woocommerce.com/document/installing-uninstalling-woocommerce/)
*   A valid [SSL Certificate](https://docs.woocommerce.com/document/ssl-and-https/)
