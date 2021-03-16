/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
/**
 * Customer balance summary block info
 */
define(
    [
        'Kerastase_CODFee/js/view/checkout/summary/fee'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            
            isFullMode: function () {

                return true;
            }
        });
    }
);
