<?php
$user_id = get_current_user_id();
$option_table = get_option(BOOKINGPORT_Settings::$option_table);

$userStandsArgs = [
    'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
    'posts_per_page' => -1,
    'meta_query' => [
        'relation' => 'OR',
        [
            'relation' => 'AND',
            [
                'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1,
                'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold,
                'compare' => '=',
            ],
            [
                'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserIdDay1,
                'value' => $user_id,
                'compare' => '=',
            ]
        ],
        [
            'relation' => 'AND',
            [
                'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2,
                'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold,
                'compare' => '=',
            ],
            [
                'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserIdDay2,
                'value' => $user_id,
                'compare' => '=',
            ]
        ]
    ]
];

$userStands = new WP_Query($userStandsArgs);
$standCounter = 1;
$invoice_page_link = get_home_url() . '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_invoice];
?>

<div class="wrapper">
    <h1 class="page-headline">Ihre Standbuchungen</h1>
    <?php if ($userStands->found_posts > 0) { ?>
        <h2>Für das Jahr <?= date('Y') ?></h2>
        <div class="user-stands">
            <?php foreach ($userStands->posts ?? [] as $userStand) {

                $days = [];
                $invoice_id = '';

                if (get_post_meta($userStand->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay1, true) === BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold) {
                    $days[] = 'Day-1';
                    $invoice_id = get_post_meta($userStand->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralInvoiceIDDay1, true);
                }

                if (get_post_meta($userStand->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusDay2, true) === BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold) {
                    $days[] = 'Day-2';
                    $invoice_id = get_post_meta($userStand->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralInvoiceIDDay2, true);
                }

                $invoice_form = " <form class='redirect-to-customer-invoices invoice-id' method='POST' action='$invoice_page_link'><input type='hidden' name='invoice_info' value='{$invoice_id}' /><input type='submit' value='Zur Rechnung' /></form> ";

                $days_name = match (count($days)) {
                    1 => BOOKINGPORT_StandStatusHandler::get_booking_day_name(lcfirst($days[0])),
                    default => BOOKINGPORT_StandStatusHandler::get_booking_day_name('both')
                };

                $id = $userStand->ID;
                $street = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $number = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true); ?>
                <div class="single-stand">
                    <div class="upper-row">
                        <p>Stand <?= $standCounter ?></p>
                    </div>
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
                        <div class="row selected-stand-number">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-grey.svg">
                            <p class="stand-number stand-street">Buchungstage: <?= $days_name ?> </p>
                        </div>
                        <div class="row selected-stand-number">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-grey.svg">
                            <p class="stand-number stand-street">
                                Rechnungsnummer: <?= $invoice_id ?><?= $invoice_form ?> </p>
                        </div>
                    </div>
                </div>
                <?php
                $standCounter++;
            } ?>
            <a class="invoice-link" href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_invoice] ?>">
                <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/maps/Info_grey.svg">
                Weitere Details können Sie Ihren Rechnungen entnehmen
            </a>
        </div>
    <?php } else { ?>
        <div class="no-stands-container">
            <h3>Sie haben für dieses Jahr noch keinen Stand gebucht</h3>
            <a class="btn-primary" href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking] ?>">Jetzt
                Stand buchen</a>
        </div>
    <?php } ?>
</div>