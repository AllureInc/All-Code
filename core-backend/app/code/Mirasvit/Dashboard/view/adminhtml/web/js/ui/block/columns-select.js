define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/ui-select'
], function ($, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            prevReport: null,
            columns: {},
            prevColumns: {}
        },

        /**
         * On report value change handler.
         * Change columns select options to report related columns.
         */
        onReportChange: function (report) {
            if (!this.prevReport) {
                this.prevReport = report;
            }

            if (this.prevReport !== report) {
                if (this.value().length) {
                    // persist previous report's columns
                    this.prevColumns[this.prevReport] = this.value();
                }

                // change cacheOptions columns to new report's columns
                this.cacheOptions.plain = this.columns[report];

                if (report in this.prevColumns) {
                    // restore previous report's columns
                    this.value(this.prevColumns[report]);
                } else {
                    this.value('');
                    this.error(false);
                }
            }

            if (report && report.length) {
                this.options(this.columns[report]);

                this.visible(true);
                this.forceVisibility = false;
                this.prevReport = report;
            }
        }
    });
});
