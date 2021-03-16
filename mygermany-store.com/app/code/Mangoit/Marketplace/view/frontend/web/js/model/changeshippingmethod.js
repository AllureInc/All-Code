define(
    [
        'jquery',
        'ko',
        'uiRegistry',
        'mage/storage',
        'mage/translate',
    ],
    function ($, ko, uiRegistry, storage, $t ) {
        'use strict';
        return function (methodCode,vtmg) {
            return storage.post(
                'marketplce/checkout/updateshippingmethod/code/'+methodCode+'/vtmg/'+vtmg,
                false
            ).done(
                function (response) {
                    jQuery('input[aria-labelledby="label_method_dropship_dropship label_carrier_dropship_dropship"]').trigger( "click" );
                    uiRegistry.async("checkout.iosc.ajax")(
                        function (ajax) {
                            ajax.update();
                        }
                    );
                    jQuery('body').trigger('processStop');
                }
            ).fail(
                function (response) {
                    console.log('fail');
                }
            );
        };
    }
);