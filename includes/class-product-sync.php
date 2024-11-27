<?php
declare(strict_types=1);

class Product_Sync
{
    /**
     * Sync product data to the external server.
     *
     * @param int $product_id Product ID.
     */
    public static function sync_product_to_server($product_id)
    {
        $product = wc_get_product($product_id);

        if (!$product) {
            return;
        }

        $product_data = [
            'id'    => $product->get_id(),
            'name'  => $product->get_name(),
            'price' => $product->get_price(),
            // add other product fields as needed
        ];

        $response = wp_remote_post('https://externalserver.com/api/sync-product', [
            'body'    => json_encode($product_data),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            error_log('Failed to sync product: ' . $response->get_error_message());
        } else {
            error_log('Product synced successfully');
        }
    }

    /**
     * Sync product with the external server when it's saved.
     *
     * @param int $post_id Post ID.
     * @param WP_Post $post Post object.
     * @param bool $update Whether this is an existing post being updated or not.
     */
    public static function sync_product_on_save($post_id, $post, $update)
    {
        // Only sync products
        if ($post->post_type !== 'product') {
            return;
        }

        self::sync_product_to_server($post_id);
    }

    /**
     * Sync products with the external server when an order is completed.
     *
     * @param int $order_id Order ID.
     */
    public static function sync_products_on_order_complete($order_id)
    {
        $order = wc_get_order($order_id);

        if(!$order) {
            return;
        }

        $order_items = $order->get_items();

        foreach ($order_items as $item) {
            $product_id = $item->get_product_id();
            self::sync_product_to_server($product_id);
        }
    }

    /**
     * Sync all WooCommerce products with the external server.
     */
    public static function sync_all_products()
    {
        // Get all WooCommerce products
        $products = wc_get_products([
            'limit' => -1,
        ]);

        // Prepare and send the data to the external server
        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id'    => $product->get_id(),
                'name'  => $product->get_name(),
                'price' => $product->get_price(),
                // add other product fields as needed
            ];
        }

        // Send data to the external server
        $response = wp_remote_post('https://externalserver.com/api/sync-products', [
            'body'    => json_encode($data),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error('Failed to sync products.');
        } else {
            wp_send_json_success('Products synced successfully.');
        }
    }
}
