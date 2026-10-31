<?php
/**
 * The side cart: WooCommerce's own Mini-Cart (refreshed by its cart fragments) in a drawer
 * that slides in, or a popup in the middle of the screen; a coupon form under its subtotal;
 * its accent colour, floating button and texts from the settings.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_footer', 'wac_side_cart_render');
add_filter('woocommerce_add_to_cart_fragments', 'wac_side_cart_fragments');

function wac_side_cart_enabled() {
    return ( wac_option('side_cart') == 'yes' ) && function_exists('WC') && ! is_cart() && ! is_checkout();
}

function wac_side_cart_count() {
    return ( function_exists('WC') && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
}

function wac_side_cart_mode() {
    return ( wac_option('side_cart_mode') == 'popup' ) ? 'popup' : 'drawer';
}

// The accent colour picked in the settings, '' when none (the side cart keeps its own colours).
function wac_side_cart_accent() {
    $color = sanitize_hex_color( (string) wac_option('side_cart_accent') );

    return $color ? $color : '';
}

// The coupon form: on in the settings, and coupons on in WooCommerce (WooCommerce > Settings > General).
function wac_side_cart_coupon_enabled() {
    return ( wac_option('side_cart_coupon') == 'yes' ) && function_exists('wc_coupons_enabled') && wc_coupons_enabled();
}

// The floating button's icons: [ fill style, inner SVG ].
function wac_side_cart_icons() {
    return array(
        'cart' => array('fill', '<path d="M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7.2 14h9.9c.8 0 1.4-.4 1.7-1.1l3.1-6.4L20.1 5.6 17.2 12H8.1L4.3 4H1v2h2l3.6 7.6-1.4 2.4C4.5 17.3 5.5 19 7 19h12v-2H7l1.1-2z"/>'),
        'cart-outline' => array('stroke', '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M1.5 2.5h3.2l2.6 12.2a1.8 1.8 0 0 0 1.8 1.4h8.8a1.8 1.8 0 0 0 1.8-1.4L21.5 7H6"/>'),
        'bag' => array('stroke', '<path d="M6 2.5 3.5 6v13.5a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2V6L18 2.5z"/><path d="M3.5 6h17"/><path d="M16 10a4 4 0 0 1-8 0"/>'),
        'basket' => array('stroke', '<path d="M2.5 9.5h19l-2.2 10.2a1.5 1.5 0 0 1-1.5 1.3H6.2a1.5 1.5 0 0 1-1.5-1.3z"/><path d="m7.5 9.5 4.5-7 4.5 7"/><path d="M9 13.5v4M12 13.5v4M15 13.5v4"/>'),
    );
}

function wac_side_cart_icon_svg() {
    $icons = wac_side_cart_icons();
    $key = (string) wac_option('side_cart_icon');
    list($style, $inner) = isset($icons[$key]) ? $icons[$key] : $icons['cart'];
    $paint = ( $style == 'fill' )
        ? 'fill="currentColor"'
        : 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';

    return '<svg class="wac-side-cart-icon" viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false" ' . $paint . '>' . $inner . '</svg>';
}

// What the side cart prints besides WordPress's post HTML: its SVG icons and the coupon form.
function wac_side_cart_allowed_html() {
    $allowed = wp_kses_allowed_html('post');
    $svg = array('class' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'aria-hidden' => true, 'focusable' => true,
        'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true);
    $allowed['svg'] = $svg;
    $allowed['path'] = array('d' => true);
    $allowed['circle'] = array('cx' => true, 'cy' => true, 'r' => true);
    $allowed['form'] = array('class' => true, 'action' => true, 'method' => true);
    $allowed['input'] = array('type' => true, 'name' => true, 'id' => true, 'class' => true, 'value' => true, 'placeholder' => true, 'autocomplete' => true, 'aria-label' => true);
    $allowed['button'] = array('type' => true, 'class' => true, 'data-coupon' => true, 'aria-label' => true, 'aria-controls' => true, 'aria-expanded' => true);
    $allowed['div']['data-apply-nonce'] = true;
    $allowed['div']['data-remove-nonce'] = true;
    $allowed['div']['hidden'] = true;
    $allowed['div']['aria-live'] = true;

    return $allowed;
}

function wac_side_cart_title() {
    $title = trim( (string) wac_option('side_cart_title') );

    return ( $title !== '' ) ? $title : __( 'Your cart', 'woocommerce-ajax-cart' );
}

function wac_side_cart_render() {
    if ( ! wac_side_cart_enabled() ) {
        return;
    }

    $mode = wac_side_cart_mode();
    $position = ( wac_option('side_cart_position') == 'left' ) ? 'left' : 'right';

    if ( wac_option('side_cart_button') == 'yes' ) {
        echo '<button type="button" class="wac-side-cart-toggle wac-pos-' . esc_attr($position) . '" aria-controls="wac-side-cart" aria-expanded="false" aria-label="' . esc_attr__( 'Open the cart', 'woocommerce-ajax-cart' ) . '">';
        echo wp_kses( wac_side_cart_icon_svg(), wac_side_cart_allowed_html() );
        echo '<span class="wac-side-cart-count">' . esc_html( wac_side_cart_count() ) . '</span>';
        echo '</button>';
    }

    echo '<div class="wac-side-cart-overlay wac-mode-' . esc_attr($mode) . '" hidden></div>';
    $classes = 'wac-side-cart wac-mode-' . $mode . ( wac_side_cart_accent() !== '' ? ' wac-has-accent' : '' );
    echo '<aside id="wac-side-cart" class="' . esc_attr($classes) . '" aria-hidden="true" aria-label="' . esc_attr__( 'Cart', 'woocommerce-ajax-cart' ) . '">';
    echo '<div class="wac-side-cart-header">';
    echo '<span class="wac-side-cart-title">' . esc_html( wac_side_cart_title() ) . '</span>';
    echo '<button type="button" class="wac-side-cart-close" aria-label="' . esc_attr__( 'Close the cart', 'woocommerce-ajax-cart' ) . '">&times;</button>';
    echo '</div>';
    // One scrolling column: the Mini-Cart's own parts are laid out around the coupon form by CSS (order).
    echo '<div class="wac-side-cart-body">';
    echo '<div class="wac-side-cart-before">';
    do_action('wac_side_cart_before_items');
    echo '</div>';
    echo '<div class="widget_shopping_cart"><div class="widget_shopping_cart_content">';
    woocommerce_mini_cart();
    echo '</div></div>';
    echo '<div class="wac-side-cart-after">';
    do_action('wac_side_cart_after_items');
    echo '</div>';
    echo wp_kses( wac_side_cart_coupon_html(), wac_side_cart_allowed_html() );
    echo '<div class="wac-side-cart-notices" aria-live="polite"></div>';
    echo '</div>';
    echo '</aside>';
}

/**
 * The coupon form, the coupons applied (each with its discount and a remove button) and the
 * subtotal after them. Its nonces travel with it: a cart fragment, rendered again after every
 * change, so a guest whose session began with an AJAX add to cart gets nonces of that session.
 */
function wac_side_cart_coupon_html() {
    if ( ! wac_side_cart_coupon_enabled() || ! function_exists('WC') || ! WC()->cart || WC()->cart->is_empty() ) {
        return '<div class="wac-side-cart-coupon" hidden></div>';
    }

    $cart = WC()->cart;
    $html = '<div class="wac-side-cart-coupon" data-apply-nonce="' . esc_attr( wp_create_nonce('apply-coupon') ) . '" data-remove-nonce="' . esc_attr( wp_create_nonce('remove-coupon') ) . '">';
    $html .= '<form class="wac-coupon-form" method="post">';
    $html .= '<input type="text" name="coupon_code" class="wac-coupon-code" value="" autocomplete="off" placeholder="' . esc_attr__( 'Coupon code', 'woocommerce-ajax-cart' ) . '" aria-label="' . esc_attr__( 'Coupon code', 'woocommerce-ajax-cart' ) . '" />';
    $html .= '<button type="submit" class="wac-coupon-apply">' . esc_html__( 'Apply', 'woocommerce-ajax-cart' ) . '</button>';
    $html .= '</form>';

    $coupons = $cart->get_applied_coupons();
    if ( ! empty($coupons) ) {
        $html .= '<ul class="wac-coupon-list">';
        foreach ( $coupons as $code ) {
            $amount = (float) $cart->get_coupon_discount_amount( $code, $cart->display_cart_ex_tax );
            $html .= '<li class="wac-coupon-applied">';
            $html .= '<span class="wac-coupon-name">' . esc_html( wc_format_coupon_code($code) ) . '</span>';
            $html .= '<span class="wac-coupon-amount">' . ( $amount > 0 ? '&minus;' . wp_kses_post( wc_price($amount) ) : esc_html__( 'Applied', 'woocommerce-ajax-cart' ) ) . '</span>';
            /* translators: %s: the coupon code */
            $html .= '<button type="button" class="wac-coupon-remove" data-coupon="' . esc_attr($code) . '" aria-label="' . esc_attr( sprintf( __( 'Remove the coupon %s', 'woocommerce-ajax-cart' ), $code ) ) . '">' . esc_html__( 'Remove', 'woocommerce-ajax-cart' ) . '</button>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '<p class="wac-coupon-total"><span>' . esc_html__( 'After discounts', 'woocommerce-ajax-cart' ) . '</span> <strong>' . wp_kses_post( wc_price( wac_cart_total_after_discounts() ) ) . '</strong></p>';
    }

    return $html . '</div>';
}

// The subtotal shown, less the discounts (what the free shipping method compares with its minimum too).
function wac_cart_total_after_discounts() {
    $cart = WC()->cart;
    $total = (float) $cart->get_displayed_subtotal();
    $total -= (float) $cart->get_discount_total();
    if ( $cart->display_prices_including_tax() ) {
        $total -= (float) $cart->get_discount_tax();
    }

    return max(0, $total);
}

function wac_side_cart_fragments( $fragments ) {
    $fragments['span.wac-side-cart-count'] = '<span class="wac-side-cart-count">' . esc_html( wac_side_cart_count() ) . '</span>';

    if ( wac_option('side_cart') == 'yes' ) {
        $fragments['div.wac-side-cart-coupon'] = wp_kses( wac_side_cart_coupon_html(), wac_side_cart_allowed_html() );
    }

    return $fragments;
}

function wac_side_cart_enqueue() {
    $dir = plugin_dir_path(WAC_BASE_FILE);
    $base = plugin_basename(WAC_BASE_FILE);

    // The drawer holds the Mini-Cart, which WooCommerce refreshes through its cart fragments; its
    // remove links work by AJAX through WooCommerce's add-to-cart script, which block themes do not load.
    wp_enqueue_script('wc-cart-fragments');
    wp_enqueue_script('wc-add-to-cart');
    wp_enqueue_style('wac-side-cart', plugins_url('assets/wac-side-cart.css', $base), array(), (string) filemtime($dir . 'assets/wac-side-cart.css'));
    wp_enqueue_script('wac-side-cart', plugins_url('assets/wac-side-cart.js', $base), array('jquery'), (string) filemtime($dir . 'assets/wac-side-cart.js'), true);
    wp_localize_script('wac-side-cart', 'wac_side_cart', array(
        'open_on_add' => wac_option('side_cart_open_on_add'),
        'checkout_text' => trim( (string) wac_option('side_cart_checkout_text') ),
        'empty_text' => trim( (string) wac_option('side_cart_empty_text') ),
        'wc_ajax_url' => class_exists('WC_AJAX') ? WC_AJAX::get_endpoint('%%endpoint%%') : '',
        'coupon_error' => __( 'The coupon could not be applied. Please reload the page and try again.', 'woocommerce-ajax-cart' ),
    ));
}
