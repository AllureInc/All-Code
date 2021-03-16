define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/sidebar'
], function ($, Component, quote, stepNavigator, sidebarModel) {
    'use strict';

    var mixin = {

        defaults: {
            template: 'Cor_Customizations/shipping-information'
        },

        isVisibleShipTo: function(){
            var shippingMethod = quote.shippingMethod();

            return shippingMethod ? ((shippingMethod.method_code == "flatrate") ? false : true ) : true;
        }
    };

    return function (target) { // target == Result that Magento_Ui/.../default returns.
        return target.extend(mixin); // new result that all other modules receive 
    };
});