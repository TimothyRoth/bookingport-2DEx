<?php
$user_id = get_current_user_id();

$args = [
    'status' => 'any',
    'type' => 'shop_order',
    'limit' => 5,
    'customer_id' => $user_id,
    'return' => 'ids',
];

$total_order_args = [
    'status' => 'any',
    'type' => 'shop_order',
    'customer_id' => $user_id,
    'return' => 'ids',
];

$total_orders = wc_get_orders($total_order_args);
$order_ids = wc_get_orders($args);
$total_order_count = count($total_orders);
$displayed_order_count = count($order_ids); ?>

<main>
    <div class="wrapper">
        <h1 class="page-headline"><?php the_title(); ?></h1>

        <div class="dropdown-filter select-filter">
            <label for="order-dropdown-filter">Sortieren nach</label>
            <select name="order-dropdown-filter">
                <option value="aufsteigend">Datum (Absteigend)</option>
                <option value="absteigend">Datum (Aufsteigend)</option>
            </select>
        </div>
        <div class="search-filter" id="order-search-filter">
            <input placeholder="Suche..." name="order-search" type="text" value="<?= $_POST['invoice_info'] ?? '' ?>">
        </div>

        <div class="order-results admin-order-results">
            <?php foreach ($order_ids ?? [] as $order_id) {
                $order = wc_get_order($order_id);
                $order_item_meta = $order->get_items();
                $order_user_id = $order->get_user_id();
                $user = get_user_by('id', $order_user_id);
                $order_user_role = null;
                if (is_object($user) && isset($user->roles[0])) {
                    $order_user_role = BOOKINGPORT_User::get_user_role_name_by_slug($user->roles[0]);
                }
                $order_price = $order->get_total();
                $order_date = $order->get_date_created()->date('Y-m-d');
                $order_user = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                $order_invoice_number = $order->get_order_number();
                $order_payment_status = $order->get_status();
                $order_user_phone = $order->get_billing_phone();
                $order_user_email = $order->get_billing_email();
                $order_electricity = get_post_meta($order_id, 'electricity', true);
                $order_water = get_post_meta($order_id, 'water', true);
                $order_sales_food = get_post_meta($order_id, 'sales_food', true);
                $order_sales_drinks = get_post_meta($order_id, 'sales_drinks', true);
                $order_association_ride = get_post_meta($order_id, 'association_ride', true);
                $order_sortiment = get_post_meta($order_id, 'sortiment', true);
                $order_meta = null;

                $order_color_theme = BOOKINGPORT_OrderHandler::get_order_color($order_payment_status);
                $order_status_description = BOOKINGPORT_OrderHandler::get_order_status_description($order_payment_status);

                foreach ($order_item_meta ?? [] as $meta) {
                    $order_meta .= $meta['Straße'] . ' ' . $meta['Standnummer'] . ' (' . $meta['Buchungstage'] . ')<br/>';
                } ?>

                <div class="single-order <?= $order_color_theme ?>">
                    <div class="open-additional-order-information">
                        <div class="stripe vertical"></div>
                        <div class="stripe horizonal"></div>
                    </div>
                    <div class="row order-date">
                        <img
                                src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/date-' . $order_color_theme . '.svg' ?>">
                        <p><?= $order_date ?></p>
                    </div>
                    <div class="row order-user-name">
                        <img
                                src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/customer-' . $order_color_theme . '.svg' ?>">
                        <p><?= $order_user ?> <br/>Benutzerrolle: <?= ucfirst($order_user_role) ?></p>
                    </div>
                    <div class="row order-user-stands">
                        <img
                                src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/stand-cat-' . $order_color_theme . '.svg' ?>">
                        <p><?= $order_meta;
                            $order_meta = null; ?></p>
                    </div>
                    <div class="row order-stand-id">
                        <img
                                src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/stand-number-' . $order_color_theme . '.svg' ?>">
                        <p>Rechnungsbetrag: <?= $order_price ?>€</p>
                    </div>
                    <div class="row order-invoice-number">
                        <img
                                src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/order-' . $order_color_theme . '.svg' ?>">
                        <p>Rechnungsnummer: <?= $order_invoice_number ?></p>
                    </div>
                    <div class="row invoice-pdf">
                        <img
                                src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/download-pdf-' . $order_color_theme . '.svg' ?>">
                        <a class="generate-user-invoice" id="invoice-<?= $order_id ?>">Download als PDF</a>
                    </div>
                    <div class="hidden-order-information">
                        <?php if (!empty($order_electricity)) : ?>
                            <div class="row order-electricity electricity">
                                <img src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/electricity-' . $order_color_theme . '.svg' ?>">
                                <p>Stromanschluss: <?= $order_electricity ?></p>
                            </div>
                        <?php endif ?>
                        <?php if (!empty($order_water)) : ?>
                            <div class="row order-water water">
                                <img src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/water-' . $order_color_theme . '.svg' ?>">
                                <p>Wasseranschluss: <?= $order_water ?></p>
                            </div>
                        <?php endif ?>
                        <?php if (!empty($order_sales_food)) : ?>
                            <div class="row order-food food">
                                <img src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/food-' . $order_color_theme . '.svg' ?>">
                                <p>Imbiss: <?= $order_sales_food ?></p>
                            </div>
                        <?php endif ?>
                        <?php if (!empty($order_sales_drinks)) : ?>
                            <div class="row order-drinks drinks">
                                <img src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/beverage-' . $order_color_theme . '.svg' ?>">
                                <p>Getränke: <?= $order_sales_drinks ?></p>
                            </div>
                        <?php endif ?>
                        <?php if (!empty($order_sortiment)) : ?>
                            <div class="row order-sortiment">
                                <img src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/stock-' . $order_color_theme . '.svg' ?>">
                                <p>Sortiment: <?= $order_sortiment ?></p>
                            </div>
                        <?php endif ?>
                        <?php if (!empty($order_association_ride)) : ?>
                            <div class="row order-ride">
                                <img src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/fairground-' . $order_color_theme . '.svg' ?>">
                                <p>Fahrgeschäft/e <?= $order_association_ride ?></p>
                            </div>
                        <?php endif ?>
                        <div class="payment-status">
                            <img
                                    src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/check-' . $order_color_theme . '.svg' ?>">
                            <p>Bezahlstatus: <?= $order_status_description ?></p>
                        </div>
                        <div class="phone">
                            <img
                                    src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/phone-' . $order_color_theme . '.svg' ?>">
                            <a href="tel:<?= $order_user_phone ?>"><?= $order_user_phone ?></a>
                        </div>
                        <div class="email">
                            <img
                                    src="<?= BOOKINGPORT_PLUGIN_URI . '/assets/images/icons/orders/mail-' . $order_color_theme . '.svg' ?>">
                            <a href="mailto:<?= $order_user_email ?>"><?= $order_user_email ?></a>
                        </div>
                    </div>
                </div>

                <?php
            }

            if ($displayed_order_count < $total_order_count) {
                echo '<div class="btn-primary show-more-orders">Mehr anzeigen</div>';
            }
            ?>
        </div>

        <div class="i-am-legend">
            <div class="row green">
                <div class="circle"></div>
                Buchung bezahlt
            </div>
            <div class="row red">
                <div class="circle"></div>
                Zahlung ausstehend
            </div>
            <div class="row orange">
                <div class="circle"> </div>
                    Storniert/Fehlgeschlagen
            </div>
        </div>
    </div>
</main>