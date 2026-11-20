<?php
/**
 * The admin side of a first run: when the plugin was first seen (the review ask waits two weeks
 * from it), and one "Get started" notice — on the Dashboard, the Plugins screen and WooCommerce's
 * screens — that points to the side cart: on for a fresh install, new to a store updating from
 * an older version (where it stays off until turned on). Dismissed once, it never comes back.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_init', 'wac_getting_started_init');
add_action('admin_notices', 'wac_getting_started_notice');

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO
function wac_getting_started_init() {
    // A store updating from a version before 1.6.0 had no install date: it starts now.
    if ( get_option('wac_installed_at', false) === false ) {
        add_option('wac_installed_at', time());
    }

    // Opening the Side cart tab is what the notice asks for: done.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reads which settings tab is open
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reads which settings tab is open
    $tab = isset($_GET['tab']) ? sanitize_file_name(wp_unslash($_GET['tab'])) : '';
    if ( $page === 'woocommerce-ajax-cart' && $tab === 'tab-side-cart.php' && ! get_option('wac_getting_started_done') ) {
        update_option('wac_getting_started_done', time(), false);
    }

    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- the nonce is checked right below, before anything is saved
    if ( empty($_GET['wac_getting_started']) || ! current_user_can('manage_woocommerce') ) {
        return;
    }
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    if ( wp_verify_nonce($nonce, 'wac_getting_started') ) {
        update_option('wac_getting_started_done', time(), false);
    }
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO
function wac_getting_started_screen() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ( ! $screen ) {
        return false;
    }

    // The plugin's own settings page says it all already.
    if ( strpos((string) $screen->id, 'woocommerce-ajax-cart') !== false ) {
        return false;
    }

    return in_array($screen->id, array('dashboard', 'plugins'), true)
        || strpos((string) $screen->id, 'woocommerce') !== false
        || in_array($screen->post_type, array('product', 'shop_order'), true);
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO
function wac_getting_started_notice() {
    if ( get_option('wac_getting_started_done') || ! current_user_can('manage_woocommerce') || ! wac_getting_started_screen() ) {
        return;
    }

    $settings = admin_url('admin.php?page=woocommerce-ajax-cart&tab=tab-side-cart.php');
    $dismiss = wp_nonce_url(add_query_arg('wac_getting_started', '1'), 'wac_getting_started');

    if ( wac_option('side_cart') == 'yes' ) {
        $text = __('Your side cart is on: it slides in when a product is added, with a coupon form and a free shipping bar. Pick its look — drawer or popup, accent colour, icon and texts — in the settings.', 'woocommerce-ajax-cart');
        $button = __('Customize the side cart', 'woocommerce-ajax-cart');
    }
    else {
        $text = __('New: a side cart that slides in when a product is added — with a coupon form, a free shipping bar and your own colours. It works on classic and block themes; turn it on in the settings.', 'woocommerce-ajax-cart');
        $button = __('Turn on the side cart', 'woocommerce-ajax-cart');
    }

    ?>
    <div class="notice notice-info">
        <p><strong><?php echo esc_html__('Ajax Cart for WooCommerce', 'woocommerce-ajax-cart'); ?></strong> — <?php echo esc_html($text); ?></p>
        <p>
            <a class="button button-primary" href="<?php echo esc_url($settings); ?>"><?php echo esc_html($button); ?></a>
            <a href="<?php echo esc_url($dismiss); ?>" style="margin-left: 10px;"><?php echo esc_html__('Dismiss', 'woocommerce-ajax-cart'); ?></a>
        </p>
    </div>
    <?php
}
