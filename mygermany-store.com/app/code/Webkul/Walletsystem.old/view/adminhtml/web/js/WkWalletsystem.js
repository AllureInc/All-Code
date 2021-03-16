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
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'uiRegistry',
    "jquery/ui",
    'domReady!'
], function ($, $t, alert, confirm, r) {
    'use strict';
    $.widget('mage.WkWalletsystem', {
        options: {
            backUrl: '',
            confirmMessageForAddAmount: $t('Are you sure you want to update amount in wallet?'),
            alertMessage: $.mage.__('Please Select Customers To Adjust Wallet Amount.'),
            buttonhtml:$.mage.__("Refund to customer's wallet")
        },
        _create: function () {
            var self = this;
            var dataForm = $(self.options.massupdateform);
            dataForm.mage('validation', {});
            $('body').append($(self.options.askdata));
            $(self.options.savebtn).on('click', function (e) {
                if ($(self.options.massupdateform).valid()!=false) {
                    var dicision = confirm({
                        content: self.options.confirmMessageForAddAmount,
                        actions: {
                            confirm: function () {
                                var customerObject = $.parseJSON($('#wkcustomerids').attr("value"));
                                var length = Object.keys(customerObject).length;
                                if (length <= 0) {
                                    alert({
                                        content: self.options.alertMessage
                                    });
                                } else {
                                    $(self.options.massupdateform).submit();
                                    $(self.options.savebtn).text($t("Saving")+'..');
                                    $(self.options.savebtn).css('opacity','0.7');
                                    $(self.options.savebtn).css('cursor','default');
                                    $(self.options.savebtn).attr('disabled','disabled');
                                }
                            },
                        }
                    });
                }
            });
            $('#html-body').delegate('.wk_addamount','click',function () {
                $('#customerid').val($(this).attr('customer-id'));
                var customername = $(this).attr('customer-name');
                $(self.options.askdata).find('.modal-title').text('Adjust Amount to '+customername+"'s Wallet");
                $(self.options.askdata).show();
            });
            $('#html-body').delegate('.wk_close','click',function () {
                $(self.options.askdata).hide();
            });
            $(self.options.submitButton).on('click', function () {
                var walletSingleForm = $(self.options.walletform);
                if ($(self.options.walletform).valid()!==false) {
                    $(this).text($t("Saving")+'..');
                    $(this).css('opacity','0.7');
                    $(this).css('cursor','default');
                    $(this).attr('disabled','disabled');
                    walletSingleForm.submit();
                }
            });
            $('.walletsystem-creditrules-edit #edit_form').on('submit', function () {
                if ($('.walletsystem-creditrules-edit #edit_form').valid()!==false) {
                    $('.walletsystem-creditrules-edit #save').attr('disabled','disabled');
                    $('.walletsystem-creditrules-edit #saveandcontinue').attr('disabled','disabled');
                    $('.walletsystem-creditrules-edit #reset').attr('disabled','disabled');
                    $('.walletsystem-creditrules-edit #my_back').attr('disabled','disabled');
                    $('.walletsystem-creditrules-edit #delete').attr('disabled','disabled');
                }
            });
            var count = 0;
            $('.sales-order-creditmemo-new .order-totals-actions .actions button').each(function () {
                var refundOfflineButton = $(this);
                if (refundOfflineButton.length > 0) {
                    var html = '<span>'+self.options.buttonhtml+'</span>';
                    refundOfflineButton.html(html);
                }
            });
            $('.sales-order-creditmemo-new .order-creditmemo-tables button').on('click', function () {
                $('.sales-order-creditmemo-new .order-totals-actions .actions button').each(function () {
                    var refundOfflineButton = $(this);
                    if (refundOfflineButton.length > 0) {
                        var html = '<span>'+self.options.buttonhtml+'</span>';
                        refundOfflineButton.html(html);
                    }
                });
            });
            $('.sales-order-creditmemo-new .order-totals-actions .actions button:nth-child(2)').hide();
        }
    });
    return $.mage.WkWalletsystem;
});
