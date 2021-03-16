/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Tax/js/view/checkout/summary/grand-total'
], function (Component) {
    'use strict';

    return Component.extend({
        /**
         * @override
         */
        isDisplayed: function () {
            console.log('isDisplayed');
            return true;
        },
        
        isCountry: function () {
            var countryCode = country_code;
            console.log('currentcountry: '+countryCode);
            var countryArr = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB'];
            if ((jQuery.inArray(countryCode, countryArr)> 0))
            {
                console.log("true");
                return true;
            } else {
                console.log("false");
                return false;
            }
        }

    });
});
