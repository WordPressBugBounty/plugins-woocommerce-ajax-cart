// The Cart block: keeps the free shipping bar in step with the cart's totals
// (the cart store and the money format: wac-cart-block-totals.js).
(function () {
    var vars = window.wac_free_shipping;
    var data = window.wp && window.wp.data;
    var cart = window.wacCartBlockTotals;
    if (!vars || !data || !cart || !(vars.threshold > 0)) {
        return;
    }
    var render = function () {
        var bar = document.querySelector('.wac-free-shipping-block');
        var store = cart.store();
        if (!bar || !store) {
            return;
        }
        var totals = store.getCartTotals();
        if (!totals || totals.total_items === undefined) {
            return;
        }
        var unit = totals.currency_minor_unit || 0;
        var total = cart.itemsTotal(totals, vars.incl_tax);
        var threshold = Math.round(vars.threshold * Math.pow(10, unit));
        var remaining = Math.max(0, threshold - total);
        bar.hidden = (store.getCartData().items || []).length === 0;
        bar.classList.toggle('wac-free-shipping-done', remaining === 0);
        bar.querySelector('.wac-free-shipping-text').textContent = remaining > 0 ? vars.spend_text.replace('%s', cart.money(remaining, totals)) : vars.done_text;
        bar.querySelector('.wac-free-shipping-track span').style.width = Math.min(100, Math.round(total / threshold * 100)) + '%';
    };
    data.subscribe(render);
    render();
})();
