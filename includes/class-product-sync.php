<?php
declare(strict_types=1);

class Product_Sync
{
    private const EXTERNAL_SERVER_URL = 'https://rarely-in-sunbeam.ngrok-free.app/api/sync-product';

    /**
     * Sync product data to the external server.
     *
     * @param int $product_id Product ID.
     */
    public static function sync_products_to_server(array $products): void
    {
        $product_data = [];

        foreach ($products as $product) {
            if (is_int($product)) {
                $product = wc_get_product($product);
            }

            if (!$product instanceof WC_Product) {
                error_log('Invalid product data: ' . print_r($product, true));
                continue;
            }

            if ($product->is_type('variable')) {
                // Get variations
                $available_variations = $product->get_available_variations();

                foreach ($available_variations as $variation) {
                    $variation_id = $variation['variation_id'];
                    $variation_obj = new WC_Product_Variation($variation_id);

                    if (!$variation_obj->get_price()) continue;
//                    if (!$variation_obj->get_stock_quantity()) continue;

                    // Process each variation as needed
                    $product_data[] = [
                        'name'         => $product->get_name(),
                        'product_id'   => $product->get_id(),
                        'variant_id'   => $variation_id,
                        'variant_name' => $variation_obj->get_name(),
                        'quantity'     => $variation_obj->get_stock_quantity() || 0,
                        'image'        => wp_get_attachment_url($variation_obj->get_image_id()),
                        'is_published' => $product->get_status() === 'publish',
                        'price'        => $variation_obj->get_price() || 0,
                        'old_price'    => $variation_obj->get_regular_price() !== $variation_obj->get_price() ? $variation_obj->get_regular_price() : 0,
                        'description'  => $product->get_description(),
                        'sku'          => $product->get_sku(),
                        'sub_category' => self::get_product_category($product),
                        'category'     => self::get_product_category($product, true),
                    ];
                }
            }

            if ($product->is_type('simple')) {
                if (!$product->get_price()) continue;
                $product_data[] = [
                    'name'         => $product->get_name(),
                    'product_id'   => $product->get_id(),
                    'variant_id'   => null,
                    'variant_name' => null,
                    'quantity'     => $product->get_stock_quantity() || 0,
                    'image'        => wp_get_attachment_url($product->get_image_id()),
                    'is_published' => $product->get_status() === 'publish',
                    'price'        => $product->get_price(),
                    'old_price'    => $product->get_regular_price() !== $product->get_price() ? $product->get_regular_price() : null,
                    'description'  => $product->get_description(),
                    'sku'          => $product->get_sku(),
                    'sub_category' => self::get_product_category($product),
                    'category'     => self::get_product_category($product, true),
                ];
            }
        }

        if (empty($product_data)) {
            error_log('No valid products to sync.');
            return;
        }

        $response = wp_remote_post(self::EXTERNAL_SERVER_URL . 's', [ // Plural 's' for bulk sync
            'body'    => json_encode($product_data),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        self::log_response($response, 'Products');
    }

    /**
     * Extract product data for syncing.
     *
     * @param WC_Product $product
     * @return array
     */
    private static function get_product_data(WC_Product $product): array
    {
        return [
            'name'         => $product->get_name(),
            'product_id'   => $product->get_id(),
            'variant_id'   => method_exists($product, 'get_variation_id') ? $product->get_variation_id() : null,
            'variant_name' => $product->is_type('variation') ? $product->get_title() : null,
            'quantity'     => $product->get_stock_quantity(),
            'image'        => wp_get_attachment_url($product->get_image_id()),
            'is_published' => $product->get_status() === 'publish',
            'price'        => $product->get_price(),
            'old_price'    => $product->get_regular_price() !== $product->get_price() ? $product->get_regular_price() : null,
            'description'  => $product->get_description(),
            'sku'          => $product->get_sku(),
            'sub_category' => self::get_product_category($product),
            'category'     => self::get_product_category($product, true),
        ];
    }

    /**
     * Log the response from the external server.
     *
     * @param WP_Error|array $response
     * @param string $entity
     */
    private static function log_response($response, string $entity): void
    {
        if (is_wp_error($response)) {
            error_log("Failed to sync $entity: " . $response->get_error_message());
        } else {
            error_log("$entity synced successfully");
        }
    }

    /**
     * Sync product when it's saved.
     *
     * @param int $post_id Post ID.
     * @param WP_Post $post Post object.
     * @param bool $update Whether this is an existing post being updated or not.
     */
    public static function sync_product_on_save(int $post_id, WP_Post $post, bool $update): void
    {
        if ($post->post_type === 'product') {
            self::sync_products_to_server([$post_id]);
        }
    }


    /**
     * Sync products when an order is completed.
     *
     * @param int $order_id Order ID.
     */
    public static function sync_products_on_order_complete(int $order_id): void
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            error_log('Order not found: ' . $order_id);
            return;
        }

        $product_ids = array_map(function($item) {
            return $item->get_product_id();
        }, $order->get_items());

        self::sync_products_to_server($product_ids);
    }

    /**
     * Get the category or sub-category of a product.
     *
     * @param WC_Product $product
     * @param bool $is_main_category
     * @return string|null
     */
    private static function get_product_category(WC_Product $product, bool $is_main_category = false): ?string
    {
        $terms = get_the_terms($product->get_id(), 'product_cat');
        if ($terms && ! is_wp_error($terms)) {
            // Sort categories to find main category if necessary
            usort($terms, function($a, $b) {
                return $a->parent - $b->parent;
            });
            return $is_main_category ? $terms[0]->name : (!empty($terms[1]) ? $terms[1]->name : null);
        }
        return null;
    }

    /**
     * Sync all WooCommerce products with the external server.
     */
    public static function sync_all_products(): void
    {
        $products = wc_get_products(['limit' => -1]);

        self::sync_products_to_server($products);
    }
}
