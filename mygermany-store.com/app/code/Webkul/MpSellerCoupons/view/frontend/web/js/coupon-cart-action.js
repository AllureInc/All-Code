/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCoupons
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

/*jshint jquery:true*/
define(
    [
    'jquery',
    'mage/url'
    ],
    function ($, url) {
        'use strict';
        $.widget(
            'mage.couponCartAction',
            {
                options : {
                    sellerCoupons : '#seller_coupon',
                    selectCoupon  : '#select_coupon',
                    cancelCoupon  : '.cancel_coupon',
                    removeCoupon  : '#remove-coupon',
                    cancelForm    : '#cancel-coupon-form',
                    blockDiscount : '#block-discount'
                },
                _create: function () {
                    var self = this;
                    var flag = self.options.flag;
                    if (flag == 1) {
                        $(self.options.blockDiscount).hide();
                    }
                    $(self.options.selectCoupon).on('change',function () {
                        if ($(this).val()!='') {
                            $(self.options.sellerCoupons).parents(".coupon_entry_field").css({"display":"block"});
                        } else {
                            $(self.options.sellerCoupons).parents(".coupon_entry_field").css({"display":"none"});
                        }
                    });

                    $(document).ready(function () {
                        var path = window.location.href;
                        var index = path.indexOf('#payment') + path.indexOf('cart');
                        if (index < 0) {
                            document.getElementById('wk-bodymain').style.display="none";
                        }
                        if (path.indexOf('#payment') > 0) {
                            document.getElementById('wk-bodymain').style.display="block";
                            document.getElementById('wk-bodymain').style.width="70%";
                        }
                    })

                    $(document).click(function () {
                        setTimeout(function () {
                            var path = window.location.href;
                            var index = path.indexOf('#payment') + path.indexOf('cart');
                            if (index < 0) {
                                document.getElementById('wk-bodymain').style.display="none";
                            }
                            if (path.indexOf('#payment') > 0) {
                                document.getElementById('wk-bodymain').style.display="block";
                                document.getElementById('wk-bodymain').style.width="70%";
                            }
                        }, 1000);
                    })
                    $(window).on('popstate', function (event) {
                        setTimeout(function () {
                            var path = window.location.href;
                            var index = path.indexOf('#payment') + path.indexOf('cart');
                            if (index < 0) {
                                document.getElementById('wk-bodymain').style.display="none";
                            }
                            if (path.indexOf('#payment') > 0) {
                                document.getElementById('wk-bodymain').style.display="block";
                                document.getElementById('wk-bodymain').style.width="70%";
                            }
                        }, 1000);
                    })

                    $('#apply_coupon').click(function ( event ) {
                        var sellerId = $('#select_coupon').val();
                        if (!sellerId) {
                            $('#select_coupon').css("border", "1px solid red");
                            return;
                        } else {
                            $('#select_coupon').css("border", "1px solid #cccccc")
                        }
                        var couponCode = $('#seller_coupon').val();
                        if (!couponCode) {
                            $('#seller_coupon').css("border", "1px solid red");
                            return;
                        } else {
                            $('#select_coupon').css("border", "1px solid #cccccc")
                        }
                        var data = {
                            'seller_id' : sellerId,
                            'coupon_code' : couponCode
                        };
                        $.ajax({
                            url : url.build('mpsellercoupons/cart/applycoupon'),
                            data : data,
                            method: "post",
                            showLoader: true,
                            success :function (result) {
                                var message = result.message;
                                var status = result.status;
                                if (!status) {
                                    $('#wkSellerCouponErrorMessage').text(message);
                                    $('#wkSellerCouponSuccessMessageContainer').hide();
                                    $('#wkSellerCouponErrorMessageContainer').show();
                                } else {
                                    window.location.reload();
                                }
                            }
                        })
                    })

                    $('.cancel_coupon').click(function (event) {
                        var sellerId = this.id;
                        $.ajax({
                            url : url.build('mpsellercoupons/cart/cancelcoupon'),
                            data : {remove: sellerId},
                            method: "post",
                            showLoader: true,
                            success :function (result) {
                                var status = result.status;
                                var message = result.message;
                                if (!status) {
                                    $('#wkSellerCouponErrorMessage').text(message);
                                    $('#wkSellerCouponSuccessMessageContainer').hide();
                                    $('#wkSellerCouponErrorMessageContainer').show();
                                } else {
                                    window.location.reload();
                                }
                            }
                        })
                    })

                }
            }
        );
        return $.mage.couponCartAction;
    }
);