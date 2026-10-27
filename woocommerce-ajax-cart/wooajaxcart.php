<?php
/*
Plugin Name: Ajax Cart for WooCommerce
Plugin URI: https://wordpress.org/plugins/woocommerce-ajax-cart/
Description: Updates the WooCommerce cart with AJAX as soon as a quantity changes — no "Update cart" click needed
Version: 1.4.0
Author: Moises Heberle
Author URI: https://mhtec.org/contact/
Text Domain: woocommerce-ajax-cart
Domain Path: /i18n/languages/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires Plugins: woocommerce
WC requires at least: 3.2
WC tested up to: 11.2
*/

if ( ! defined( 'ABSPATH' ) ) exit;

defined('WAC_BASE_FILE') || define('WAC_BASE_FILE', __FILE__);

if ( !function_exists('wac_init') ) {
    add_action('init', 'wac_init');
    add_filter('mh_wac_settings', 'wac_settings');
    add_filter('mh_wac_premium_url', 'wac_premium_url');
    add_action('woocommerce_before_cart_table', 'wac_enqueue_scripts');
    add_action('wp_enqueue_scripts', 'wac_enqueue_scripts' );
    add_filter('woocommerce_cart_item_quantity', 'wac_filter_woocommerce_cart_item_quantity', 10, 3);
    add_filter('wc_get_template', 'wac_get_template', 10, 5 );
    add_action('wp_footer', 'wac_side_cart_render');
    add_filter('woocommerce_add_to_cart_fragments', 'wac_side_cart_fragments');

    add_action( 'before_woocommerce_init', function() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    } );

    function wac_init() {
        // common lib init
        include_once( 'common/MHCommon.php' );
        WAC_MHCommon::initializeV2(
            'woocommerce-ajax-cart',
            'wac',
            WAC_BASE_FILE,
            __('Ajax Cart for WooCommerce', 'woocommerce-ajax-cart')
        );

        // plugin checks
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- only marks the shopper's own AJAX cart refresh as a cart request; changes no data
        if ( !empty($_POST['is_wac_ajax']) && !defined( 'WOOCOMMERCE_CART' ) ) {
            define( 'WOOCOMMERCE_CART', true );
        }

        do_action('wac_init');
    }
    
    function wac_settings($arr) {
        $arr['show_qty_buttons'] = array(
            'label' => __('Display -/+ buttons around product quantity input', 'woocommerce-ajax-cart'),
            'tab' => __('General', 'woocommerce-ajax-cart'),
            'type' => 'checkbox',
            'default' => 'yes',
        );
        $arr['qty_as_select'] = array(
            'label' => __('Show item quantity as select instead numeric field', 'woocommerce-ajax-cart'),
            'tab' => __('General', 'woocommerce-ajax-cart'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['select_items'] = array(
            'label' => __('Items to show on select', 'woocommerce-ajax-cart'),
            'tab' => __('General', 'woocommerce-ajax-cart'),
            'type' => 'number',
            'default' => 5,
            'depends_on' => 'qty_as_select',
            'min' => 1,
            'max' => 1000,
        );
        $arr['confirmation_zero_qty'] = array(
            'label' => __('Show user confirmation when change item quantity to zero or empty', 'woocommerce-ajax-cart'),
            'tab' => __('General', 'woocommerce-ajax-cart'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['side_cart'] = array(
            'label' => __('Enable the side cart: a cart drawer that slides in from the side, on every page but the cart and checkout', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['side_cart_open_on_add'] = array(
            'label' => __('Open the side cart when a product is added to the cart', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'checkbox',
            'default' => 'yes',
            'depends_on' => 'side_cart',
        );
        $arr['side_cart_button'] = array(
            'label' => __('Show a floating cart button with the item count', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'checkbox',
            'default' => 'yes',
            'depends_on' => 'side_cart',
        );
        $arr['ajax_timeout'] = array(
            'label' => __('AJAX timeout in milliseconds to refresh the cart on change', 'woocommerce-ajax-cart'),
            'tab' => __('General', 'woocommerce-ajax-cart'),
            'type' => 'number',
            'default' => 800,
            'min' => 1,
            'max' => 5000,
        );
    
        return $arr;
    }
    
    function wac_premium_url() {
        return 'http://gum.co/wajaxcart';
    }
    
    function wac_option($name) {
        return apply_filters('mh_wac_setting_value', $name);
    }
    
    function wac_enqueue_scripts() {
        // check if WooCommerce was enabled
        if ( !class_exists('WooCommerce') ) {
            return;
        }

        $deps = array('jquery');

        if ( is_cart() ) {
            $deps[] = 'wc-cart';
        }

        // Versioned by the file's own date, so a new release never meets a cached copy.
        $dir = plugin_dir_path(WAC_BASE_FILE);
        wp_enqueue_style('wooajaxcart', plugins_url('assets/wooajaxcart.css', plugin_basename(WAC_BASE_FILE)), array(), (string) filemtime($dir . 'assets/wooajaxcart.css'));
        wp_enqueue_script('wooajaxcart', plugins_url('assets/wooajaxcart.js', plugin_basename(WAC_BASE_FILE)), $deps, (string) filemtime($dir . 'assets/wooajaxcart.js'), false);
    
        $frontendData = wac_frontend_vars();
        wp_localize_script( 'wooajaxcart', 'wooajaxcart', $frontendData );

        if ( wac_side_cart_enabled() ) {
            // The drawer holds the Mini-Cart, which WooCommerce refreshes through its cart fragments.
            wp_enqueue_script('wc-cart-fragments');
            wp_enqueue_style('wac-side-cart', plugins_url('assets/wac-side-cart.css', plugin_basename(WAC_BASE_FILE)), array(), (string) filemtime($dir . 'assets/wac-side-cart.css'));
            wp_enqueue_script('wac-side-cart', plugins_url('assets/wac-side-cart.js', plugin_basename(WAC_BASE_FILE)), array('jquery'), (string) filemtime($dir . 'assets/wac-side-cart.js'), true);
            wp_localize_script('wac-side-cart', 'wac_side_cart', array(
                'open_on_add' => wac_option('side_cart_open_on_add'),
            ));
        }

        // The Cart block updates quantities by itself; what it lacks is a confirmation before an item is removed.
        if ( is_cart() && wac_option('confirmation_zero_qty') == 'yes' && has_block('woocommerce/cart') ) {
            wp_enqueue_script('wac-cart-block', plugins_url('assets/wac-cart-block.js', plugin_basename(WAC_BASE_FILE)), array(), (string) filemtime($dir . 'assets/wac-cart-block.js'), true);
            wp_localize_script('wac-cart-block', 'wac_cart_block', array(
                'warn_remove_text' => __( 'Are you sure you want to remove this item from cart?', 'woocommerce-ajax-cart' ),
            ));
        }
    }

    function wac_side_cart_enabled() {
        return ( wac_option('side_cart') == 'yes' ) && function_exists('WC') && ! is_cart() && ! is_checkout();
    }

    function wac_side_cart_count() {
        return ( function_exists('WC') && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
    }

    // The drawer: WooCommerce's own Mini-Cart (refreshed by its cart fragments) in a panel that slides in.
    function wac_side_cart_render() {
        if ( ! wac_side_cart_enabled() ) {
            return;
        }

        if ( wac_option('side_cart_button') == 'yes' ) {
            echo '<button type="button" class="wac-side-cart-toggle" aria-controls="wac-side-cart" aria-expanded="false" aria-label="' . esc_attr__( 'Open the cart', 'woocommerce-ajax-cart' ) . '">';
            echo '<svg class="wac-side-cart-icon" viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false"><path fill="currentColor" d="M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7.2 14h9.9c.8 0 1.4-.4 1.7-1.1l3.1-6.4L20.1 5.6 17.2 12H8.1L4.3 4H1v2h2l3.6 7.6-1.4 2.4C4.5 17.3 5.5 19 7 19h12v-2H7l1.1-2z"/></svg>';
            echo '<span class="wac-side-cart-count">' . esc_html( wac_side_cart_count() ) . '</span>';
            echo '</button>';
        }

        echo '<div class="wac-side-cart-overlay" hidden></div>';
        echo '<aside id="wac-side-cart" class="wac-side-cart" aria-hidden="true" aria-label="' . esc_attr__( 'Cart', 'woocommerce-ajax-cart' ) . '">';
        echo '<div class="wac-side-cart-header">';
        echo '<span class="wac-side-cart-title">' . esc_html__( 'Your cart', 'woocommerce-ajax-cart' ) . '</span>';
        echo '<button type="button" class="wac-side-cart-close" aria-label="' . esc_attr__( 'Close the cart', 'woocommerce-ajax-cart' ) . '">&times;</button>';
        echo '</div>';
        do_action('wac_side_cart_before_items');
        echo '<div class="widget_shopping_cart wac-side-cart-body"><div class="widget_shopping_cart_content">';
        woocommerce_mini_cart();
        echo '</div></div>';
        do_action('wac_side_cart_after_items');
        echo '</aside>';
    }

    function wac_side_cart_fragments( $fragments ) {
        $fragments['span.wac-side-cart-count'] = '<span class="wac-side-cart-count">' . esc_html( wac_side_cart_count() ) . '</span>';

        return $fragments;
    }

    function wac_frontend_vars() {
        return apply_filters('wac_frontend_vars', array(
            'updating_text' => __( 'Updating...', 'woocommerce-ajax-cart' ),
            'warn_remove_text' => __( 'Are you sure you want to remove this item from cart?', 'woocommerce-ajax-cart' ),
            'ajax_timeout' => wac_option('ajax_timeout'),
            'confirm_zero_qty' => wac_option('confirmation_zero_qty'),
        ));
    }
    
    // define the woocommerce_cart_item_quantity callback
    // add the + and - buttons
    function wac_filter_woocommerce_cart_item_quantity( $inputDiv, $cart_item_key, $cart_item = null ) {
        // check config
        if ( ( wac_option('show_qty_buttons') == 'no' ) || preg_match('/type=\"hidden\"/', $inputDiv) ) {
            return $inputDiv;
        }
    
        // avoid duplication issue some users related
        if ( preg_match('/wac-qty-button/', $inputDiv) ) {
            return $inputDiv;
        }
    
        // add plus and minus buttons
        $minus = wac_button_link('-', 'sub');
        $plus = wac_button_link('+', 'inc');
    
        $input = str_replace(array('<div class="quantity">', '</div>'), array('', ''), $inputDiv);
        $newDiv = '<div class="quantity wac-quantity">' . $minus . $input . $plus . '</div>';
    
        return apply_filters('wac_quantity_div', $newDiv);
    };
    
    function wac_button_link($label, $identifier) {
        $html  = '<a href="" class="wac-qty-button wac-btn-'.$identifier.'">';
        $html .= $label;
        $html .= '</a>';

        return apply_filters('wac_button_link', $html);
    }
    
    function wac_get_template( $located, $template_name, $args, $template_path, $default_path ) {
    
        // ignore if select disabled
        if ( wac_option('qty_as_select') != 'yes' ) {
            return $located;
        }
    
        // modify input template to use select
        if ( 'global/quantity-input.php' == $template_name ) {
            // make the template behavior same as WooCommerce
            $ignoreDisplaySelect = ( !empty($args['min_value']) && !empty($args['max_value']) && ( $args['min_value'] == $args['max_value'] ) );

            if ( !$ignoreDisplaySelect ) {
                $located = plugin_dir_path(WAC_BASE_FILE) . '/templates/wac-qty-select.php';
            }
        }
    
        return apply_filters('wac_template_file', $located, $template_name, $args);
    }
}
