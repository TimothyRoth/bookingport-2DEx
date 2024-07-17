<?php
if (!current_user_can('administrator')) {
    wp_redirect('/');
}

current_user_can('administrator') ? $is_admin = true : $is_admin = false;

$args = [
    'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
    'posts_per_page' => 5,
];

$option_table = get_option(BOOKINGPORT_Settings::$option_table);
$filteredStands = new WP_Query($args); ?>

<div class="map" id="admin-stand-overview-map"></div>

<div class="download-excel-modal">
    <h3>Die Standbuchungsliste wird für Sie vorbereitet<br/>
        Dieser Vorgang kann einige Zeit in Anspruch nehmen <br/>
        Wir bitten Sie daher um etwas Geduld</h3>
</div>

<div class="wrapper">
    <div class="i-am-legend">
        <div class="row">
            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-green.svg">
            <p>An beiden Tagen verfügbare Stände</p>
        </div>
        <div class="row">
            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-red.svg">
            <p>An beiden Tagen gebucht / reserviert</p>
        </div>
        <div class="row">
            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-blue.svg">
            <p>Nur am <?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_1_name] ?> gebucht /
                reserviert</p>
        </div>
        <div class="row">
            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-lila.svg">
            <p>Nur am <?= $option_table[BOOKINGPORT_Settings::$option_general_booking_day_2_name] ?> gebucht /
                reserviert</p>
        </div>
        <div class="row">
            <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/maps/fairground.svg">
            <p>Fahrgeschäft</p>
        </div>
    </div>
</div>

<div class="wrapper">
    <h1 class="page-headline"><?php the_title(); ?></h1>
    <div class="select-filter">
        <label for="filter-stand-status">Sortieren nach</label>
        <select name="filter-stand-status">
            <option value="all">Alle Stände</option>
            <option value="accessible">Frei</option>
            <option value="booked">Gebucht</option>
            <option value="reserved">Vom Kunden angefragt</option>
            <option value="admin-reserved-requested">Vom Admin reserviert</option>
            <option value="expired">Aus abgelaufenen Angeboten</option>
        </select>
    </div>

    <div class="search-filter">
        <input name="admin-filter-stands" type="text" class="search-stand-filter" placeholder="Suche...">
    </div>

    <div class="booking-overview">
        <?php
        $free_units = BOOKINGPORT_CptStands::get_amount_of_stand_units_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free);
        $reserved_units = BOOKINGPORT_CptStands::get_amount_of_stand_units_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved);
        $booked_units = BOOKINGPORT_CptStands::get_amount_of_stand_units_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold);
        $customer_requested_units = BOOKINGPORT_CptStands::get_amount_of_stand_units_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer);
        $admin_requested_units = BOOKINGPORT_CptStands::get_amount_of_stand_units_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin);
        $expired_units = BOOKINGPORT_CptStands::get_amount_of_stand_units_by_status(BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired);
        ?>


        <p id="accessible">
            Frei: <?= $free_units ?> <?= $free_units === 1 ? 'Standeinheit' : 'Standeinheiten' ?></p>

        <?php if ($reserved_units > 0): ?>
            <p id="booked">
                Im
                Warenkorb: <?= $reserved_units ?> <?= $reserved_units === 1 ? 'Standeinheit' : 'Standeinheiten' ?></p>
        <?php endif; ?>
        <?php if ($booked_units > 0): ?>
            <p id="booked">
                Gebucht: <?= $booked_units ?> <?= $free_units === 1 ? 'Standeinheit' : 'Standeinheiten' ?></p>
        <?php endif; ?>
        <?php if ($customer_requested_units > 0): ?>
            <p id="reserved">
                Vom Kunden
                angefragt: <?= $customer_requested_units ?> <?= $customer_requested_units === 1 ? 'Standeinheit' : 'Standeinheiten' ?>
            </p>
        <?php endif; ?>
        <?php if ($admin_requested_units > 0): ?>
            <p id="admin-reserved-requested">
                Vom Admin
                reserviert: <?= $admin_requested_units ?> <?= $admin_requested_units === 1 ? 'Standeinheit' : 'Standeinheiten' ?>
            </p>
        <?php endif; ?>
        <?php if ($expired_units > 0): ?>
            <p id="expired">
                Einheiten aus abgelaufenen
                Angeboten: <?= $expired_units ?> <?= $expired_units === 1 ? 'Standeinheit' : 'Standeinheiten' ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="export-bookings btn-secondary">
        Buchungsliste exportieren
        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/Buchungsliste.svg">
    </div>

    <div id="filter-result-container">
        <?php
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
                $invoice_id_day_2 = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralInvoiceIDDay2, true);
                $invoice_form_day_1 = '';
                $invoice_form_day_2 = '';

                if (!empty($invoice_id_day_1)) {
                    $invoice_form_day_1 = " <form class='redirect-to-customer-invoices invoice-id' method='POST' action='$invoice_page_link'><input type='hidden' name='invoice_info' value='{$invoice_id_day_1}' /><input type='submit' value='Zur Rechnung' /></form> ";
                }

                if (!empty($invoice_id_day_2)) {
                    $invoice_form_day_2 = " <form class='redirect-to-customer-invoices invoice-id' method='POST' action='$invoice_page_link'><input type='hidden' name='invoice_info' value='{$invoice_id_day_2}' /><input type='submit' value='Zur Rechnung' /></form> ";
                }

                $days = ['Day-1' => [
                    'user_id' => $current_user_id_day_1,
                    'user' => $user_day_1,
                    'user_name' => $user_name_day_1,
                    'user_role' => $user_role_day_1,
                    'status' => $status_day_1,
                    'invoice_id' => $invoice_id_day_1,
                    'invoice_form' => $invoice_form_day_1,
                ],
                    'Day-2' => [
                        'user_id' => $current_user_id_day_2,
                        'user' => $user_day_2,
                        'user_name' => $user_name_day_2,
                        'user_role' => $user_role_day_2,
                        'status' => $status_day_2,
                        'invoice_id' => $invoice_id_day_2,
                        'invoice_form' => $invoice_form_day_2,
                    ]
                ];

                ob_start(); ?>
                <div class="single-result">
                    <div class="inner-content">
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

                        <?php
                        foreach ($days as $day => $meta) { ?>
                            <b><?= BOOKINGPORT_StandStatusHandler::get_booking_day_name(lcfirst($day)) ?></b>
                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/stand-cat-grey.svg">
                                <p><?= $meta['status'] ?><?= $meta['invoice_form'] ?></p>
                            </div>
                            <?php if ($is_admin && $meta['status'] != 'Frei') { ?>
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
                                    <input type="hidden" name="invoice_info" value="<?= $meta['user_name']  ?>">
                                    <input type="submit" value="Alle Rechnungen des Kunden"/>
                                </form>
                            <?php }
                        }
                        ?>

                    </div>
                </div>
                <?php
                echo ob_get_clean();
            }

            if ($filteredStands->found_posts > 5) { ?>
                <div class="btn-secondary show-more-stands">Mehr anzeigen</div>
            <?php }
        } ?>
    </div>
</div>
