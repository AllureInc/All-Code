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
                mainMenu : '.product-attr-nav',
                subMenu : '.product-attr-child-menu',
                otherMenu : '.item'
            },
            _create: function () {
                var self = this;
                
                $(self.options.mainMenu).on('mouseover', function () {                    
                    $(self.options.subMenu).show();
                });
                $(self.options.otherMenu).on('mouseleave', function () {
                    $(self.options.subMenu).hide();
                });
                $(self.options.mainMenu).on('mouseleave', function () {
                    $(self.options.subMenu).hide();
                });

            },
        });
        return $.mage.preorderLink;
    });
