/**
 * Copyright Â© 2016 MagestyApps. All rights reserved.
 * See LICENSE.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'jquery',
        'Magento_Checkout/js/action/get-totals'
    ],
    function (quote, fullScreenLoader, jQuery, getTotalsAction) {
        'use strict';
        return function (paymentMethod) {
            quote.paymentMethod(paymentMethod);

            //if (window.paymentFeeConfig.isEnabled)
            //@todo make it configuration based
            if (true) {
                fullScreenLoader.startLoader();

                jQuery.ajax('/kerastase_codfee/cod/apply', {
                    data: { payment_method: paymentMethod },
                    dataType: "json",
                    method: "POST",
                    complete: function () {
                        getTotalsAction([]);
                        fullScreenLoader.stopLoader();
                    }
                });
            }
        };
    }
);
