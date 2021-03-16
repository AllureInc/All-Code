/**
 * @category  Webkul
 * @package   Webkul_MpSellerVacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    "jquery/ui"
], function ($, $t) {
    'use strict';
    $.widget('mage.wkvacation', {
        _create: function () {
            var self = this;
            $(self.options.productitems).each(function () {
                var product = $(this);
                var productData = $(self.options.vacationProductData);
                var productId = product.find('.price-box').attr('data-product-id');
                if (productData.length) {
                  var vacationmsg = productData[0][productId];
                  if (vacationmsg != undefined) {
                    self.addVacationText(product,vacationmsg);
                  }
                }
            });
        },
        addVacationText:function (currentObject, vacationmsg) {
            var self = this;
            currentObject.find(".tocart").attr('disabled',true);
            currentObject.find(".tocart span").html(vacationmsg);
            currentObject.find(".tocart").addClass('disabled');
        }
    });
    return $.mage.wkvacation;
});
