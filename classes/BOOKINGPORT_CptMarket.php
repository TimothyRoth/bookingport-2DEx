<?php

class BOOKINGPORT_CptMarket
{
    public static string $Cpt_Market = 'bookingport_market';
    public static string $Cpt_MarketOfferTime = 'bookingport_market_offer_date';
    public static string $Cpt_MarketStatus = 'bookingport_market_status';
    public static string $Cpt_MarketStatusAdminAccepted = 'Vom Admin abgesendet';
    public static string $Cpt_MarketStatusCustomerAccepted = 'Vom Kunden angenommen';
    public static string $Cpt_MarketStatusExpired = 'Angebot abgelaufen (Zeitraum überschritten)';
    public static string $Cpt_MarketStatusAdminDenied = 'Vom Admin abgelehnt';
    public static string $Cpt_MarketStatusCustomerDenied = 'Vom Kunden abgelehnt';
    public static string $Cpt_MarketStatusCustomerRequested = 'Vom Kunden Angefragt';
    public static string $Cpt_MarketType = 'bookingport_market_type';
    public static string $Cpt_MarketOffer = 'Angebot';
    public static string $Cpt_MarketRequest = 'Anfrage';
    public static string $CPT_MarketPrice = 'bookingport_market_price';
    public static string $CPT_MarketComment = 'bookingport_market_comment';
    public static string $CPT_ReasonCustomerDenied = 'bookingport_market_reason_customer_denied';
    public static string $CPT_MarketUserID = 'bookingport_market_userID';
    public static string $CPT_MarketStands = 'bookingport_market_stands';
    public static string $CPT_MarketWidth = 'bookingport_market_stand_width';
    public static string $CPT_MarketDepth = 'bookingport_market_stand_depth';
    public static string $CPT_MarketRequiresElectricity = 'bookingport_market_electricity_required';
    public static string $CPT_MarketRequiresWater = 'bookingport_market_water_required';
    public static string $CPT_MarketSalesFood = 'bookingport_market_sales_food';
    public static string $CPT_MarketSalesDrinks = 'bookingport_market_sales_drinks';
    public static string $CPT_MarketAssociationName = 'bookingport_market_stand_association_name';
    public static string $CPT_MarketAssociationSortiment = 'bookingport_market_association_sortiment';
    public static string $CPT_MarketAssociationRide = 'bookingport_market_association_ride';
    public static array $private_group = [
        'privat_anlieger',
        'privat_troedel',
    ];
    public static array $admin_group = [
        'administrator',
    ];

    public static array $commercial_group = [
        'gewerblich',
        'schausteller',
        'verein',
        'mitglieder'
    ];

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('init', [__CLASS__, 'registerCPTMarket']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
    }

    public static function add_meta_boxes(): void
    {
        add_meta_box(
            'market_meta',
            __('Metadaten:', 'bookingport'),
            [__CLASS__, 'market_meta_callback'],
            self::$Cpt_Market,
            'advanced',
            'default'
        );
    }

    public static function market_meta_callback($post): void
    {
        $price = get_post_meta($post->ID, self::$CPT_MarketPrice, true);
        $offerTime = get_post_meta($post->ID, self::$Cpt_MarketOfferTime, true);

        if ($offerTime) {
            $offerTime = date('d.m.Y H:i:s', (int)$offerTime);
        }

        $status = get_post_meta($post->ID, self::$Cpt_MarketStatus, true);
        $reason_customer_denied = get_post_meta($post->ID, self::$CPT_ReasonCustomerDenied, true);
        $type = get_post_meta($post->ID, self::$Cpt_MarketType, true);
        $comment = get_post_meta($post->ID, self::$CPT_MarketComment, true);

        $userID = get_post_meta($post->ID, self::$CPT_MarketUserID, true);
        $user = get_user_by('id', $userID);
        $user_info = $user->billing_first_name . ' ' . $user->billing_last_name . ' (' . $user->user_email . ')';

        $stands = get_post_meta($post->ID, self::$CPT_MarketStands, true);
        $width = get_post_meta($post->ID, self::$CPT_MarketWidth, true);
        $depth = get_post_meta($post->ID, self::$CPT_MarketDepth, true);
        $association_name = get_post_meta($post->ID, self::$CPT_MarketAssociationName, true);
        $association_sortiment = get_post_meta($post->ID, self::$CPT_MarketAssociationSortiment, true);
        $association_ride = get_post_meta($post->ID, self::$CPT_MarketAssociationRide, true);
        $requires_water = get_post_meta($post->ID, self::$CPT_MarketRequiresWater, true);
        $requires_electricity = get_post_meta($post->ID, self::$CPT_MarketRequiresElectricity, true);
        $sales_drinks = get_post_meta($post->ID, self::$CPT_MarketSalesDrinks, true);
        $sales_food = get_post_meta($post->ID, self::$CPT_MarketSalesFood, true); ?>
        <div class="admin-panel">

            <?php if (!empty($type)) { ?>
                <p><b><label for="<?php echo self::$Cpt_MarketType; ?>">Typ:</label></b></br>
                    <input type="text" name="<?php echo self::$Cpt_MarketType; ?>"
                           id="<?php echo self::$Cpt_MarketType ?>" value="<?php echo $type; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($status)) { ?>
                <p><b><label for="<?php echo self::$Cpt_MarketStatus; ?>">Status:</label></b></br>
                    <input type="text" name="<?php echo self::$Cpt_MarketStatus; ?>"
                           id="<?php echo self::$Cpt_MarketStatus ?>" value="<?php echo $status; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($reason_customer_denied)) { ?>
                <p><b><label for="<?php echo self::$CPT_ReasonCustomerDenied ?>">Begründung des Kunden:</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_ReasonCustomerDenied ?>"
                           id="<?php echo self::$CPT_ReasonCustomerDenied ?>"
                           value="<?php echo $reason_customer_denied; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($offerTime)) { ?>
                <p><b><label for="<?php echo self::$Cpt_MarketOfferTime; ?>">Zeitpunkt des Angebots vom Admin:</label></b></br>
                    <input type="text" name="<?php echo self::$Cpt_MarketOfferTime; ?>"
                           id="<?php echo self::$Cpt_MarketOfferTime ?>" value="<?php echo $offerTime; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($price)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketPrice; ?>">Preis in €:</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_MarketPrice; ?>"
                           id="<?php echo self::$CPT_MarketPrice ?>" value="<?php echo $price; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($user_info)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketUserID; ?>">Kunde:</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_MarketUserID; ?>"
                           id="<?php echo self::$CPT_MarketUserID ?>" value="<?php echo $user_info; ?>" readonly></p>
            <?php } ?>
            <p><b><label">Stände:</label></b></br>
                <?php foreach ($stands as $stand): ?>
                    <input type="text" name="<?= $stand['standID'] ?>"
                           id="<?= $stand['standID'] ?>"
                           value="<?= get_the_title($stand['standID']) ?> <?= BOOKINGPORT_StandStatusHandler::get_booking_day_name($stand['days']) ?>"
                           readonly> <br/><br/>
                <?php endforeach; ?>
            </p>

            <?php if (!empty($requires_electricity)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketRequiresElectricity; ?>">Elekrizität:</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_MarketRequiresElectricity; ?>"
                           id="<?php echo self::$CPT_MarketRequiresElectricity ?>"
                           value="<?php echo $requires_electricity; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($requires_water)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketRequiresWater; ?>">Wasser:</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_MarketRequiresWater; ?>"
                           id="<?php echo self::$CPT_MarketRequiresWater ?>" value="<?php echo $requires_water; ?>"
                           readonly></p>
            <?php } ?>

            <?php if (!empty($sales_food)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketSalesFood; ?>">Imbiss:</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_MarketSalesFood; ?>"
                           id="<?php echo self::$CPT_MarketSalesFood ?>" value="<?php echo $sales_food; ?>" readonly>
                </p>
            <?php } ?>

            <?php if (!empty($sales_drinks)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketSalesDrinks; ?>">Getränke:</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_MarketSalesDrinks; ?>"
                           id="<?php echo self::$CPT_MarketSalesDrinks ?>" value="<?php echo $sales_drinks; ?>"
                           readonly></p>
            <?php } ?>

            <?php if (!empty($width)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketWidth; ?>">Breite (m) :</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_MarketWidth; ?>"
                           id="<?php echo self::$CPT_MarketWidth ?>" value="<?php echo $width; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($depth)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketDepth; ?>">Tiefe (m):</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_MarketDepth; ?>"
                           id="<?php echo self::$CPT_MarketDepth ?>" value="<?php echo $depth; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($comment)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketComment; ?>">Anmerkung:</b></label></br>
                    <textarea name="<?php echo self::$CPT_MarketComment; ?>"
                              id="<?php echo self::$CPT_MarketComment ?>"
                              readonly><?php echo $comment; ?></textarea></p>
            <?php } ?>

            <?php if (!empty($association_name)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketAssociationName ?>">Name / Bezeichnung:</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_MarketAssociationName; ?>"
                           id="<?php echo self::$CPT_MarketAssociationName ?>" value="<?php echo $association_name; ?>"
                           readonly></p>
            <?php } ?>

            <?php if (!empty($association_sortiment)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketAssociationSortiment ?>">Vereinssortiment:</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_MarketAssociationSortiment; ?>"
                           id="<?php echo self::$CPT_MarketAssociationSortiment ?>"
                           value="<?php echo $association_sortiment; ?>" readonly></p>
            <?php } ?>


            <?php if (!empty($association_ride)) { ?>
                <p><b><label for="<?php echo self::$CPT_MarketAssociationRide ?>">Geschäft/e</label></b></br>
                    <input type="text" name="<?php echo self::$CPT_MarketAssociationRide; ?>"
                           id="<?php echo self::$CPT_MarketAssociationRide ?>"
                           value="<?php echo $association_ride; ?>" readonly></p>
            <?php } ?>
        </div>

        <style>
            .admin-panel p {
                width: 300px;
            }

            .admin-panel input, textarea {
                width: 100%;
            }
        </style>

        <?php

    }

    public static function registerCPTMarket(): void
    {

        $labels = [
            'name' => _x('Inserate', 'post type general name', 'bookingport'),
            'singular_name' => _x('Inserat', 'post type singular name', 'bookingport'),
            'add_new' => _x('Hinzufügen', 'Inserat', 'bookingport'),
            'add_new_item' => __('Neues Inserat hinzufügen', 'bookingport'),
            'edit_item' => __('Inserat bearbeiten', 'bookingport'),
            'new_item' => __('Neues Inserat', 'bookingport'),
            'view_item' => __('Inserat anzeigen', 'bookingport'),
            'search_items' => __('Inserate suchen', 'bookingport'),
            'not_found' => __('Keine Inserate gefunden', 'bookingport'),
            'not_found_in_trash' => __('Keine Inserate im Papierkorb', 'bookingport'),
            'parent_item_colon' => ''
        ];

        $args = [
            'label' => __('Inserate', 'bookingport'),
            'description' => __('Inserate Beschreibung', 'bookingport'),
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-cart',
            'menu_position' => 3,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'publicly_queryable' => false,
            'has_archive' => true,
            'exclude_from_search' => true,
            'capability_type' => 'post',
            '_builtin' => false,
            'query_var' => true,
            'rewrite' => ['slug' => self::$Cpt_Market, 'with_front' => true],
            'supports' => ['title'],
            'show_in_rest' => false,
        ];

        register_post_type(self::$Cpt_Market, $args);
        flush_rewrite_rules();

    }
}