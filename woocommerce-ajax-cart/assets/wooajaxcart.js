/**
 * The classic Cart page: a quantity changed (typed, picked, or through the -/+ buttons) updates
 * the cart by AJAX — WooCommerce's own "Update cart" button, clicked for the shopper.
 *
 * window.wacChange, window.wacRefreshCart and window.wacListenQtyButtons are public on purpose:
 * the PRO replaces wacRefreshCart (its "faster AJAX" update) and theme snippets call them.
 *
 * Developer event: `wac_before_cart_update` (on document.body, with the quantity field) just
 * before the cart update is sent.
 */
jQuery(function($){
    var wacUpdateTimeout = null;
    var wacGridPollPending = false;

    window.wacChange = function(qtyInput) {
        // ask user if they really want to remove this product
        if ( ( wooajaxcart.confirm_zero_qty == 'yes' ) && !wacZeroQuantityCheck(qtyInput) ) {
            return false;
        }

        // when qty is set to zero, then fires default woocommerce remove link
        if ( qtyInput.val() == 0 ) {
            qtyInput.closest('.cart_item').find('.product-remove a').trigger('click');
            return false;
        }

        // clear previous timeout, if exists
        if ( wacUpdateTimeout !== null ) {
            clearTimeout(wacUpdateTimeout);
        }

        wacUpdateTimeout = setTimeout(function(){
            $(document.body).trigger('wac_before_cart_update', [ qtyInput ]);
            window.wacRefreshCart(qtyInput);
        }, wooajaxcart.ajax_timeout);

        return true;
    };

    window.wacRefreshCart = function(qtyInput) {
        // some themes creates multiple cart forms so it will try to find the correct update button
        var cartForm = qtyInput.closest('.woocommerce-cart-form');
        var mainSelector = cartForm.length ? cartForm : $(document);

        // deal with update cart button
        var updateButton = mainSelector.find("button[name='update_cart']:not(.dgfw-add-gift-button),input[name='update_cart']:not(.dgfw-add-gift-button)");

        updateButton.removeAttr('disabled')
                    .trigger('click')
                    .val( wooajaxcart.updating_text )
                    .prop('disabled', true);

        // change the Update cart button
        $("a.checkout-button.wc-forward").addClass('disabled')
                                         .html( wooajaxcart.updating_text );
    };

    // The quantity back to the field's minimum (1 when it has none).
    var wacResetNewQty = function(qtyInput) {
        var min = qtyInput.attr('min');

        return ( min > 0 ) ? min : 1;
    };

    // "Remove this item?" when the quantity goes to zero or the field is emptied; "Cancel" puts it back.
    var wacZeroQuantityCheck = function(qtyInput) {
        // when changes quantity to zero
        if ( parseInt(qtyInput.val()) == 0 && !confirm(wooajaxcart.warn_remove_text) ) {
            qtyInput.val( wacResetNewQty(qtyInput) );
            return false;
        }

        // when empty the quantity input
        if ( qtyInput.val().length === 0 && !confirm(wooajaxcart.warn_remove_text) ) {
            qtyInput.val( wacResetNewQty(qtyInput) ).trigger('change');
            return false;
        }

        return true;
    };

    var wacListenQtyChange = function() {
        $(document).on('change', '.qty', function(e){
            var qtyInput = $(this);

            // prevent troubles with WooCommerce Product Bundles plugin
            if ( $('.bundle_add_to_cart_button').length ) {
                return true;
            }

            // prevent to set invalid quantity on select
            if ( qtyInput.is('select') && ( qtyInput.attr('max') > 0 ) &&
                 ( parseInt(qtyInput.val()) > parseInt(qtyInput.attr('max')) ) ) {
                qtyInput.val( qtyInput.attr('max') );

                e.preventDefault();
                return false;
            }

            return window.wacChange( qtyInput );
        });
    };

    var wacFindInputQty = function(btn) {
        var inputQty = btn.parent().parent().parent().find('.qty');

        if ( !inputQty.length ) {
            inputQty = btn.closest('.cart').find('.qty');
        }

        if ( !inputQty.length ) {
            inputQty = btn.closest('.product-quantity').find('.qty');
        }

        return inputQty;
    };

    // The -/+ buttons. A field with no max (or no min) is never capped: parseFloat() of a missing
    // attribute is NaN, and any comparison with NaN is false.
    var wacIncrement = function(e){
        if ( $(this).hasClass('disabled') ) {
            e.preventDefault();
            return false;
        }

        var inputQty = wacFindInputQty( $(this) );

        if ( parseFloat(inputQty.val()) >= parseFloat(inputQty.attr('max')) ) {
            return false;
        }

        inputQty.val( function(i, oldval) { return ++oldval; });
        inputQty.trigger('change');

        return false;
    };

    var wacDecrement = function(e){
        if ( $(this).hasClass('disabled') ) {
            e.preventDefault();
            return false;
        }

        var inputQty = wacFindInputQty( $(this) );
        var oldVal = inputQty.val();

        if ( parseInt(inputQty.attr('min')) >= oldVal ) {
            return false;
        }

        inputQty.val( --oldVal );
        inputQty.trigger('change');

        return false;
    };

    // Bound on the buttons themselves (a theme that stops the click on its way up still gets them) and
    // delegated on the document (buttons drawn later). The direct handler returns false, which stops
    // the click there, so one click never counts twice.
    window.wacListenQtyButtons = function() {
        if ( $('.wac-btn-inc').length ) {
            // fix common style problem in single product page
            if ( !$('#wac-style-prod-qty').length ) {
                $("<style type='text/css' id='wac-style-prod-qty'>.product .quantity { display: inline; float: none !important; margin-right: 0 !important; }</style>").appendTo('head');
            }

            $('.wac-btn-inc').off('click.wac2910').on('click.wac2910', wacIncrement);
            $('.wac-btn-sub').off('click.wac2911').on('click.wac2911', wacDecrement);

            // Grid themes draw their products after load: bind the new buttons every half second.
            // One timer at a time — before, every cart fragments refresh started one more.
            if ( $('.grid').length && !wacGridPollPending ) {
                wacGridPollPending = true;
                setTimeout(function(){
                    wacGridPollPending = false;
                    window.wacListenQtyButtons();
                }, 500);
            }
        }

        $(document).off('click.wac3000').on('click.wac3000', '.wac-btn-inc', wacIncrement);
        $(document).off('click.wac3001').on('click.wac3001', '.wac-btn-sub', wacDecrement);
    };

    // onload calls
    wacListenQtyChange();
    window.wacListenQtyButtons();

    $(document.body).on('wc_fragments_refreshed', function(){
        window.wacListenQtyButtons();
    });

    $(document.body).on('added_to_cart', function(){
        // prevent woocommerce to reload the page after Add to cart in certain conditions (outside Cart page)
        // the window.location.reload() was defined in woo cart.js script
        if ( $('.woocommerce-cart-form').length == 0 ) {
            $('body').append('<div class="woocommerce-cart-form" style="display: none;"></div>');
        }
    });
});
