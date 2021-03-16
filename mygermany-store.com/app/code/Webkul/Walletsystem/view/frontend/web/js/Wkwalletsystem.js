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
    "Magento_Ui/js/modal/modal",
    "jquery/ui"
], function ($, $t, alert, confirm, customerData, url, modal) {
    'use strict';
    $.widget('mage.Wkwalletsystem', {
        options: {
            confirmMessageForDeleteProduct: $t('Are you sure, you want to delete Wallet Amount product?'),
            ajaxErrorMessage: $t('There is some error during executing this process, please try again later.'),
            confirmMessageForDeletePayee: $t('Are you sure, you want to delete payee?'),
            addTransferModel:null
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

            $("body").delegate('a.action.edit, a.action.back', "click", function (e) {
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
            $('.wk_ws_sub_head_transfer').on('click', function () {
                self.addTransferBlockToPage();
            });
            $('.wk_ws_sub_add_payee').on('click', function () {
                self.addPayeeBlockOnPage();
            });
            $('.payee_edit').on('click', function () {
                self.updateLayoutToEdit($(this));
            });
            $('.payee_update').on('click', function () {
                self.updatePayeeData($(this));
            });
            $('.payee_delete').on('click', function () {
                self.deletePayee($(this));
            });
            $('.wk_ws_main').delegate('.payee_cancel', 'click', function () {
                self.cancelPayee($(this));
            });
        },
        setwalletamount :function () {
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
        },
        addTransferBlockToPage: function () {
            var self = this;
            $('.wk_ws_bank_transfer').modal({
                type: 'popup',
                responsive: true,
                innerScroll: true,
                validation:{},
                title: "Enter Transfer Details", //write your popup title
                buttons: [
                    {
                        text: $.mage.__('Submit'),
                        class: 'button',
                        click: function () {
                            var form = $('#walletsystem_trasfer_amount');
                            if ($(form).validation() && $(form).validation('isValid')) {
                                form.submit();
                            }
                        }
                    },
                    {
                        text: $.mage.__('Reset'),
                        class: 'reset',
                        click: function () {
                            var form = $('#walletsystem_trasfer_amount');
                            $(form)[0].reset();
                        }
                    }
                ]
            });
            $('.wk_ws_bank_transfer').modal('openModal');
        },
        addPayeeBlockOnPage: function () {
            var self = this;
            $('.wk_ws_add_payee_modal').modal({
                type: 'popup',
                responsive: true,
                innerScroll: true,
                validation:{},
                title: "Enter Payee Details", //write your popup title
                buttons: [
                    {
                        text: $.mage.__('Submit'),
                        class: 'button',
                        click: function () {
                            var form = $('#walletsystem_add_payee');
                            if ($(form).validation() && $(form).validation('isValid')) {
                                self.sendForAddPayee(form);
                            }
                        }
                    },
                    {
                        text: $.mage.__('Reset'),
                        class: 'reset',
                        click: function () {
                            var form = $('#walletsystem_add_payee');
                            $(form)[0].reset();
                            $('.wk_msg_notification h4').text('');
                            $('.wk_msg_notification h4').removeClass('error_msg');
                        }
                    }
                ]
            });
            $('.wk_ws_add_payee_modal').modal().on('modalclosed', function () {
                var form = $('#walletsystem_add_payee');
                $(form)[0].reset();
                $('.wk_msg_notification h4').text('');
                $('.wk_msg_notification h4').removeClass('error_msg');
            });
            $('.wk_ws_add_payee_modal').modal('openModal');
        },
        sendForAddPayee: function (form) {
            $('body').trigger('processStart');
            var data = $(form).serialize();
            var url = $(form).attr('action');
            var ajaxreturn = $.ajax({
                url:url,
                type:"POST",
                dataType:'json',
                data:data,
                success:function (content) {
                    if (content.error == 1) {
                        $('.wk_msg_notification h4').text(content.error_msg);
                        $('.wk_msg_notification h4').addClass('error_msg');
                    } else {
                        $('.wk_msg_notification h4').text('');
                        $('.wk_msg_notification h4').removeClass('error_msg');
                        $('.wk_ws_add_payee_modal').modal('closeModal');
                        if (content.backUrl) {
                            window.location = content.backUrl;
                        }
                    }
                },
                complete:function (content) {
                    $('body').trigger('processStop');
                }
            });
        },
        updateLayoutToEdit: function (element) {
            var nicknameElement = $(element).parents('tr').find('.nickname');
            var $input = $("<input>", {
                val: $.trim($(nicknameElement).text()),
                type: "text"
            });
            $input.addClass("nickname");
            $(nicknameElement).after($input);
            $(nicknameElement).hide();
            $($input).focus();
            var editElement = $(element).parents('tr').find('.payee_edit');
            $(editElement).hide();
            var UpdateElement = $(element).parents('tr').find('.payee_update');
            $(UpdateElement).show();
            var deleteElement = $(element).parents('tr').find('.payee_delete');
            $(deleteElement).hide();
            var removeElement = $(element).parents('tr').find('.payee_cancel');
            $(removeElement).show();
        },
        updatePayeeData: function (element) {
            var self = this;
            var nicknameElement = $(element).parents('tr').find('td.nickname');
            var inputElement = $(element).parents('tr').find('input.nickname');
            if ($(inputElement).val()!='') {
                $(inputElement).removeClass('required mage-error');
                self.sendRequestForUpdatePayee(inputElement);
            } else {
                $(inputElement).addClass('required mage-error');
                $(inputElement).focus();
            }
        },
        cancelPayee: function (element) {
            var nicknameElement = $(element).parents('tr').find('td.nickname');
            var $input = $(element).parents('tr').find('input.nickname');
            $($input).remove();
            $(nicknameElement).show();
            var editElement = $(element).parents('tr').find('.payee_edit');
            $(editElement).show();
            var UpdateElement = $(element).parents('tr').find('.payee_update');
            $(UpdateElement).hide();
            var deleteElement = $(element).parents('tr').find('.payee_delete');
            $(deleteElement).show();
            var removeElement = $(element).parents('tr').find('.payee_cancel');
            $(removeElement).hide();
        },
        sendRequestForUpdatePayee: function (element) {
            var updateElement = $(element).parents('tr').find('.payee_update');
            var ajaxUrl = $(updateElement).attr('data-url');
            var id = $(updateElement).attr('data-id');
            $('body').trigger('processStart');
            var ajaxreturn = $.ajax({
                url:ajaxUrl,
                type:"POST",
                dataType:'json',
                data:{'id':id,'nickname':$(element).val()},
                success:function (content) {
                    if (content.error == 1) {
                        $(element).addClass('required mage-error');
                        $(element).focus();
                    } else {
                        if (content.backUrl) {
                            window.location = content.backUrl;
                        }
                        var nicknameElement = $(element).parents('tr').find('td.nickname');
                        $(nicknameElement).text($(element).val());
                        $(nicknameElement).show();
                        var editElement = $(element).parents('tr').find('.payee_edit');
                        $(editElement).show();
                        var UpdateElement = $(element).parents('tr').find('.payee_update');
                        $(UpdateElement).hide();
                        var deleteElement = $(element).parents('tr').find('.payee_delete');
                        $(deleteElement).show();
                        var removeElement = $(element).parents('tr').find('.payee_cancel');
                        $(removeElement).hide();
                        $(element).remove();
                    }
                },
                complete:function (content) {
                    $('body').trigger('processStop');
                }
            });
        },
        deletePayee: function (element) {
            var self = this;
            var url = $(element).attr('data-url');
            var dicision = confirm({
                content: self.options.confirmMessageForDeletePayee,
                actions: {
                    confirm: function () {
                        var deleteurl = url;
                        window.location = deleteurl;
                    }
                }
            });
        }
    });
    return $.mage.Wkwalletsystem;
});