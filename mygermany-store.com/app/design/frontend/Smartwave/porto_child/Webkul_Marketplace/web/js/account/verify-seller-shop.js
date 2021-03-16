/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($, $t, mageTemplate, alert) {
    'use strict';
    console.log('overrided-js');
    $.widget('mage.verifySellerShop', {
        options: {
            backUrl: '',
            shopUrl: '[data-role="shop-url"]',
            becomeSellerBoxWrapper: '[data-role="wk-mp-become-seller-box-wrapper"]',
            available: '.available',
            unavailable: '.unavailable',
            emailAddress: '#email_address',
            wantSellerDiv: '#wantptr',
            wantSellerTemplate: '#wantptr-template',
            profileurlClass: '.profileurl',
            wantpartnerClass: '.wantpartner',
            pageLoader: '#wk-load',
            shopTitle: shopTitle,
            shopText: shopText,
            websiteUrlClass: '.website_url',
            shopLabel: shopLabel,
            websiteTitle: websiteTitle,
            websiteText: websiteText,
            websitePlaceholder: websitePlaceholder,


            contactNumberClass: '.contact_number',
            ContactLabel: telephone,
            ContactTitle: telephone,
            ContactPlaceholder: telephone,

            addressClass: '.comp_address',
            addressLabel: company_address,
            addressTitle: company_address,
            addressPlaceholder: company_address,
            invoiceSettingsClass: '.invoice_settings' 
        },
        _create: function () {
            var self = this;
            $(self.options.emailAddress).parents('div.field').after($(self.options.wantSellerDiv));
            $(self.options.wantSellerDiv).show();
            $(self.options.wantpartnerClass).on('change', function () {
                self.callAppendShopBlockFunction(this.element, $(this).val());
            });
            $(this.element).delegate(self.options.shopUrl, 'keyup', function () {
                var shopUrlVal = $(this).val();
                $(self.options.shopUrl).val(shopUrlVal.replace(/[^a-z^A-Z^0-9\.\-]/g,''));
            });
            $(this.element).delegate(self.options.shopUrl, 'change', function () {
                self.callAjaxFunction();
            });
        },
        callAppendShopBlockFunction: function (parentelem, elem) {
            var self = this;
            if (elem==1) {
                $(self.options.pageLoader).parents(parentelem)
                .find('button.submit').addClass('disabled');
                var progressTmpl = mageTemplate(self.options.wantSellerTemplate),
                          tmpl;
                tmpl = progressTmpl({
                    data: {
                        label: self.options.shopLabel,
                        src: self.options.loaderImage,
                        title: self.options.shopTitle,
                        text: self.options.shopText,
                        websiteTitle: self.options.websiteTitle,
                        websitePlaceholder: self.options.websitePlaceholder,
                        websiteText: self.options.websiteText,

                        contactTitle: self.options.ContactTitle,
                        contactPlaceholder: self.options.ContactPlaceholder,

                        addressTitle: self.options.addressTitle,
                        addressPlaceholder: self.options.addressPlaceholder,

                        generateInvoiceLabel: self.options.generateInvoiceLabel
                    }
                });
                $(self.options.wantSellerDiv).after(tmpl);
            } else {
                $(self.options.pageLoader).parents(parentelem)
                .find('button.submit').removeClass('disabled');
                $(self.options.profileurlClass).remove();
                $(self.options.websiteUrlClass).remove();
                $(self.options.contactNumberClass).remove();
                $(self.options.addressClass).remove();
                $(self.options.invoiceSettingsClass).remove();
            }
        },
        callAjaxFunction: function () {
            var self = this;
            $(self.options.button).addClass('disabled');
            var shopUrlVal = $(self.options.shopUrl).val();
            $(self.options.available).remove();
            $(self.options.unavailable).remove();
            if (shopUrlVal) {
                $(self.options.pageLoader).removeClass('no-display');
                $.ajax({
                    type: "POST",
                    url: self.options.ajaxSaveUrl,
                    data: {
                        profileurl: shopUrlVal
                    },
                    success: function (response) {
                        $(self.options.pageLoader).addClass('no-display');
                        if (response===0) {
                            $(self.options.button).removeClass('disabled');
                            $(self.options.becomeSellerBoxWrapper).append(
                                $('<div/>').addClass('available message success')
                                .text(self.options.successMessage)
                            );
                            // var baseUrl = window.authenticationPopup.baseUrl+'marketplace/seller/profile/shop'+shopUrlVal;
                            // $(self.options.becomeSellerBoxWrapper).prepend(
                            //     $('<div/>').addClass('available message success')
                            //     .text(baseUrl)
                            // );
                        } else {
                            $(self.options.button).addClass('disabled');
                            $(self.options.shopUrl).val('');
                            $(self.options.becomeSellerBoxWrapper).append(
                                $('<div/>').addClass('available message error')
                                .text(self.options.errorMessage)
                            );
                        }
                    },
                    error: function (response) {
                        alert({
                            content: $t('There was error during verifying seller shop data')
                        });
                    }
                });
            }
        }
    });
    return $.mage.verifySellerShop;
});