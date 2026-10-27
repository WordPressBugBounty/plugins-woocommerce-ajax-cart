// The Cart block already updates quantities by itself; this asks before an item is removed
// (the "confirmation when quantity goes to zero" setting). Listening in the capture phase on the
// document runs before the block's own handler, so a "Cancel" stops the removal.
document.addEventListener('click', function (e) {
    var link = e.target.closest && e.target.closest('.wc-block-cart-item__remove-link');
    if (link && !window.confirm(wac_cart_block.warn_remove_text)) {
        e.preventDefault();
        e.stopImmediatePropagation();
    }
}, true);
