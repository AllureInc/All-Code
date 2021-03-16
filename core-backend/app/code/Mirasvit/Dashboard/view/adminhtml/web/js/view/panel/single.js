define([
    'jquery',
    'underscore',
    './abstract',
    'mageUtils',
    'Mirasvit_Report/js/lib/chart'
], function ($, _, Panel, utils, Chart) {
    'use strict';

    return Panel.extend({
        panel:      null,
        gaugeChart: null,

        defaults: {
            template: 'Mirasvit_Dashboard/view/panel/single',

            panel: {
                gauge: {
                    isActive: '0',
                    minValue: 0,
                    maxValue: 100
                },

                sparkline: {
                    isActive: '0'
                }
            },

            tracks: {
                block: true,

                value:          true,
                formattedValue: true,

                cValue:          true,
                cFormattedValue: true,

                values: true,
                labels: true,

                cValues: true,
                cLabels: true
            }
        },

        setDefaults: function () {
            _.each(this.block.get('single'), function (value, key) {
                this.set('panel.' + key, value);
            }.bind(this));
        },

        render: function () {
            if (!this.$el) {
                return;
            }

            this.$el.height(this.$el.closest('li').height() - 30);

            this.onDataReceived();

            if (this.panel.gauge.isActive === '1') {
                this.renderGauge();
            }

            if (this.panel.sparkline.isActive === '1') {
                this.renderSparkLines();
            }
        },

        onDataReceived: function () {
            this.setDefaults();

            var value, formattedValue, cValue, cFormattedValue;
            var values, cValues = [];
            var labels, cLabels = [];
            var data = this.block.get('value');

            if (data && data.type === 'Mirasvit\\ReportApi\\Api\\ResponseInterface') {
                value = data.totals['data']['value'];
                formattedValue = data.totals['formatted_data']['value'];

                cValue = data.totals['data']['c|value'];
                cFormattedValue = data.totals['formatted_data']['c|value'];

                values = _.map(data.items, function (item) {
                    return item.data['value'];
                });
                labels = _.map(data.items, function (item) {
                    return item['formatted_data']['label'];
                });
                cValues = _.map(data.items, function (item) {
                    return ('c|value' in item.data) ? item.data['c|value'] : 0;
                });
                cLabels = _.map(data.items, function (item) {
                    return ('c|value' in item['formatted_data']) ? item['formatted_data']['c|label'] : 0;
                });

            } else {
                if (_.isArray(data) || _.isObject(data)) {
                    var row = _.first(data);
                    if (row && row['value']) {
                        value = row['value'];
                    } else {
                        value = _.first(_.values(row));
                    }
                } else {
                    value = data;
                }

                formattedValue = value;
            }

            this.set('value', value);
            this.set('formattedValue', formattedValue);
            this.set('cValue', cValue);
            this.set('cFormattedValue', cFormattedValue);
            this.set('values', values);
            this.set('labels', labels);
            this.set('cValues', cValues);
            this.set('cLabels', cLabels);
        },

        getValueText: function () {
            if (this.value === undefined || this.value === false) {
                return 'N/A';
            }

            return this.formattedValue;
        },

        getCValueText: function () {
            if (this.cValue === undefined || this.cValue === false) {
                return null;
            }

            return this.cFormattedValue;
        },

        getCValuePercent: function () {
            if (this.cValue === undefined || this.cValue === false) {
                return null;
            }

            return Math.ceil((this.value - this.cValue) / this.cValue * 100);
        },

        renderSparkLines: function () {
            if (this.chart) {
                this.chart.destroy();
            }

            var $chart = $('.spark-lines', this.$el);

            $chart
                .width(this.$el.width())
                .height(this.$el.height())
                .attr('width', this.$el.width())
                .attr('height', this.$el.height())
                .show();

            this.chart = new Chart($chart[0].getContext('2d'), {
                type:    'line',
                options: {
                    title:               {
                        display: false
                    },
                    responsive:          true,
                    maintainAspectRatio: false,
                    scales:              {
                        xAxes: [
                            {
                                display: false,
                                stacked: true
                            }
                        ],
                        yAxes: [
                            {
                                display: false,
                                ticks:   {
                                    beginAtZero: true
                                }
                            }
                        ]
                    },
                    tooltips:            {
                        mode:      'index',
                        intersect: false,
                        position:  'nearest'
                    }
                }
            });

            this.chart.data = {
                labels:   this.labels,
                datasets: [{
                    stack:           1,
                    backgroundColor: Chart.helpers.color('#97CC64').alpha(0.1).rgbString(),
                    borderColor:     [Chart.helpers.color('#97CC64').alpha(0.8).rgbString()],
                    borderWidth:     1,
                    pointRadius:     0,
                    data:            this.values
                }]
            };

            if (this.cValue) {
                this.chart.data.datasets.push({
                    stack:           0,
                    backgroundColor: Chart.helpers.color('#007bdb').alpha(0.1).rgbString(),
                    borderColor:     [Chart.helpers.color('#007bdb').alpha(0.8).rgbString()],
                    borderWidth:     1,
                    pointRadius:     0,
                    data:            this.cValues
                });
            }

            this.chart.update(0, true);
        },

        renderGauge: function () {
            if (this.gaugeChart) {
                this.gaugeChart.destroy();
            }

            var $gauge = $('.gauge', this.$el);

            $gauge
                .width(this.$el.width())
                .height(this.$el.height())
                .attr('width', this.$el.width())
                .attr('height', this.$el.height())
                .show();

            this.gaugeChart = new Chart($gauge[0].getContext('2d'), {
                type:    'doughnut',
                data:    {},
                options: {
                    responsive:       true,
                    cutoutPercentage: 70,
                    rotation:         0.9 * Math.PI,
                    circumference:    1.2 * Math.PI,
                    legend:           {
                        display: false
                    },
                    tooltips:         {
                        enabled: false
                    }
                }
            });

            var value = Number(this.value);

            if (value < this.panel.gauge.minValue) {
                value = 0;
            } else if (value > this.panel.gauge.maxValue) {
                value = this.panel.gauge.maxValue;
            }

            this.gaugeChart.data = {
                datasets: [{
                    borderWidth:     0,
                    data:            [
                        value, this.panel.gauge.maxValue - value
                    ],
                    backgroundColor: [
                        Chart.helpers.color('#97CC64').alpha(0.9).rgbString(),
                        Chart.helpers.color('#97CC64').alpha(0.1).rgbString()
                    ]
                }],
                labels:   ['A']
            };
            this.gaugeChart.update(0, true);
        }
    });
});
