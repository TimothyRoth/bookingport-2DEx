<?php

class BOOKINGPORT_WCHandler
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('woocommerce_proceed_to_checkout', [__CLASS__, 'add_session_to_remarks_container'], 10);
        add_action('woocommerce_proceed_to_checkout', [__CLASS__, 'custom_cart_buttons'], 20);
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'apply_custom_price_globally']);
        add_action('woocommerce_checkout_process', [__CLASS__, 'check_if_order_is_valid']);
        add_action('woocommerce_thankyou', [__CLASS__, 'save_order_id_in_stand_meta'], 10, 1);
        add_action('woocommerce_before_checkout_form', [__CLASS__, 'check_session_expiry_checkout'], 1);
        add_filter('woocommerce_checkout_fields', [__CLASS__, 'custom_override_checkout_fields']);
        add_action('woocommerce_checkout_create_order_line_item', [__CLASS__, 'save_custom_data_to_order_item_meta'], 10, 4);
        add_filter('the_title', [__CLASS__, 'custom_title_order_received'], 10, 2);
        add_filter('woocommerce_checkout_fields', [__CLASS__, 'custom_change_label_and_placeholder']);
        add_action('clear_auth_cookie', [__CLASS__, 'empty_cart_on_logout']);
        add_action('wp_logout', [__CLASS__, 'reset_stands_on_logout']);
        add_action('wp_logout', [__CLASS__, 'destroy_session_on_logout']);
        add_filter('woocommerce_login_redirect', [__CLASS__, 'custom_login_redirect'], 10, 2);
        add_filter('gettext', [__CLASS__, 'change_login_text'], 20, 3);
        add_filter('woocommerce_gateway_title', [__CLASS__, 'change_cod_payment_method_label'], 10, 2);
        add_filter('wc_empty_cart_message', [__CLASS__, 'custom_wc_empty_cart_message']);
        add_filter('register_post_type_args', [__CLASS__, 'custom_modify_product_args'], 10, 2);
        add_action('woocommerce_before_cart', [__CLASS__, 'add_stand_timer_container']);
        add_action('woocommerce_before_checkout_form', [__CLASS__, 'add_stand_timer_container']);
        add_action('woocommerce_cart_contents', [__CLASS__, 'display_additional_order_meta'], 99);
        add_action('woocommerce_review_order_after_cart_contents', [__CLASS__, 'display_additional_order_meta'], 99);
        add_action('woocommerce_order_details_after_order_table_items', [__CLASS__, 'display_order_meta_in_order_details']);;
        add_action('woocommerce_checkout_update_order_meta', [__CLASS__, 'update_order_meta']);
    }

    public static function display_order_meta_in_order_details($order): void
    {
        $order_id = $order->get_id();
        $order_electricity = get_post_meta($order_id, 'electricity', true);
        $order_water = get_post_meta($order_id, 'water', true);
        $order_sales_food = get_post_meta($order_id, 'sales_food', true);
        $order_sales_drinks = get_post_meta($order_id, 'sales_drinks', true);
        $order_association_ride = get_post_meta($order_id, 'association_ride', true);
        $order_sortiment = get_post_meta($order_id, 'sortiment', true);

        echo "<tr><td colspan='2'>";
        if (!empty($order_electricity)):
            echo "<b>Stormanschluss:</b> {$order_electricity}<br>";
        endif;
        if (!empty($order_water)):
            echo "<b>Wasseranschluss:</b> {$order_water}<br>";
        endif;
        if (!empty($order_sales_food)):
            echo "<b>Imbiss:</b> {$order_sales_food}<br>";
        endif;
        if (!empty($order_sales_drinks)):
            echo "<b>Getränke:</b> {$order_sales_drinks}<br>";
        endif;
        if (!empty($order_association_ride)):
            echo "<b>Fahrgeschäft/e:</b> {$order_association_ride}<br>";
        endif;
        if (!empty($order_sortiment)):
            echo "<b>Sortiment:</b> {$order_sortiment}<br>";
        endif;

        echo "</td></tr>";
    }

    public static function display_additional_order_meta(): void
    {
        $reservation_order_meta = $_SESSION['reservation_order_meta'] ?? null;

        echo "<tr><td colspan='6'>";
        if (!empty($reservation_order_meta['electricity'])):
            echo "Stormanschluss: {$reservation_order_meta['electricity']}<br>";
        endif;
        if (!empty($reservation_order_meta['water'])):
            echo "Wasseranschluss: {$reservation_order_meta['water']}<br>";
        endif;
        if (!empty($reservation_order_meta['sales_food'])):
            echo "Imbiss: {$reservation_order_meta['sales_food']}<br>";
        endif;
        if (!empty($reservation_order_meta['sales_drinks'])):
            echo "Getränke: {$reservation_order_meta['sales_drinks']}<br>";
        endif;
        if (!empty($reservation_order_meta['association_ride'])):
            echo "Fahrgeschäft/e: {$reservation_order_meta['association_ride']}<br>";
        endif;
        if (!empty($reservation_order_meta['sortiment'])):
            echo "Sortiment: {$reservation_order_meta['sortiment']}<br>";
        endif;
        echo "</td></tr>";
    }

    public static function add_session_to_remarks_container(): void
    {
        if (isset($_SESSION['order_remarks'])) {
            $value = $_SESSION['order_remarks'];
        }

        ob_start(); ?>

        <div class="remarks-container">
            <h3>Sonstiges</h3>
            <p>Anmerkungen / Wünsche</p>
            <textarea name="user_remarks" id="user-remarks"
                      placeholder="Haben Sie besondere Anmerkungen zu Ihrem Aufbau, Wünsche oder ähnliches? Dann hinterlassen Sie uns hier eine kurze Nachricht."><?php if (isset($value)) {
                    echo $value;
                } ?></textarea>
        </div>

        <?php echo ob_get_clean();
    }

    public static function custom_cart_buttons(): void
    {
        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        echo '<a class="btn-secondary button" href="/' . $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking] . '">Abbrechen</a>';
    }

    public static function apply_custom_price_globally($cart): void
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $wc_cart = $cart->get_cart();

        foreach ($wc_cart ?? [] as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];

            $product_price = [
                'value' => 0,
                'is_set' => false
            ];

            foreach ($cart_item['articles_reserved_by_admin'] ?? [] as $item) {
                if (!$product_price['is_set']) {

                    $product_price = [
                        'value' => $item['price'],
                        'is_set' => true
                    ];

                    break;
                }
            }

            // Update the product price
            if ($product_price['is_set']) {
                $product->set_price($product_price['value']);
            }
        }
    }

       public static function check_if_order_is_valid(): bool
    {

        $items = !empty($_SESSION['items']) ? $_SESSION['items'] : [];
        $session_expired = BOOKINGPORT_StandStatusHandler::validate_stands_client_side($items);

        if ($session_expired) {
            wc_add_notice('Ihre Sitzung ist abgelaufen. Bitte legen Sie Ihren Artikel erneut in den Warenkorb.', 'error');
            BOOKINGPORT_StandStatusHandler::reset_stands($items);
            BOOKINGPORT_StandStatusHandler::reset_user_cart_and_session();
            return false;
        }

        if (!self::check_required_fields()) {
            return self::check_required_fields();
        }

        BOOKINGPORT_StandStatusHandler::set_stand_status_to_sold($items);
        return true;
    }

    private static function check_required_fields(): bool
    {
        $required_fields = array('billing_first_name', 'billing_first_name', 'billing_address_1', 'billing_postcode', 'billing_city', 'billing_phone', 'billing_email'); 
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                return false;
            }
        }

        return true;
    }

    public static function save_order_id_in_stand_meta($order_id): void
    {
        if (empty($order_id) && isset($GLOBALS['wp']->query_vars['order-received'])) {
            $order_id = absint($GLOBALS['wp']->query_vars['order-received']);
        }

        $order_meta = [
            'items' => !empty($_SESSION['items']) ? $_SESSION['items'] : [],
            'order_id' => $order_id
        ];

        BOOKINGPORT_StandStatusHandler::save_order_id_in_stands($order_meta);
    }

    public static function check_session_expiry_checkout(): void
    {

        $items_requested_by_customer = !empty($_SESSION['items']) ? $_SESSION['items'] : [];
        $session_expired = BOOKINGPORT_StandStatusHandler::validate_stands_client_side($items_requested_by_customer);

        if ($session_expired) {
            BOOKINGPORT_StandStatusHandler::reset_user_cart_and_session();
            BOOKINGPORT_StandStatusHandler::reset_stands($items_requested_by_customer);

            $option_table = get_option(BOOKINGPORT_Settings::$option_table);

            wp_redirect('/' . $option_table[BOOKINGPORT_Settings::$option_redirects_session_expired]);
            return;
        }

        BOOKINGPORT_StandStatusHandler::renew_reserved_stands_timestamps($items_requested_by_customer);

    }

    public static function custom_override_checkout_fields($fields): array
    {
        if (!empty($_SESSION['order_remarks'])) {
            $value = $_SESSION['order_remarks'];
            $fields['order']['order_comments']['default'] = $value;
            $fields['order']['order_comments']['label'] = 'Anmerkungen/Wünsche';
            $fields['order']['order_comments']['placeholder'] = 'Haben Sie besondere Anmerkungen zu Ihrem Aufbau, Wünsche oder ähnliches? Dann hinterlassen Sie uns hier eine kurze Nachricht.';
        }

        return $fields;
    }


    public static function update_order_meta($order_id): void
    {
        $reservation_order_meta = $_SESSION['reservation_order_meta'] ?? null;

        /*
         * @description: Save additional order meta data to order
         * */

        if (!empty($reservation_order_meta['electricity'])):
            update_post_meta($order_id, 'electricity', $reservation_order_meta['electricity'], true);
        endif;
        if (!empty($reservation_order_meta['water'])):
            update_post_meta($order_id, 'water', $reservation_order_meta['water'], true);
        endif;
        if (!empty($reservation_order_meta['sales_food'])):
            update_post_meta($order_id, 'sales_food', $reservation_order_meta['sales_food'], true);
        endif;
        if (!empty($reservation_order_meta['sales_drinks'])):
            update_post_meta($order_id, 'sales_drinks', $reservation_order_meta['sales_drinks'], true);
        endif;
        if (!empty($reservation_order_meta['association_ride'])):
            update_post_meta($order_id, 'association_ride', $reservation_order_meta['association_ride'], true);
        endif;
        if (!empty($reservation_order_meta['sortiment'])):
            update_post_meta($order_id, 'sortiment', $reservation_order_meta['sortiment'], true);
        endif;
    }

    public static function save_custom_data_to_order_item_meta($item, $cart_item_key, $values, $order): void
    {
        if (isset($values['articles_reserved_by_admin']) && is_array($values['articles_reserved_by_admin'])) {

            foreach ($values['articles_reserved_by_admin'] ?? [] as $reserved_item) {

                $width = $reserved_item['width'] . 'm';
                $depth = $reserved_item['depth'] . 'm';
                $street = $reserved_item['street'];
                $standnumber = $reserved_item['number'];
                $days = BOOKINGPORT_StandStatusHandler::get_booking_day_name($reserved_item['days']);
                $item->add_meta_data('Straße', $street, true);
                $item->add_meta_data('Standnummer', $standnumber, true);
                $item->add_meta_data("Buchungstage", $days, true);
                $item->add_meta_data('Standbreite', $width, true);
                $item->add_meta_data('Standtiefe', $depth, true);
            }
            return;
        }

        $street = $values['street'];
        $standnumber = $values['number'];
        $days = BOOKINGPORT_StandStatusHandler::get_booking_day_name($values['days']);
        $width = '3m';
        $depth = '0,6m';

        $item->add_meta_data('Straße', $street, true);
        $item->add_meta_data('Standnummer', $standnumber, true);
        $item->add_meta_data('Buchungstage', $days, true);
        $item->add_meta_data('Standbreite', $width, true);
        $item->add_meta_data('Standtiefe', $depth, true);
    }

    public static function custom_title_order_received($title, $id)
    {
        if (is_order_received_page() && get_the_ID() === $id) {
            $title = "Standbuchung erhalten";
        }

        return $title;
    }

    public static function custom_change_label_and_placeholder($fields): array
    {
        $fields['billing']['billing_company']['label'] = 'Firmen oder Vereinsname';
        $fields['billing']['billing_company']['placeholder'] = 'Geben Sie den Firmen- oder Vereinsnamen ein';
        return $fields;
    }

    public static function destroy_session_on_logout(): void
    {
        session_start();
        session_destroy();
    }

    public static function empty_cart_on_logout(): void
    {
        WC()->cart->empty_cart();
    }

    public static function reset_stands_on_logout(): void
    {
        session_start();
        $items = !empty($_SESSION['items']) ? $_SESSION['items'] : [];
        BOOKINGPORT_StandStatusHandler::reset_stands($items);
    }

    public static function custom_login_redirect($redirect, $user)
    {
        // Check if the user was redirected from the /reservierungen-annehmen page
        if (isset($_SESSION['redirect_from_reservierungen_annehmen'])) {
            return $_SESSION['redirect_from_reservierungen_annehmen'];
        }
        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        return '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard];
    }

    public static function change_login_text($translated_text, $text, $domain)
    {
        if ($translated_text === 'Anmelden') {
            $translated_text = 'Login';
        }

        return $translated_text;
    }

    public static function change_cod_payment_method_label($title, $gateway_id)
    {
        if ($gateway_id === 'cod') { // 'cod' is the payment gateway ID for Cash on Delivery
            $title = 'Auf Rechnung';
        }

        if ($gateway_id === 'woocommerce_payments') {
            $title = 'Kreditkartenzahlung';
        }
        return $title;
    }

    public static function custom_modify_product_args($args, $post_type)
    {
        if ($post_type === 'product') {
            // Change the public static ly_queryable argument to false
            $args['public static ly_queryable'] = false;
        }
        return $args;
    }

    public static function add_stand_timer_container(): void
    {
        if (is_cart() || is_checkout()) {
            $timer_image = BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/clock-magenta.svg';
            echo '<div class="stand-timer-wrapper">
                    <img src=" ' . $timer_image . '">
                    <p>Ihre Bestellung ist noch <span class="stand-timer">10:00</span> min für Sie reserviert und verliert danach ihre Gültigkeit.</p>
              </div>';
        }
    }

    public static function custom_wc_empty_cart_message()
    {
        return 'Derzeit befinden sich keine Stände in Ihrem Warenkorb.';
    }

    public static function get_total_user_amount(): string
    {
        $user_count = count_users();
        return $user_count['total_users'];
    }

    public static function get_total_sales_volume(string $year): string
    {
        $args = array(
            'post_type' => 'shop_order',
            'post_status' => array('wc-completed', 'payment-accepted'),
            'posts_per_page' => -1,
            'date_query' => array(
                array(
                    'year' => $year,
                ),
            ),
        );

        $orders = wc_get_orders($args);
        $total_revenue = 0;
        foreach ($orders as $order) {
            $total_revenue += $order->get_total();
        }
        return $total_revenue;
    }

}