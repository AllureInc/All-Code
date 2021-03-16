define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function ($, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            customName: '${ $.parentName }.${ $.index }_input'
        },
        /**
         * Change currently selected option
         *
         * @param {String} id
         */
        handleChanges: function (id) {
            if (id == 1) {
                this.disabled(false);
            } else {
                this.disabled(true);
            }
        }
    });
});