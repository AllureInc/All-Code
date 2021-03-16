/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
"jquery",
"jquery/ui",
"mage/translate"
], function ($) {
    'use strict';
    $.widget('mage.orderItems', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var preorderUrl = self.options.orderurl;
                if(preorderUrl){
                     var msgBox = $('<div/>').addClass("wk-msg-box wk-info").append($('<a/>').attr('href',preorderUrl).text($.mage.__("Check Order Reference.")));
                    $(".wk-mp-design .page-title-wrapper").append(msgBox);
                }                                
            });
        }
    });
    return $.mage.orderItems;
});
