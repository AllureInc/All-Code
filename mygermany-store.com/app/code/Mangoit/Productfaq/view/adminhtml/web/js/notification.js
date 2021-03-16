/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/template',
    'uiComponent',
    'ko',
    ], function ($, mageTemplate, Component, ko) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
                var self = this;
                this.notifyFaqTmp = mageTemplate('#wk_faq_notification_template');
                this.notifysellerProfTmp = mageTemplate('#wk_sellser_profile_notification_template');
                this.faqData = window.notificationConfig.faqNotification;
                this.sellerProfileNotification = window.notificationConfig.sellerProfileNotification;
                console.log(this.sellerProfileNotification);
                if (this.faqData.length > 0) {
                    this._showFaqNotification(this.faqData);
                }
                if (this.sellerProfileNotification.length > 0) {
                    this._sellerProfileNotification(this.sellerProfileNotification);
                }
                $('.wk-notifications-action.marketplace-dropdown').on('click', function (event) {
                    event.preventDefault();
                    self._showFaqNotificationBox($(this));
                });
                $('body').on('click', function (event) {
                    self._faqCloseNotifyWindow(event);
                });

                $('.wk-notifications-action.marketplace-dropdown').on('click', function (event) {
                    event.preventDefault();
                    self._showsellerProfileNotificationBox($(this));
                });
                $('body').on('click', function (event) {
                    self._sellerProfileCloseNotifyWindow(event);
                });
            },
            _showFaqNotification: function (faqData) {
                $('#menu-ced-productfaq-view .item-management').css('position','relative');
                var data = {},
                    notifyFaqTmp;

                data.notificationCount = faqData.length;
                data.notificationImage = window.notificationConfig.image;
                data.notifications = faqData;
                data.notificationType = 'seller';
                notifyFaqTmp = this.notifyFaqTmp({
                    data: data
                });
                $(notifyFaqTmp)
                .appendTo($('#menu-ced-productfaq-view .item-management'));
            },
            _showFaqNotificationBox: function (element) {
                
                if ($(element).parent('.faq-notification-block').length) {
                    if ($(element).hasClass('active')) {
                        $(element).removeClass('active');
                        $(element).parent('.faq-notification-block').removeClass('active');
                        $(element).next('.faq-menu').hide();
                    } else {
                        $('.faq-menu').hide();
                        $('.wk-notifications-action.marketplace-dropdown').removeClass('active');
                        $('.faq-notification-block').removeClass('active');
                        $(element).addClass('active');
                        $(element).parent('.faq-notification-block').addClass('active');
                        $(element).next('.faq-menu').show();
                    }
                }
            },
            _faqCloseNotifyWindow: function (event) {
                var className = event.target.className;
                if (className !== 'faq-notification-block' &&
                    className !== 'wk-notifications-action marketplace-dropdown' &&
                    className !== 'wk-notifications-action marketplace-dropdown' &&
                    className !== 'wk-notification-img' &&
                    className !== 'faq-menu' &&
                    className !== 'wk-notifications-action marketplace-dropdown active' &&
                    className !== 'wk-notification-count'
                ) {
                    $('.wk-notifications-action').removeClass('active');
                    $('.faq-notification-block').removeClass('active');
                    $('.faq-menu').hide();
                }
            },
            _sellerProfileNotification: function (sellerProfileData) {
                $('#menu-webkul-marketplace-marketplace .item-seller').css('position','relative');
                var data = {},
                    notifysellerProfTmp;

                data.notificationCount = sellerProfileData.length;
                data.notificationImage = window.notificationConfig.image;
                console.log(sellerProfileData);
                data.notifications = sellerProfileData;
                data.notificationType = 'sellerprofile';
                notifysellerProfTmp = this.notifysellerProfTmp({
                    data: data
                });
                $(notifysellerProfTmp)
                .appendTo($('#menu-webkul-marketplace-marketplace .item-seller'));
            },
            _showsellerProfileNotificationBox: function (element) {
                
                if ($(element).parent('.seller-profile-notification-block').length) {
                    if ($(element).hasClass('active')) {
                        $(element).removeClass('active');
                        $(element).parent('.seller-profile-notification-block').removeClass('active');
                        $(element).next('.seller-profile-dropdown-menu').hide();
                    } else {
                        $('.seller-profile-dropdown-menu').hide();
                        $('.wk-notifications-action.marketplace-dropdown').removeClass('active');
                        $('.seller-profile-notification-block').removeClass('active');
                        $(element).addClass('active');
                        $(element).parent('.seller-profile-notification-block').addClass('active');
                        $(element).next('.seller-profile-dropdown-menu').show();
                    }
                }
            },
            _sellerProfileCloseNotifyWindow: function (event) {
                var className = event.target.className;
                if (className !== 'seller-profile-notification-block' &&
                    className !== 'wk-notifications-action marketplace-dropdown' &&
                    className !== 'wk-notifications-action marketplace-dropdown' &&
                    className !== 'wk-notification-img' &&
                    className !== 'seller-profile-dropdown-menu' &&
                    className !== 'wk-notifications-action marketplace-dropdown active' &&
                    className !== 'wk-notification-count'
                ) {
                    $('.wk-notifications-action').removeClass('active');
                    $('.seller-profile-notification-block').removeClass('active');
                    $('.seller-profile-dropdown-menu').hide();
                }
            }
        });
    });