/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'jquery/ui',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function ($, _) {
    'use strict';

    $.widget('mage.alert', $.mage.confirm, {
        options: {
            modalClass: 'confirm',
            title: window.attention_title,
            actions: {

                /**
                 * Callback always - called on all actions.
                 */
                always: function () {}
            },
            buttons: [{
                text: window.ok_title,
                class: 'action-primary action-accept',

                /**
                 * Click handler.
                 */
                click: function () {
                    this.closeModal(true);
                }
            }]
        },

        /**
         * Close modal window.
         */
        closeModal: function () {
            this.options.actions.always();
            this.element.bind('alertclosed', _.bind(this._remove, this));

            return this._super();
        }
    });

    return function (config) {
        return $('<div></div>').html(config.content).alert(config);
    };
});
