=== Ajax Cart for WooCommerce ===
Contributors: moiseh
Tags: woocommerce, ajax, cart, side cart, quantity
Requires at least: 5.0
Tested up to: 7.1
Stable tag: 1.5.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AJAX cart for WooCommerce: totals update as soon as a quantity changes, plus a side cart with a coupon form and a free shipping bar.

== Description ==

Ajax Cart for WooCommerce makes buying smoother in two ways:

* **On the classic cart page** (the `[woocommerce_cart]` shortcode most stores and classic themes use), totals are recalculated with AJAX as soon as a quantity changes — no "Update cart" click — with -/+ buttons around the quantity.
* **A side cart** (optional): a cart drawer that slides in from the side — or a popup — when a product is added, with a floating cart button, a coupon form and a free shipping progress bar — on classic and block themes alike, on every page but the cart and checkout.

On the newer **Cart block** (the default for stores created since WooCommerce 8.3), WooCommerce already updates quantities by itself; there the plugin adds a confirmation before an item is removed, and the side cart works the same.

No other hacks, code or theme changes are needed: the features are active as soon as the plugin is.

You can try and play with this plugin demonstration [clicking here](https://tastewp.com/new?pre-installed-plugin-slug=woocommerce%2Cwoocommerce-ajax-cart&redirect=plugins.php&ni=true)

[youtube https://www.youtube.com/watch?v=UXX8h4ClMy4]

Free version features:

* Automatically reload and recalculate the classic Cart using AJAX when quantity changes
* Show -/+ buttons around item quantity on cart page
* Show item quantity as select instead numeric field
* Show user confirmation when change item quantity to zero — and, on the Cart block, before an item is removed
* Side cart: a cart drawer that slides in when a product is added — or a popup in the middle of the screen — with a floating cart button and item count
* Coupon form inside the side cart: apply or remove a coupon without leaving the page, the totals and WooCommerce's messages right there
* Your own look for the side cart: accent colour, the floating button's icon and corner, and its texts (title, checkout button, empty cart)
* Free shipping progress bar ("Spend $12.00 more for free shipping") in the side cart, Mini-Cart, classic Cart and Cart block — the amount read from your free shipping method, or your own

Premium version features:

* Allow to change/synchronize quantities in shop, minicart and single product pages [view demo](https://youtu.be/8p_Zu8_rcO8)
* Make the `Add to cart` button to perform with AJAX, without full page reload [view demo](https://youtu.be/-Ko6BZUoDhQ)
* Faster AJAX reload call when change quantities in Cart page
* Option to lock quantity inputs to allow only change using plus and minus buttons
* Update price vs quantity calculation automatically on Single Product pages ([view demo](https://youtu.be/9ukJhstrIHc))
* Change quantities with -/+ buttons inside the side cart ([view demo](https://youtu.be/nl5G8h2ppiA))
* Undo a removed item on the Cart block ([view demo](https://youtu.be/8iwx75FxTo0))
* Minimum, maximum and step for every product quantity, on the classic and the block cart
* Rewards progress bar: your milestones on the way to bigger orders ("$50: free gift", "$100: 10% off") in the side cart, Mini-Cart, classic Cart and Cart block — it shows the progress, the rewards are your own coupons or rules ([view demo](https://youtu.be/VZEhx2PCi1Y))
* Product suggestions in the side cart: the cart items' cross-sells (or upsells), each with an AJAX "Add" button ([view demo](https://youtu.be/NgZjkvj98rg))

Get the PRO version [here](https://gum.co/wajaxcart).

[youtube https://www.youtube.com/watch?v=a7MG9YykSfg]

If you looking for a related plugin with more complete features maybe you can try [Better Usability for WooCommerce](https://wordpress.org/plugins/woo-better-usability) plugin.

== Installation ==

1. Upload `woocommerce-ajax-cart.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Done. This plugin no requires extra configurations to work

== Frequently Asked Questions ==

= Can a developer change what the plugin does? =

Yes, with WordPress hooks (filters and actions) and JavaScript events, in a child theme's functions.php or a small plugin:

* `wac_side_cart_enabled` (filter, bool `$enabled`): the side cart and its floating button on or off on the current page.
* `wac_side_cart_before_items` / `wac_side_cart_after_items` (actions): print your own HTML in the side cart, above or below its items.
* `wac_free_shipping_threshold` (filter, float `$threshold`): the order amount that gives free shipping, as the bars show it — for example converted to the shopper's currency; 0 hides the bar.
* `wac_progress_bar_html` (filter, string `$html`): the free shipping bar printed in the side cart, Mini-Cart and classic Cart.
* `wac_free_shipping_block_enabled` (filter, bool `$enabled`): whether the Cart block gets the free shipping bar.
* `wac_quantity_div` (filter: `$html`, `$cart_item_key`, `$cart_item`, `$original_html`): a cart item's quantity field with its -/+ buttons.
* `wac_button_link` (filter: `$html`, `$label`, `$identifier` — `sub` or `inc`): the HTML of a - or + button.
* `wac_template_file` (filter: `$located`, `$template_name`, `$args`): the quantity dropdown template ("Show item quantity as select").
* `wac_frontend_vars` (filter, array `$vars`): the settings and texts the cart script reads.
* `mh_wac_settings` (filter, array `$settings`): the settings, with their defaults.
* `wac_before_cart_update` (JavaScript event on `document.body`, with the quantity field): just before a changed quantity updates the classic cart.

== Screenshots ==

1. The classic cart page: a click on "+" or "-" updates the totals with AJAX, without clicking "Update cart" — with the free shipping progress bar.
2. The side cart opens when a product is added: a coupon form, the discount and the total after it, and the free shipping progress bar.
3. The side cart as a popup, in your accent colour, with the floating cart button and its item count.
4. The free shipping progress bar on WooCommerce's Cart block.
5. The side cart settings: drawer or popup, coupon form, accent colour, the floating button's icon and corner, and the texts.

== Changelog ==

= 1.5.2 =
* Developers: new hooks — `wac_side_cart_enabled` (the side cart on or off on a page), `wac_free_shipping_threshold` (the free shipping amount, for example in the shopper's currency), more arguments on `wac_quantity_div` and `wac_button_link`, and the JavaScript event `wac_before_cart_update`; every hook is listed in the FAQ (2026-11-03)
* Hardened: the plugin declares to WooCommerce that it works with the Cart and Checkout blocks (WooCommerce 8.x listed a plugin that did not as incompatible) (2026-11-03)
* Fixed: on themes with a grid product layout, every cart refresh started one more half-second timer re-binding the -/+ buttons; there is one timer now (2026-11-03)
* Hardened: the scripts cleaned up, with no change in what they do — the functions other code calls are declared on purpose, repeated code merged, the deprecated `$.trim()` (jQuery 3.5) replaced, and the Cart block's bars read the cart's totals through one shared script (2026-11-03)
* Fixed: WooCommerce's System Status no longer lists the plugin's quantity dropdown template as out of date ("version 4.0.0 is out of date") — it follows WooCommerce's current quantity template: a label for screen readers, the field's id and WooCommerce's classes (2026-11-03)

= 1.5.1 =
* Hardened: WordPress.org Plugin Check warnings (2026-11-02) — every hook and function name checked and documented as the plugin's own, WooCommerce's constant and template variables marked as such

= 1.5.0 =
* New: a coupon form inside the side cart — apply or remove a coupon with AJAX, the coupons applied with their discount, the subtotal after them and WooCommerce's messages in the cart; shown when coupons are enabled in WooCommerce (Settings > Side cart)
* New: the side cart as a popup in the middle of the screen, or the drawer as before (Settings > Side cart)
* New: the side cart's look — an accent colour (WordPress's colour picker), the floating button's icon (cart, outline cart, bag, basket) and corner (bottom right or left), and its texts: title, checkout button, empty cart (Settings > Side cart)
* Changed: the free shipping progress bar, until now a PRO feature, is part of the free plugin — in the side cart, Mini-Cart, classic Cart and Cart block (Settings > Free shipping bar)

= 1.4.1 =
* Changed: a cleaner Get PRO tab — the PRO features one per row with a check, a red YouTube "Watch demo" button for each feature video, and the buy button at the top and the bottom

= 1.4.0 =
* Fixed: the Side cart settings tab opened empty (a tab name with a space)
* New: side cart — a cart drawer that slides in from the side when a product is added, with a floating cart button and item count (Settings > Side cart; off by default)
* New: on the Cart block, a confirmation before an item is removed (with the "confirmation when quantity goes to zero" setting)
* The description now says where each feature applies: the classic cart page, and what the Cart block already does by itself
* Changed: a cleaner settings page — the settings in a card, one row each with the label and its field aligned, on/off settings as switches, multiple choices as chips, a setting's sub-options in a panel under it, and Reset set apart from Save

= 1.3.28 =
* Changed: the settings page's script variable, nonces and notice links now carry the plugin's own prefix; "Tested up to" is declared only in readme.txt

= 1.3.27 =
* Renamed to "Ajax Cart for WooCommerce" and given a new icon, following the WordPress.org trademark guidelines (the plugin's address and settings are unchanged)
* The plugin header now declares its license and that it requires WooCommerce
* Fixed: with another of our plugins on the same site, the two no longer share — and override — each other's settings, notices and update code

= 1.3.26 =
* Tested with WordPress 7.1 and WooCommerce 11.2 (on PHP 8.4)
* Security hardening: the settings page escapes every value it prints and unslashes what it saves, and dismissing an admin notice now requires a valid nonce

= 1.3.25 =
* Added `High-Performance order storage` compatibility

= 1.3.24 =
* Reset quantity to minimum value when denying the confirmation to remove item from the cart

= 1.3.23 =
* Tested with WooCommerce 6.4.1 and WordPress 5.9.3
* Fixed reported PHP warning error at Admin panel

= 1.3.22 =
* Changed default quantity buttons CSS for better looking
* Testing with WordPress 5.8.1 and WooCommerce 5.8.0

= 1.3.21 =
* Avoid to add hide style for product quantity multiple times
* Prevent user from click on quantity buttons when disabled
* Tested with WordPress 5.8

= 1.3.20 =
* Tested with WooCommerce 5.0.0
* Fixed translation domain path

= 1.3.19 =
* Added dropdown_steps argument support for woocommerce_quantity_input_args hook

= 1.3.18 =
* Fixed fatal error on frontend when WooCommerce plugin not enabled
* Let dropdown respect min_value and step values
* Added wac_quantity_div and wac_template_file filters
* Removed old legacy migration

= 1.3.17 =
* Using document instead document.body listeners that was causing issues with some themes
* Trigger correct Update cart button in Cart for themes that using multiple layouts
* Changed HTML structure of buttons to make it more clickable

= 1.3.16 =
* Tested with latest WordPress and WooCommerce version
* Fixed critical bug when changing quantity in Cart

= 1.3.15 =
* Tested with latest WordPress and WooCommerce versions
* Removed unwanted admin notices

= 1.3.14 =
* Optimized plus and minus quantity javascript listeners

= 1.3.13 =
* Show confirmation to remove product when user empty quantity input

= 1.3.12 =
* Removing deprecated PHP short_open_tag blocks

= 1.3.11 =
* Updated template override compatibility to 4.0.0 and WC tested up to 4.2.0

= 1.3.10 =
* Reversed changelog ordering to make it more standard
* Changed plugin notices to respect the guidelines

= 1.3.9 =
* Added compatibility support with WooCommerce 4.0.1

= 1.3.8 =
* Compatibility with WooCommerce Bundled Products

= 1.3.7 =
* Hide quantity select in Cart when maximum value is same as minimum value

= 1.3.6 =
* Updated supported woocommerce version to 3.8.1

= 1.3.5 =
* Updated supported woocommerce version to 3.7.1
* Prevent page reload when adding to cart in certain conditions

= 1.3.4 =
* Changed limit from 50 to 1000 for quantity in select
* Added compatibility with Giftable for WooCommerce plugin

= 1.3.3 =
* Removing +/- buttons when product is sold individually

= 1.3.2 =
* Added compatibility with WooCommerce 3.6.4
* Standardized to `use strict` mode on frontend script, removing unused code

= 1.3.1 =
* Registering scripts using common hook `wp_enqueue_scripts`


== Upgrade Notice ==
