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
        $.widget('mage.preorderLink', {
            options: {
                mainMenu : '.payment-overview-nav',
                subMenu : '.payment-overview-child-menu',
                marketingMainMenu : '.marketing-nav',
                marketingSubMenu : '.marketing-child-menu',
                amazonMainMenu : '.amazon-nav',
                amazonSubMenu : '.amazon-child-menu',
                rakutenMainMenu : '.rakuten-nav',
                rakutenSubMenu : '.rakuten-child-menu',
                otherMenu : '.item'
            },
            _create: function () {
                var self = this;
                
                //Payment menus
                $(self.options.mainMenu).on('mouseover', function () {                    
                    $(self.options.subMenu).show();
                });
                $(self.options.otherMenu).on('mouseleave', function () {
                    $(self.options.subMenu).hide();
                });
                $(self.options.mainMenu).on('mouseleave', function () {
                    $(self.options.subMenu).hide();
                });
                //Marketing menus
                $(self.options.marketingMainMenu).on('mouseover', function () {                    
                    $(self.options.marketingSubMenu).show();
                });
                $(self.options.otherMenu).on('mouseleave', function () {
                    $(self.options.marketingSubMenu).hide();
                });
                $(self.options.marketingMainMenu).on('mouseleave', function () {
                    $(self.options.marketingSubMenu).hide();
                });
                
                //Amazon
                $(self.options.amazonMainMenu).on('mouseover', function () {                    
                    $(self.options.amazonSubMenu).show();
                });
                $(self.options.otherMenu).on('mouseleave', function () {
                    $(self.options.amazonSubMenu).hide();
                });
                $(self.options.amazonMainMenu).on('mouseleave', function () {
                    $(self.options.amazonSubMenu).hide();
                });

                //Rakuten
                $(self.options.rakutenMainMenu).on('mouseover', function () {                    
                    $(self.options.rakutenSubMenu).show();
                });
                $(self.options.otherMenu).on('mouseleave', function () {
                    $(self.options.rakutenSubMenu).hide();
                });
                $(self.options.rakutenMainMenu).on('mouseleave', function () {
                    $(self.options.rakutenSubMenu).hide();
                });

            },
        });
        return $.mage.preorderLink;
    });
