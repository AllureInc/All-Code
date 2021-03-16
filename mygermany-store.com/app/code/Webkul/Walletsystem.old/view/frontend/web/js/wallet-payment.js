define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'Magento_Checkout/js/checkout-data'
    ], function ($, Component, ko, checkoutData) {
        'use strict';
        var walletconfig = window.walletconfig.walletsystem;
        var leftamount = ko.observable(walletconfig.leftamount);
        var leftinWallet= ko.observable(walletconfig.leftinwallet);
        var myValue = ko.observable(walletconfig.walletstatus);
        var getUpdatedGrandTotal = ko.observable(walletconfig.grand_total);
        var updatebyflag = false;
        return Component.extend({
            defaults: {
                template: 'Webkul_Walletsystem/wallet-payment',
                currecysymbol:walletconfig.currencysymbol,
                myValue: myValue,
                updatebyflag:updatebyflag,
                leftinWallet: leftinWallet,
                leftamount: leftamount,
                getUpdatedGrandTotal:getUpdatedGrandTotal,
                walletformatamount: walletconfig.walletformatamount
            },
            initialize: function () {
                this._super();
                var mainthis = this;
                $(document).ready(function() {
                    $('.item-title:has(input[type="radio"][value="walletsystem"][name="payment[method]"])').hide();
                });
                $("body").delegate(".payment-method .radio", "click", function () {
                    mainthis.changeWalletPayment();
                });
                this.setwalletamount();
            },
            getWalletamount: function () {
                return walletconfig.walletamount;
            },
            getCode: function () {
                return 'walletsystem';
            },
            getGrandTotal:function () {
                return walletconfig.grand_total;
            },
            changeWalletPayment:function () {
                if (this.myValue()) {
                    if (this.leftamount()==0) {
                        this.myValue(false);
                        this.updatebyflag = true;
                        this.setwalletamount();
                    }
                }
            },
            setwalletamount:function () {
                var paymentmethod = this;
                var restamount = 0;
                var type;
                if (this.myValue()) {
                    type = 'set';
                } else {
                    type = 'reset';
                }
                $('body').trigger('processStart');
                var ajaxreturn = $.ajax({
                    url:this.getajaxUrl(),
                    type:"POST",
                    dataType:'json',
                    data:{wallet:type,grandtotal:paymentmethod.getGrandTotal()},
                    success:function (content) {
                        getUpdatedGrandTotal(content.grand_total);
                        restamount = content.grand_total - content.amount;
                        var finalRestAmount = restamount.toFixed(2);
                        leftinWallet(content.leftinWallet);
                        leftamount(finalRestAmount);
                        if (finalRestAmount<=0) {
                            var walletpayment = $('body').find('input[type="radio"][name="payment[method]"][value="walletsystem"]');
                            $(walletpayment).prop('checked',true).trigger('click');
                            checkoutData.setSelectedPaymentMethod('walletsystem');
                        } else {
                            if (!paymentmethod.updatebyflag) {
                                var walletpayment = $('body').find('input[type="radio"][name="payment[method]"][value="walletsystem"]');
                                $(walletpayment).prop('checked',false);
                                checkoutData.setSelectedPaymentMethod(null);
                            }
                        }
                        paymentmethod.updatebyflag = false;
                        $('body').trigger('processStop');
                        return true;
                    }
                });
                if (ajaxreturn) {
                    return true;
                }
            },
            getajaxUrl: function () {
                return walletconfig.ajaxurl;
            }
        });
    }
);
