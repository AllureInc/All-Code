define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Cnnb_WhatsappApi/js/model/telephoneValidation'
    ],
    function (Component, additionalValidators, validateTelephone) {
        'use strict';
        additionalValidators.registerValidator(validateTelephone);
        return Component.extend({});
    }
);