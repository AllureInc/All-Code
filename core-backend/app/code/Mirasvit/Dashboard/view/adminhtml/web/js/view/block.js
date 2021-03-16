define([
    'underscore',
    'jquery',
    'uiComponent',
    'uiLayout',
    'uiRegistry',
    'mageUtils',
    '../action/block/edit',
    '../action/block/refresh',
    '../action/block/delete'
], function (_, $, Component, Layout, Registry, utils, editBlock, refreshBlock, deleteBlock) {
    'use strict';
    
    return Component.extend({
        defaults: {
            template: 'Mirasvit_Dashboard/view/block',
            
            imports: {
                board:      '${ $.provider }:board',
                isEditable: '${ $.provider }:isEditable',
                params:     '${ $.provider }:params'
            },
            
            listens: {
                params: 'refresh'
            },
            
            exports: {},
            
            tracks: {
                isLoading:  true,
                isEditable: true
            }
        },
        
        interval: null,
        
        initialize: function () {
            utils.limit(this, 'refresh', 100);
            
            _.bindAll(this, 'afterRender', 'render');
            
            this._super();
            
            this.block.subscribe(this.render);
        },
        
        afterRender: function () {
            this.el().attr({
                'data-row':   this.block.get('pos')[0],
                'data-col':   this.block.get('pos')[1],
                'data-sizex': this.block.get('size')[0],
                'data-sizey': this.block.get('size')[1]
            }).addClass('gs-w');
            
            this.grid.add_widget(
                this.el()
            );
            
            this.initListener();
            this.refresh();
        },
        
        render: function () {
            var elem = _.findWhere(this.elems(), {id: this.block.get('block_id')});
            
            // destroy element if we changed the block's renderer
            if (elem && elem.component.indexOf(this.block.get('renderer')) === -1) {
                elem.destroy();
                elem = null;
            }
            
            if (elem) {
                elem.set('block', this.block);
            } else {
                if (!this.block.get('renderer')) {
                    return;
                }
                
                Layout([{
                    component:  'Mirasvit_Dashboard/js/view/panel/' + this.block.get('renderer'),
                    id:         this.block.get('block_id'),
                    name:       this.block.get('block_id'),
                    parentName: this.name,
                    config:     {
                        block: this.block
                    }
                }], this);
            }
        },
        
        edit: function () {
            editBlock(this.provider, this.board, this.block).then(function () {
                this.refresh();
            }.bind(this));
        },
        
        remove: function () {
            deleteBlock(this.provider, this.board, this.block);
            
            this.grid.remove_widget.apply(this.grid, this.el());
            
            this.destroy();
        },
        
        initListener: function () {
            this.interval = setInterval(function () {
                if (this.isEditable === true) {
                    return;
                }
                
                this.refresh();
            }.bind(this), 60000 * 2); // update block every 2 minutes
            
            return true;
        },
        
        destroy: function () {
            this.block.unsubscribe();
            
            this._super();
        },
        
        refresh: function () {
            refreshBlock(this.provider, this.board, this.block, this);
        },
        
        el: function () {
            return $("[data-id='" + this.block.get('block_id') + "']");
        }
    });
});
