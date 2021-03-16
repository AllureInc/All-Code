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
"ko",
"jquery/ui",
"mage/translate"
], function ($, ko, $t) {
    'use strict';
    $.widget('mage.pageview', {
        options: {
            addToCartButtonLabel: '',
            stockLabel: '',
            preOrderLabel: $.mage.__("Pre Order"),
            preorderData : window.preorderData
        },
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var url = self.options.url;
                var pay = self.options.payHtml;
                var message = self.options.msg;
                var flag = self.options.flag;
                self.options.addToCartButtonLabel = $("#product-addtocart-button span").text();
                self.options.stockLabel = $(".product-info-stock-sku .stock").text();
                var payHtml = pay;
                var msg = message;
                msg = msg.replace(/\n/g, "<br />");
                var count = 0;
                var isPreorder = flag;
                if (isPreorder == 1) {
                    self.setPreOrderLabel();
                    $(".product-info-price").after(msg);
                    $(".product-info-price").after(payHtml);
                }
                if (self.options.config == 1) {
                    $("#product-options-wrapper").after(self.options.configmsg);
                };
                $('#product-addtocart-button').click(function () {
                    count = 0;
                });
                $('#product-addtocart-button span').bind("DOMSubtreeModified",function () {
                    var title = $(this).text();
                    if (isPreorder == 1) {
                        if (title == self.options.addToCartButtonLabel) {
                            count++;
                            if (count == 1) {
                                //setPreOrderLabel();
                            }
                        }
                    }
                });
                $('#product-options-wrapper .super-attribute-select').change(function () {
                    var flag = 1;
                    setTimeout(function () {
                        $("#product_addtocart_form input[type='hidden']").each(function () {
                            $('#product-options-wrapper .super-attribute-select').each(function () {
                                if ($(this).val() == "") {
                                    flag = 0;
                                }
                            });
                            var name = $(this).attr("name");
                            if (name == "selected_configurable_option") {
                                self.setDefaultLabel();
                                isPreorder = 0;
                                $(".wk-msg-box").remove();
                                var productId = $(this).val();
                                $.each(self.options.preorderData, function (i, v) {
                                    if (v.id != 'undefined' && productId == v.id) {
                                        if (v.preorder == 1) {
                                            $(".wk-msg-box").remove();
                                            self.setPreOrderLabel();
                                            isPreorder = 1;
                                            $(".product-info-price").after(v.msg);
                                            $(".product-info-price").after(v.payHtml);
                                        } else {
                                            self.setDefaultLabel();
                                            isPreorder = 0;
                                            $(".wk-msg-box").remove();
                                        }
                                    }
                                });
                            }
                        });
                    }, 0);
                });
            });
        },
        setPreOrderLabel: function () {
            var self = this;
            $("#product-addtocart-button span").text(self.options.preOrderLabel);
            $("#product-addtocart-button").attr("title",self.options.preOrderLabel);
            $(".product-info-stock-sku .stock").text(self.options.preOrderLabel);
        },
        setDefaultLabel: function () {
            var self = this;
            $("#product-addtocart-button span").text(self.options.addToCartButtonLabel);
            $("#product-addtocart-button").attr("title",self.options.addToCartButtonLabel);
            $(".product-info-stock-sku .stock").text(self.options.stockLabel);
        }
    });
    return $.mage.pageview;
});