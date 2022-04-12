<?php
declare(strict_types=1);

/**
 * Plugin Name: Klump WooCommerce Payment Gateway
 * Plugin URI: https://useklump.com/
 * Description: WooCommerce payment gateway for Klump BNPL.
 * Version: 1.0.0
 * Author: Klump Team
 * Author URI: https://useklump.com/developers
 * License: MIT License
 * WC requires at least: 3.0.0
 * WC tested up to: 5.9
 */

if (!defined('ABSPATH')) {
    exit;
}

define( 'KLP_WC_PLUGIN_FILE', __FILE__ );
define( 'KLP_WC_PLUGIN_DIR_PATH', plugin_dir_path( KLP_WC_PLUGIN_FILE ) );

function klp_wc_payment_init() {
    if ( !class_exists( 'WC_Payment_Gateway' ) ) {
        add_action( 'admin_notices', 'klp_wc_payment_wc_missing_notice' );
        return;
    }

    require_once( KLP_WC_PLUGIN_DIR_PATH . 'includes/class-klp-wc-payment-gateway.php' );

    add_filter('woocommerce_payment_gateways', 'klp_wc_add_payment_gateway', 99 );
}
add_action('plugins_loaded', 'klp_wc_payment_init', 99);

/**
 * Add settings link to plugin
 * @param Array $links Existing links on the plugin page
 * @return Array
 */
function klp_wc_plugin_action_links( $links ) {
    $klp_settings_url = esc_url( get_admin_url( null, 'admin.php?page=wc-settings&tab=checkout&section=klump' ) );
    array_unshift( $links, "<a title='Klump Settings Page' href='$klp_settings_url'>Settings</a>" );

    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'klp_wc_plugin_action_links' );

/**
 * Display a notice if WooCommerce is not installed
 */
function klp_wc_payment_wc_missing_notice() {
    echo '<div class="error"><p><strong>' . sprintf( 'Klump requires WooCommerce to be installed and active. Click %s to install WooCommerce.', '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539' ) . '" class="thickbox open-plugin-details-modal">here</a>' ) . '</strong></p></div>';
}

/**
 * Add plugin to Woocommerce
 * @param Array $methods
 * @return Array
 */
function klp_wc_add_payment_gateway($methods) {
    $methods[] = 'KLP_WC_Payment_Gateway';

    return $methods;
}
