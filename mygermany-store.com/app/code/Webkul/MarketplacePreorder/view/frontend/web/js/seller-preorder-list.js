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
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    'mage/calendar'
], function ($, $t, alert) {
    'use strict';
    $.widget('mage.sellerPreorderList', {
        _create: function () {
            var self = this;
            $("#special-from-date").calendar({'dateFormat':'mm/dd/yy'});
            $("#special-to-date").calendar({'dateFormat':'mm/dd/yy'});

            $('body').delegate('.mp-edit','click',function () {
                var dicision=confirm($.mage.__(" Are you sure you want to edit this product ? "));
                if (dicision === true) {
                    var $url=$(this).attr('data-url');
                    window.location = $url;
                }
            });
            $('#mass-send-butn').click(function (e) {
                var flag =0;
                $('.mpcheckbox').each(function () {
                    if (this.checked === true) {
                        flag =1;
                    }
                });
                if (flag === 0) {
                    alert({content : $.mage.__(' No Checkbox is checked ')});
                    return false;
                } else {
                        alert({content : $.mage.__('Are you sure you want to notify to customer(s) ?'),
                            buttons: [
                            {
                                text: $.mage.__('No'),
                                class: '',
                                click: function () {
                                    this.closeModal();
                                }
                            },
                            {
                                text: $.mage.__('Yes'),
                                attr: {
                                    'data-action': 'confirm'
                                },
                                class: 'action-primary action-accept',
                                click: function () {
                                    this.closeModal();
                                    $('#form-order-massemail').submit();
                                }
                            }]
                        });
                        return false;
                }
            });

            $('#mpselecctall').click(function (event) {
                if (this.checked) {
                    $('.mpcheckbox').each(function () {
                        this.checked = true;
                    });
                } else {
                    $('.mpcheckbox').each(function () {
                        this.checked = false;
                    });
                }
            });
        }
    });
        return $.mage.sellerPreorderList;
});
