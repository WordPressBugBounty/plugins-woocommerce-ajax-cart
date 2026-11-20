<?php
/*
Plugin Name: Ajax Cart for WooCommerce – Side Cart, Free Shipping Bar & Quantity Buttons
Plugin URI: https://mhtec.org/plugins/woocommerce-ajax-cart?utm_source=wp-admin&utm_medium=plugin&utm_content=plugin-uri
Description: A side cart that slides in when a product is added, a free shipping bar, and a classic cart that updates with AJAX as soon as a quantity changes — no "Update cart" click needed
Version: 1.6.0
Author: Moises Heberle
Author URI: https://mhtec.org/contact/
Text Domain: woocommerce-ajax-cart
Domain Path: /i18n/languages/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.2
Requires Plugins: woocommerce
WC requires at least: 3.2
WC tested up to: 11.3
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

    if ( is_admin() ) {
        include_once plugin_dir_path(__FILE__) . 'includes/getting-started.php';
    }

    // A fresh install starts with the side cart and the free shipping bar on (an update keeps its settings).
    register_activation_hook( WAC_BASE_FILE, 'wac_activate' );

    add_action( 'before_woocommerce_init', function() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            // The Cart and Checkout blocks: the side cart, the free shipping bar and the removal
            // confirmation work on them too (the block cart updates its quantities by itself).
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
        }
    } );

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
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
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WooCommerce's own constant: marks the request as a cart request
            define( 'WOOCOMMERCE_CART', true );
        }

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- public hook name kept for compatibility (the PRO and other extensions hook into it)
        do_action('wac_init');
    }
    
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
    function wac_settings($arr) {
        $arr['show_qty_buttons'] = array(
            'label' => __('Display -/+ buttons around product quantity input', 'woocommerce-ajax-cart'),
            'tab' => __('General', 'woocommerce-ajax-cart'),
            'type' => 'checkbox',
            'default' => 'yes',
        );
        $arr['qty_as_select'] = array(
            'label' => __('Show the item quantity as a dropdown instead of a number field', 'woocommerce-ajax-cart'),
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
            'label' => __('Ask the shopper to confirm before an item is removed (its quantity set to zero or emptied)', 'woocommerce-ajax-cart'),
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
        $arr['side_cart_open_from_cart_links'] = array(
            'label' => __('Open the side cart from the theme\'s cart links and icons (the header cart) instead of going to the cart page', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'checkbox',
            'default' => 'yes',
            'depends_on' => 'side_cart',
        );
        $arr['side_cart_button_hide_empty'] = array(
            'label' => __('Hide the floating cart button while the cart is empty', 'woocommerce-ajax-cart'),
            'tab' => __('Side cart', 'woocommerce-ajax-cart'),
            'type' => 'checkbox',
            'default' => 'no',
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
            'label' => __('Wait this long (milliseconds) after a quantity changes before the cart is updated', 'woocommerce-ajax-cart'),
            'tab' => __('General', 'woocommerce-ajax-cart'),
            'type' => 'number',
            'default' => 800,
            'min' => 1,
            'max' => 5000,
        );

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- public hook name kept for compatibility (the PRO and other extensions hook into it)
        if ( ! apply_filters('mh_wac_is_premium', false) ) {
            $arr += wac_pro_locked_settings();
        }

        return $arr;
    }

    /**
     * The PRO's settings, shown locked (a dimmed switch with a PRO badge and its demo video) beside
     * the free ones they extend — never saved. The PRO has the real settings instead.
     */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
    function wac_pro_locked_settings() {
        $general = __('General', 'woocommerce-ajax-cart');
        $sideCart = __('Side cart', 'woocommerce-ajax-cart');

        return array(
            '_pro_ajaxify_add2cart' => array(
                'label' => __('AJAX Add to cart on product pages — no page reload', 'woocommerce-ajax-cart'),
                'tab' => $general,
                'type' => 'pro_locked',
                'demo' => 'https://youtu.be/-Ko6BZUoDhQ',
            ),
            '_pro_qty_shop' => array(
                'label' => __('Quantity -/+ buttons on the Shop and in the Mini-Cart, kept in sync with the cart', 'woocommerce-ajax-cart'),
                'tab' => $general,
                'type' => 'pro_locked',
                'demo' => 'https://youtu.be/8p_Zu8_rcO8',
            ),
            '_pro_live_price' => array(
                'label' => __('Live price on product pages: the price follows the quantity chosen', 'woocommerce-ajax-cart'),
                'tab' => $general,
                'type' => 'pro_locked',
                'demo' => 'https://youtu.be/9ukJhstrIHc',
            ),
            '_pro_side_cart_qty' => array(
                'label' => __('Change quantities with -/+ buttons inside the side cart', 'woocommerce-ajax-cart'),
                'tab' => $sideCart,
                'type' => 'pro_locked',
                'demo' => 'https://youtu.be/nl5G8h2ppiA',
            ),
            '_pro_side_cart_suggestions' => array(
                'label' => __('Product suggestions in the side cart: cross-sells and upsells with an "Add" button', 'woocommerce-ajax-cart'),
                'tab' => $sideCart,
                'type' => 'pro_locked',
                'demo' => 'https://youtu.be/NgZjkvj98rg',
            ),
            '_pro_rewards_bar' => array(
                'label' => __('Rewards progress bar: your own milestones ("$50: free gift", "$100: 10% off") on the way to bigger orders', 'woocommerce-ajax-cart'),
                'tab' => __('Free shipping bar', 'woocommerce-ajax-cart'),
                'type' => 'pro_locked',
                'demo' => 'https://youtu.be/VZEhx2PCi1Y',
            ),
        );
    }

    /**
     * The PRO's page on Gumroad, tagged with where the click came from (utm_content: the Get PRO
     * tab's buttons, a locked setting…) so the store's traffic report tells the places apart.
     */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
    function wac_premium_url( $url = '', $content = 'wp-admin' ) {
        return add_query_arg( array(
            'utm_source' => 'wp-admin',
            'utm_medium' => 'plugin',
            'utm_campaign' => 'woocommerce-ajax-cart',
            'utm_content' => sanitize_key( (string) $content ),
        ), 'https://moiseh.gumroad.com/l/wajaxcart' );
    }

    /** First activation of a fresh install: the side cart and the free shipping bar on (it hides itself without a free shipping amount). */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
    function wac_activate() {
        if ( get_option('wac_settings', false) === false ) {
            add_option('wac_settings', array('side_cart' => 'yes', 'free_shipping_bar' => 'yes'));
            add_option('wac_fresh_install', time());
        }

        if ( get_option('wac_installed_at', false) === false ) {
            add_option('wac_installed_at', time());
        }
    }
    
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
    function wac_option($name) {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- public hook name kept for compatibility (the PRO and other extensions hook into it)
        return apply_filters('mh_wac_setting_value', $name);
    }
    
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
    function wac_enqueue_scripts() {
        // check if WooCommerce was enabled
        if ( !class_exists('WooCommerce') ) {
            return;
        }

        $deps = array('jquery');

        if ( is_cart() ) {
            $deps[] = 'wc-cart';
        }

        // Versioned by the file's own date, so a new release never meets a cached copy. Registered on every
        // page: the free shipping bar's styles (in the Mini-Cart of any page) build on these.
        $dir = plugin_dir_path(WAC_BASE_FILE);
        wp_register_style('wooajaxcart', plugins_url('assets/wooajaxcart.css', plugin_basename(WAC_BASE_FILE)), array(), (string) filemtime($dir . 'assets/wooajaxcart.css'));
        wp_register_script('wooajaxcart', plugins_url('assets/wooajaxcart.js', plugin_basename(WAC_BASE_FILE)), $deps, (string) filemtime($dir . 'assets/wooajaxcart.js'), false);

        // The accent colour of the side cart and the progress bars (once: this also runs before the cart table).
        static $accented = false;
        if ( ! $accented && wac_side_cart_accent() !== '' ) {
            $accented = true;
            wp_add_inline_style('wooajaxcart', ':root{--wac-accent:' . wac_side_cart_accent() . ';}');
        }

        wac_free_shipping_enqueue();

        if ( ! wac_load_assets() ) {
            return;
        }

        wp_enqueue_style('wooajaxcart');
        wp_enqueue_script('wooajaxcart');

        static $localized = false;
        if ( ! $localized ) {
            $localized = true;
            wp_localize_script( 'wooajaxcart', 'wooajaxcart', wac_frontend_vars() );
        }

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

    /**
     * Whether this page needs the cart script and styles: the Cart page (its -/+ buttons and AJAX
     * totals, or the Cart block's confirmation), a page with the side cart, or a classic cart
     * printed by its shortcode elsewhere. Not a blog post or a page without any of them.
     */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
    function wac_load_assets() {
        $load = is_cart() || doing_action('woocommerce_before_cart_table') || wac_side_cart_enabled();

        /**
         * Filters whether the plugin's cart script and styles load on the current page (the PRO
         * loads them on every store page: its quantities live on the shop, product and Mini-Cart).
         *
         * @since 1.6.0
         *
         * @param bool $load True on the Cart page, a page with the classic cart or the side cart.
         */
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO
        return (bool) apply_filters('wac_load_assets', $load);
    }

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
    function wac_frontend_vars() {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- public hook name kept for compatibility (the PRO and other extensions hook into it)
        return apply_filters('wac_frontend_vars', array(
            'updating_text' => __( 'Updating...', 'woocommerce-ajax-cart' ),
            'warn_remove_text' => __( 'Are you sure you want to remove this item from cart?', 'woocommerce-ajax-cart' ),
            'ajax_timeout' => wac_option('ajax_timeout'),
            'confirm_zero_qty' => wac_option('confirmation_zero_qty'),
        ));
    }
    
    // define the woocommerce_cart_item_quantity callback
    // add the + and - buttons
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
    function wac_filter_woocommerce_cart_item_quantity( $inputDiv, $cart_item_key, $cart_item = null ) {
        // check config
        if ( ( wac_option('show_qty_buttons') == 'no' ) || preg_match('/type=\"hidden\"/', $inputDiv) ) {
            return $inputDiv;
        }
    
        // Ours already there, or the theme draws its own -/+ (Woodmart, Flatsome, Astra…: `plus` / `minus`
        // buttons, a `buttons_added` wrapper): a second pair would sit beside them.
        if ( wac_has_own_qty_buttons($inputDiv) ) {
            return $inputDiv;
        }
    
        // add plus and minus buttons
        $minus = wac_button_link('-', 'sub');
        $plus = wac_button_link('+', 'inc');

        // Only WooCommerce's own wrapper comes off (`<div class="quantity">…</div>`, whatever its classes):
        // the field's other markup keeps its tags — every </div> was taken out before, leaving any inner
        // <div> of a theme's template unclosed.
        $input = $inputDiv;
        if ( preg_match('#^\s*<div\b[^>]*\bclass=["\'][^"\']*\bquantity\b[^"\']*["\'][^>]*>(.*)</div>\s*$#is', $inputDiv, $wrapped) ) {
            $input = $wrapped[1];
        }
        $newDiv = '<div class="quantity wac-quantity">' . $minus . $input . $plus . '</div>';

        /**
         * Filters a cart item's quantity field with its -/+ buttons, on the classic Cart page.
         *
         * @since 1.5.2 The $cart_item_key, $cart_item and $original_html parameters.
         *
         * @param string     $html          The field between its buttons, in `<div class="quantity wac-quantity">`.
         * @param string     $cart_item_key The cart item's key.
         * @param array|null $cart_item     The cart item.
         * @param string     $original_html WooCommerce's field, without the buttons.
         */
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- public hook name kept for compatibility (the PRO and other extensions hook into it)
        return apply_filters('wac_quantity_div', $newDiv, $cart_item_key, $cart_item, $inputDiv);
    }
    
    /** Whether a quantity field's HTML already has -/+ buttons: ours, or a theme's. */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
    function wac_has_own_qty_buttons($html) {
        return (bool) preg_match('/wac-qty-button|buttons_added|class=["\'][^"\']*\b(?:plus|minus)\b/i', (string) $html);
    }

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
    function wac_button_link($label, $identifier) {
        $html  = '<a href="" class="wac-qty-button wac-btn-'.$identifier.'">';
        $html .= $label;
        $html .= '</a>';

        /**
         * Filters the HTML of a - or + quantity button (Cart page; with the PRO, the product,
         * shop and Mini-Cart quantities too). Keep its `wac-qty-button wac-btn-sub|inc` classes:
         * the script finds the buttons by them.
         *
         * @since 1.5.2 The $label and $identifier parameters.
         *
         * @param string $html       The button: `<a href="" class="wac-qty-button wac-btn-…">`.
         * @param string $label      Its text: "-" or "+".
         * @param string $identifier "sub" (the - button) or "inc" (the + button).
         */
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- public hook name kept for compatibility (the PRO and other extensions hook into it)
        return apply_filters('wac_button_link', $html, $label, $identifier);
    }
    
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- wac_ is this plugin's prefix, the same in the free plugin and its PRO; the name is kept for the code and extensions that call it
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
    
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- public hook name kept for compatibility (the PRO and other extensions hook into it)
        return apply_filters('wac_template_file', $located, $template_name, $args);
    }
}
