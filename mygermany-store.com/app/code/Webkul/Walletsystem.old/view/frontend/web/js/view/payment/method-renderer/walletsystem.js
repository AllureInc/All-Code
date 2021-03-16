/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Checkout/js/model/cart/cache'
    ],
    function (ko, $, Component, quote, selectPaymentMethodAction, checkoutData, additionalValidators, totals, defaultTotal, cartCache) {
        'use strict';
        var walletconfig = window.checkoutConfig.payment.walletsystem;
        var leftamount = ko.observable(walletconfig.leftamount);
        var leftinWallet= ko.observable(walletconfig.leftinwallet);
        var myValue = ko.observable(walletconfig.walletstatus);
        var getUpdatedGrandTotal = ko.observable(0);
        var updatebyflag = false;
        return Component.extend({
            defaults: {
                template: 'Webkul_Walletsystem/payment/walletsystem',
                currecysymbol:walletconfig.currencysymbol,
                myValue: myValue,
                updatebyflag:updatebyflag,
                leftinWallet: leftinWallet,
                leftamount: leftamount,
                getUpdatedGrandTotal:getUpdatedGrandTotal,
                walletformatamount: walletconfig.walletformatamount
            },
            totals: quote.getTotals(),
            initialize: function () {
                this._super();
                var mainthis = this;
                this.setwalletamount();
                $("body").delegate(".payment-method .radio", "click", function () {
                    mainthis.changeWalletPayment();
                });
                quote.totals.subscribe(function(newvalue){
                    var walletAmount = 0;
                    $(newvalue.total_segments).each(function(key, value){
                        if (value.code=='wallet_amount') {
                            walletAmount = value.value;
                        }                    
                    });
                    if (walletAmount ==0 && mainthis.myValue()) {
                        mainthis.myValue(false);
                        mainthis.updatebyflag = false;
                        mainthis.setwalletamount();
                    }                  
                });
            },
            getGrandTotal:function () {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('grand_total').value;
                }
                return price;
            },
            getLoaderImage: function () {
                return walletconfig.loaderimage;
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
            getWalletamount: function () {
                return walletconfig.walletamount;
            },
            getajaxUrl: function () {
                return walletconfig.ajaxurl;
            },
            getData: function () {
                return {
                    "method": this.myValue()?'walletsystem':null,
                    "po_number": null,
                    "additional_data": null
                };
            },
            getPaymentData: function () {
                return {
                    "method": null,
                    "po_number": null,
                    "additional_data": null
                };
            },
            getCode: function () {
                return 'walletsystem';
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
                        cartCache.set('totals',null);
                        defaultTotal.estimateTotals();
                        getUpdatedGrandTotal(content.grand_total);
                        restamount = content.grand_total - content.amount;
                        var finalRestAmount = restamount.toFixed(2);
                        leftinWallet(content.leftinWallet);
                        leftamount(finalRestAmount);
                        if (finalRestAmount<=0) {
                            console.log("okaay");
                            selectPaymentMethodAction(paymentmethod.getData());
                            checkoutData.setSelectedPaymentMethod('walletsystem');
                        } else {
                            if (!paymentmethod.updatebyflag) {
                                selectPaymentMethodAction(paymentmethod.getPaymentData());
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
            }
        });
    }
);