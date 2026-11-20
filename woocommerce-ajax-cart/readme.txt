=== Ajax Cart for WooCommerce – Side Cart, Free Shipping Bar & Quantity Buttons ===
Contributors: moiseh
Tags: side cart, free shipping bar, cart drawer, quantity buttons, ajax cart
Requires at least: 5.0
Tested up to: 7.1
Requires PHP: 7.2
Stable tag: 1.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A side cart that slides in when a product is added, a free shipping bar, and -/+ quantity buttons that update the cart totals with AJAX.

== Description ==

Ajax Cart for WooCommerce makes buying smoother in two ways:

* **A side cart**: a cart drawer that slides in from the side — or a popup — the moment a product is added, with a floating cart button, a coupon form and a free shipping progress bar. It works on classic and block themes alike, on every page but the cart and checkout, and the theme's own header cart can open it too.
* **On the classic cart page** (the `[woocommerce_cart]` shortcode most stores and classic themes use), totals are recalculated with AJAX as soon as a quantity changes — no "Update cart" click — with -/+ buttons around the quantity.

On the newer **Cart block** (the default for stores created since WooCommerce 8.3), WooCommerce already updates quantities by itself; there the plugin adds a confirmation before an item is removed, the free shipping bar, and the side cart works the same.

No code or theme changes are needed: a new install starts with the side cart and the free shipping bar on, and everything else is a switch in WooCommerce > Ajax Cart for WooCommerce.

**Need more?** The PRO adds a rewards progress bar, product suggestions and -/+ quantities inside the side cart, AJAX Add to cart and quantities kept in sync on the shop and product pages — **$35 one-time** (no subscription), with 1 year of updates and support and a 7-day money-back guarantee. See the PRO features below.

You can try and play with this plugin demonstration [clicking here](https://tastewp.com/new?pre-installed-plugin-slug=woocommerce%2Cwoocommerce-ajax-cart&redirect=plugins.php&ni=true)

[youtube https://www.youtube.com/watch?v=zDqIakWksak]

Free version features:

* Side cart: a cart drawer that slides in when a product is added — or a popup in the middle of the screen — with a floating cart button and item count; the theme's header cart icon opens it too
* Coupon form inside the side cart: apply or remove a coupon without leaving the page, the totals and WooCommerce's messages right there
* Your own look for the side cart: accent colour, the floating button's icon and corner, and its texts (title, checkout button, empty cart)
* Free shipping progress bar ("Spend $12.00 more for free shipping") in the side cart, Mini-Cart, classic Cart and Cart block — the amount read from your free shipping method, or your own
* Automatically reload and recalculate the classic Cart using AJAX when quantity changes
* Show -/+ buttons around item quantity on cart page — stepping by the quantity's own step, and left out when your theme draws its own
* Show item quantity as select (a dropdown) instead of a number field
* Ask the shopper to confirm when an item quantity is set to zero — and, on the Cart block, before an item is removed

Premium version features:

* Rewards progress bar: your milestones on the way to bigger orders ("$50: free gift", "$100: 10% off") in the side cart, Mini-Cart, classic Cart and Cart block — it shows the progress, the rewards are your own coupons or rules ([view demo](https://youtu.be/VZEhx2PCi1Y))
* Product suggestions in the side cart: the cart items' cross-sells (or upsells), each with an AJAX "Add" button ([view demo](https://youtu.be/NgZjkvj98rg))
* Change quantities with -/+ buttons inside the side cart ([view demo](https://youtu.be/nl5G8h2ppiA))
* AJAX Add to cart on product pages: the product goes in without a page reload ([view demo](https://youtu.be/-Ko6BZUoDhQ))
* Quantity -/+ buttons on the Shop, Mini-Cart and product pages, kept in sync with the cart ([view demo](https://youtu.be/8p_Zu8_rcO8))
* Live price on product pages: the price follows the quantity chosen (quantity × unit price) ([view demo](https://youtu.be/9ukJhstrIHc))
* Undo a removed item on the Cart block ([view demo](https://youtu.be/8iwx75FxTo0))
* Minimum, maximum and step for every product quantity, on the classic and the block cart — checked again when the cart is updated
* Faster AJAX update of the classic Cart page
* Lock the quantity fields so they change only with the -/+ buttons

Get the PRO version [here](https://moiseh.gumroad.com/l/wajaxcart?utm_source=wporg&utm_medium=plugin&utm_campaign=woocommerce-ajax-cart&utm_content=readme).

[youtube https://www.youtube.com/watch?v=fAP6Jphtrdw]

Looking for more checkout and product page tools — a "Buy now" button, a shorter checkout, quantities on the checkout? Try our [Better Usability for WooCommerce](https://wordpress.org/plugins/woo-better-usability/) plugin.

== Installation ==

1. In your WordPress admin go to Plugins > Add New, search for "Ajax Cart for WooCommerce" and click Install, then Activate (or upload `woocommerce-ajax-cart.zip` there)
2. That's it: a new install starts with the side cart and the free shipping bar on
3. To change anything — the side cart's look, the -/+ buttons, the free shipping amount — open WooCommerce > Ajax Cart for WooCommerce

== Frequently Asked Questions ==

= Does it work with the Cart block? =

Yes. On WooCommerce's Cart block (the default cart of stores created since WooCommerce 8.3) the block already updates the totals by itself; the plugin adds a confirmation before an item is removed and the free shipping bar there, and the side cart works on every page with either cart. The -/+ buttons and AJAX totals are for the classic cart page (the `[woocommerce_cart]` shortcode).

= My theme already has -/+ buttons. Will I get two pairs? =

No. When the theme's quantity field already has its own plus and minus buttons, the plugin leaves its own out and keeps updating the cart when the theme's buttons change the quantity. You can also turn the plugin's buttons off in WooCommerce > Ajax Cart for WooCommerce > General.

= How do I open the side cart from my header cart icon? =

It opens by itself: with the side cart on, a click on any link to the cart page — the theme's header cart, its icon or count — opens the side cart instead (turn that off in Settings > Side cart). For your own button or menu item, give it the CSS class `wac-open-side-cart`.

= Where is the free shipping amount taken from? =

From the "Free shipping" method of the shopper's shipping zone (its minimum order amount), or from the amount you type in Settings > Free shipping bar. With no amount the bar stays hidden.

= Can I hide the floating cart button? =

Yes: turn "Show a floating cart button" off, or have it hidden only while the cart is empty, in Settings > Side cart.

= Can a developer change what the plugin does? =

Yes, with WordPress hooks (filters and actions) and JavaScript events, in a child theme's functions.php or a small plugin:

* `wac_side_cart_enabled` (filter, bool `$enabled`): the side cart and its floating button on or off on the current page.
* `wac_load_assets` (filter, bool `$load`): whether the plugin's cart script and styles load on the current page (by default the Cart page, a page with the classic cart, and pages with the side cart).
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

= 1.6.0 =
* New: the theme's own header cart (any link to the cart page) opens the side cart instead of leaving the page — on by default, Settings > Side cart (2026-11-20)
* New: the floating cart button can stay hidden while the cart is empty (Settings > Side cart) (2026-11-20)
* Changed: a new install starts with the side cart and the free shipping bar on, and a one-time "Get started" notice points to the side cart settings; an update keeps your settings as they are (2026-11-20)
* Changed: the settings page shows the PRO's settings beside the free ones they extend, locked, each with its demo video; the PRO notice moved from the Plugins screen to the plugin's own settings page, where it can be hidden for 90 days; the Get PRO tab says the price and what comes with it (2026-11-20)
* Changed: after two weeks the settings page asks, once, for a review on WordPress.org ("Maybe later" puts it off a month) (2026-11-20)
* Fixed: on themes that draw their own -/+ buttons (Woodmart, Flatsome, Astra…) the cart page no longer gets a second pair, and the quantity field's own markup is kept whole — every `</div>` in it was removed before (2026-11-20)
* Fixed: the -/+ buttons move the quantity by the field's step and stop at its minimum and maximum (they always moved by 1) (2026-11-20)
* Fixed: on a theme without an "Update cart" button, a changed quantity no longer leaves the checkout button stuck on "Updating..." — the cart is updated by a page reload instead (2026-11-20)
* Fixed: a Product Bundles button anywhere on the page no longer turns off the cart's automatic update; only the bundle's own quantities are left alone (2026-11-20)
* Fixed: on grid themes, the -/+ buttons are bound again for ten seconds after the page or a cart refresh, not every half second for as long as the page is open (2026-11-20)
* Hardened: the cart script and styles load only where they are used — the Cart page, a page with the classic cart or with the side cart — not on every page of the site (filter `wac_load_assets`); the admin notices are set up only in the admin (2026-11-20)
* Readme: the plugin's name says what it does (Side Cart, Free Shipping Bar & Quantity Buttons), new tags, the PRO's price, and answers to the common questions (Cart block, a theme's own -/+ buttons, opening the side cart from the header cart) (2026-11-20)

= 1.5.5 =
* Tested with WooCommerce 11.3 and WordPress 7.1

= 1.5.4 =
* Changed: a tidier settings page — a long setting's label sits on its own line over its field (it ran to four lines in the label column), and a group with one long label shows all its labels that way

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
