/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, totals) {
        
        return Component.extend({
            defaults: {
                template: 'Kerastase_CODFee/checkout/summary/fee'
            },
            totals: totals.totals(),

            /**
             * get fee without any formatting
             *
             * @return {number}
             */
            getPureValue: function (code) {
                var price = 0;
                if (this.totals) {
                    var segment = totals.getSegment(code);
                    if (segment) {
                        price = segment['value'];
                    }
                }
                return price;
            },
            /**
             * Fee with currency sign and localization
             *
             * @return {string}
             */
            getValue: function () {
                return this.getFormattedPrice(this.getPureValue('cod_fee'));
            },
            /**
             * Base Fee with currency sign and localization
             *
             * @return {string}
             */
            getBaseValue: function () {
                return this.getFormattedPrice(this.getPureValue('base_cod_fee'));
            },
            /**
             * Availability status
             *
             * @returns {boolean}
             */
            isAvailable: function() {
                return this.isFullMode() && this.getPureValue('cod_fee') != 0;
            }
        });
    }
);