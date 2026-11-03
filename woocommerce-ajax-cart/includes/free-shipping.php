<?php
/**
 * The free shipping progress bar ("Spend $12.00 more for free shipping"): in the Mini-Cart
 * (and so the side cart), the classic Cart page and the Cart block.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('woocommerce_before_mini_cart', 'wac_print_free_shipping_bar');
add_action('woocommerce_before_cart_table', 'wac_print_free_shipping_bar');
add_filter('render_block_woocommerce/cart', 'wac_cart_block_free_shipping', 10, 1);

/**
 * The order amount that gives free shipping: the setting, or the minimum of the
 * free shipping method in the shopper's shipping zone. 0 when there is none.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
function wac_free_shipping_threshold() {
    $manual = (float) str_replace(',', '.', (string) wac_option('free_shipping_amount'));
    $threshold = ( $manual > 0 ) ? $manual : wac_free_shipping_zone_minimum();

    /**
     * Filters the order amount that gives free shipping, as the bars show it (the side cart,
     * the Mini-Cart, the Cart page and the Cart block). 0 hides the free shipping bar.
     * A multi-currency shop can convert it to the shopper's currency here.
     *
     * @since 1.5.2
     *
     * @param float $threshold The amount: the "Order amount for free shipping" setting, or the
     *                         minimum of the free shipping method of the shopper's shipping zone.
     */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO
    return (float) apply_filters('wac_free_shipping_threshold', $threshold);
}

// The minimum order of the free shipping method in the shopper's shipping zone, 0 when there is none.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO
function wac_free_shipping_zone_minimum() {
    if ( !function_exists('WC') || !WC()->cart || !class_exists('WC_Shipping_Zones') ) {
        return 0;
    }

    $packages = WC()->cart->get_shipping_packages();
    $zone = !empty($packages[0]) ? WC_Shipping_Zones::get_zone_matching_package($packages[0]) : null;
    if ( !$zone ) {
        return 0;
    }

    foreach ( $zone->get_shipping_methods(true) as $method ) {
        if ( $method->id === 'free_shipping' && in_array($method->get_option('requires'), array('min_amount', 'either', 'both'), true) ) {
            $min = (float) $method->get_option('min_amount');
            if ( $min > 0 ) {
                return $min;
            }
        }
    }

    return 0;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
function wac_free_shipping_bar_html() {
    $hidden = '<div class="wac-free-shipping" hidden></div>';
    if ( wac_option('free_shipping_bar') != 'yes' || !function_exists('WC') || !WC()->cart || WC()->cart->is_empty() ) {
        return $hidden;
    }

    $threshold = wac_free_shipping_threshold();
    if ( $threshold <= 0 ) {
        return $hidden;
    }

    $total = wac_cart_total_after_discounts();
    $remaining = max(0, $threshold - $total);
    $percent = (int) min(100, round($total / $threshold * 100));
    $text = ( $remaining > 0 )
        /* translators: %s: the amount still to spend, with its currency */
        ? sprintf( __( 'Spend %s more for free shipping', 'woocommerce-ajax-cart' ), wc_price($remaining) )
        : __( 'You have free shipping!', 'woocommerce-ajax-cart' );

    return '<div class="wac-free-shipping' . ( $remaining > 0 ? '' : ' wac-free-shipping-done' ) . '">'
        . '<p class="wac-free-shipping-text">' . wp_kses_post($text) . '</p>'
        . '<div class="wac-free-shipping-track"><span style="width: ' . esc_attr($percent) . '%"></span></div>'
        . '</div>';
}

// The bar printed in the Mini-Cart and the classic Cart; `wac_progress_bar_html` lets another bar take its place.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
function wac_progress_bar_html() {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- public hook name kept for compatibility (the PRO and other extensions hook into it)
    return (string) apply_filters('wac_progress_bar_html', wac_free_shipping_bar_html());
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
function wac_print_free_shipping_bar() {
    echo wp_kses_post( wac_progress_bar_html() );
}

// Whether the Cart block gets the bar (`wac_free_shipping_block_enabled`: false when another bar takes its place).
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
function wac_free_shipping_block_enabled() {
    $enabled = ( wac_option('free_shipping_bar') == 'yes' ) && wac_free_shipping_threshold() > 0;

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- public hook name kept for compatibility (the PRO and other extensions hook into it)
    return (bool) apply_filters('wac_free_shipping_block_enabled', $enabled);
}

// The Cart block: an empty bar the block script fills from the cart's live totals.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
function wac_cart_block_free_shipping($content) {
    if ( ! wac_free_shipping_block_enabled() ) {
        return $content;
    }

    return '<div class="wac-free-shipping wac-free-shipping-block" hidden><p class="wac-free-shipping-text"></p><div class="wac-free-shipping-track"><span></span></div></div>' . $content;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
function wac_free_shipping_enqueue() {
    $dir = plugin_dir_path(WAC_BASE_FILE);
    $base = plugin_basename(WAC_BASE_FILE);

    // Registered always: another bar (the PRO's rewards) is drawn with the same styles, and reads
    // the Cart block's totals with the same script.
    wp_register_style('wac-free-shipping', plugins_url('assets/wac-free-shipping.css', $base), array('wooajaxcart'), (string) filemtime($dir . 'assets/wac-free-shipping.css'));
    wp_register_script('wac-cart-block-totals', plugins_url('assets/wac-cart-block-totals.js', $base), array('wp-data'), (string) filemtime($dir . 'assets/wac-cart-block-totals.js'), true);
    if ( wac_option('free_shipping_bar') == 'yes' ) {
        wp_enqueue_style('wac-free-shipping');
    }

    if ( is_cart() && has_block('woocommerce/cart') && wac_free_shipping_block_enabled() ) {
        wp_enqueue_script('wac-free-shipping-block', plugins_url('assets/wac-free-shipping-block.js', $base), array('wp-data', 'wac-cart-block-totals'), (string) filemtime($dir . 'assets/wac-free-shipping-block.js'), true);
        wp_localize_script('wac-free-shipping-block', 'wac_free_shipping', array(
            'threshold' => wac_free_shipping_threshold(),
            'incl_tax' => ( function_exists('WC') && WC()->cart && WC()->cart->display_prices_including_tax() ) ? 'yes' : 'no',
            /* translators: %s: the amount still to spend, with its currency */
            'spend_text' => __( 'Spend %s more for free shipping', 'woocommerce-ajax-cart' ),
            'done_text' => __( 'You have free shipping!', 'woocommerce-ajax-cart' ),
        ));
    }
}
