<?php

class BOOKINGPORT_CartHandler
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_ajax_empty_cart', [__CLASS__, 'empty_cart']);
        add_action('wp_ajax_nopriv_empty_cart', [__CLASS__, 'empty_cart']);

        add_action('wp_ajax_add_items_to_cart', [__CLASS__, 'add_items_to_cart']);
        add_action('wp_ajax_nopriv_add_items_to_cart', [__CLASS__, 'add_items_to_cart']);

        add_action('wp_ajax_add_order_remarks', [__CLASS__, 'add_order_remarks']);
        add_action('wp_ajax_nopriv_add_order_remarks', [__CLASS__, 'add_order_remarks']);

        add_action('wp_ajax_update_order_remarks', [__CLASS__, 'update_order_remarks']);
        add_action('wp_ajax_nopriv_update_order_remarks', [__CLASS__, 'update_order_remarks']);
    }

    /**
     * @throws JsonException
     */
    public static function empty_cart(): void
    {
        WC()->cart->empty_cart();
        $success = 'All cart items have been successfully removed';
        wp_die(json_encode($success, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function add_items_to_cart(): void
    {
        $pre_cart_session = $_SESSION['items'];
        $response = 'success';
        $product_id = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);

        if (!empty($pre_cart_session)) {

            $cart_items = [];
            $current_cart = WC()->cart->get_cart();

            foreach ($pre_cart_session as $itemMeta) {

                $ID = $itemMeta['standID'];
                $days = $itemMeta['days'];
                $street = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $size = '3m/1 Tapeziertisch';
                $number = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $quantity = count(BOOKINGPORT_StandStatusHandler::convert_days_into_array($days));

                $new_cart_item_meta = [
                    'id' => $ID,
                    'street' => $street,
                    'housenumber' => $number,
                    'size' => $size,
                    'number' => $number,
                    'days' => $days
                ];

                // check if the stand/item is part of the current cart
                $is_duplicate_cart_item = false;
                $duplicate_cart_item_id = null;

                foreach ($current_cart ?? [] as $cart_item_key => $cart_item) {
                    if (($cart_item['id'] === $new_cart_item_meta['id']) && ($cart_item['day'] === $new_cart_item_meta['day'])) {
                        $is_duplicate_cart_item = true;
                        $duplicate_cart_item_id = $cart_item_key;
                        break;
                    }
                }

                // if the stand/item is a duplicate then replace the duplicate else create a new cart item
                $cart_item_key = !$is_duplicate_cart_item ?
                    WC()->cart->add_to_cart($product_id, $quantity, 0, [], $new_cart_item_meta) :
                    self::replace_cart_item_with_duplicate($duplicate_cart_item_id, $new_cart_item_meta, $product_id, $quantity);

                $cart_items[$cart_item_key] = $new_cart_item_meta;

            }


            // Store the cart item metadata in the session to keep the session synchronized with the current cart
            $_SESSION['cart_items'] = $cart_items;

        }

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    private static function replace_cart_item_with_duplicate($existing_cart_item_key, $add_to_cart_item_meta, $product_id, $item_quantity)
    {
        $cart_item_data = WC()->cart->get_cart_item($existing_cart_item_key);
        $cart_item_data['data'] = $add_to_cart_item_meta;
        WC()->cart->remove_cart_item($existing_cart_item_key);
        return WC()->cart->add_to_cart($product_id, $item_quantity, 0, [], $add_to_cart_item_meta);
    }

    /**
     * @throws JsonException
     */
    public static function add_order_remarks(): void
    {
        $order_remarks = $_POST['remarks'];
        $_SESSION['order_remarks'] = $order_remarks;
        $response = $_SESSION['order_remarks'] = $order_remarks;
        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function update_order_remarks(): void
    {
        $order_remarks = $_POST['remarks'];
        $_SESSION['order_remarks'] = $order_remarks;
        $response = $_SESSION['order_remarks'] = $order_remarks;
        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

}
