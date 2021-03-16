define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Mangoit_Marketplace/js/model/privacyValidate'
    ],
    function (Component, additionalValidators, yourValidator) {
        'use strict';
        additionalValidators.registerValidator(yourValidator);
        return Component.extend({});
    }
);