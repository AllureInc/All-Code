/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpFavouriteSeller
 * @author      Webkul
 * @copyright   Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */

/*jshint jquery:true*/
define(
    [
    'jquery',
    'mage/translate',
    'mage/mage',
    'Magento_Ui/js/modal/alert',
    'mage/calendar',
    ],
    function ($,$t,mage,alert) {
        'use strict';
        var globalThis;
        $.widget(
            'mage.sellerCouponManager',
            {
                _create: function () {
                    globalThis = this;

                    $('.btn-coupon-generate').on('click',function (event) {
                        event.preventDefault();
                        $(this).attr('disabled','disabled');
                        $(this).parents('#form-generatecoupon-filter').submit();
                    });

                    $("#coupon_expiry").calendar({'dateFormat':'mm/dd/yy','minDate': new Date()});
                    $('#mass-delete-butn').click(
                        function (e) {
                            var flag =0;
                            $('.mpcheckbox').each(
                                function () {
                                    if (this.checked === true) {
                                        flag =1;
                                    }
                                }
                            );
                            if (flag === 0) {
                                alert({content : $.mage.__(" No Checkbox is checked ")});
                                return false;
                            } else {
                                /*var dicisionapp=confirm($t(" Are you sure you want to delete these coupons ? "));*/
                                var dicisionapp=confirm($.mage.__(" Are you sure you want to delete these coupons ? "));
                                if (dicisionapp === true) {
                                    $('#form-shopfollower-massdelete').submit();
                                } else {
                                    return false;
                                }
                            }
                        }
                    );

                    $('#mpselecctall').click(
                        function (event) {
                            if (this.checked) {
                                $('.mpcheckbox').each(
                                    function () {
                                        this.checked = true;
                                    }
                                );
                            } else {
                                $('.mpcheckbox').each(
                                    function () {
                                        this.checked = false;
                                    }
                                );
                            }
                        }
                    );

                    $('.mp-delete').click(
                        function () {
                            var dicisionapp=confirm($.mage.__(" Are you sure you want to delete this coupon ? "));
                            if (dicisionapp === true) {
                                var $url=$(this).attr('data-url');
                                window.location = $url;
                            }
                        }
                    );

                    $('#couponCodeToSearch').keyup(function (event) {
                        var yourInput = $('#couponCodeToSearch').val();
                        var re = /[`~!#$%^&*()|+\=?;:'",<>\{\}\[\]\\\/]/gi;
                        var isSplChar = re.test(yourInput);
                        if (isSplChar) {
                            var no_spl_char = yourInput.replace(/[`~!#$%^&*()|+\-=?;:'",<>\{\}\[\]\\\/]/gi, '');
                            $(this).val(no_spl_char);
                        }

                    });

                    $('#couponCodeToCreate').keyup(function (event) {
                        var yourInput = $('#couponCodeToCreate').val();
                        var re = /[`~!#$%^&*()|+\=?;:'",<>\{\}\[\]\\\/]/gi;
                        var isSplChar = re.test(yourInput);
                        if (isSplChar) {
                            var no_spl_char = yourInput.replace(/[`~!#$%^&*()|+\-=?;:'",<>\{\}\[\]\\\/]/gi, '');
                            $(this).val(no_spl_char);
                        }

                    });
                }
            }
        );
        return $.mage.sellerCouponManager;
    }
);