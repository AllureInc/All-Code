/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
define([
    "jquery",
    'uiRegistry',
    'domReady',
    'Magento_Ui/js/form/element/select',
    "jquery/ui"
], function ($, r, domReady) {
    'use strict';
    $.widget('mage.wkWalletProductSetAttribute', {
        _create: function () {
            var self = this;
            var length = $('select[name="product[wallet_credit_based_on]"]').length;
            if (length > 0) {
                var attrValue = $('select[name="product[wallet_credit_based_on]"]').attr('value');
                if (attrValue!=1) {
                    $('input[name="product[wallet_cash_back]"]').val(0);
                    $('input[name="product[wallet_cash_back]"]').trigger('change');
                    $('input[name="product[wallet_cash_back]"]').parents(".admin__field").hide();
                }
            }
            $('#html-body').delegate('select[name="product[wallet_credit_based_on]"]', 'click', function () {
                var attrValue = $(this).attr('value');
                if (attrValue==1) {
                    $('input[name="product[wallet_cash_back]"]').parents(".admin__field").show();
                    $('input[name="product[wallet_cash_back]"]').val('');
                    $('input[name="product[wallet_cash_back]"]').trigger('change');
                } else {
                    $('input[name="product[wallet_cash_back]"]').parents(".admin__field").hide();
                    $('input[name="product[wallet_cash_back]"]').val(0);
                    $('input[name="product[wallet_cash_back]"]').trigger('change');
                }
            });
        }
    });
    return $.mage.wkWalletProductSetAttribute;
});
