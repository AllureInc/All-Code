define([
    'jquery',
    'ko',
    'Scommerce_CookiePopup/js/widget/choice',
    'uiComponent',
    'underscore'
], function ($, ko, scommerceCookiePopupChoice, Component, _) {
    'use strict';

    return Component.extend({
        defaults: {
            element: null,
            setting: null
        },

        numerator: 1,

        choicesData: ko.observableArray([]),
        customerChoicesData: ko.observableArray([]),

        initialize: function () {
            this._super();

            this.getChoicesAjax();

            // Just hack for loading once
            if (window.jsCookiepopupPreference) {
                return;
            }
            window.jsCookiepopupPreference = true;

            var self = this;

            $(document).on('click', this.element, function(e) {
                e.preventDefault();
                var $target = $('#cookiePopupWrapper');
                $target.scommerceCookiePopupChoice(self.getModalConfig());
                $target.scommerceCookiePopupChoice('openModal');
            });

            $(document).on("click", ".popup-tab-link", function() {
                var tabId = $(this).attr('href');
                $(this).closest('ul').find('li').removeClass('active');
                $(this).closest('li').addClass('active');
                $('#cookiePopupWrapper .tab-item').removeClass('active');
                $(tabId).addClass('active');
                return false;
            });

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'canAdd',
                'canAddAll'
            ]);

            this.canAdd(true);
            this.canAddAll(false);

            return this;
        },

        // TODO Нужно пробросить стили в хедер и футер модалки.
        // TODO См Magento_Ui/js/modal/modal
        // TODO Шаблон Magento_Ui/[web]/[templates]/modal/modal-popup.html
        getModalConfig() {
            var config = this.setting.modal;
            this.checkAddAll();
            var active = jQuery('#cookiePopupWrapper .tab-header li.active');
            if (active.length == 0) {
                var tab = jQuery('#cookiePopupWrapper .tab-header li:first');
                if (tab.length > 0) {
                    tab.addClass('first').addClass('active');
                    jQuery('#cookiePopupWrapper ' + tab.find('a').attr('href')).addClass('active')
                }
            }
            return {
                title: config.title,
                actions: {

                    /**
                     * Callback always - called on all actions.
                     */
                    always: function () {},

                    /**
                     * Callback confirm.
                     */
                    confirm: function () {
                        //alert('CONFIRMED');
                    },

                    /**
                     * Callback cancel.
                     */
                    cancel: function () {}
                },
                buttons: [{
                    text: $.mage.__('Cancel'),
                    class: 'action-secondary action-dismiss',

                    /**
                     * Click handler.
                     */
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: config.button_text,
                    class: 'action-primary action-accept',

                    /**
                     * Click handler.
                     */
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }]
            }
        },

        add: function() {
            jQuery('#cookiePopupForm').submit();
            return false;
        },

        addAll: function() {
            jQuery('#cookiePopupForm input[name=save_all]').val('1');
            jQuery('#cookiePopupForm').submit();
            return false;
        },

        getChoices: function() {
            //return _.toArray(this.choicesData());
            //console.log(_.toArray(this.setting.choices));
            //return _.toArray(this.setting.choices);
        },

        getChoicesAjax: function() {
            var self = this;
            $.ajax({
                showLoader: false,
                url: self.setting.getChoicesUrl,
                //data: param,
                type: "POST",
                dataType: 'json'
            }).done(function (data) {
                if (data) {
                    var newData = [];
                    for(var i in data.choices) {
                        newData.push(data.choices[i]);
                    }
                    self.choicesData(newData);
                }
            }).fail(function() {
                console.log('failed');
            });
        },

        tabClick: function() {
            return false;
        },

        getNumber: function(data) {
            var result = this.numerator;
            this.numerator++;
            return result;
        },

        getChoiceName: function(data) {
            if (this.setting.modal.numberTabs == false) {
                if (data === false) {
                    return '';
                }
                return data.choice_name;
            }
            if (data === false) {
                return this.getNumber(1);
            }
            return this.getNumber(1) + ' ' + data.choice_name;
        },

        checkAddAll: function() {
            var allChecks = jQuery('.cookie-choice-check').length;
            var checkedCount = jQuery('.cookie-choice-check:checked').length;
            if (allChecks > checkedCount) {
                this.canAddAll(true);
            } else {
                this.canAddAll(false);
            }
        },

        checkClick: function(parent) {
            parent.checkAddAll();
            return true;
        },

        isCheckVisible: function(parent, data) {
            return data.required == 0 ||
                (data.required == 1 && !parent.setting.modal.hasRequiredText);
        },
    });
});
