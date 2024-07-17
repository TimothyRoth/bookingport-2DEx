<?php

class OrderHandler
{

    public static function get_order_color($order_payment_status): string
    {
        return match ($order_payment_status) {
            'completed' => 'green',
            'payment-accepted' => 'green',
            'pending' => 'red',
            'failed' => 'orange',
            default => 'red'
        };
    }

    public static function get_order_status_description($order_payment_status): string
    {
        return match ($order_payment_status) {
            'completed' => 'Bezahlt',
            'payment-accepted' => 'Bezahlt',
            'pending' => 'Zahlung ausstehend',
            'failed' => 'Storniert/Fehlgeschlagen',
            default => 'Zahlung ausstehend'
        };
    }

}