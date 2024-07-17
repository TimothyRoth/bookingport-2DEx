<?php

class BOOKINGPORT_StandStatusHandler
{

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {

        add_action('wp_ajax_reset_stand_meta', [__CLASS__, 'reset_stand_meta']);
        add_action('wp_ajax_nopriv_reset_stand_meta', [__CLASS__, 'reset_stand_meta']);

        add_action('wp_ajax_render_pre_cart_view', [__CLASS__, 'render_pre_cart_view']);
        add_action('wp_ajax_nopriv_render_pre_cart_view', [__CLASS__, 'render_pre_cart_view']);

    }

    public static function set_stand_status_to_sold(array $items): void
    {
        foreach ($items as $item_meta) {
            $ordered_stand_id = $item_meta['standID'];
            $days = self::convert_days_into_array($item_meta['days']);
            foreach ($days as $day) {
                update_post_meta($ordered_stand_id, 'stand_meta_generalSellStatus-' . $day, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold);
            }
        }
    }

    public static function save_order_id_in_stands(array $order_meta): void
    {

        $order_id = $order_meta['order_id'];
        foreach ($order_meta['items'] ?? [] as $item_meta) {
            $ordered_stand_id = $item_meta['standID'];
            $days = self::convert_days_into_array($item_meta['days']);
            foreach ($days as $day) {
                update_post_meta($ordered_stand_id, 'stand_meta_generalInvoiceID-' . $day, $order_id);
            }
        }

        session_destroy();

    }

    public static function convert_day_name_into_array(string $day_name): array
    {
        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        return match ($day_name) {
            $option_table[BOOKINGPORT_Settings::$option_general_booking_day_1_name] => ['Day-1'],
            $option_table[BOOKINGPORT_Settings::$option_general_booking_day_2_name] => ['Day-2'],
            default => ['Day-1', 'Day-2']
        };
    }

    public static function reset_stand_data($itemMeta): void
    {
        $days = self::convert_days_into_array($itemMeta['days']);
        $itemID = $itemMeta['standID'];

        foreach ($days as $day) {
            $stand_status = get_post_meta($itemID, 'stand_meta_generalSellStatus-' . $day, true);

            /*
             * @description
             * reset stand data when the stand is not requested by the customer (edited by the admin)
             * including an exception array for double safety
             * */

            $excluded_stand_status = [
                BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer,
            ];

            if (!in_array($stand_status, $excluded_stand_status, true)) {
                update_post_meta($itemID, 'stand_meta_generalSellStatus-' . $day, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free);
                update_post_meta($itemID, 'stand_meta_generalSellStatusLastChange-' . $day, time());
                update_post_meta($itemID, 'stand_meta_generalSellUserId-' . $day, null);
                update_post_meta($itemID, 'stand_meta_generalSellUserName-' . $day, null);
                update_post_meta($itemID, 'stand_meta_generalInvoiceID-' . $day, null);
            }

        }
    }

    public static function reset_customer_requested_stand_data($itemMeta): void
    {
        $days = self::convert_days_into_array($itemMeta['days']);
        $itemID = $itemMeta['standID'];

        foreach ($days as $day) {

            /*
             * @description
             * reset stand data when the stand is requested by the customer (edited by the admin)
             * */

            update_post_meta($itemID, 'stand_meta_generalSellStatus-' . $day, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free);
            update_post_meta($itemID, 'stand_meta_generalSellStatusLastChange-' . $day, time());
            update_post_meta($itemID, 'stand_meta_generalSellUserId-' . $day, null);
            update_post_meta($itemID, 'stand_meta_generalSellUserName-' . $day, null);
            update_post_meta($itemID, 'stand_meta_generalInvoiceID-' . $day, null);
        }


    }

    public static function set_stand_to_expired($itemMeta): void
    {
        $days = self::convert_days_into_array($itemMeta['days']);
        $itemID = $itemMeta['standID'];

        foreach ($days as $day) {
            update_post_meta($itemID, 'stand_meta_generalSellStatus-' . $day, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired);
            update_post_meta($itemID, 'stand_meta_generalSellStatusLastChange-' . $day, time());
        }
    }

    public static function renew_reserved_stands_timestamps($items): void
    {

        foreach ($items as $itemMeta) {
            $days = self::convert_days_into_array($itemMeta['days']);
            $itemID = $itemMeta['standID'];

            foreach ($days as $day) {
                update_post_meta($itemID, 'stand_meta_generalSellStatusLastChange-' . $day, time());
            }
        }
    }

    public static function reset_user_cart_and_session(): void
    {
        session_start();
        session_destroy();
        WC()->cart->empty_cart();
    }

    /*
     * @description
     * this function checks if the user is still the owner of the stand. If not, the stand is set to free again
     * only checks for stands that are in cart or pre cart status and reserved by customers or the admin
     * @return bool => true if the user is not the owner of the stand anymore
     * */

    public static function validate_stand_client_side(array $itemMeta, mixed $current_user): bool
    {
        $days = self::convert_days_into_array($itemMeta['days']);
        $itemID = $itemMeta['standID'];
        $invalid = false;

        foreach ($days as $day) {

            $stand_status = get_post_meta($itemID, 'stand_meta_generalSellStatus-' . $day, true);
            $check_for_stand_status = [
                BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved,
            ];

            if (in_array($stand_status, $check_for_stand_status, true)) {
                $timestamp = get_post_meta($itemID, 'stand_meta_generalSellStatusLastChange-' . $day, true);
                $current_product_owner = get_post_meta($itemID, 'stand_meta_generalSellUserId-' . $day, true);
                $currentTime = time();
                $timeDifferenceInMin = ($currentTime - (int)$timestamp) / 60;
                $tenMinutes = 10;

                if ((int)$current_product_owner !== (int)$current_user) {
                    $invalid = true;
                }

                if ($timeDifferenceInMin >= $tenMinutes) {
                    $invalid = true;
                }
            }
        }

        return $invalid;
    }

    /*
    * @description
    * loops through multiple stands using the single stand validation method
    * @return bool => true if the user is not the owner of any stand
    * */

    public static function validate_stands_client_side(?array $items): bool
    {

        $current_user = get_current_user_id();
        $invalid = false;

        if ($items === null) {
            $invalid = true;
        }

        foreach ($items as $itemMeta) {

            if (self::validate_stand_client_side($itemMeta, $current_user)) {
                $invalid = true;
            }
        }

        return $invalid;
    }

    public static function handle_customer_reserved_stand_status_server_side(): void
    {

        $days = ['day-1', 'day-2'];

        foreach ($days as $day) {

            $customer_reserved_stands_args = [
                'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
                'posts_per_page' => -1,
                'meta_query' => [
                    [
                        'key' => 'stand_meta_generalSellStatus-' . ucfirst($day),
                        'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved,
                        'compare' => '=',
                    ]
                ]
            ];

            $customer_reserved_stands = new WP_Query($customer_reserved_stands_args);

            foreach ($customer_reserved_stands->posts ?? [] as $customer_reserved_stand) {

                $timestamp = get_post_meta($customer_reserved_stand->ID, 'stand_meta_generalSellStatusLastChange-' . ucfirst($day), true);
                $currentTime = time();
                $timeDifferenceInMin = ($currentTime - (int)$timestamp) / 60;
                $tenMinutes = 10;

                if ($timeDifferenceInMin >= $tenMinutes) {
                    self::reset_stand_data(
                        [
                            'standID' => $customer_reserved_stand->ID,
                            'days' => lcfirst($day)
                        ]
                    );
                }
            }
        }

    }

    public static function handle_admin_reserved_stand_status_server_side(): void
    {

        $admin_reserved_stands_args = [
            'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => BOOKINGPORT_CptMarket::$Cpt_MarketStatus,
                    'value' => BOOKINGPORT_CptMarket::$Cpt_MarketStatusAdminAccepted,
                    'compare' => '=',
                ]
            ]
        ];

        $admin_reserved_requests = new WP_Query($admin_reserved_stands_args);

        foreach ($admin_reserved_requests->posts ?? [] as $admin_reserved_request) {

            $timestamp = get_post_meta($admin_reserved_request->ID, BOOKINGPORT_CptMarket::$Cpt_MarketOfferTime, true);
            $currentTime = time();
            $timeDifferenceInMin = ($currentTime - (int)$timestamp) / 60;
            $options_table = get_option(BOOKINGPORT_Settings::$option_table);
            $is_short_reservation_active = $options_table[BOOKINGPORT_Settings::$option_short_reservation_interval] ?? '0';

            $time_interval_setting = match ($is_short_reservation_active) {
                '1' => 2880,
                default => 60 * 60 * 24 * 60
            };

            if ($timeDifferenceInMin >= $time_interval_setting) {

                $headers = [
                    'MIME-Version: 1.0',
                    'Content-type: text/html; charset=UTF-8'
                ];

                $additional_headers = implode("\r\n", $headers);
                $option_table = get_option(BOOKINGPORT_Settings::$option_table);
                $admin_email = $option_table[BOOKINGPORT_Settings::$option_email_booking_request];
                $market_prefix = $option_table[BOOKINGPORT_Settings::$option_market_prefix];
                $message = "Hallo admin, <br/> Das Angebot mit der Angebotsnummer " . $market_prefix . $admin_reserved_request->ID . " wurde nicht innerhalb des vorgegebenen Zeitfensters von 48 Stunden vom Kunden angenommen.";
                $subject = 'Abgelaufenes Angebot ' . $market_prefix . $admin_reserved_request->ID;
                wp_mail($admin_email, $subject, $message, $additional_headers);

                // change the status to expired and set the post to draft when the offer is expired
                update_post_meta($admin_reserved_request->ID, BOOKINGPORT_CptMarket::$Cpt_MarketStatus, BOOKINGPORT_CptMarket::$Cpt_MarketStatusExpired);
                wp_update_post([
                    'ID' => $admin_reserved_request->ID,
                    'post_status' => 'draft'
                ]);

                $stands = get_post_meta($admin_reserved_request->ID, BOOKINGPORT_CptMarket::$CPT_MarketStands, true);

                foreach ($stands ?? [] as $stand_meta) {
                    self::set_stand_to_expired($stand_meta);
                }
            }
        }
    }

    /**
     * @throws JsonException
     */
    public static function reset_stand_meta(): void
    {
        $item_meta = $_POST['itemToDelete'];
        self::reset_stand_data($item_meta);
        wp_die(json_encode($item_meta, JSON_THROW_ON_ERROR));
    }

    public static function reset_stands($items): void
    {
        foreach ($items ?? [] as $itemMeta) {
            $stand_id = $itemMeta['standID'];
            $days = self::convert_days_into_array($itemMeta['days']);

            foreach ($days as $day) {
                $stand_status = get_post_meta($stand_id, 'stand_meta_generalSellStatus-' . $day, true);
                if ($stand_status === BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved) {
                    self::reset_stand_data(
                        [
                            'standID' => $stand_id,
                            'days' => lcfirst($day)
                        ]
                    );
                }
            }
        }
    }

    public static function reset_all_stands(): bool
    {

        $args = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];

        $stands = new WP_Query($args);

        foreach ($stands->posts ?? [] as $stand) {
            self::reset_stand_data(
                ['standID' => $stand->ID,
                    'days' => 'both'
                ]
            );
        }

        return false;
    }

    /**
     * @throws JsonException
     */
    public static function render_pre_cart_view(): void
    {
        $response = [];
        $error_messages = [];
        $current_pre_cart_session = $_SESSION['items'] ?? [];
        $stands_requested_by_user = $_POST['items'] ?? [];

        $is_edit_request = $_POST['editRequest'];
        $requesting_user_id = get_current_user_id();
        $requesting_user = get_user_by('id', $requesting_user_id);
        $requesting_user_name = $requesting_user->billing_first_name . ' ' . $requesting_user->billing_last_name;
        $product_id = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);

        foreach ($stands_requested_by_user ?? [] as $item_meta) {

            $days = self::convert_days_into_array($item_meta['days']);
            $standID = $item_meta['standID'];
            $option_table = get_option(BOOKINGPORT_Settings::$option_table);

            $booked_days_array = [];
            $non_available_days = [];

            $street = get_post_meta($standID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
            $number = get_post_meta($standID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);

            foreach ($days as $day) {
                $user_id_is_saved_in_product = get_post_meta($item_meta['standID'], 'stand_meta_generalSellUserId-' . $day, true);

                /*
                 * @description
                 * check if the stand is already reserved by another user. If so, add an error message to the error message array
                 * */

                $stand_status = get_post_meta($item_meta['standID'], 'stand_meta_generalSellStatus-' . $day, true);

                if ($user_id_is_saved_in_product && $user_id_is_saved_in_product != $requesting_user_id && $stand_status === BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved) {
                    $non_available_days[] = self::get_booking_day_name(
                        lcfirst($day)
                    );

                } else {
                    /*
                     * @description
                     * if the stand is not reserved by another user, set the status to reserved and save the user id and user name
                     * */
                    $stand_sell_status = get_post_meta($item_meta['standID'], 'stand_meta_generalSellStatus-' . $day, true);
                    if ($stand_sell_status !== BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer) {

                        /*
                         * description
                         * here we add the stand to the booked stands and update the post meta accordingly
                         * */

                        $booked_days_array[] = lcfirst($day);

                        update_post_meta($item_meta['standID'], 'stand_meta_generalSellStatus-' . $day, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved);
                        update_post_meta($item_meta['standID'], 'stand_meta_generalSellUserId-' . $day, $requesting_user_id);
                        update_post_meta($item_meta['standID'], 'stand_meta_generalSellUserName-' . $day, $requesting_user_name);
                        update_post_meta($item_meta['standID'], 'stand_meta_generalSellStatusLastChange-' . $day, time());
                    }
                }
            }

            /*
             * description
             * if the stand is already reserved by another user, add an error message to the error message array
             * */

            if (!empty($non_available_days) && $is_edit_request === "false") {
                $error_message = 'Der Stand ' . $street . ' ' . $number . ' ist bereits für den ' . implode(', ', $non_available_days) . ' reserviert.';
                $error_messages[] = $error_message;
            }

            /*
            * @description
            * if the stand is not already in the current pre cart session, add it to the session
            * */

            if (!in_array($item_meta['standID'], $current_pre_cart_session, true) && count($booked_days_array) > 0) {

                $item_meta = [
                    'standID' => $item_meta['standID'],
                    'days' => self::convert_days_array_into_string($booked_days_array)
                ];

                $current_pre_cart_session[] = $item_meta;
            }

            if ($is_edit_request === "true") {

                $item_meta = [
                    'standID' => $item_meta['standID'],
                    'days' => $item_meta['days']
                ];

                $key = array_search($item_meta, $current_pre_cart_session, true);

                if ($key !== false) {
                    unset($current_pre_cart_session[$key]);
                }

                $current_pre_cart_session[] = $item_meta;
            }

        }

        if (!empty($current_pre_cart_session)) {

            foreach ($current_pre_cart_session as $session_item) {

                $option_table = get_option(BOOKINGPORT_Settings::$option_table);
                $standID = $session_item['standID'];
                $street = get_post_meta($standID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $number = get_post_meta($standID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $imageUrl = BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/stands/';
                $price = wc_get_product($product_id)->get_price() * count(self::convert_days_into_array($session_item['days']));

                $size = '';
                if (current_user_can('privat_troedel') || current_user_can('privat_anlieger') || current_user_can('administrator')) {
                    $size = '3m/1 Tapeziertisch';
                }

                $requested_item = [
                    'id' => $standID,
                    'booked_days' => $session_item['days'],
                    'street' => $street . ' ' . $number,
                    'number' => $number,
                    'image_urls' => [
                        'marker' => $imageUrl . 'stand-marker-white.svg',
                        'number' => $imageUrl . 'stand-number-white.svg',
                        'space' => $imageUrl . 'space-white.svg',
                        'delete' => $imageUrl . 'delete.svg'
                    ]
                ];

                if (current_user_can('privat_troedel') || current_user_can('privat_anlieger')) {
                    $requested_item['size'] = $size;
                    $requested_item['price'] = $price;
                }

                $response[] = $requested_item;
            }

            $_SESSION['items'] = $current_pre_cart_session;

            /*
             * @description
             * if the user is not the owner of the stand anymore, reset all stands and the session
             * else renew the timestamp of the reserved stands
             * */

            $reservation_is_invalid = self::validate_stands_client_side($current_pre_cart_session);

            if ($reservation_is_invalid && $is_edit_request === "false") {
                self::reset_stands($current_pre_cart_session);
                $_SESSION['items'] = [];
                $current_pre_cart_session = [];
                $error_messages[] = "Ihre Sitzung ist abgelaufen. Bitte buchen Sie ihre Stände erneut.";
            } else {
                self::renew_reserved_stands_timestamps($current_pre_cart_session);
            }
        }

        $output = [
            'response' => $response,
            'error_messages' => $error_messages,
            'current_pre_cart_session' => $current_pre_cart_session,
            'is_edit_request' => $is_edit_request,
        ];

        wp_die(json_encode($output, JSON_THROW_ON_ERROR));
    }

    public static function convert_days_into_array(string $days): array
    {
        return match ($days) {
            "day-1" => ["Day-1"],
            "day-2" => ["Day-2"],
            default => ["Day-1", "Day-2"]
        };
    }

    public static function convert_days_array_into_string(array $days): string
    {
        return match ($days) {
            ['day-1', 'day-2'] => 'both',
            default => lcfirst($days[0])
        };
    }

    public static function get_booking_day_name(string $day): string
    {
        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        return match ($day) {
            "day-1" => $option_table[BOOKINGPORT_Settings::$option_general_booking_day_1_name],
            "day-2" => $option_table[BOOKINGPORT_Settings::$option_general_booking_day_2_name],
            default => $option_table[BOOKINGPORT_Settings::$option_general_booking_both_days_name]
        };
    }

    public static function get_booking_day_prefix(string $day): string
    {
        return match ($day) {
            "both" => "Buchungstage",
            default => "Buchungstag"
        };
    }
}


