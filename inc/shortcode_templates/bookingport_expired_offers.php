<?php

$option_table = get_option(BOOKINGPORT_Settings::$option_table);
$market_prefix = $option_table[BOOKINGPORT_Settings::$option_market_prefix];

$expiredOfferArgs = [
    'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market,
    'posts_per_page' => -1,
    'post_status' => 'draft',
    'meta_query' => [
        [
            'key' => BOOKINGPORT_CptMarket::$Cpt_MarketStatus,
            'value' => BOOKINGPORT_CptMarket::$Cpt_MarketStatusExpired,
            'compare' => '='
        ]
    ]
];

$expiredOffers = new WP_Query($expiredOfferArgs); ?>
<div class="wrapper">
    <h1 class="page-headline"><?php the_title() ?></h1>
    <div class="search-filter">
        <input name="filter_expired_offers" type="text" class="search-stand-filter" placeholder="Suche...">
    </div>
    <div class="expired-offers-container">
        <?php if ($expiredOffers->found_posts === 0) { ?>
            <p>Derzeit gibt es keine abgelaufenen Angebote</p>
        <?php } ?>

        <?php foreach ($expiredOffers->posts ?? [] as $expiredOffer) {

            $ID = $expiredOffer->ID;
            $customer_ID = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketUserID, true);
            $offer_customer = get_user_by('id', $customer_ID);
            $customer_name = $offer_customer->billing_first_name . ' ' . $offer_customer->billing_last_name;
            $customer_email = $offer_customer->user_email;
            $customer_phone = $offer_customer->billing_phone;
            $offer_comment = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketComment, true);
            $offer_price = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketPrice, true);
            $offer_width = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketWidth, true);
            $offer_depth = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketDepth, true);
            $offer_electricity = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketRequiresElectricity, true);
            $offer_water = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketRequiresWater, true);;
            $offer_sales_food = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketSalesFood, true);
            $offer_sales_drinks = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketSalesDrinks, true);
            $offer_sortiment = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketAssociationSortiment, true);
            $offer_ride = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketAssociationRide, true);
            $offered_stands_array = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketStands, true); ?>

            <div class="single-offer">

                <h3 class="single-offer-title"><?php echo $market_prefix . $ID ?></h3>

                <div class="row single-offer-customer-name">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/customers/customer-grey.svg"
                    <p><?php echo $customer_name ?></p>
                </div>

                <div class="row single-offer-customer-email">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/customers/mail-grey.svg">
                    <a href="mailto:<?= $customer_email ?>"><?php echo $customer_email ?></a>
                </div>

                <div class="row single-offer-customer-phone">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/customers/phone-grey.svg">
                    <a href="tel:<?= $customer_phone ?>"><?php echo $customer_phone ?></a>
                </div>

                <?php $counter = 0;
                foreach ($offered_stands_array as $singleStand) {
                    $standID = $singleStand['standID'];
                    $offered_days_string = $singleStand['days'];
                    $offered_days = BOOKINGPORT_StandStatusHandler::get_booking_day_name($offered_days_string, $standID);
                    $stand_name = get_the_title($standID);
                    ?>
                    <div class="row single-offer-stand">
                        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/stand-cat-grey.svg">
                        <p class="single-offer-position"
                           id="<?= $standID ?>"><?= "{$stand_name} ({$offered_days})" ?></p>
                    </div>
                    <?php $counter++;
                } ?>

                <div class="row single-offer-stand-dimensions">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/stands/space-grey.svg">
                    <div>
                        <p>Breite: <?= $offer_width ?>m</p>
                        <?php if (!empty($offer_depth)) { ?>
                            <p>Tiefe: <?= $offer_depth ?>m</p>
                        <?php } ?>
                    </div>

                </div>

                <?php if (!empty($offer_electricity)) { ?>
                    <div class="row electricity">
                        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/electricity-grey.svg">
                        <p>Stromanschluss: <?= $offer_electricity ?></p>
                    </div>
                <?php } ?>

                <?php if (!empty($offer_water)) { ?>
                    <div class="row water">
                        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/water-grey.svg">
                        <p>Wasseranschluss: <?= $offer_water ?></p>
                    </div>
                <?php } ?>

                <?php if (!empty($offer_sales_food)) { ?>
                    <div class="row food">
                        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/food-grey.svg">
                        <p>Imbiss: <?= $offer_sales_food ?></p>
                    </div>
                <?php } ?>

                <?php if (!empty($offer_sales_drinks)) { ?>
                    <div class="row drinks">
                        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/beverage-grey.svg">
                        <p>Getränke: <?= $offer_sales_drinks ?></p>
                    </div>
                <?php } ?>

                <?php if (!empty($offer_sortiment)) { ?>
                        <div class="row sortiment">
                            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/stock-grey.svg">
                            <p>Sortiment: <?= $offer_sortiment ?></p>
                        </div>
                <?php } ?>

                <?php if (!empty($offer_ride)) { ?>
                    <div class="row ride">
                        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/orders/fairground-grey.svg">
                        <p>Fahrgeschäft/e: <?= $offer_ride ?></p>
                    </div>
                <?php } ?>

                <?php if (!empty($offer_comment)) { ?>
                    <label for="user_remarks"><strong>Meine Anmerkungen:</strong></label> <br/>
                    <p><?= $offer_comment ?></p>
                <?php } ?>

                <div class="seperator"></div>

                <div class="row single-offer-price">
                    <p><strong>Angebotspreis:</strong> <?= $offer_price ?> €</p>
                </div>

                <div class="button-row">
                    <div class="btn-primary btn trigger-modal open-reactivate-offer-modal" id="reactivate-offer">Angebot
                        erneut
                        ausstellen
                    </div>
                    <div class="btn-tertiary btn trigger-modal open-delete-offer-modal" id="delete-offer">Angebot
                        löschen
                    </div>
                </div>

                <div class="reactivate-offer-modal modal">
                    <div class="inner-modal">
                        <h3>Sind Sie sicher, dass Sie das Angebot <?= $market_prefix . $ID ?> erneut ausstellen
                            wollen?</h3>
                        <div class="button-row">
                            <div data-src="<?= $ID ?>" class="btn-primary btn reactivate-offer">Angebot
                                erneut ausstellen
                            </div>
                            <div class="btn-secondary btn back-to-offer close-modal">Zurück</div>
                        </div>
                    </div>
                </div>

                <div class="delete-offer-modal modal">
                    <div class="inner-modal">
                        <h3>Sind Sie sicher, dass Sie das Angebot <?= $market_prefix . $ID ?> löschen wollen?</h3>
                        <div class="button-row">
                            <div class="btn-primary btn delete-offer" data-src="<?= $ID ?>">Angebot
                                löschen
                            </div>
                            <div class="btn-secondary btn back-to-offer close-modal">Zurück</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

