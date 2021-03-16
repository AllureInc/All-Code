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
        "mage/template",
        "mage/mage",
    ], function ($, $t,mageTemplate, alert) {
        'use strict';
        $.widget('mage.preorderConfig', {
            options: {
                preorderType : '#preorder_type',
                percent: '.mppreorder-percent',
                action: '#preorder_action',
                fewProduct: '.mppreorder-few',
                disableProduct: '.mppreorder-allproducts',
                mainMenu : '.main-menu'
            },
            _create: function () {
                var self = this;

                $(self.options.percent).hide();
                $(self.options.fewProduct).hide();
                $(self.options.disableProduct).hide();
                var percentvalue = $('.mppreorder-percent #preorder_percent').val();
                if (percentvalue != '' && percentvalue != 'undefined') {
                    $(self.options.percent).show();
                }

                if ($(self.options.action).val() == 2) {
                    $(self.options.fewProduct).show();
                };

                if ($(self.options.action).val() == 3) {
                    $(self.options.disableProduc).show();
                };

                $('.mppreorder-percent #preorder_percent').removeAttr('data-validate');
                $('.mppreorder-allproducts #disable_products').removeAttr('data-validate');
                $('.mppreorder-few #few_products').removeAttr('data-validate');

                $(self.options.preorderType).on('change', function () {
                    self.getPercent($(this).val());
                });

                $(self.options.action).on('change', function () {
                    self.getProduct($(this).val());
                });
            },
            getPercent: function (changeValue) {
                var self = this;
                $('.mppreorder-percent #preorder_percent').removeAttr('data-validate');
                $(self.options.percent).hide();
                $(self.options.percent).val('');

                if (changeValue == 1) {
                    $('.mppreorder-percent #preorder_percent').removeAttr('data-validate');
                    $('.mppreorder-percent #preorder_percent').attr('data-validate','{required:true}');
                    $(self.options.percent).show();
                }
            },
            getProduct: function (changeValue) {
                $('.mppreorder-allproducts #disable_products').removeAttr('data-validate');
                $('.mppreorder-few #few_products').removeAttr('data-validate');
                $('.mppreorder-few').hide();
                $('.mppreorder-allproducts').hide();
                if (changeValue == 2) {
                    $('.mppreorder-allproducts #disable_products').removeAttr('data-validate');
                    $('.mppreorder-few #few_products').removeAttr('data-validate');
                    $('.mppreorder-few #few_products').attr('data-validate','{required:true}');
                    $('.mppreorder-few').show();
                    $('.mppreorder-allproducts').hide();
                } else if (changeValue == 3) {
                    $('.mppreorder-allproducts #disable_products').removeAttr('data-validate');
                    $('.mppreorder-few #few_products').removeAttr('data-validate');
                    $('.mppreorder-allproducts #disable_products').attr('data-validate','{required:true}');
                    $('.mppreorder-few').hide();
                    $('.mppreorder-allproducts').show();
                }
            },
        });
        return $.mage.preorderConfig;
    });