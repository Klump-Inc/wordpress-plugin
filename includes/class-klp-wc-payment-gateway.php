<?php
declare(strict_types=1);

class KLP_WC_Payment_Gateway extends WC_Payment_Gateway
{

    // Show klump ads on checkout page
    public $show_klp_ads;

    // Toggle cancel order button on checkout page
    public $remove_cancel_order_button;

    // Toggle autocomplete order mode
    public $is_autocomplete_order_enabled;

    // test mode
    public $test_mode;

    // Credentials
    protected $public_key;
    protected $secret_key;

    public function __construct()
    {
        $this->id   = 'klump'; // payment gateway ID
        $this->icon = '';
//        $this->icon               = plugins_url('assets/images/klump.png', KLP_WC_PLUGIN_FILE); // payment gateway icon
        $this->has_fields         = false; // for custom credit card form
        $this->title              = 'Pay with Klump'; // vertical tab title
        $this->method_title       = 'Pay with Klump'; // payment method name
        $this->method_description = 'Use Klump to buy today, and pay in 4 equal instalments over 3 months! Businesses accept payments and increase conversion rate and revenue. The Easy Life! Try it now.'; // payment method description

        $this->supports = ['products'];

        // load backend options fields
        $this->init_form_fields();

        // load the settings.
        $this->init_settings();
        $this->title                         = $this->get_option('title');
        $this->description                   = $this->get_option('description');
        $this->enabled                       = $this->get_option('enabled');
        $this->show_klp_ads                  = 'yes' === $this->get_option('show_klp_ads');
        $this->remove_cancel_order_button    = 'yes' === $this->get_option('remove_cancel_order_button');
        $this->is_autocomplete_order_enabled = 'yes' === $this->get_option('is_autocomplete_order_enabled');

        $this->test_mode = 'yes' === $this->get_option('test_mode');

        $this->secret_key = $this->test_mode ? $this->get_option('test_secret_key') : $this->get_option('secret_key');
        $this->public_key = $this->test_mode ? $this->get_option('test_public_key') : $this->get_option('public_key');

        add_action('admin_notices', [$this, 'admin_notices']);

        add_action('woocommerce_receipt_' . $this->id, [$this, 'receipt_page']);
        add_action('woocommerce_api_klp_wc_payment_gateway', [$this, 'klp_verify_payment']);

        add_action('woocommerce_api_klp_wc_payment_webhook', [$this, 'klp_webhook']);

        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        // Action hook to load custom JavaScript
        add_action('wp_enqueue_scripts', [$this, 'payment_scripts']);
    }

    public function init_form_fields(): void
    {
        $this->form_fields = [
            'enabled'                       => [
                'title'       => __('Enable/Disable', 'klp-payments'),
                'label'       => __('Enable Klump Payment', 'klp-payments'),
                'type'        => 'checkbox',
                'description' => __('Enable Klump to allow your customers pay for your products by installments.', 'klp-payments'),
                'default'     => 'no',
                'desc_tip'    => true,
            ],
            'title'                         => [
                'title'       => __('Title', 'klp-payments'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'klp-payments'),
                'default'     => __('Pay in 4 Installment', 'klp-payments'),
                'desc_tip'    => true,
            ],
            'description'                   => [
                'title'       => __('Description', 'klp-payments'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'klp-payments'),
                'default'     => __('Enjoy ease of payment by splitting cost and paying in four installments.', 'klp-payments'),
            ],
            'test_mode'                     => [
                'title'       => __('Test mode', 'klp-payments'),
                'label'       => __('Enable Test Mode', 'klp-payments'),
                'type'        => 'checkbox',
                'description' => __('Place the payment gateway in test mode using test API keys.', 'klp-payments'),
                'default'     => 'yes',
                'desc_tip'    => true,
            ],
            'test_public_key'               => [
                'title' => __('Test Public Key', 'klp-payments'),
                'type'  => 'text',
            ],
            'test_secret_key'               => [
                'title' => __('Test Secret Key', 'klp-payments'),
                'type'  => 'password',
            ],
            'public_key'                    => [
                'title' => __('Live Public Key', 'klp-payments'),
                'type'  => 'text',
            ],
            'secret_key'                    => [
                'title' => __('Live Private Key', 'klp-payments'),
                'type'  => 'password',
            ],
            'show_klp_ads'                  => [
                'title'       => __('Enable Klump Ads', 'klp-payments'),
                'label'       => __('Show Klump Ads', 'klp-payments'),
                'type'        => 'checkbox',
                'description' => __('Show Klump ads to entice your customers to convert.', 'klp-payments'),
                'default'     => 'no',
                'desc_tip'    => true,
            ],
            'remove_cancel_order_button'    => [
                'title'       => __('Disallow cancel order', 'klp-payments'),
                'label'       => __('Remove cancel order button', 'klp-payments'),
                'type'        => 'checkbox',
                'description' => __('Remove cancel order button', 'klp-payments'),
                'default'     => 'no',
                'desc_tip'    => false,
            ],
            'is_autocomplete_order_enabled' => [
                'title'       => __('Autocomplete order', 'klp-payments'),
                'label'       => __('Enable autocomplete orders', 'klp-payments'),
                'type'        => 'checkbox',
                'description' => __('Enable orders autocomplete', 'klp-payments'),
                'default'     => 'no',
                'desc_tip'    => false,
            ],
        ];
    }

    /**
     * Handles admin notices
     *
     * @return void
     */
    public function admin_notices(): void
    {
        if ('no' === $this->enabled) {
            return;
        }

        /**
         * Check if public key is provided
         */
        if (!$this->public_key || !$this->secret_key) {
            $mode = ($this->test_mode) ? 'test' : 'live';
            echo '<div class="error"><p>';
            echo sprintf(
                'Provide your ' . $mode . ' public key and secret key <a href="%s">here</a> to be able to use the Pay with Klump Gateway plugin. If you don\'t have one, kindly sign up at <a href="https://useklump.com" target="_blank">https://useklump.com</a>.',
                admin_url('admin.php?page=wc-settings&tab=checkout&section=klump')
            );
            echo '</p></div>';
        }

    }

    public function payment_scripts(): void
    {
        if (!is_checkout_pay_page()) {
            return;
        }

        // stop enqueue JS if payment gateway is disabled
        if ('no' === $this->enabled) {
            return;
        }

        // stop enqueue JS if API keys are not set
        if (empty($this->public_key)) {
            return;
        }

        $primary_key = $this->public_key;

        // stop enqueue JS if site without SSL
        if (!$this->test_mode && !is_ssl()) {
            return;
        }

        $order_key = urldecode($_GET['key']);
        $order_id  = absint(get_query_var('order-pay'));

        $order = wc_get_order($order_id);

        $payment_method = method_exists($order, 'get_payment_method') ? $order->get_payment_method() : $order->payment_method;

        if ($this->id !== $payment_method) {
            return;
        }

        // payment processor JS that allows to get a token
        wp_enqueue_script('klp_payment_js', KLP_WC_SDK_URL, [], '1.0.0', true);

        wp_enqueue_script('klp_js', plugins_url('assets/js/klp-payment.js', KLP_WC_PLUGIN_FILE), [], '1.0.0', true);

        $cb_url = WC()->api_request_url('KLP_WC_Payment_Gateway');

        $payment_params = [];

        if (get_query_var('order-pay')) {
            $email         = method_exists($order, 'get_billing_email') ? $order->get_billing_email() : $order->billing_email;
            $amount        = $order->get_total();
            $txnref        = 'KLP_' . $order_id . '_' . time();
            $txnref        = filter_var($txnref, FILTER_SANITIZE_STRING);
            $currency      = method_exists($order, 'get_currency') ? $order->get_currency() : $order->order_currency;
            $the_order_key = method_exists($order, 'get_order_key') ? $order->get_order_key() : $order->order_key;
            $firstname     = $order->get_billing_first_name();
            $lastname      = $order->get_billing_last_name();
            $shipping_fee  = $order->get_shipping_total();

            $order_items = [];
            foreach ($order->get_items() as $key => $item) {
                $product   = wc_get_product($item->get_product_id());
                $image_url = wp_get_attachment_image_url($product->get_image_id(), 'full');

                $order_items[] = [
                    'image_url'  => $image_url,
                    'item_url'   => $product->get_permalink(),
                    'name'       => $item->get_name(),
                    'unit_price' => ($item->get_subtotal() / $item->get_quantity()),
                    'quantity'   => $item->get_quantity(),
                ];
            }

            if ($the_order_key === $order_key) {
                $payment_params = compact(
                    'amount',
                    'email',
                    'txnref',
                    'primary_key',
                    'currency',
                    'firstname',
                    'lastname',
                    'cb_url',
                    'order_items',
                    'shipping_fee',
                    'order_id'
                );
            }

            update_post_meta($order_id, '_klp_payment_txn_ref', $txnref);
        }

        wp_localize_script('klp_js', 'klp_payment_params', $payment_params);
    }

    /**
     * Displays the payment page.
     *
     * @param $order_id
     */
    public function receipt_page($order_id): void
    {
        $order = wc_get_order($order_id);

        echo '<p>' . __('Thank you for your order, please click the button below to pay with Klump.', 'klp-payments') . '</p>';

        echo '<div id="klump__checkout"></div>';

        if ($this->show_klp_ads) {
            echo '<div id="klump__ad">';
            echo '<input type="number" value="2000" id="klump__price">';
            echo '<input type="text" value="' . $this->public_key . '" id="klump__merchant__public__key">';
            echo '<input type="text" value="NGN" id="klump__currency">';
            echo '</div>';
        }

        if (!$this->remove_cancel_order_button) {
            echo '<a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">Cancel order';
            echo '</a>';
        }
    }

    /**
     * @param $order_id
     * @return array
     */
    public function process_payment($order_id): array
    {
        $order = wc_get_order($order_id);

        return [
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url(true),
        ];
    }

    /**
     * @return void
     */
    public function klp_verify_payment(): void
    {
        $reference = $order_id = null;
        if (isset($_REQUEST['reference'], $_REQUEST['order_id'])) {
            $reference = sanitize_text_field(urldecode($_REQUEST['reference']));
            $order_id  = sanitize_text_field(urldecode($_REQUEST['order_id']));
        }

        @ob_clean();

        if ($reference && $order_id) {
            $order = wc_get_order($order_id);

            $verifyUrl = KLP_WC_SDK_VERIFICATION_URL . $reference . '/verify';
            $args      = [
                'headers' => [
                    'klump-secret-key' => $this->secret_key,
                    'Content-Type'     => 'application/json',
                ],
                'timeout' => 60,
            ];

            $request = wp_remote_get($verifyUrl, $args);

            if (!is_wp_error($request) && 200 === wp_remote_retrieve_response_code($request)) {
                $klp_response = json_decode(wp_remote_retrieve_body($request), true);

                $klp_merchant_reference = $klp_response['data']['merchant_reference'];
                $klp_amount             = $klp_response['data']['amount'];
                $klp_currency           = $klp_response['data']['currency'];

                $order_details     = explode('_', $klp_merchant_reference);
                $verified_order_id = $order_details[1];

                if ('new' === $klp_response['data']['status'] && $verified_order_id == $order_id) {
                    if (in_array($order->get_status(), ['processing', 'completed', 'on-hold'])) {
                        wp_redirect($this->get_return_url($order));
                        exit;
                    }

                    $order_total      = $order->get_total();
                    $order_currency   = $order->get_currency();
                    $currency_symbol  = get_woocommerce_currency_symbol($order_currency);
                    $amount_paid      = $klp_amount;
                    $payment_currency = strtoupper($klp_currency);
                    $gateway_symbol   = get_woocommerce_currency_symbol($payment_currency);

                    if ($payment_currency !== $order_currency || $amount_paid < $order_total) {
                        if ($payment_currency !== $order_currency) {

                            $order->update_status('on-hold', '');

                            update_post_meta($order_id, '_transaction_id', $order_id);

                            $notice      = sprintf(__('Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'klp-payments'), '<br />', '<br />', '<br />');
                            $notice_type = 'notice';

                            // Add Customer Order Note
                            $order->add_order_note($notice, 1);

                            // Add Admin Order Note
                            $admin_order_note = sprintf(__('<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Klump Payment Transaction Reference:</strong> %9$s', 'klp-payments'), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $reference);
                            $order->add_order_note($admin_order_note);

                            if (function_exists('wc_reduce_stock_levels')) {
                                wc_reduce_stock_levels($order_id);
                            }
                            wc_add_notice($notice, $notice_type);

                        }

                        if ($amount_paid < $order_total) {
                            $order->update_status('on-hold', '');
                            add_post_meta($order_id, '_transaction_id', $order_id, true);

                            $notice      = sprintf(__('Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'klp-payments'), '<br />', '<br />', '<br />');
                            $notice_type = 'notice';

                            // Add Customer Order Note
                            $order->add_order_note($notice, 1);

                            // Add Admin Order Note
                            $admin_order_note = sprintf(__('<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Klump Payment Transaction Reference:</strong> %9$s', 'klp-payments'), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $reference);
                            $order->add_order_note($admin_order_note);

                            if (function_exists('wc_reduce_stock_levels')) {
                                wc_reduce_stock_levels($order_id);
                            }

                            wc_add_notice($notice, $notice_type);
                        }
                    } else {
                        $order->payment_complete($order_id);
                        $order->add_order_note(sprintf(__('Payment via Klump successful (Transaction Reference: %s)', 'klp-payments'), $reference));

                        if ($this->is_autocomplete_order_enabled) {
                            $order->update_status('completed');
                        }

                        WC()->cart->empty_cart();
                    }
                } elseif ($order) {
                    $order->update_status('failed', __('Payment was declined by Klump.', 'klp-payments'));
                }
            }

            wp_redirect($this->get_return_url($order));
            exit;
        }

        wp_redirect(wc_get_page_permalink('cart'));
        exit;
    }

    public function klp_webhook(): void
    {
        // @todo Implement webhook
    }
}
