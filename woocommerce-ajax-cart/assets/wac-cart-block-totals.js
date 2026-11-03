// The Cart block's cart (rendered by WooCommerce in React, its data in the wc/store/cart store),
// read the same way by every bar drawn on it: the free shipping bar (wac-free-shipping-block.js)
// and the PRO's rewards bar (wacpro-cart-block.js).
window.wacCartBlockTotals = (function () {
    var STORE = 'wc/store/cart';
    var data = window.wp && window.wp.data;

    return {
        STORE: STORE,
        // The cart store registers with the Cart block's own scripts: until then there is nothing to read.
        store: function () {
            var s = data && data.select(STORE);
            return s && s.getCartData ? s : null;
        },
        // An amount in minor units (cents) as the store shows it: its symbol and separators.
        money: function (minor, totals) {
            var unit = totals.currency_minor_unit || 0;
            var value = (minor / Math.pow(10, unit)).toFixed(unit);
            var parts = value.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, totals.currency_thousand_separator || '');
            return (totals.currency_prefix || '') + parts.join(totals.currency_decimal_separator || '.') + (totals.currency_suffix || '');
        },
        // The items' total less the discounts, in minor units — with their taxes when prices show with tax.
        itemsTotal: function (totals, inclTax) {
            var total = parseInt(totals.total_items, 10) - parseInt(totals.total_discount || 0, 10);
            if (inclTax === 'yes') {
                total += parseInt(totals.total_items_tax || 0, 10) - parseInt(totals.total_discount_tax || 0, 10);
            }
            return total;
        }
    };
})();
