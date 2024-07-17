<?php

class BOOKINGPORT_PreCartHandler
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_ajax_delete_pre_cart_item', [__CLASS__, 'delete_pre_cart_item']);
        add_action('wp_ajax_nopriv_delete_pre_cart_item', [__CLASS__, 'delete_pre_cart_item']);

        add_action('wp_ajax_filter_stands', [__CLASS__, 'filter_stands']);
        add_action('wp_ajax_nopriv_filter_stands', [__CLASS__, 'filter_stands']);

        add_action('wp_ajax_show_current_pre_cart_selection', [__CLASS__, 'show_current_pre_cart_selection']);
        add_action('wp_ajax_nopriv_show_current_pre_cart_selection', [__CLASS__, 'show_current_pre_cart_selection']);

    }

    /**
     * @throws JsonException
     */
    public static function show_current_pre_cart_selection(): void
    {
        $response = [
            'current' => [],
            'reserved' => []
        ];

        foreach ($_SESSION['items'] ?? [] as $stand_meta) {

            $response['reserved'][] = [
                'name' => get_the_title($stand_meta['standID']),
                'reserved_days' => $stand_meta['days'],
            ];
        }

        foreach ($_POST['preCartItems'] ?? [] as $preCartItem) {
            $item_title = get_the_title($preCartItem['standID']);
            $item_days = BOOKINGPORT_StandStatusHandler::get_booking_day_name($preCartItem['days']);
            $response['current'][] = "{$item_title} ({$item_days})";
        }

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public
    static function filter_stands(): void
    {

        $response = [];

        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        $search_query = sanitize_text_field($_POST['searchQuery']);
        $amount = 5;

        if (!empty($_POST['showAll']) && empty($search_query)) {
            $amount = -1;
        }

        $clicked_stand = $_POST['itemID'];
        
        $filter_args = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => $amount,
            'order' => 'ASC',
            'post_status' => 'publish',
            's' => $search_query,
            'meta_query' => [
                [
                    'relation' => 'OR',
                    [
                        'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1,
                        'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                        'compare' => '=',
                    ],
                    [
                        'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2,
                        'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                        'compare' => '='
                    ]
                ]
            ]
        ];

        $day_filter = [
            $day1 = $option_table[BOOKINGPORT_Settings::$option_general_booking_day_1_name],
            $day2 = $option_table[BOOKINGPORT_Settings::$option_general_booking_day_2_name],
            $both = $option_table[BOOKINGPORT_Settings::$option_general_booking_both_days_name]
        ];

        if (in_array($search_query, $day_filter, true)) {

            $filter_args['meta_query'] = [];
            $filter_args['s'] = "";
            if ($search_query === $day1) {
                $filter_args['meta_query'][] = [
                    [
                        'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1,
                        'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                        'compare' => '=',
                    ]
                ];
            } else if ($search_query === $day2) {
                $filter_args['meta_query'][] =
                    [
                        [
                            'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2,
                            'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                            'compare' => '=',
                        ]
                    ];
            } else {
                $filter_args['meta_query'] = [
                    'relation' => 'AND',
                    [
                        'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1,
                        'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                        'compare' => '=',
                    ],
                    [
                        'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2,
                        'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                        'compare' => '='
                    ]
                ];
            }
        }

        if (!empty($clicked_stand)) {
            $filter_args['p'] = $clicked_stand;
        }

        $filtered_stands = new WP_Query($filter_args);
        $product_ID = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);
        $stand_lat = null;
        $stand_lng = null;

        if ($filtered_stands->found_posts > 0) {
            foreach ($filtered_stands->posts as $filtered_stand) {

                $availableDays = [];
                $ID = $filtered_stand->ID;

                if (get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1, true) === BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free) {
                    $availableDays[] = 'day-1';
                }

                if (get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2, true) === BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free) {
                    $availableDays[] = 'day-2';
                }

                $street = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $size = '3m / 1 Tapeziertisch';
                $price = (int)wc_get_product($product_ID)->get_price() * count($availableDays);
                $number = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $imageUrl = BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/stands/';

                if (empty($stand_lat) && empty($stand_lng)) {
                    $stand_lat = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLatitude, true);
                    $stand_lng = get_post_meta($ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLongitude, true);
                }

                $stand = [
                    'id' => $ID,
                    'availableDays' => $availableDays,
                    'geo' => [
                        'lat' => $stand_lat,
                        'lng' => $stand_lng
                    ],
                    'street' => $street . ' ' . $number,
                    'number' => $number,
                    'image_urls' => [
                        'marker' => $imageUrl . 'stand-marker-grey.svg',
                        'number' => $imageUrl . 'stand-number-grey.svg',
                        'space' => $imageUrl . 'space-grey.svg',
                    ]
                ];

                if (current_user_can('privat_troedel') || current_user_can('privat_anlieger')) {
                    $stand['size'] = $size;
                    $stand['price'] = $price;
                }

                $response[] = $stand;

            }

        } else {
            $response[] = ['message' => 'Leider konnten wir keine Stände finden, die Ihrer Suchanfrage entsprechen . '];
        }

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public
    static function delete_pre_cart_item(): void
    {
        $item_meta = $_POST['itemToDelete'];
        $current_session = $_SESSION['items'] ?? [];

        $key = array_search($item_meta, $current_session, true);

        if ($key !== false) {
            unset($_SESSION['items'][$key]);
        }

        /**
         * @description
         * IF the stand status is not a customer requested stand, the metadata will be reset. Else it will only be removed from the session
         * Without this adjustment, stands can not hold their request status when being added or deleted from pre-cart by the admin */

        BOOKINGPORT_StandStatusHandler::reset_stand_data($item_meta);

        wp_die(json_encode($item_meta['standID'], JSON_THROW_ON_ERROR));
    }

}