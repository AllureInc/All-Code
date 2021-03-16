define([
    'underscore',
    'jquery',
    'ko',
    'uiComponent',
    'https://cdn.jsdelivr.net/npm/clipboard@1/dist/clipboard.min.js'
], function (_, $, ko, Component, Clipboard) {
    'use strict';
    
    return Component.extend({
        defaults: {
            template:          'Mirasvit_ReportBuilder/form/schema',
            tables:            [],
            currentTable:      null,
            searchQueryTable:  '',
            searchQueryColumn: ''
        },
        
        initialize: function () {
            this._super();
            
            _.bindAll(this, 'handleTableSelect');
            
            new Clipboard('.clipboard', {
                text: function (btn) {
                    return $('input', $(btn)).val();
                }
            });
        },
        
        initObservable: function () {
            this.currentTable = ko.observable();
            this.searchQueryTable = ko.observable('');
            this.searchQueryColumn = ko.observable('');
            
            return this._super();
        },
        
        findTables: function () {
            var tables;
            
            tables = _.filter(this.tables, function (table) {
                if (table.name.indexOf(this.searchQueryTable()) >= 0) {
                    return table;
                }
            }.bind(this));
            
            return _.sortBy(tables, 'name')
        },
        
        findColumns: function () {
            var columns;
            
            if (!this.currentTable()) {
                return [];
            }
            
            columns = _.filter(this.currentTable().columns, function (column) {
                if (column.name.indexOf(this.searchQueryColumn()) >= 0) {
                    return column;
                }
            }.bind(this));
            
            return _.sortBy(columns, 'name')
        },
        
        findRelations: function () {
            if (!this.currentTable()) {
                return [];
            }
            
            return this.currentTable().relations;
        },
        
        handleTableSelect: function (table) {
            this.currentTable(table);
            console.log(this.currentTable())
        },
        
        elem: function () {
            return $('.reportBuilder__container');
        }
    });
});