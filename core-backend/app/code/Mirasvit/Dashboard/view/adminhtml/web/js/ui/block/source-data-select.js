define([
    'Magento_Ui/js/form/element/ui-select'
], function (Select) {
    'use strict';

    return Select.extend({
        /**
         * Toggle activity list element
         *
         * @param {Object} data - selected option data
         * @returns {Object} Chainable
         */
        toggleOptionSelected: function(data) {
            if (data.hasOwnProperty('optgroup') || !data.path) {
                return this;
            }

            return this._super();
        }
    });
});