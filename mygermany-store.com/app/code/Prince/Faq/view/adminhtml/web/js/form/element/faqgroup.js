define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/multiselect',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, multiselect, modal) {
    'use strict';
    return multiselect.extend({

        initialize: function () {
            this._super();
            this.optionsValuesCustom = this.indexedOptions;
            window.FAQCategories = this.optionsValuesCustom;
        },
    });
});