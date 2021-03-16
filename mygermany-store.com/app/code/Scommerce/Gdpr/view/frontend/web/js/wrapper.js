define([
    'uiComponent',
    'jquery',
    'mage/translate',
    'mage/cookies',
    'ko'
], function(Component, $, $t, cookie, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            template: "Scommerce_Gdpr/wrapper",
            wrapperClass: 'scommerce-gdpr-disabled',
            bottomClass: 'scommerce-gdpr-bottom-position',
            topClass: 'scommerce-gdpr-top-position'
        },

        wrapper: null,

        hasAllChoices: ko.observable(true),

        getCookieTextMessage: function() {
            return this.cookieTextMessage;
        },

        initialize: function() {
            this._super();

            this.wrapper = $(this.cssPageWrapperClass);

            if (this.isPopupEnabled) {
                this.checkAllChoices();
            }
        },

        checkAllChoices: function() {
            var self = this;
            $.ajax({
                showLoader: false,
                url: self.getChoicesUrl,
                type: "POST",
                dataType: 'json'
            }).done(function (data) {
                if (data) {
                    var checkedCount = 0;
                    var allCount = 0;
                    for (var i in data.choices) {
                        allCount++;
                        if (data.choices[i].checked == true) {
                            checkedCount++;
                        }
                    }
                    self.hasAllChoices(allCount == checkedCount);
                    self.renderBlock();
                }
            }).fail(function() {
                console.log('failed');
            });
        },

        renderDone: function() {
            if (!this.isPopupEnabled) {
                this.renderBlock();
            }
        },

        renderBlock: function() {
            if ((!this.get(this.cookieClosedKey) && !this.isPopupEnabled) || (this.isPopupEnabled && !this.hasAllChoices())) {
                this.getBlock().show();
            }
            if (this.isBlocked
                && (
                     (!this.get(this.cookieKey) && !this.isPopupEnabled) || (this.isPopupEnabled && !this.hasAllChoices())
                   )
            ) {
                this.disable();
            }
            if (this.isBottom) {
                this.bottom();
            } else {
                this.top();
            }
        },

        getBlock: function() {
            return $('#js-cookienotice');
        },

        // Set cookie value
        set: function(name, value) {
            $.cookie.domain = window.location.hostname;
            $.cookie(name, value, {expires: new Date(new Date().getTime() + (100 * 365 * 24 * 60 * 60))});
        },

        // Get cookie value (just check to value is 1)
        get: function(name) {
            return $.cookie(name) == 1;
        },

        // Close gdpr block
        close: function() {
            this.set(this.cookieClosedKey, 1);
            this.getBlock().hide();
        },

        // Accept rules
        accept: function() {
            this.close();
            this.set(this.cookieKey, 1);
            if (this.isBlocked) {
                this.enable();
            }
            if (this.isPopupEnabled) {
                window.location = this.saveUrl;
            }
        },

        // Decline rules
        decline: function() {
            this.close();
            this.set(this.cookieKey, 0);
        },

        // Disable page wrapper ("close" access to site page)
        disable: function() {
            this.wrapper.addClass(this.wrapperClass);
        },

        // Enable page wrapper ("allow" access to site page)
        enable: function() {
            this.wrapper.removeClass(this.wrapperClass);
        },

        // "Move" message to bottom
        bottom: function() {
            this.getBlock().addClass(this.bottomClass);
        },

        // "Move" message to top
        top: function() {
            this.getBlock().addClass(this.topClass);
        }
    });
});