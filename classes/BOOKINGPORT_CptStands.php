<?php

class BOOKINGPORT_CptStands
{
    public static string $Cpt_Stands = 'bookingport_stands';
    public static string $Cpt_Stand_Meta_GeoLatitude = 'stand_meta_geoLatitude';
    public static string $Cpt_Stand_Meta_GeoLongitude = 'stand_meta_geoLongitude';
    public static string $Cpt_Stand_Meta_GeoStreetname = 'stand_meta_geoStreetname';
    public static string $Cpt_Stand_Meta_GeoStreetNumber = 'stand_meta_geoStreetNumber';
    public static string $Cpt_Stand_Meta_GeneralNumber = 'stand_meta_generalNumber';
    public static string $Cpt_Stand_Meta_GeneralSellStatusDay1 = 'stand_meta_generalSellStatus-Day-1';
    public static string $Cpt_Stand_Meta_GeneralSellStatusDay2 = 'stand_meta_generalSellStatus-Day-2';
    public static string $Cpt_Stand_Meta_GeneralInvoiceIDDay1 = 'stand_meta_generalInvoiceID-Day-1';
    public static string $Cpt_Stand_Meta_GeneralInvoiceIDDay2 = 'stand_meta_generalInvoiceID-Day-2';
    public static string $Cpt_Stand_Meta_GeneralSellStatusLastChangeDay1 = 'stand_meta_generalSellStatusLastChange-Day-1';
    public static string $Cpt_Stand_Meta_GeneralSellStatusLastChangeDay2 = 'stand_meta_generalSellStatusLastChange-Day-2';
    public static string $Cpt_Stand_Meta_GeneralSellUserIdDay1 = 'stand_meta_generalSellUserId-Day-1';
    public static string $Cpt_Stand_Meta_GeneralSellUserIdDay2 = 'stand_meta_generalSellUserId-Day-2';
    public static string $Cpt_Stand_Meta_GeneralSellUserNameDay1 = 'stand_meta_generalSellUserName-Day-1';
    public static string $Cpt_Stand_Meta_GeneralSellUserNameDay2 = 'stand_meta_generalSellUserName-Day-2';
    public static string $Cpt_Stand_Status_Free = '0';
    public static string $Cpt_Stand_Status_Reserved = '1';
    public static string $Cpt_Stand_Status_Sold = '2';
    public static string $Cpt_Stand_Status_Reserved_By_Admin = '3';
    public static string $Cpt_Stand_Status_Requested_By_Customer = '4';
    public static string $Cpt_Stand_Status_Admin_Offer_Expired = '5';

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('init', [__CLASS__, 'registerCPTStands']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_fields']);
    }

    public static function add_meta_boxes(): void
    {
        add_meta_box(
            'stand_meta_geo',
            __('Stand: Geo', 'bookingport'),
            [__CLASS__, 'stand_meta_geodata_callback'],
            self::$Cpt_Stands,
            'advanced',
            'default'
        );

        add_meta_box(
            'stand_meta_general',
            __('Stand: General', 'bookingport'),
            [__CLASS__, 'stand_meta_general_callback'],
            self::$Cpt_Stands,
            'advanced',
            'default'
        );

    }

    public static function registerCPTStands(): void
    {

        $labels = [
            'name' => _x('Stände', 'post type general name', 'bookingport'),
            'singular_name' => _x('Stand', 'post type singular name', 'bookingport'),
            'add_new' => _x('Hinzufügen', 'Stand hinzufügen', 'bookingport'),
            'add_new_item' => __('Neuen Stand hinzufügen', 'bookingport'),
            'edit_item' => __('Stand bearbeiten', 'bookingport'),
            'new_item' => __('Neuer Stand', 'bookingport'),
            'view_item' => __('Stand anzeigen', 'bookingport'),
            'search_items' => __('Nach Ständen suchen', 'bookingport'),
            'not_found' => __('Keine Stände gefunden', 'bookingport'),
            'not_found_in_trash' => __('Keine Stände im Papierkorb', 'bookingport'),
            'parent_item_colon' => ''
        ];

        $args = [
            'label' => __('Stände', 'bookingport'),
            'description' => __('Stände Beschreibung', 'bookingport'),
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-admin-multisite',
            'menu_position' => 2,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'publicly_queryable' => false,
            'has_archive' => true,
            'exclude_from_search' => true,
            'capability_type' => 'post',
            '_builtin' => false,
            'query_var' => true,
            'rewrite' => ['slug' => self::$Cpt_Stands, 'with_front' => true],
            'supports' => ['title'],
            'show_in_rest' => false,
        ];

        register_post_type(self::$Cpt_Stands, $args);
        flush_rewrite_rules();
    }

    public static function stand_meta_general_callback($post): void
    {

        wp_nonce_field(self::$Cpt_Stands . 'PostMeta_data', self::$Cpt_Stands . 'PostMeta_nonce');

        $number = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralNumber, true);
        $sellStatusLastChangeDay1 = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralSellStatusLastChangeDay1, true);
        $invoiceIDDay1 = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralInvoiceIDDay1, true);
        $sellUserIdDay1 = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralSellUserIdDay1, true);
        $sellUserNameDay1 = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralSellUserNameDay1, true);
        $sellStatusLastChangeDay2 = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralSellStatusLastChangeDay2, true);
        $invoiceIDDay2 = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralInvoiceIDDay2, true);
        $sellUserIdDay2 = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralSellUserIdDay2, true);
        $sellUserNameDay2 = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeneralSellUserNameDay2, true);
        $sellStatus_Label_day_1 = self::get_sell_status_label($post->ID, self::$Cpt_Stand_Meta_GeneralSellStatusDay1);
        $sellStatus_Label_day_2 = self::get_sell_status_label($post->ID, self::$Cpt_Stand_Meta_GeneralSellStatusDay2);

        if ($sellStatusLastChangeDay1) {
            $sellStatusLastChangeDay1 = date('m.d.Y H:i:s', (int)$sellStatusLastChangeDay1);
        }

        if ($sellStatusLastChangeDay2) {
            $sellStatusLastChangeDay2 = date('m.d.Y H:i:s', (int)$sellStatusLastChangeDay2);
        } ?>

        <div>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralNumber ?>">Stand-Nummer</label>
                <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralNumber ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralNumber ?>" value="<?= $number ?>"></p>
            <br/>
            <hr>
            <br/>
            <b>Tag 1</b>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellStatusDay1 ?>">Verkaufs-Status</label>
                <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellStatusDay1 ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralSellStatusDay1 ?>"
                       value="<?= $sellStatus_Label_day_1 ?>" disabled>
            </p>
            <?php if (!empty($invoiceIDDay1)) { ?>
                <p><label for="<?= self::$Cpt_Stand_Meta_GeneralInvoiceIDDay1 ?>">Rechnungsnummer</label>
                    <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralInvoiceIDDay1 ?>"
                           id="<?= self::$Cpt_Stand_Meta_GeneralInvoiceIDDay1 ?>"
                           value="<?= $invoiceIDDay1 ?>" disabled>
                </p>
            <?php } ?>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellStatusLastChangeDay1 ?>">Datum der letzten
                    Statusänderung</label>
                <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellStatusLastChangeDay1 ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralSellStatusLastChangeDay1 ?>"
                       value="<?= $sellStatusLastChangeDay1 ?>" disabled></p>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellUserIdDay1 ?>">User-ID</label>
                <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellUserIdDay1 ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralSellUserIdDay1 ?>" value="<?= $sellUserIdDay1 ?>" disabled>
            </p>
            <?php if (!empty($sellUserNameDay1)) { ?>
                <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellUserNameDay1 ?>">User-Name</label>
                    <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellUserNameDay1 ?>"
                           id="<?= self::$Cpt_Stand_Meta_GeneralSellUserNameDay1 ?>" value="<?= $sellUserNameDay1 ?>"
                           disabled>
                </p>
            <?php } ?>
            <br/>
            <hr>
            <br/>
            <b>Tag 2</b>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellStatusDay2 ?>">Verkaufs-Status</label>
                <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellStatusDay2 ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralSellStatusDay2 ?>"
                       value="<?= $sellStatus_Label_day_2 ?>" disabled>
            </p>
            <?php if (!empty($invoiceIDDay2)) { ?>
                <p><label for="<?= self::$Cpt_Stand_Meta_GeneralInvoiceIDDay2 ?>">Rechnungsnummer</label>
                    <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralInvoiceIDDay2 ?>"
                           id="<?= self::$Cpt_Stand_Meta_GeneralInvoiceIDDay2 ?>"
                           value="<?= $invoiceIDDay2 ?>" disabled>
                </p>
            <?php } ?>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellStatusLastChangeDay2 ?>">Datum der letzten
                    Statusänderung</label>
                <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellStatusLastChangeDay2 ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralSellStatusLastChangeDay2 ?>"
                       value="<?= $sellStatusLastChangeDay2 ?>" disabled></p>
            <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellUserIdDay2 ?>">User-ID</label>
                <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellUserIdDay2 ?>"
                       id="<?= self::$Cpt_Stand_Meta_GeneralSellUserIdDay2 ?>" value="<?= $sellUserIdDay2 ?>" disabled>
            </p>
            <?php if (!empty($sellUserNameDay2)) { ?>
                <p><label for="<?= self::$Cpt_Stand_Meta_GeneralSellUserNameDay2 ?>">User-Name</label>
                    <input type="text" name="<?= self::$Cpt_Stand_Meta_GeneralSellUserNameDay2 ?>"
                           id="<?= self::$Cpt_Stand_Meta_GeneralSellUserNameDay2 ?>" value="<?= $sellUserNameDay2 ?>"
                           disabled>
                </p>
            <?php } ?>
            <br/>
        </div>

        <?php

    }

    public static function stand_meta_geodata_callback($post): void
    {

        wp_nonce_field(self::$Cpt_Stands . 'PostMeta_data', self::$Cpt_Stands . 'PostMeta_nonce');

        $geoLatitude = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeoLatitude, true);
        $geoLongitude = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeoLongitude, true);
        $streetName = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeoStreetname, true);
        $streetNumber = get_post_meta($post->ID, self::$Cpt_Stand_Meta_GeoStreetNumber, true);

        ?>
        <div>
            <label for="<?= self::$Cpt_Stand_Meta_GeoLatitude ?>">Latitude:</label>
            <input type="text" name="<?= self::$Cpt_Stand_Meta_GeoLatitude ?>"
                   id="<?= self::$Cpt_Stand_Meta_GeoLatitude ?>" value="<?= $geoLatitude ?>"><br>
            <label for="<?= self::$Cpt_Stand_Meta_GeoLongitude ?>">Longitude:</label>
            <input type="text" name="<?= self::$Cpt_Stand_Meta_GeoLongitude ?>"
                   id="<?= self::$Cpt_Stand_Meta_GeoLongitude ?>" value="<?= $geoLongitude ?>"><br>
            <label for="<?= self::$Cpt_Stand_Meta_GeoStreetname ?>">Straße:</label>
            <input type="text" name="<?= self::$Cpt_Stand_Meta_GeoStreetname ?>"
                   id="<?= self::$Cpt_Stand_Meta_GeoStreetname ?>" value="<?= $streetName ?>"><br>
            <label for="<?= self::$Cpt_Stand_Meta_GeoStreetNumber ?>">Hausnummer:</label>
            <input type="text" name="<?= self::$Cpt_Stand_Meta_GeoStreetNumber ?>"
                   id="<?= self::$Cpt_Stand_Meta_GeoStreetNumber ?>" value="<?= $streetNumber ?>">
        </div>
        <?php
    }

    public static function save_fields($post_id)
    {

        if (!isset($_POST[self::$Cpt_Stands . 'PostMeta_nonce'])) {
            return $post_id;
        }

        $nonce = $_POST[self::$Cpt_Stands . 'PostMeta_nonce'];
        if (!wp_verify_nonce($nonce, self::$Cpt_Stands . 'PostMeta_data')) {
            return $post_id;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        /**
         * @description
         * Save the status of the stand as free the first time, the stand is added to the custom post type
         */
        if (empty(get_post_meta($post_id, self::$Cpt_Stand_Meta_GeneralSellStatusDay1, true))) {
            update_post_meta($post_id, self::$Cpt_Stand_Meta_GeneralSellStatusDay1, self::$Cpt_Stand_Status_Free);
        }

        /**
         * @description
         * Save the status of the stand as free the first time, the stand is added to the custom post type
         */
        if (empty(get_post_meta($post_id, self::$Cpt_Stand_Meta_GeneralSellStatusDay2, true))) {
            update_post_meta($post_id, self::$Cpt_Stand_Meta_GeneralSellStatusDay2, self::$Cpt_Stand_Status_Free);
        }

        $meta_fields = [
            self::$Cpt_Stand_Meta_GeoLatitude,
            self::$Cpt_Stand_Meta_GeoLongitude,
            self::$Cpt_Stand_Meta_GeoStreetname,
            self::$Cpt_Stand_Meta_GeoStreetNumber,
            self::$Cpt_Stand_Meta_GeneralSellStatusDay1,
            self::$Cpt_Stand_Meta_GeneralSellStatusDay2,
            self::$Cpt_Stand_Meta_GeneralNumber,
            self::$Cpt_Stand_Meta_GeneralSellStatusLastChangeDay1,
            self::$Cpt_Stand_Meta_GeneralSellStatusLastChangeDay2,
            self::$Cpt_Stand_Meta_GeneralSellUserIdDay1,
            self::$Cpt_Stand_Meta_GeneralSellUserIdDay2
        ];

        foreach ($meta_fields ?? [] as $meta_field) {
            if (isset($_POST[$meta_field])) {
                update_post_meta($post_id, $meta_field, $_POST[$meta_field]);
            }
        }

        return $post_id;
    }

    public static function get_sell_status_label(int $post_id, string $day): string
    {

        return match (get_post_meta($post_id, $day, true)) {
            self::$Cpt_Stand_Status_Free => 'Frei',
            self::$Cpt_Stand_Status_Reserved => 'Reserviert',
            self::$Cpt_Stand_Status_Sold => 'Verkauft',
            self::$Cpt_Stand_Status_Reserved_By_Admin => 'Reserviert (Admin)',
            self::$Cpt_Stand_Status_Requested_By_Customer => 'Angefragt',
            self::$Cpt_Stand_Status_Admin_Offer_Expired => 'Abgelaufen',
            default => 'kein Status'
        };
    }

    public static function get_admin_map_stand_meta(): array
    {

        $admin_map_stand_meta = [];

        $args = [
            'post_type' => self::$Cpt_Stands,
            'posts_per_page' => -1,
        ];

        $allStands = new WP_Query($args);

        foreach ($allStands->posts as $p) {

            $standMeta = [];
            $stand_ID = $p->ID;

            $standMeta['standID'] = $stand_ID;
            $standMeta['days'] = [
                'day-1' => get_post_meta($stand_ID, self::$Cpt_Stand_Meta_GeneralSellStatusDay1, true),
                'day-2' => get_post_meta($stand_ID, self::$Cpt_Stand_Meta_GeneralSellStatusDay2, true)
            ];

            $admin_map_stand_meta[] = $standMeta;
        }

        return $admin_map_stand_meta;
    }

    public static function get_amount_of_stand_units_by_status($status): int
    {

        $total_stand_meta = BOOKINGPORT_CptStands::get_admin_map_stand_meta();
        $units = 0;

        foreach ($total_stand_meta as $iValue) {
            foreach ($iValue['days'] as $day => $value) {
                if ($value === $status) {
                    $units++;
                }
            }
        }

        return $units;
    }

    public static function get_single_product_price(): int
    {
        $product_id = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);
        return (int)wc_get_product($product_id)->get_price();
    }
}
