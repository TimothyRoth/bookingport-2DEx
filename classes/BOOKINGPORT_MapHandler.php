<?php

class BOOKINGPORT_MapHandler
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_ajax_getGoogleMapsStandsList', [__CLASS__, 'getGoogleMapsStandsList']);
        add_action('wp_ajax_nopriv_getGoogleMapsStandsList', [__CLASS__, 'getGoogleMapsStandsList']);

        add_action('wp_ajax_getGoogleMapsStandsList_Admin', [__CLASS__, 'getGoogleMapsStandsList_Admin']);
        add_action('wp_ajax_nopriv_getGoogleMapsStandsList_Admin', [__CLASS__, 'getGoogleMapsStandsList_Admin']);

        add_action('wp_ajax_admin_map_stand_filter', [__CLASS__, 'admin_map_stand_filter']);
        add_action('wp_ajax_nopriv_admin_map_stand_filter', [__CLASS__, 'admin_map_stand_filter']);

        add_action('wp_ajax_get_map_fairground', [__CLASS__, 'get_map_fairground']);
        add_action('wp_ajax_nopriv_get_map_fairground', [__CLASS__, 'get_map_fairground']);
    }

    /**
     * @throws JsonException
     */
    public static function getGoogleMapsStandsList(): void
    {
        $filter = $_POST['filter'];
        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        $result = [];

        $argsStands = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ];

        if (!empty($filter)) {

            if ($filter === $option_table[BOOKINGPORT_Settings::$option_general_booking_both_days_name]) {
                $argsStands['meta_query'] = [
                    'relation' => 'AND',
                    [
                        'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1,
                        'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                        'compare' => '=',
                    ],
                    [
                        'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2,
                        'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                        'compare' => '=',
                    ]
                ];
            } else if ($filter === $option_table[BOOKINGPORT_Settings::$option_general_booking_day_1_name]) {
                $argsStands['meta_query'] = [
                    [
                        'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1,
                        'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                        'compare' => '=',
                    ]
                ];
            } else {
                $argsStands['meta_query'] = [
                    [
                        'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2,
                        'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                        'compare' => '=',
                    ]
                ];
            }

        }

        $StandsData = new WP_Query($argsStands);

        if ($StandsData->found_posts > 0) {
            foreach ($StandsData->posts ?? [] as $p) {

                $title = get_the_title($p->ID);

                $result[] = [
                    'title' => $title,
                    'post_id' => $p->ID,
                    'lat' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLatitude, true),
                    'lng' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLongitude, true),
                    'stand_status' => [
                        'day-1' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1, true),
                        'day-2' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2, true)
                    ]
                ];
            }
        }

        wp_die(json_encode($result, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function getGoogleMapsStandsList_Admin(): void
    {

        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        $filter = $_POST['filter'];
        $result = [];

        $argsStands = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ];

        if (!empty($filter) && $filter !== "all") {

            $value = match ($filter) {
                'accessible' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                'booked' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold,
                'admin-reserved-requested' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin,
                'reserved' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer,
                'expired' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired,
                default => null
            };

            $argsStands['meta_query'] = [
                'relation' => 'OR',
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1,
                    'value' => $value,
                    'compare' => '=',
                ],
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2,
                    'value' => $value,
                    'compare' => '=',
                ]
            ];
        }

        $StandsData = new WP_Query($argsStands);

        if ($StandsData->found_posts > 0) {
            foreach ($StandsData->posts ?? [] as $p) {

                $title = get_the_title($p->ID);

                $result[] = [
                    'title' => $title, 'post_id' => $p->ID,
                    'lat' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLatitude),
                    'lng' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLongitude),
                    'stand_status' => [
                        'day-1' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1, true),
                        'day-2' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2, true)
                    ]
                ];
            }
        }

        wp_die(json_encode($result, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function admin_map_stand_filter(): void
    {
        $search = $_POST['search'];
        $dropdown = $_POST['dropdown'];
        $amount = $_POST['amount'];
        $itemID = $_POST['itemID'];
        $html = [];
        $geo = [];

        current_user_can('administrator') ? $is_admin = true : $is_admin = false;

        $args = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'post_status' => 'publish',
            'posts_per_page' => $amount,
        ];

        if ($dropdown !== 'all') {

            $value = match ($dropdown) {
                'accessible' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                'booked' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold,
                'admin-reserved-requested' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin,
                'reserved' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer,
                'expired' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired,
                default => null
            };

            $args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1,
                    'value' => $value,
                    'compare' => '=',
                ],
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2,
                    'value' => $value,
                    'compare' => '=',
                ],
            ];
        }

        if (isset($search) && !empty($search)) {
            $args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserNameDay1,
                    'value' => $search,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserNameDay2,
                    'value' => $search,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber,
                    'value' => $search,
                    'compare' => 'LIKE',
                ],
            ];

            // check if the meta query search for either user name or number is returns any results
            // if it doesnt we will perform the 's' query to search through the titles
            $check_if_meta_query_search_is_empty = new WP_Query($args);

            if (empty($check_if_meta_query_search_is_empty->posts)) {
                $args['meta_query'] = null;
                $args['s'] = $search;
            }
        }

        if (isset($itemID) && !empty($itemID)) {
            $args['p'] = $itemID;
        }

        $filteredStands = new WP_Query($args);
        $stand_geo_data = null;

        if ($filteredStands->found_posts > 0) {
            $option_table = get_option(BOOKINGPORT_Settings::$option_table);
            $invoice_page_link = get_home_url() . '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_invoice];
            foreach ($filteredStands->posts ?? [] as $p) {
                $id = $p->ID;
                $street = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $number = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $current_user_id_day_1 = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserIdDay1, true);
                $current_user_id_day_2 = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserIdDay2, true);
                $user_day_1 = get_user_by('ID', $current_user_id_day_1);
                $user_day_2 = get_user_by('ID', $current_user_id_day_2);
                $user_role_day_1 = null;
                $user_role_day_2 = null;

                if (is_object($user_day_1) && isset($user_day_1->roles[0])) {
                    $user_role_day_1 = BOOKINGPORT_User::get_user_role_name_by_slug($user_day_1->roles[0]);
                }

                if (is_object($user_day_2) && isset($user_day_2->roles[0])) {
                    $user_role_day_2 = BOOKINGPORT_User::get_user_role_name_by_slug($user_day_2->roles[0]);
                }

                $billing_first_name_day_1 = get_user_meta($current_user_id_day_1, 'billing_first_name', true);
                $billing_last_name_day_1 = get_user_meta($current_user_id_day_1, 'billing_last_name', true);
                $user_name_day_1 = $billing_first_name_day_1 . ' ' . $billing_last_name_day_1;

                $billing_first_name_day_2 = get_user_meta($current_user_id_day_2, 'billing_first_name', true);
                $billing_last_name_day_2 = get_user_meta($current_user_id_day_2, 'billing_last_name', true);
                $user_name_day_2 = $billing_first_name_day_2 . ' ' . $billing_last_name_day_2;

                $status_day_1 = match (get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1, true)) {
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved => 'In Reservierung',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold => 'Gebucht',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin => 'Vom Admin Reserviert',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer => 'Vom Kunden angefragt',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free => 'Frei',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired => '
                    Abgelaufenes Angebot',
                    default => null
                };
                $status_day_2 = match (get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2, true)) {
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved => 'In Reservierung',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold => 'Gebucht',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin => 'Vom Admin Reserviert',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer => 'Vom Kunden angefragt',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free => 'Frei',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired => '
                    Abgelaufenes Angebot',
                    default => null
                };
                $invoice_id_day_1 = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralInvoiceIDDay1, true);

                $status_form_day_1 = '';
                $status_form_day_2 = '';

                if (!empty($invoice_id_day_1)) {
                    $status_form_day_1 .= " <form class='redirect-to-customer-invoices invoice-id' method='POST' action='$invoice_page_link'><input type='hidden' name='invoice_info' value='{$invoice_id_day_1}' /><input type='submit' value='Zur Rechnung' /></form> ";
                }

                $invoice_id_day_2 = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralInvoiceIDDay2, true);

                if (!empty($invoice_id_day_2)) {
                    $status_form_day_2 .= " <form class='redirect-to-customer-invoices invoice-id' method='POST' action='$invoice_page_link'><input type='hidden' name='invoice_info' value='{$invoice_id_day_2}' /><input type='submit' value='Zur Rechnung' /></form> ";
                }

                $size = '3m/1 Tapeziertisch';

                // Here I grab the first from the loop as a reference for the map center, since the first result is the most accurate

                if (empty($stand_geo_data)) {
                    $stand_geo_data = [
                        'lat' => get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLatitude, true),
                        'lng' => get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLongitude, true)
                    ];
                }

                $days = [
                    'Day-1' => [
                        'user_id' => $current_user_id_day_1,
                        'user' => $user_day_1,
                        'user_name' => $user_name_day_1,
                        'user_role' => $user_role_day_1,
                        'status' => $status_day_1,
                        'invoice_id' => $invoice_id_day_1,
                        'form' => $status_form_day_1,
                    ],
                    'Day-2' => [
                        'user_id' => $current_user_id_day_2,
                        'user' => $user_day_2,
                        'user_name' => $user_name_day_2,
                        'user_role' => $user_role_day_2,
                        'status' => $status_day_2,
                        'invoice_id' => $invoice_id_day_2,
                        'form' => $status_form_day_2,
                    ]
                ];

                ob_start(); ?>

                <div class="single-result">
                    <div
                            class="inner-content">
                        <div class="row selected-stand-street">
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-grey.svg">
                            <p class="stand-street"><?= $street ?></p>
                        </div>
                        <div class="row selected-stand-number">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-grey.svg">
                            <p class="stand-number stand-street">Standnummer: <?= $number ?> </p>
                        </div>
                        <div class="row selected-stand-space">
                            <img class="stand-space-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/space-grey.svg">
                            <p class="stand-space"><?= $size ?></p>
                        </div>

                        <?php foreach ($days as $day => $meta) { ?>
                            <b><?= BOOKINGPORT_StandStatusHandler::get_booking_day_name(lcfirst($day)) ?></b>
                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/stand-cat-grey.svg">
                                <p><?= "{$meta['status']} {$meta['form']}" ?></p>
                            </div>
                            <?php if ($is_admin && $meta['status'] !== 'Frei') { ?>
                                <div class="row selected-stand-number">
                                    <img class="stand-number-image"
                                         src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/customer-grey.svg">
                                    <p><?php echo $meta['user_name'] . ' (' . ucfirst($meta['user_role']) . ')'; ?></p>
                                </div>

                                <div class="row selected-stand-number">
                                    <img class="stand-number-image"
                                         src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/mail-grey.svg">
                                    <a href="mailto<?= $meta['user']->user_email ?>"><?= $meta['user']->user_email ?></a>
                                </div>

                                <div class="row selected-stand-number">
                                    <img class="stand-number-image"
                                         src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/phone-grey.svg">
                                    <a href="tel:<?= $meta['user']->billing_phone ?>"><?= $meta['user']->billing_phone ?></a>
                                </div>

                                <div class="change-customer">
                                    <a class="customer-change-button"
                                       href="<?= get_home_url() ?>/wp-admin/user-edit.php?user_id=<?= $meta['user_id'] ?>"
                                       id="change_customer">
                                        Zur Kundenverwaltung
                                    </a>
                                </div>

                                <form class="redirect-to-all-customer-invoices" method="POST"
                                      action="<?= $invoice_page_link ?>">
                                    <input type="hidden" name="invoice_info" value="<?= $meta['user_name'] ?>">
                                    <input type="submit" value="Alle Rechnungen des Kunden"/>
                                </form>
                            <?php }
                        } ?>
                    </div>
                </div>
                <?php
                $html[] = ob_get_clean();
            }
            if ($filteredStands->found_posts > $amount) {
                $html[] = '<div class="btn-secondary show-more-stands">Mehr anzeigen</div>';
            }
        } else {
            $html[] = "Leider konnten wir keine Stände finden, die Ihrer Suchanfrage entsprechen.";
        }

        $response = [
            'html' => $html,
            'geo' => $stand_geo_data,
        ];

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function get_map_fairground(): void
    {

        $response = [];

        $args = [
            'post_type' => BOOKINGPORT_CptFreespace::$Cpt_Freespace,
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];

        $freespaces = new WP_Query($args);

        if ($freespaces->found_posts > 0) {
            foreach ($freespaces->posts ?? [] as $freespace) {

                $latitude = get_post_meta($freespace->ID, BOOKINGPORT_CptFreespace::$Cpt_Freespace_Lat, true);
                $longitude = get_post_meta($freespace->ID, BOOKINGPORT_CptFreespace::$Cpt_Freespace_Lng, true);
                $infoText = get_post_field('post_content', $freespace->ID);

                $mapObject = [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'infoText' => $infoText
                ];

                $response[] = $mapObject;
            }
        }

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));

    }

}