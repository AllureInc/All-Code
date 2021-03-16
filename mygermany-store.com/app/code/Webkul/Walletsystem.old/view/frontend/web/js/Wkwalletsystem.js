/**
 * Webkul Wkwalletsystem.js
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'Magento_Customer/js/customer-data',
    'mage/url',
    "jquery/ui"
], function ($, $t, alert, confirm, customerData, url) {
    'use strict';
    $.widget('mage.Wkwalletsystem', {
        options: {
            confirmMessageForDeleteProduct: $t('Are you sure, you want to delete Wallet Amount product?'),
            ajaxErrorMessage: $t('There is some error during executing this process, please try again later.')
        },
        _create: function () {
            var self = this;
            this._super();
            var dataForm = $(self.options.walletformdata);
            dataForm.mage('validation', {});
            customerData.reload(['cart'], true);
            $('body #shipping-method-buttons-container button.continue').on('click', function (e) {
                $('body').trigger('processStart');
                e.preventDefault();
                var ajaxreturn = $.ajax({
                    url:self.options.ajaxurl,
                    type:"POST",
                    dataType:'json',
                    data:{wallet:'reset',grandtotal:self.options.grandtotal},
                    success:function (content) {
                        $('body').trigger('processStop');
                        $('#co-shipping-method-form').submit();
                    }
                });
            });

            $("body").delegate('a.action.edit, a.action.back', "click", function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                var abc = self.setwalletamount();
                if (abc) {
                    window.location = url;
                }
            });
            $(self.options.deletelink).on('click', function (e) {
                var element = $(this);
                var datapost = element.attr('data-post');
                if (datapost==='undefined' || datapost=='' || datapost==null) {
                    var dicision = confirm({
                        content: self.options.confirmMessageForDeleteProduct,
                        actions: {
                            confirm: function () {
                                var deleteurl = JSON.parse(element.attr('url'));
                                var updatedUrl = deleteurl.action;
                                $.each(deleteurl.data, function (key, value) {
                                    updatedUrl = updatedUrl + key +"/"+ value + '/';
                                });
                                element.attr('data-post',element.attr('url'));
                                $(self.options.deletelink).trigger('click');
                            },
                        }
                    });
                }
            });
            
        },
        setwalletamount :function() {
            var paymentmethod = this;
            var restamount = 0;
            var type = 'reset';
            var ajaxUrl = window.authenticationPopup.baseUrl+'walletsystem/index/applypaymentamount';
            $('body').trigger('processStart');
            var ajaxreturn = $.ajax({
                url:ajaxUrl,
                type:"POST",
                dataType:'json',
                data:{wallet:type,grandtotal:''},
                success:function (content) {
                    $('body').trigger('processStop');
                    return true;
                }
            });
            if (ajaxreturn) {
                return true;
            }
        }
    });
    return $.mage.Wkwalletsystem;
});