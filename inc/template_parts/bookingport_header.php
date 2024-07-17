<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table);

if (is_user_logged_in()):
    $current_user = wp_get_current_user();
    $user_role_slug = $current_user->roles[0];
    $user_image_uri = BOOKINGPORT_PLUGIN_URI . 'assets/images/icons/orders/customer-red.svg';
    $my_account_link = get_permalink(get_option('woocommerce_myaccount_page_id'));
    $display_name = BOOKINGPORT_User::get_user_role_name_by_slug($user_role_slug);
endif; ?>

    <header id="header" class="header">
        <nav class="wrapper <?php if (is_front_page()) {
            echo 'frontpage-container';
        } ?>">

            <div class="burger-menu">
                <div class="stripe stripe-top"></div>
                <div class="stripe stripe-mid"></div>
                <div class="stripe stripe-bottom"></div>
            </div>

            <div class="header-content-container <?php if (is_front_page()) {
                echo 'frontpage-container';
            } ?>">

                <?php
                $market_logo = $option_table[BOOKINGPORT_Settings::$option_market_logo];
                $market_date = $option_table[BOOKINGPORT_Settings::$option_market_date];
                $market_header_video = $option_table[BOOKINGPORT_Settings::$option_market_header_video];
                ?>
                <?php if (!empty($market_logo)) { ?>
                    <div class="logo-wrapper">
                        <a href="<?= get_home_url() ?>" class="custom-logo-link">
                            <img src="<?= $market_logo ?>">
                            <?php if (!empty($market_date)) { ?>
                                <p><?= $market_date ?></p>
                            <?php } ?>
                        </a>
                    </div>
                <?php } else { ?>
                    <a href="<?= home_url() ?>">
                        <p class="site-title"><?php bloginfo('title') ?></p>
                        <span><?php bloginfo('description') ?></span>
                    </a>
                <?php } ?>
            </div>

            <?php if (!is_user_logged_in()) {
                include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/menu-sidebar/bookingport_sidebar_user_logged_out.php');
            }

            if (is_user_logged_in() && !current_user_can('administrator')) {
                include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/menu-sidebar/bookingport_sidebar_user_logged_in.php');
            }

            if (current_user_can('administrator')) {
                include(BOOKINGPORT_PLUGIN_PATH . '/inc/template_parts/menu-sidebar/bookingport_sidebar_administrator.php');
            } ?>

        </nav>

        <?php
        $archive_slug = $option_table[BOOKINGPORT_Settings::$option_redirects_faq];
        if (is_user_logged_in() && !current_user_can('administrator') && !is_archive($archive_slug)) { ?>
            <a href="<?= home_url() ?>/<?= $archive_slug ?>"
               class="faq-icon user-logged-in">
                <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/faq.svg">
            </a>
        <?php } ?>

        <?php if (is_front_page() && !empty($market_header_video)) { ?>
            <div class="video-background-wrapper">
                <img id="background-video" src="<?= $market_header_video ?>">
            </div>
        <?php }

        if (isset($display_name)):
            echo "<div class='wrapper'><div class='user_role_wrapper'><a href='{$my_account_link}'><img src='{$user_image_uri}'><p>{$display_name}</p></a></div></div>";
        endif; ?>
    </header>
<?php
