define([
    'underscore',
    'jquery',
    'ko',
    'uiComponent',
    'mageUtils'
], function (_, $, ko, Component, Utils) {
    'use strict';
    
    return Component.extend({
        $el: null,
        
        initialize: function () {
            Utils.limit(this, 'render', 5);
            _.bindAll(this, 'render');
            
            this._super();
            
            this.block.subscribe(this.render);
            
            this.setDefaults();
        },
        
        setElement: function (element) {
            this.$el = $(element);
            this.render();
        },
        
        setDefaults: function () {
        
        },
        
        render: function () {
        
        }
    });
});
