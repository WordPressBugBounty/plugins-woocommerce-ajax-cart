// The Cart block (rendered by WooCommerce in React, its data in the wc/store/cart store):
// keeps the free shipping bar in step with the cart's totals.
(function () {
    var vars = window.wac_free_shipping;
    var data = window.wp && window.wp.data;
    if (!vars || !data || !(vars.threshold > 0)) {
        return;
    }
    var STORE = 'wc/store/cart';
    // The cart store registers with the Cart block's own scripts: until then there is nothing to read.
    var store = function () {
        var s = data.select(STORE);
        return s && s.getCartData ? s : null;
    };
    var money = function (minor, totals) {
        var unit = totals.currency_minor_unit || 0;
        var value = (minor / Math.pow(10, unit)).toFixed(unit);
        var parts = value.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, totals.currency_thousand_separator || '');
        return (totals.currency_prefix || '') + parts.join(totals.currency_decimal_separator || '.') + (totals.currency_suffix || '');
    };
    var render = function () {
        var bar = document.querySelector('.wac-free-shipping-block');
        if (!bar || !store()) {
            return;
        }
        var totals = store().getCartTotals();
        if (!totals || totals.total_items === undefined) {
            return;
        }
        var unit = totals.currency_minor_unit || 0;
        var total = parseInt(totals.total_items, 10) - parseInt(totals.total_discount || 0, 10);
        if (vars.incl_tax === 'yes') {
            total += parseInt(totals.total_items_tax || 0, 10) - parseInt(totals.total_discount_tax || 0, 10);
        }
        var threshold = Math.round(vars.threshold * Math.pow(10, unit));
        var remaining = Math.max(0, threshold - total);
        bar.hidden = (store().getCartData().items || []).length === 0;
        bar.classList.toggle('wac-free-shipping-done', remaining === 0);
        bar.querySelector('.wac-free-shipping-text').textContent = remaining > 0 ? vars.spend_text.replace('%s', money(remaining, totals)) : vars.done_text;
        bar.querySelector('.wac-free-shipping-track span').style.width = Math.min(100, Math.round(total / threshold * 100)) + '%';
    };
    data.subscribe(render);
    render();
})();
