<?php
$user_id = get_current_user_id();
$option_table = get_option(BOOKINGPORT_Settings::$option_table);

$openRequestArg = [
    'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market,
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => BOOKINGPORT_CptMarket::$Cpt_MarketType,
            'value' => BOOKINGPORT_CptMarket::$Cpt_MarketRequest,
            'compare' => '=',
        ],
        [
            'key' => BOOKINGPORT_CptMarket::$CPT_MarketUserID,
            'value' => $user_id,
            'compare' => '=',
        ],
    ]
];

$readyRequestArg = [
    'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market,
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => BOOKINGPORT_CptMarket::$Cpt_MarketType,
            'value' => BOOKINGPORT_CptMarket::$Cpt_MarketOffer,
            'compare' => '=',
        ],
        [
            'key' => BOOKINGPORT_CptMarket::$CPT_MarketUserID,
            'value' => $user_id,
            'compare' => '=',
        ]
    ]
];

$openRequests = new WP_Query($openRequestArg);
$readyRequests = new WP_Query($readyRequestArg);
$offer_price = null;
$offer_width = null;
$offer_depth = null;
?>

<div class="wrapper">
    <h1 class="page-headline"><?php the_title(); ?></h1>

    <?php
    if (isset($_SESSION['successfully_deleted']) && !empty($_SESSION['successfully_deleted'])) {
        $deleted_items = $_SESSION['successfully_deleted']; ?>
        <div class="deleted-items">
            <h3>Sie haben das Angebot für die Stände:</h3>
            <p>
                <?php foreach ($deleted_items as $itemMeta) {
                    $days_string = $itemMeta['days'];
                    $days_name = BOOKINGPORT_StandStatusHandler::get_booking_day_name($days_string);
                    $standID = $itemMeta['standID'];
                    echo get_the_title($standID) . ' (' . $days_name . ')<br/>';
                } ?>
            </p>
            <h3>erfolgreich abgelehnt.</h3>
        </div>

        <?php
        $options_table = get_option(BOOKINGPORT_Settings::$option_table);
        $phone = $options_table[BOOKINGPORT_Settings::$option_general_phone];
        $mail = $options_table[BOOKINGPORT_Settings::$option_email_general_email]; ?>

        <div class="further-questions">
            <h3>Bei Rückfragen:</h3>
            <p>Freckenhorster Werbegemeinschaft <a
                        href="mailto:<?= $mail ?>"><?= $mail ?></a>
                oder per Telefon <a
                        href="tel:<?= $phone ?>"><?= $phone ?></a>
            </p>
        </div>
        <?php $_SESSION['successfully_deleted'] = null;
    }
    ?>

    <?php if ($readyRequests->found_posts > 0) {
        $options_table = get_option(BOOKINGPORT_Settings::$option_table);
        $market_prefix = $options_table[BOOKINGPORT_Settings::$option_market_prefix];
        foreach ($readyRequests->posts ?? [] as $readyRequest) {
            $stands = [];
            $standIds = []; ?>

            <div class="ready-request">
                <h3>Für Sie wurden folgende Stände als Paket hinterlegt:</h3>
                <h3>Angebot <?= $market_prefix . $readyRequest->ID ?></h3>

                <?php

                $offer_price = get_post_meta($readyRequest->ID, BOOKINGPORT_CptMarket::$CPT_MarketPrice, true);
                $offer_width = get_post_meta($readyRequest->ID, BOOKINGPORT_CptMarket::$CPT_MarketWidth, true);
                $offer_depth = get_post_meta($readyRequest->ID, BOOKINGPORT_CptMarket::$CPT_MarketDepth, true);
                $request_electricity = get_post_meta($readyRequest->ID, BOOKINGPORT_CptMarket::$CPT_MarketRequiresElectricity, true);
                $request_sortiment = get_post_meta($readyRequest->ID, BOOKINGPORT_CptMarket::$CPT_MarketAssociationSortiment, true);
                $request_water = get_post_meta($readyRequest->ID, BOOKINGPORT_CptMarket::$CPT_MarketRequiresWater, true);
                $request_sales_food = get_post_meta($readyRequest->ID, BOOKINGPORT_CptMarket::$CPT_MarketSalesFood, true);
                $request_sales_drinks = get_post_meta($readyRequest->ID, BOOKINGPORT_CptMarket::$CPT_MarketSalesDrinks, true);
                $request_ride = get_post_meta($readyRequest->ID, BOOKINGPORT_CptMarket::$CPT_MarketAssociationRide, true);
                $request_comment = get_post_meta($readyRequest->ID, BOOKINGPORT_CptMarket::$CPT_MarketComment, true);
                $offered_stands_array = get_post_meta($readyRequest->ID, BOOKINGPORT_CptMarket::$CPT_MarketStands, true); ?>

                <div class="offer-data">
                    <p>
                        <strong>Stand/Stände</strong> <br/>
                        <?php
                        $counter = 0;

                        foreach ($offered_stands_array as $item_meta):
                            $days_string = $item_meta['days'];
                            $days_name = BOOKINGPORT_StandStatusHandler::get_booking_day_name($days_string);
                            $item_ID = $item_meta['standID'];
                            echo '<div class="single-offer-position row"><img src="' . BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/stand-cat-grey.svg"><p>' . get_the_title($item_ID) . ' (' . $days_name . ')</p></div>';
                            $counter++;
                        endforeach; ?>

                    </p>
                    <hr>
                    <p><strong>Standmaße</strong></p>
                    <div class="row">
                        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/stands/space-grey.svg">
                        <p>Breite: <?= $offer_width ?>m</p>
                    </div>
                    <?php if (!empty($offer_depth)) { ?>
                        <div class="row">
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/stands/space-grey.svg">
                            <p>Tiefe: <?= $offer_depth ?>m</p>
                        </div>
                    <?php } ?>
                    <hr>
                    <?php if (!empty($request_electricity)) { ?>
                        <div class="row electricity">
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/electricity-grey.svg">
                            <p>Stromanschluss: <?= $request_electricity ?></p>
                        </div>
                    <?php } ?>

                    <?php if (!empty($request_water)) { ?>
                        <div class="row water">
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/water-grey.svg">
                            <p>Wasseranschluss: <?= $request_water ?></p>
                        </div>
                    <?php } ?>

                    <?php if (!empty($request_sales_food)) { ?>
                        <div class="row food">
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/food-grey.svg">
                            <p>Imbiss: <?= $request_sales_food ?></p>
                        </div>
                    <?php } ?>

                    <?php if (!empty($request_sales_drinks)) { ?>
                        <div class="row drinks">
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/beverage-grey.svg">
                            <p>Getränke: <?= $request_sales_drinks ?></p>
                        </div>
                    <?php } ?>

                    <?php if (!empty($request_sortiment)) { ?>
                        <div class="row sortiment">
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/stock-grey.svg">
                            <p>Sortiment: <?= $request_sortiment ?></p>
                        </div>
                    <?php } ?>

                    <?php if (!empty($request_ride)) { ?>
                        <div class="row ride">
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/fairground-grey.svg">
                            <p>Fahrgeschäft/e: <?= $request_ride ?></p>
                        </div>
                    <?php } ?>

                    <br/>
                    <?php if (!empty($request_comment)) { ?>
                        <label for="user_remarks"><strong>Meine Anmerkungen:</strong></label> <br/>
                        <p><?= $request_comment ?></p>
                    <?php } ?>
                    <hr>
                    <p><strong>Angebotspreis:</strong> <?= $offer_price ?> €</p>
                    <div class="button-row">
                        <div class="btn-primary btn trigger-modal open-modal" id="accept-<?= $readyRequest->ID ?>">Zur
                            Annahme
                        </div>
                        <div class="btn-secondary btn trigger-modal open-modal" id="deny-<?= $readyRequest->ID ?>">
                            Angebot
                            ablehnen
                        </div>
                    </div>
                </div>
            </div>

            <div class="deny-offer-container modal deny-<?= $readyRequest->ID ?>-modal">
                <div class="inner-modal">
                    <h3>Sind Sie sicher, dass Sie das Angebot <?= $market_prefix . $readyRequest->ID ?> ablehnen
                        wollen?</h3>
                    <textarea name="reason_deny_admin_offer" placeholder="Begründung (optional)"></textarea>
                    <div class="button-row">
                        <div data-src="<?= $readyRequest->ID ?>" class="btn-primary btn deny-offer">Angebot
                            ablehnen
                        </div>
                        <div class="btn-secondary btn back-to-offer close-modal">Zurück</div>
                    </div>
                </div>
            </div>

            <div class="accept-offer-container modal accept-<?= $readyRequest->ID ?>-modal">
                <div class="inner-modal">
                    <h3>Sind Sie sicher, dass Sie das Angebot <?= $market_prefix . $readyRequest->ID ?> annehmen
                        wollen?</h3>
                    <div class="button-row">
                        <a class="btn-primary btn"
                           href="/reservierungen-annehmen?request_id=<?= $readyRequest->ID ?>">Angebot
                            annehmen</a>
                        <div class="btn-secondary btn back-to-offer close-modal">Zurück</div>
                    </div>
                </div>
            </div>
            <?php
        }
    } ?>

    <h3 id="current-request-counter">Offene Anfragen: <?= count($openRequests->posts) ?></h3>
    <?php if ($openRequests->found_posts > 0) { ?>
        <div class="open-requests">
            <div class="inner-content">
                <?php foreach ($openRequests->posts ?? [] as $p) {
                    $request_width = get_post_meta($p->ID, BOOKINGPORT_CptMarket::$CPT_MarketWidth, true);
                    $request_depth = get_post_meta($p->ID, BOOKINGPORT_CptMarket::$CPT_MarketDepth, true);
                    $request_comment = get_post_meta($p->ID, BOOKINGPORT_CptMarket::$CPT_MarketComment, true);
                    $request_sortiment = get_post_meta($p->ID, BOOKINGPORT_CptMarket::$CPT_MarketAssociationSortiment, true);
                    $request_electricity = get_post_meta($p->ID, BOOKINGPORT_CptMarket::$CPT_MarketRequiresElectricity, true);
                    $request_water = get_post_meta($p->ID, BOOKINGPORT_CptMarket::$CPT_MarketRequiresWater, true);
                    $request_sales_food = get_post_meta($p->ID, BOOKINGPORT_CptMarket::$CPT_MarketSalesFood, true);
                    $request_sales_drinks = get_post_meta($p->ID, BOOKINGPORT_CptMarket::$CPT_MarketSalesDrinks, true);
                    $request_ride = get_post_meta($p->ID, BOOKINGPORT_CptMarket::$CPT_MarketAssociationRide, true);
                    $request_stand_meta = get_post_meta($p->ID, BOOKINGPORT_CptMarket::$CPT_MarketStands, true);
                    /*
                     * @description
                     * We can use [0] index here because we know that requests can only contain a single item
                     * */
                    $request_stand = get_the_title($request_stand_meta[0]['standID']);
                    $request_days = BOOKINGPORT_StandStatusHandler::get_booking_day_name($request_stand_meta[0]['days']);
                    ?>
                    <div class="single-request">
                        <h3><?= get_the_title($p->ID) ?></h3>
                        <div>
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-grey.svg">
                            <p><?php echo "{$request_stand} {$request_days}" ?></p>
                        </div>
                        <div>
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/space-grey.svg">
                            <p>Angeforderte Breite: <?= $request_width ?>m</p>
                        </div>

                        <?php if (!empty($request_depth)) { ?>
                            <div>
                                <img class="stand-marker-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/space-grey.svg">
                                <p>Angeforderte Tiefe: <?= $request_depth ?>m</p>
                            </div>
                        <?php } ?>
                        <?php if (!empty($request_electricity)) { ?>
                            <div class="row electricity">
                                <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/electricity-grey.svg">
                                <p>Stromanschluss: <?= $request_electricity ?></p>
                            </div>
                        <?php } ?>

                        <?php if (!empty($request_water)) { ?>
                            <div class="row water">
                                <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/water-grey.svg">
                                <p>Wasseranschluss: <?= $request_water ?></p>
                            </div>
                        <?php } ?>

                        <?php if (!empty($request_sales_food)) { ?>
                            <div class="row food">
                                <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/food-grey.svg">
                                <p>Imbiss: <?= $request_sales_food ?></p>
                            </div>
                        <?php } ?>

                        <?php if (!empty($request_sales_drinks)) { ?>
                            <div class="row drinks">
                                <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/beverage-grey.svg">
                                <p>Getränke: <?= $request_sales_drinks ?></p>
                            </div>
                        <?php } ?>

                        <?php if (!empty($request_sortiment)) { ?>
                            <div class="row sortiment">
                                <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/stock-grey.svg">
                                <p>Sortiment: <?= $request_sortiment ?></p>
                            </div>
                        <?php } ?>

                        <?php if (!empty($request_ride)) { ?>
                                <div class="row ride">
                                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/fairground-grey.svg">
                                    <p>Fahrgeschäft/e: <?= $request_ride ?></p>
                                </div>
                        <?php } ?>

                        <?php if (!empty($request_comment)) { ?>
                            <label for="user_remarks"><strong>Meine Anmerkungen:</strong></label>
                            <textarea name="user_remarks" readonly><?= $request_comment ?></textarea>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } else { ?>
        <p class="no-current-offers">Derzeit liegen keine offenen Anfragen von Ihnen vor.</p>
        <a id="new-stand-button" href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking] ?>"
           class="btn-tertiary btn">Neuen Stand Buchen</a>
    <?php } ?>
