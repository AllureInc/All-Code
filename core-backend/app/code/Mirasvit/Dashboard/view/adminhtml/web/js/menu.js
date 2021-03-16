define([
    'underscore',
    'ko',
    'uiComponent',
    'Magento_Ui/js/lib/collapsible',
    'jquery',
    'uiRegistry',
    'Mirasvit_Dashboard/js/lib/utils'
], function (_, ko, Component, Collapsible, $, Registry, utils) {
    'use strict';
    
    return Collapsible.extend({
        defaults: {
            template: 'Mirasvit_Dashboard/menu',
            
            imports: {
                boards: '${ $.provider }:boards',
                board:  '${ $.provider }:board'
            },
            
            tracks: {
                board:  true,
                boards: true
            },
            
            listens: {
                board: 'updateTitle'
            },
            
            closeOnOuter: false
        },
        
        initialize: function () {
            this._super();
            
            _.bindAll(this, 'selectBoard', 'editBoard', 'deleteBoard', 'addBoard');
            
            return this;
        },
        
        selectBoard: function (board) {
            Registry.get(this.provider).trigger('board.select', board.get('board_id'));
            
            this.opened(false);
        },
        
        editBoard: function () {
            Registry.get(this.provider).trigger('board.edit', this.board.get('board_id'));
            
            this.opened(false);
        },
        
        deleteBoard: function () {
            Registry.get(this.provider).trigger('board.delete', this.board.get('board_id'));
            
            this.opened(false);
        },
        
        addBoard: function () {
            Registry.get(this.provider).trigger('board.add');
            
            this.opened(false);
        },
        
        updateTitle: function () {
            if (this.board.get('title')) {
                document.title = this.board.get('title');
            }
        }
    });
});
