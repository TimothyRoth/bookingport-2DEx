<div id="error-messages"></div>

<h3 class="headline-stand-selection" id="headline-stand-selection">Standauswahl</h3>
<?php
$user_ID = get_current_user_id();
$session_items = $_SESSION['items'] ?? [];
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>

<div class="no-selected-stands <?php if (!empty($session_items)) {
    echo 'hide';
} ?>">
    <p id="choose-stands-disclaimer">Einen oder mehrere Standplätze wählen</p>
    <div class="btn-primary trigger-privat-stand-booking-modal">Standplatz auf Karte wählen</div>
    <a class="faq-link" href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_faq] ?>">
        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/faq.svg">
        Sie haben Fragen zum Buchungsvorgang?
    </a>

</div>

<div class="selected-stands <?php if (empty($session_items)) {
    echo 'hide';
} ?>">
    <div id="selected-stands-container">
        <?php if (count($session_items) > 0) {
            $counter = 1;
            $product_id = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);

            foreach ($session_items as $session_item) {
                $id = $session_item['standID'];
                $days = BOOKINGPORT_StandStatusHandler::convert_days_into_array($session_item['days']);
                $street = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $number = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $imageUrl = BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/stands/';
                $price = wc_get_product($product_id)->get_price() * count($days);
                $booking_days = BOOKINGPORT_StandStatusHandler::get_booking_day_name($session_item['days']);
                $booking_days_prefix = BOOKINGPORT_StandStatusHandler::get_booking_day_prefix($session_item['days']);
                $size = '3m/1 Tapeziertisch';
                ?>

                <div class="single-result">
                    <div class="upper-row">
                        <p>Stand <?= $counter ?></p>
                        <div class="delete-item" data-src="<?= $id ?>" data-days="<?= $session_item['days'] ?>">Stand
                            löschen <img
                                    src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/delete.svg">
                        </div>
                    </div>
                    <div class="inner-content">
                        <div class="row selected-stand-street">
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-white.svg">
                            <p class="stand-street"><?= $street ?></p>
                        </div>
                        <div class="row selected-stand-number">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-white.svg">
                            <p class="stand-number stand-street">Standnummer: <?= $number ?> </p>
                        </div>
                        <div class="row selected-stand-days">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-white.svg">
                            <p class="stand-number stand-street"><?= $booking_days_prefix ?>: <?= $booking_days ?> </p>
                        </div>
                        <div class="row selected-stand-space">
                            <img class="stand-space-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/space-white.svg">
                            <p class="stand-space"><?= $size ?></p>
                        </div>
                        <div class="row selected-stand-price">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-white.svg">
                            <p class="stand-price"><?= $price ?> €</p>
                        </div>
                    </div>
                </div>
                <?php
                $counter++;
            }
        }
        ?>
    </div>
    <div class="btn-secondary trigger-privat-stand-booking-modal">Standauswahl ändern?</div>
    <div class="remarks-container">
        <h3>Sonstiges</h3>
        <p>Anmerkungen / Wünsche</p>
        <textarea name="user_remarks" id="user-remarks"
                  placeholder="Haben Sie besondere Anmerkungen zu Ihrem Aufbau, Wünsche oder ähnliches? Dann hinterlassen Sie uns hier eine kurze Nachricht."></textarea>
    </div>
    <div class="button-row">


        <input type="submit" onclick="" class="btn-primary <?= wp_get_current_user()->roles[0] ?>" id="user-checkout"
               value="Zahlung und Buchungsabschluss">
        <a href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard] ?>" class="btn-secondary">Abbrechen</a>
    </div>
</div>
