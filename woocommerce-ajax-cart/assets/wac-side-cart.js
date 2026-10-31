jQuery(function($){
    var $panel = $('#wac-side-cart');
    if ( !$panel.length ) {
        return;
    }
    var $overlay = $('.wac-side-cart-overlay');
    var $toggle = $('.wac-side-cart-toggle');
    var vars = window.wac_side_cart || {};

    var open = function() {
        $panel.addClass('wac-open').attr('aria-hidden', 'false');
        $overlay.prop('hidden', false);
        $toggle.attr('aria-expanded', 'true');
        $('body').addClass('wac-side-cart-locked');
        $panel.find('.wac-side-cart-close').trigger('focus');
    };
    var close = function() {
        $panel.removeClass('wac-open').attr('aria-hidden', 'true');
        $overlay.prop('hidden', true);
        $toggle.attr('aria-expanded', 'false');
        $('body').removeClass('wac-side-cart-locked');
        $panel.find('.wac-side-cart-notices').empty();
    };

    $toggle.on('click', function(){ $panel.hasClass('wac-open') ? close() : open(); });
    $overlay.on('click', close);
    $panel.on('click', '.wac-side-cart-close', close);
    $(document).on('keyup', function(e){ if ( e.key === 'Escape' && $panel.hasClass('wac-open') ) { close(); } });

    // Any link pointing at the cart opens the drawer instead, for the theme's own cart icons too.
    $(document).on('click', '.wac-open-side-cart', function(e){ e.preventDefault(); open(); });

    // added_to_cart: WooCommerce's AJAX buttons; wac_added_to_cart: the PRO's AJAX "Add to cart".
    var lastJQueryAdd = 0;
    $(document.body).on('added_to_cart wac_added_to_cart', function(){
        lastJQueryAdd = Date.now();
        if ( vars.open_on_add === 'yes' ) {
            open();
        }
    });

    // Block themes: the product blocks add through the Store API and announce it with a DOM event
    // (WooCommerce also echoes every jQuery added_to_cart as one: those are already handled above).
    document.body.addEventListener('wc-blocks_added_to_cart', function(){
        if ( Date.now() - lastJQueryAdd < 1500 ) {
            return;
        }
        $(document.body).trigger('wc_fragment_refresh');
        if ( vars.open_on_add === 'yes' ) {
            open();
        }
    });

    // The Mini-Cart's remove links work by AJAX through WooCommerce's add-to-cart script; block themes
    // drop that script, so without it the side cart removes the item itself, the same way.
    $panel.on('click', '.remove_from_cart_button', function(e){
        if ( window.wc_add_to_cart_params || !vars.wc_ajax_url ) {
            return;
        }
        e.preventDefault();
        var $link = $(this);
        $link.closest('.woocommerce-mini-cart-item').css('opacity', 0.5);
        $.post(endpoint('remove_from_cart'), { cart_item_key: $link.attr('data-cart_item_key') }, null, 'json')
            .done(function(response){
                if ( !response || !response.fragments ) {
                    window.location = $link.attr('href');
                    return;
                }
                $.each(response.fragments, function(selector, html){
                    $(selector).replaceWith(html);
                });
                $(document.body).trigger('removed_from_cart', [ response.fragments, response.cart_hash, $link ]);
            })
            .fail(function(){
                window.location = $link.attr('href');
            });
    });

    // The texts set in the settings, on the Mini-Cart inside the side cart only (the theme's own
    // Mini-Cart keeps WooCommerce's). Run again whenever the cart fragments replace it.
    var applyTexts = function() {
        if ( vars.checkout_text ) {
            $panel.find('.woocommerce-mini-cart__buttons .checkout').text(vars.checkout_text);
        }
        if ( vars.empty_text ) {
            $panel.find('.woocommerce-mini-cart__empty-message').text(vars.empty_text);
        }
    };
    applyTexts();
    $(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart', applyTexts);

    // --- The coupon form: WooCommerce's own AJAX endpoints, as on its Cart page ----------------
    var endpoint = function(name) {
        return String(vars.wc_ajax_url || '').replace('%%endpoint%%', name);
    };
    var $notices = $panel.find('.wac-side-cart-notices');
    var showNotices = function(html) {
        $notices.html(html);
    };
    var refreshCart = function() {
        $(document.body).trigger('wc_fragment_refresh');
    };
    var couponRequest = function($button, action, data, event) {
        if ( !vars.wc_ajax_url ) {
            return;
        }
        $button.prop('disabled', true);
        $notices.empty();
        $.ajax({ type: 'POST', url: endpoint(action), data: data, dataType: 'html' })
            .done(function(html){
                showNotices(html);
                refreshCart();
                $(document.body).trigger(event, [ data.coupon_code || data.coupon ]);
            })
            .fail(function(){
                showNotices($('<ul class="woocommerce-error" role="alert"><li></li></ul>').find('li').text(vars.coupon_error).end());
            })
            .always(function(){
                $button.prop('disabled', false);
            });
    };

    $panel.on('submit', '.wac-coupon-form', function(e){
        e.preventDefault();
        var $form = $(this);
        var code = $.trim($form.find('.wac-coupon-code').val());
        if ( !code ) {
            $form.find('.wac-coupon-code').trigger('focus');
            return;
        }
        var $box = $form.closest('.wac-side-cart-coupon');
        couponRequest($form.find('.wac-coupon-apply'), 'apply_coupon', { security: $box.attr('data-apply-nonce'), coupon_code: code }, 'applied_coupon');
    });

    $panel.on('click', '.wac-coupon-remove', function(e){
        e.preventDefault();
        var $button = $(this);
        var $box = $button.closest('.wac-side-cart-coupon');
        couponRequest($button, 'remove_coupon', { security: $box.attr('data-remove-nonce'), coupon: $button.attr('data-coupon') }, 'removed_coupon');
    });
});
