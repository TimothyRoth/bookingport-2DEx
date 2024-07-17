<?php

use League\Csv\Writer;

class BOOKINGPORT_FileExporter
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_ajax_getStandsStatusList_Export', [__CLASS__, 'getStandsStatusList_Export']);
        add_action('wp_ajax_nopriv_getStandsStatusList_Export', [__CLASS__, 'getStandsStatusList_Export']);
    }

    /**
     * @throws \League\Csv\Exception
     * @throws JsonException
     */
    public static function getStandsStatusList_Export(): void
    {

        $stand_ids = self::get_all_stand_ids();
        $days = ['Day-1', 'Day-2'];
        $result = [];

        $result[] = ['Standname', 'Buchungstag', 'Standstatus', 'Kunde', 'Rechnungsnummer', 'Standpreis in €', 'Standbreite', 'Standtiefe', 'Stromanschluss', 'Wasseranschluss', 'Imbiss', 'Getränke', 'Fahrgeschäft/e', 'Sortiment', 'Notizen vom Kunden', 'Bezahlstatus'];

        if (count($stand_ids) > 0) :
            foreach ($stand_ids as $stand_id) :

                $standNumber = get_post_meta($stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $stand_name = get_post_meta($stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true) . ' ' . get_post_meta($stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);

                foreach ($days as $day):
                    $booking_day = BOOKINGPORT_StandStatusHandler::get_booking_day_name(lcfirst($day));
                    $order_id = get_post_meta($stand_id, 'stand_meta_generalInvoiceID-' . $day, true);
                    $status = BOOKINGPORT_CptStands::get_sell_status_label($stand_id, 'stand_meta_generalSellStatus-' . $day);
                    $customer = get_user_by('id', get_post_meta($stand_id, 'stand_meta_generalSellUserId-Day-1', true));
                    $customerRole = $customer->roles[0] ?? '';
                    !empty($customer) ? $customer_name = $customer->billing_first_name . ' ' . $customer->billing_last_name : $customer_name = 'Kein Kunde';
                    $customer_name !== 'Kein Kunde' ? $customer_name .= ' (' . ucfirst($customerRole) . ')' : $customer_name .= '';
                    $displayed_order_id = '-';

                    $standMetaFromOrder = [];

                    if (!empty($order_id)) :
                        $standMetaFromOrder = self::get_stand_meta_from_order((int)$order_id, (string)$standNumber);
                        $displayed_order_id = $order_id;
                        $order_id = null;
                    endif;

                    $payment_status = $standMetaFromOrder['status'] ?? '-';
                    $stand_width = $standMetaFromOrder['width'] ?? '-';
                    $stand_depth = $standMetaFromOrder['depth'] ?? '-';
                    $stand_total = $standMetaFromOrder['total'] ?? '-';
                    $stand_notes = $standMetaFromOrder['notes'] ?? '-';
                    $stand_electricity = $standMetaFromOrder['electricity'] ?? '-';
                    $stand_water = $standMetaFromOrder['water'] ?? '-';
                    $stand_sales_food = $standMetaFromOrder['sales_food'] ?? '-';
                    $stand_sales_drinks = $standMetaFromOrder['sales_drinks'] ?? '-';
                    $stand_association_ride = $standMetaFromOrder['association_ride'] ?? '-';
                    $stand_sortiment = $standMetaFromOrder['sortiment'] ?? '-';
                    $result[] = [$stand_name, $booking_day, $status, $customer_name, $displayed_order_id, $stand_total, $stand_width, $stand_depth, $stand_electricity, $stand_water, $stand_sales_food, $stand_sales_drinks, $stand_association_ride, $stand_sortiment, $stand_notes, $payment_status];
                endforeach;
            endforeach;
        endif;

        $name = 'buchungsliste-' . date('m-d-Y', time()) . '.csv';
        $writer = Writer::createFromPath(BOOKINGPORT_PLUGIN_PATH . '/exportFiles/' . $name, 'w+');
        $writer->insertAll($result);

        wp_die(json_encode(BOOKINGPORT_PLUGIN_URI . '/exportFiles/' . $name, JSON_THROW_ON_ERROR));

    }

    private
    static function get_all_stand_ids(): array
    {
        $stand_ids = [];

        $argsStands = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
            'order' => 'ASC'
        ];

        $stands = new WP_Query($argsStands);

        foreach ($stands->posts as $stand_id) {
            $stand_ids[] = $stand_id;
        }

        return $stand_ids;

    }

    private
    static function get_stand_meta_from_order(int $order_id, string $standNumber): array
    {
        $standMeta = [];
        $order = wc_get_order($order_id);
        $standMeta['notes'] = $order->get_customer_note();
        $status = $order->get_status();
        $orderStands = $order->get_items();
        $standMeta['status'] = BOOKINGPORT_OrderHandler::get_order_status_description($status);

        foreach ($orderStands as $orderStand) {
            $thisStandNumber = wc_get_order_item_meta($orderStand->get_id(), 'Standnummer', true);

            if ($thisStandNumber === $standNumber) {
                $standMeta['width'] = wc_get_order_item_meta($orderStand->get_id(), 'Standbreite', true);
                $standMeta['depth'] = wc_get_order_item_meta($orderStand->get_id(), 'Standtiefe', true);
                $standMeta['total'] = round($orderStand->get_total(), '2') . '€';
                $standMeta['electricity'] = get_post_meta($order_id, 'electricity', true);
                $standMeta['water'] = get_post_meta($order_id, 'water', true);
                $standMeta['sales_food'] = get_post_meta($order_id, 'sales_food', true);
                $standMeta['sales_drinks'] = get_post_meta($order_id, 'sales_drinks', true);
                $standMeta['association_ride'] = get_post_meta($order_id, 'association_ride', true);
                $standMeta['sortiment'] = get_post_meta($order_id, 'sortiment', true);
            }
        }

        return $standMeta;
    }
}
