jQuery(function($){
    var $panel = $('#wac-side-cart');
    if ( !$panel.length ) {
        return;
    }
    var $overlay = $('.wac-side-cart-overlay');
    var $toggle = $('.wac-side-cart-toggle');

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
    };

    $toggle.on('click', function(){ $panel.hasClass('wac-open') ? close() : open(); });
    $overlay.on('click', close);
    $panel.on('click', '.wac-side-cart-close', close);
    $(document).on('keyup', function(e){ if ( e.key === 'Escape' && $panel.hasClass('wac-open') ) { close(); } });

    // Any link pointing at the cart opens the drawer instead, for the theme's own cart icons too.
    $(document).on('click', '.wac-open-side-cart', function(e){ e.preventDefault(); open(); });

    // added_to_cart: WooCommerce's AJAX buttons; wac_added_to_cart: the PRO's AJAX "Add to cart".
    $(document.body).on('added_to_cart wac_added_to_cart', function(){
        if ( wac_side_cart.open_on_add === 'yes' ) {
            open();
        }
    });
});
