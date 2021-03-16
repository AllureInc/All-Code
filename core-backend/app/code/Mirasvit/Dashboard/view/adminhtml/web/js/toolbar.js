define([
    'uiComponent',
    'uiRegistry'
], function (Component, Registry) {
    'use strict';
    
    return Component.extend({
        defaults: {
            template: 'Mirasvit_Dashboard/toolbar',
            
            imports: {
                isLoading:  '${ $.provider }:isLoading',
                isEditable: '${ $.provider }:isEditable',
                isChanged:  '${ $.provider }:isChanged',
                board:      '${ $.provider }:board'
            },
            
            exports: {
                isEditable: '${ $.provider }:isEditable'
            },
            
            tracks: {
                isEditable: true,
                isLoading:  true,
                isChanged:  true
            }
        },
        
        initialize: function () {
            this._super();
            
            if (this.board.get('blocks').length === 0) {
                this.set('isEditable', true);
            }
        },
        
        toggleMode: function () {
            this.set('isEditable', !this.isEditable);
        },
        
        refresh: function () {
            Registry.get(this.provider).trigger('board.refresh');
        },
        
        save: function () {
            Registry.get(this.provider).trigger('board.save');
        },
        
        reset: function () {
            this.set('isEditable', false);
            
            Registry.get(this.provider).trigger('board.reset');
        },
        
        addBlock: function () {
            Registry.get(this.provider).trigger('block.add');
        }
    });
});
