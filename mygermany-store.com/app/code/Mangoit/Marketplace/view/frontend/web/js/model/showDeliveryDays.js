define(
    [
        'jquery',
        'ko',
        'uiRegistry',
        'mage/storage',
        'mage/translate',
    ],
    function ($, ko,uiRegistry, storage, $t ) {
        'use strict';
        return function (countryCode, postalCode) {
            return storage.post(
                'marketplce/checkout/showdeliverydays/',
                false
            ).done(
                function (response) {
                    if (response != 'Fields missing!') {
                        jQuery('.vendor_product_delivery_ul').remove();
                        jQuery('#opc-shipping_method').append(response);
                    }
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