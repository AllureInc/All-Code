define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function ($, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            columns: {}
        },

        /**
         * On changing a report or a metric's column change available for filter columns.
         */
        changeOptions: function (report) {
            if (!report || !report.length) {
                return;
            }

            var id = report.split('|').shift();
            var option = this.columns.find(function(option) {
                return option.reports.indexOf(id) !== -1;
            });

            this.setOptions(option.columns);
        }
    });
});
