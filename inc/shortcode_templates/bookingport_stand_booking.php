<?php
$user = wp_get_current_user();
$userRole = $user->roles[0];

/*
 * @description:
 * Checking if the items in the Session are still valid.
 */

$items = $_SESSION['items'] ?? [];
$invalid = BOOKINGPORT_StandStatusHandler::validate_stands_client_side($items);
$err_msg = "";

if ($invalid) {
    BOOKINGPORT_StandStatusHandler::reset_stands($items);
    $_SESSION['items'] = [];
    $err_msg = "Ihre Sitzung ist abgelaufen. Bitte buchen Sie ihre Stände erneut.";
}

$option_table = get_option(BOOKINGPORT_Settings::$option_table);

if (isset($option_table[BOOKINGPORT_Settings::$option_allow_stand_booking])) {
    wp_redirect($option_table[BOOKINGPORT_Settings::$option_redirects_dashboard]);
    exit();
}

if ($userRole === 'privat_anlieger' && !isset($option_table[BOOKINGPORT_Settings::$option_allow_private_anlieger])) {
    wp_redirect($option_table[BOOKINGPORT_Settings::$option_redirects_booking_not_available]);
    exit();
}

if ($userRole === 'privat_troedel' && !isset($option_table[BOOKINGPORT_Settings::$option_allow_private_troedler])) {
    wp_redirect($option_table[BOOKINGPORT_Settings::$option_redirects_booking_not_available]);
    exit();
} ?>


<div class="wrapper">
    <?php
    /*
     * @description:
     * throwing an error message if the session is invalid else renew their timestamps
     * */
    if (!empty($err_msg)): ?>
        <div id="error-messages">
            <div class="error-msg">
                <p class="error-messages"><?= $err_msg ?></p>
            </div>
        </div>
    <?php else:
        BOOKINGPORT_StandStatusHandler::renew_reserved_stands_timestamps($items);
    endif; ?>
    <h1 class="page-headline"><?= current_user_can('administrator') ? 'Anfragen bearbeiten' : the_title() ?></h1>
    <div class="form">

        <?php if (!current_user_can('administrator')) { ?>
            <h3 class="headline-conditions">Rahmenbedingungen</h3>
            <p>
                <label for="booking_year">Buchungsjahr</label>
                <input class="prefilled-input" type="text" name="booking_year" placeholder="<?= date('Y') ?>">
            </p>
            <p>
                <label for="booking_type">Verkäufer</label>
                <input class="prefilled-input" type="text" name="booking_type"
                       placeholder="<?= BOOKINGPORT_User::get_user_role_name_by_slug($userRole) ?>">
            </p>
        <?php } ?>

        <?php if (in_array($userRole, BOOKINGPORT_CptMarket::$private_group, true)) {
            include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/stand-booking/bookingport_stand_booking_private.php');
        } ?>

        <?php if (in_array($userRole, BOOKINGPORT_CptMarket::$commercial_group, true)) {
            include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/stand-booking/bookingport_stand_booking_association.php');
        } ?>

        <!--        --><?php //if (in_array($userRole, BOOKINGPORT_CptMarket::$business_group, true)) {
        //                include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/stand-booking/bookingport_stand_booking_business.php');
        //        } ?>

        <?php if (in_array($userRole, BOOKINGPORT_CptMarket::$admin_group, true)) {
            include(BOOKINGPORT_PLUGIN_PATH . 'inc/template_parts/stand-booking/bookingport_stand_booking_admin.php');
        } ?>

        <?php if (!current_user_can('administrator')) { ?>
            <p class="disclaimer">* Pflichtfelder</p>
        <?php } ?>
    </div>
</div>
<!-- adding to close the main tag inside the shortcode template to exclude the modal => otherwise the modal will slide out with the <main> container -->
</main>

<?php if (current_user_can('administrator') || current_user_can('privat_anlieger') || current_user_can('privat_troedel')) { ?>
    <div class="modal" id="privat-stand-booking-modal">
        <div class="close-stand-booking-modal" id="close-stand-booking-modal">
            <div class="stripe"></div>
            <div class="stripe"></div>
        </div>
        <h2 id="map-overview">Übersichtskarte</h2>
        <div class="customer-stand-booking-map map" id="customer-stand-booking-map"></div>
        <div class="map-explanation">
            In der <a href="#map-overview">Übersichtskarte</a> können Sie einzelne Stände anwählen, sowie
            zusätzliche
            Informationen zu Eingängen, Ständen, Gassen in
            der Nähe erhalten.
            Den angewählten Stand können Sie dann weiter unten in der <a href="#stand-selection">Standauswahl</a>
            auswählen und zu Ihrer "aktuellen
            Auswahl" hinzufügen.
            Mit "Auswahl bestätigen" gelangen Sie weiter zu Ihren Reservierungen.
        </div>
        <div class="info-wrapper">
            <div class="i-am-legend">
                <div class="row">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-green.svg">
                    <p>An beiden Tagen verfügbare Stände</p>
                </div>
                <div class="row">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-red.svg">
                    <p>An beiden Tagen belegte Stände</p>
                </div>
                <div class="row">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-lila.svg">
                    <p>Nur am <?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_1_name] ?> frei</p>
                </div>
                <div class="row">
                    <img
                            src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-blue.svg">
                    <p>Nur am <?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_2_name] ?> frei</p>
                </div>
                <div class="row">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/maps/fairground.svg">
                    <p>Fahrgeschäft</p>
                </div>
            </div>
            <div class="stand-filter">
                <div>
                    <label for="show-all-stands">An beiden Tagen verfügbare Stände</label>
                    <input id="show-all-stands" type="radio"
                           value="<?= $option_table[BOOKINGPORT_Settings::$option_general_booking_both_days_name] ?>"
                           name="customer-stand-filter">
                </div>
                <div>
                    <label for="<?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_1_name] ?>">Am <?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_1_name] ?>
                        verfügbare
                        Stände anzeigen</label>
                    <input id="<?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_1_name] ?>"
                           type="radio"
                           value="<?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_1_name] ?>"
                           name="customer-stand-filter">
                </div>

                <div>
                    <label for="<?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_2_name] ?>">Am <?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_2_name] ?>
                        verfügbare
                        Stände anzeigen</label>
                    <input id="<?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_2_name] ?>"
                           type="radio"
                           value="<?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_2_name] ?>"
                           name="customer-stand-filter">
                </div>

            </div>
        </div>
        <h3 id="stand-selection">Standauswahl</h3>
        <div class="customer-current-stand-status-info-container">
            <div class="currently-reserved-wrapper">
                <?php if (!current_user_can('administrator')) { ?>
                    <h4>Bereits für Sie reserviert</h4>
                <?php } ?>
                <?php if (current_user_can('administrator')) { ?>
                    <h4>Bereits in Reservierung</h4>
                <?php } ?>
                <div class="show-current-reservations"></div>
            </div>
            <div class="current-selection-wrapper">
                <h4>Aktuelle Auswahl</h4>
                <div class="show-current-selection"></div>
            </div>
        </div>
        <div class="form">
            <p id="search-filter-wrapper">
                <input type="text" name="filter_prefered_street" placeholder="Wunschstraße eingeben">
            </p>
            <div id="filter-prefered-street-result-container"></div>
            <div class="btn-primary close-privat-stand-booking-modal" id="submit-stand-booking">Auswahl übernehmen</div>
        </div>
    </div>
<?php } ?>

