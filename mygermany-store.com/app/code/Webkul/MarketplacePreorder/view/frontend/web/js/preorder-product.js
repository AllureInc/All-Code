/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    'mage/calendar'
], function ($) {
    'use strict';
    $.widget('mage.preorderProduct', {
        _create: function () {
            var self = this;
            $("#wk-marketplace-availability").calendar({
                'dateFormat':'mm/dd/yy',
                minDate: new Date()                
            });
        }
    });
    return $.mage.preorderProduct;
});