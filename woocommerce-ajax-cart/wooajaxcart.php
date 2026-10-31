<?php
/*
Plugin Name: Ajax Cart for WooCommerce
Plugin URI: https://wordpress.org/plugins/woocommerce-ajax-cart/
Description: Updates the WooCommerce cart with AJAX as soon as a quantity changes — no "Update cart" click needed
Version: 1.5.0
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

    include_once plugin_dir_path(__FILE__) . 'includes/side-cart.php';
    include_once plugin_dir_path(__FILE__) . 'includes/free-shipping.php';

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
            'label' => __('Enable the side cart: a cart that slides in from the side (or opens as a popup), on every page but the cart and checkout', 'woocommerce-ajax-cart'),
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
        $arr['side_cart_coupon'] = array(
            'label' => __('Show a coupon form in the side cart (when coupons are enabled in WooCommerce)', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'checkbox',
            'default' => 'yes',
            'depends_on' => 'side_cart',
        );
        $arr['side_cart_mode'] = array(
            'label' => __('Side cart style: a drawer that slides in from the side, or a popup in the middle of the screen', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'select',
            'default' => 'drawer',
            'options' => array(
                'drawer' => __('Drawer', 'woocommerce-ajax-cart'),
                'popup' => __('Popup', 'woocommerce-ajax-cart'),
            ),
        );
        $arr['side_cart_accent'] = array(
            'label' => __('Accent colour (floating button, checkout button, progress bar) — empty: the default colours', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'color',
            'default' => '',
        );
        $arr['side_cart_icon'] = array(
            'label' => __('Floating button icon', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'select',
            'default' => 'cart',
            'options' => array(
                'cart' => __('Cart', 'woocommerce-ajax-cart'),
                'cart-outline' => __('Cart (outline)', 'woocommerce-ajax-cart'),
                'bag' => __('Shopping bag', 'woocommerce-ajax-cart'),
                'basket' => __('Basket', 'woocommerce-ajax-cart'),
            ),
        );
        $arr['side_cart_position'] = array(
            'label' => __('Floating button position', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'select',
            'default' => 'right',
            'options' => array(
                'right' => __('Bottom right', 'woocommerce-ajax-cart'),
                'left' => __('Bottom left', 'woocommerce-ajax-cart'),
            ),
        );
        $arr['side_cart_title'] = array(
            'label' => __('Side cart title (empty: "Your cart")', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'text',
            'default' => '',
            'size' => 30,
        );
        $arr['side_cart_checkout_text'] = array(
            'label' => __('Checkout button text (empty: WooCommerce\'s "Checkout")', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'text',
            'default' => '',
            'size' => 30,
        );
        $arr['side_cart_empty_text'] = array(
            'label' => __('Empty cart text (empty: WooCommerce\'s "No products in the cart.")', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'text',
            'default' => '',
            'size' => 30,
        );
        $arr['free_shipping_bar'] = array(
            'label' => __('Show a free shipping progress bar in the side cart, Mini-Cart, Cart page and Cart block', 'woocommerce-ajax-cart'),
            'tab' => __('Free shipping bar', 'woocommerce-ajax-cart'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['free_shipping_amount'] = array(
            'label' => __('Order amount for free shipping (empty: read from the free shipping method of the shopper\'s shipping zone)', 'woocommerce-ajax-cart'),
            'tab' => __('Free shipping bar', 'woocommerce-ajax-cart'),
            'type' => 'text',
            'default' => '',
            'depends_on' => 'free_shipping_bar',
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

        // The accent colour of the side cart and the progress bars (once: this also runs before the cart table).
        static $accented = false;
        if ( ! $accented && wac_side_cart_accent() !== '' ) {
            $accented = true;
            wp_add_inline_style('wooajaxcart', ':root{--wac-accent:' . wac_side_cart_accent() . ';}');
        }

        wac_free_shipping_enqueue();

        if ( wac_side_cart_enabled() ) {
            wac_side_cart_enqueue();
        }

        // The Cart block updates quantities by itself; what it lacks is a confirmation before an item is removed.
        if ( is_cart() && wac_option('confirmation_zero_qty') == 'yes' && has_block('woocommerce/cart') ) {
            wp_enqueue_script('wac-cart-block', plugins_url('assets/wac-cart-block.js', plugin_basename(WAC_BASE_FILE)), array(), (string) filemtime($dir . 'assets/wac-cart-block.js'), true);
            wp_localize_script('wac-cart-block', 'wac_cart_block', array(
                'warn_remove_text' => __( 'Are you sure you want to remove this item from cart?', 'woocommerce-ajax-cart' ),
            ));
        }
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
