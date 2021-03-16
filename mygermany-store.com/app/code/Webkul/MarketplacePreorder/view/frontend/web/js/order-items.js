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
                var option1 = self.options.option;
                var infoo = self.options.info;
                var preorderUrl = self.options.orderurl;
                var flags = self.options.flag;
                var orderid = self.options.orderid;
                var incrementid = self.options.incrementId;
                var preorderCompleteProductId = self.options.preorderCompleteProductId;
                var url = self.options.url;
                var formKey = "";
                var options = $.parseJSON(option1);
                var flag = flags;
                var sellerId = self.options.sellerid;
                $("body input[type='hidden']").each(function () {
                    var name = $(this).attr("name");
                    if (name == "form_key") {
                        formKey = $(this).val();
                    }
                });
                if (flag == 1) {
                    var msgBox = $('<div/>').addClass("wk-msg-box wk-info").text($.mage.__("This order contains Preorder Product(s)."));
                    $(".page-title-wrapper").append(msgBox);
                }
                if(preorderUrl){
                     var msgBox = $('<div/>').addClass("wk-msg-box wk-info").append($('<a/>').attr('href',preorderUrl).text($.mage.__("Check Order Reference.")));
                    $(".page-title-wrapper").append(msgBox);
                }
                var count = 0;
                var info = infoo;
                $("#my-orders-table tbody").each(function () {
                    if (info[count]['available'] == 1 && info[count]['preorder'] == 1) {
                        $(this).find("tr td:last-child").append('<button class="wk-preorder-complete action tocart primary" data-key="'+count+'" title="Complete Preorder" type="submit"><span>'+$.mage.__("Complete Preorder")+'</span></button>');
                    }
                    if (info[count]['preorder'] == 1) {
                        $(this).find("tr td:first-child").append("<span class='order-status'><strong>"+$.mage.__("Preorder Pending")+"</strong></span>");
                    }
                    if (info[count]['preorder'] == 2) {
                        $(this).find("tr td:first-child").append("<span class='order-status'><strong>"+$.mage.__("Preorder Complete")+"</strong></span>");
                    }
                    count++;
                });
                $(document).on('click', '.wk-preorder-complete', function (event) {
                    var option = {};
                    var orderId = orderid;
                    var incrementId = ''+incrementid+'';
                    var key = $(this).attr("data-key");
                    var productId = info[key]['product_id'];
                    var itemId = info[key]['item_id'];
                    var qty = info[key]['qty'];
                    var name = info[key]['product_name'];
                    $.each(options, function (k, v) {
                        var optionId = v.id;
                        var optionTitle = v.title;
                        if (optionTitle == 'Product Name') {
                            option[optionId] = name;
                        }
                        if (optionTitle == 'Order Refernce') {
                            option[optionId] = incrementId;
                        }
                    });
                    $.ajax({
                        url: ''+url+'',
                        type: 'POST',
                        showLoader: true,
                        data: { pro_id:productId, form_key:formKey, options:option, seller_id:sellerId, order_id:orderId, item_id : itemId, product : preorderCompleteProductId, qty:qty },
                        // dataType: 'json',
                        success: function (data) {
                            
                        }
                    });
                });
            });
        }
    });
    return $.mage.orderItems;
});
