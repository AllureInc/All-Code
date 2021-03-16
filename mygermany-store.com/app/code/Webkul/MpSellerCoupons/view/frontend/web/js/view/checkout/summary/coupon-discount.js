
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCoupons
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define(
    [
       'jquery',
       'Magento_Checkout/js/view/summary/abstract-total',
       'Magento_Checkout/js/model/quote',
       'Magento_Checkout/js/model/totals',
       'Magento_Catalog/js/price-utils',
       'mage/url'
    ],
    function ($,Component,quote,totals,priceUtils,url) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Webkul_MpSellerCoupons/checkout/summary/coupon-discount'
            },
            totals: quote.getTotals(),
            isDisplayedCoupondiscountTotal : function () {
                var price = 0;
                price = totals.getSegment('coupondiscount_total').value;
                if (price) {
                    return true;
                } else {
                    return false;
                }
            },
            getCoupondiscountTotal : function () {
                var price = 0;
                price = totals.getSegment('coupondiscount_total').value;
                return this.getFormattedPrice(price);
            },
            
         });
    }
);