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
                'marketplce/checkout/shippingrateupdate/CC/'+countryCode+'/PC/'+postalCode,
                false
            ).done(
                function (response) {
                    if (response != 'Fields missing!') {
                        jQuery('.scc_shipping_methods').remove();
                        jQuery('#opc-shipping_method').append(response);
                        jQuery('.form-shipping-address [name=country_id]').find(":selected").val();
                        jQuery('[name=mis_shippings_radio]:first').trigger( "click" );
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