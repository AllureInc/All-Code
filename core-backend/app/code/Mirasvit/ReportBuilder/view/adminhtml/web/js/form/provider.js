define([
    'Magento_Ui/js/form/provider',
    'jquery',
    'ko',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert'
], function (Provider, $, ko, _, Registry, modal, alert) {
    'use strict';
    
    return Provider.extend({
        defaults: {
            render_url: '',
            
            listens: {
                data: 'handleDataUpdate'
            }
        },
        
        initialize: function () {
            this._super();
            
            //this.data.report_id = this.data.current['report_id'];
            //this.data.title = this.data.current['title'];
            //
            //
            //var config = this.data.current.config;
            //this.data.table = this.prepareValue(config, 'table');
            //this.data.default_dimension = this.prepareValue(config, 'default_dimension');
            //this.data.default_columns = this.prepareValue(config, 'default_columns');
            //
            //
            return this;
        },
        
        prepareValue: function (scope, key) {
            if (!scope) {
                return '';
            }
            
            if (!scope[key]) {
                return '';
            }
            
            if (_.isArray(scope[key])) {
                return scope[key].join("\n");
            }
            
            return scope[key];
        },
        
        handleDataUpdate: function () {
            //this.renderReport();
        },
        
        run: function () {
            this.renderReport();
        },
        
        renderReport: function () {
            $('body').trigger('processStart');
            
            $.ajax({
                url:      this.render_url,
                data:     this.data,
                dataType: 'json',
                success:  function (response) {
                    
                    if (response.success) {
                        var modal = $('<div/>').modal({
                            type:          'slide',
                            modalClass:    'library-aside',
                            closeOnEscape: true,
                            opened:        function () {
                                if (Registry.get('mst_report.mst_report')) {
                                    Registry.get('mst_report.mst_report').destroy();
                                }
                                $(modal).html(response.html)
                                    .trigger('contentUpdated');
                                ko.applyBindings(this, $('.report__container')[0]);
                            }.bind(this),
                            closed:        function () {
                                $('.library-aside').remove();
                            },
                            
                            buttons: []
                        });
                        
                        modal.modal('openModal');
                    } else {
                        alert({content: response.message});
                    }
                    
                    $('body').trigger('processStop');
                }.bind(this)
            });
        }
    });
});
