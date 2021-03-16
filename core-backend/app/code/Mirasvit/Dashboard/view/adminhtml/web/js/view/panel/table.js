define([
    'underscore',
    'jquery',
    './abstract',
    'ko',
    'uiComponent',
    'uiRegistry'
], function (_, $, Panel, ko, Component) {
    'use strict';
    
    return Panel.extend({
        panel: null,
        
        defaults: {
            template: 'Mirasvit_Dashboard/view/panel/table',
            
            tracks: {
                block:   true,
                headers: true,
                values:  true
            }
        },
        
        setDefaults: function () {
            _.each(this.block.get('table'), function (value, key) {
                this.set('panel.' + key, value);
            }.bind(this));
        },
        
        render: function () {
            this.setDefaults();
            
            var headers = [];
            var values = [];
            var data = this.block.get('value');
            
            if (data && data.type === 'Mirasvit\\ReportApi\\Api\\ResponseInterface') {
                headers = _.map(data.columns, function (column) {
                    return column.label;
                });

                values = _.map(data.items, function (item) {
                    return item.formatted_data;
                });
            }
            
            this.set('headers', headers);
            this.set('values', values);
        }
    });
});
