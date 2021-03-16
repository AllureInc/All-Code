define([
    'underscore',
    'jquery',
    'ko',
    'uiComponent',
    'uiRegistry'
], function (_, $, ko, Component, Registry) {
    'use strict';
    
    return Component.extend({
        defaults: {
            template: 'Mirasvit_Dashboard/qr',
            
            imports: {
                data:            '${ $.provider }:data',
                isMobileEnabled: '${ $.provider }:data.is_mobile_enabled'
            },
            listens: {
                isMobileEnabled: 'handleMobileEnabled'
            },
            tracks:  {
                data: true
            }
        },
        
        initialize: function () {
            this.visible = ko.observable(false);
            
            this._super();
            
            _.bindAll(this, 'handleMobileEnabled')
        },
        
        handleMobileEnabled: function (value) {
            this.visible(parseInt(value));
        }
    });
});
