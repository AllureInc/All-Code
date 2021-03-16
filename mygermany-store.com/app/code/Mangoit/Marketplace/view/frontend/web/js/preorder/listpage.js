/*Overrided JS from Webkul_MarketplacePreorder */
define([
"jquery",
"jquery/ui",
], function ($) {
    'use strict';
    $.widget('mage.listpage', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var preorderInfo = self.options.preorderInfo;
                var count = 0;
                var isPreorder = 0;
                var preOrderLabel = "Pre Order";
                    $(".products ol.product-items > li.product-item").each(function () {
                        var productLink = $(this).find(".product-item-link").attr("href");                        
                        console.log(preorderInfo[productLink]);
                        if (typeof(preorderInfo[productLink]) != 'undefined' ) {
                            if (preorderInfo[productLink]['preorder'] == 1) {
                                setPreOrderLabel($(this));
                            }                            
                        }
                    });

                    $('.action.tocart').click(function () {
                        var url = $(this).parents(".product-item-info").find(".product-item-link").attr("href");
                        isPreorder = preorderInfo[url]['preorder'];
                        count = 0;
                    });
                    $('.action.tocart span').bind("DOMSubtreeModified",function () {
                        var title = $(this).text();
                        if (isPreorder == 1) {
                            if (title == "Add to Cart") {
                                count++;
                                if (count == 1) {
                                    $(this).parent().attr("title",preOrderLabel);
                                    $(this).text(preOrderLabel);
                                }
                            }
                        }
                    });
                    function setPreOrderLabel(currentObject)
                    {
                        currentObject.find(".action.tocart.primary").attr("title",preOrderLabel);
                        currentObject.find(".action.tocart.primary").find("span").text(preOrderLabel);
                    }
            });
        }
    });
    return $.mage.listpage;
});