<?php

class BOOKINGPORT_User
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {

        add_action('init', [__CLASS__, 'add_user_roles']);
        add_filter('manage_users_columns', [__CLASS__, 'add_verification_status_column']);
        add_action('manage_users_custom_column', [__CLASS__, 'display_verification_status_column'], 10, 3);
        add_action('admin_head', [__CLASS__, 'enqueue_custom_admin_styles']);
        add_filter('woocommerce_available_payment_gateways', [__CLASS__, 'disable_cod_for_non_members']);

    }

    public static function add_user_roles(): void
    {

        add_role('privat_troedel', 'Privater Trödel', [
            'read' => true,
        ]);

        add_role('privat_anlieger', 'Privater Anlieger', [
            'read' => true,
        ]);

        add_role('gewerblich', 'Gewerbe', [
            'read' => true,
        ]);

        add_role('schausteller', 'Schausteller', [
            'read' => true,
        ]);

        add_role('verein', 'Verein', [
            'read' => true,
        ]);

        add_role('mitglieder', 'Mitglied', [
            'read' => true,
        ]);
    }

    public static function is_user_verified($user_id): bool
    {
        $token = get_user_meta($user_id, 'verification_token', true);
        return empty($token);
    }

    public static function add_verification_status_column($columns)
    {
        $columns['verification_status'] = 'Account Status'; // Customize the column title
        return $columns;
    }

    public static function display_verification_status_column($value, $column_name, $user_id)
    {
        if ($column_name === 'verification_status') {
            if (self::is_user_verified($user_id)) {
                return '<span class="verified-user-row">Anmeldung bestätigt</span>';
            }
            return '<span class="unverified-user-row">Bestätigung Ausstehend</span>';
        }
        return $value;
    }

    public static function enqueue_custom_admin_styles(): void
    {
        global $pagenow;

        if ($pagenow === 'users.php') {
            ob_start(); ?>

            <style>
                .verified-user-row {
                    color: #000;
                    background-color: #b8e994; /* Green for verified users */
                }

                .unverified-user-row {
                    background-color: #d70000; /* Red for unverified users */
                    color: #fff;
                }
            </style> <?php

            echo ob_get_clean();
        }
    }

    public static function get_user_role_name_by_slug(string $role_slug): string
    {
        $role_mapping = array(
            'privat_troedel' => 'Privater Trödel',
            'privat_anlieger' => 'Privater Anlieger',
            'gewerblich' => 'Gewerbe',
            'schausteller' => 'Schausteller',
            'verein' => 'Verein',
            'mitglieder' => 'Mitglied',
            'administrator' => 'Admin'
        );

        return $role_mapping[$role_slug];
    }

    // method to disable the payment method cod for all customers that dont have the role "mitglieder"
    public static function disable_cod_for_non_members($available_gateways)
    {
        // current user role
        $user = wp_get_current_user();
        $user_role = $user->roles[0] ?? null;

        $valid_roles = [
            'gewerblich',
            'verein',
            'mitglieder',
            'schausteller'
        ];

        if (isset($available_gateways['cod']) && !in_array($user_role, $valid_roles, true)) {
            unset($available_gateways['cod']);
        }

        return $available_gateways;
    }
}