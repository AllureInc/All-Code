define([
    'underscore',
    'jquery',
    'uiComponent',
    'uiLayout',
    'uiRegistry',
    '../action/block/add',
    './../lib/gridster'
], function (_, $, Component, layout, Registry, addBlock) {
    'use strict';
    
    return Component.extend({
        defaults: {
            template: 'Mirasvit_Dashboard/view/board',
            
            imports: {
                board:      '${ $.provider }:board',
                isEditable: '${ $.provider }:isEditable'
            },
            
            listens: {
                isEditable: 'switchMode',
                
                '${ $.provider }:block.add': 'addBlock'
            },
            
            tracks: {
                isEditable: true,
                board:      true,
                grid:       true
            }
        },
        
        initialize: function () {
            this._super();
            _.bindAll(this, 'synchronize', 'renderBlocks', 'afterRender');
            
            this.board.subscribe(this.renderBlocks);
        },
        
        afterRender: function () {
            this.initializeGrid();
            this.renderBlocks();
        },
        
        initializeGrid: function () {
            this.grid = $('.dashboard__board').gridster({
                autogenerate_stylesheet:   true,
                widget_base_dimensions:    ['auto', 10],
                min_cols:                  3,
                max_cols:                  20,
                min_rows:                  1,
                max_rows:                  1000,
                widget_margins:            [5, 5],
                shift_widgets_up:          false,
                shift_larger_widgets_down: false,
                collision:                 {
                    wait_for_mouseup: true
                },
                serialize_params:          function ($w, w) {
                    var elem = _.findWhere(this.elems(), {
                        id: parseInt($w.attr('data-id'))
                    });
                    
                    var block = elem.get('block');
                    block.set('pos', [w.row, w.col]);
                    block.set('size', [w.size_x, w.size_y]);
                    
                    return block;
                }.bind(this),
                resize:                    {
                    enabled: true
                }
            }).data('gridster');
            
            this.grid.disable();
            this.grid.disable_resize();
            
            this.grid.options.resize.stop = this.synchronize;
            this.grid.options.draggable.stop = this.synchronize;
            this.grid.options.remove = this.synchronize;
            this.grid.options.add = this.synchronize;
            this.grid.options.update_widget_position = this.synchronize;
        },
        
        renderBlocks: function () {
            _.each(this.board.get('blocks'), function (block) {
                var elem = _.findWhere(this.elems(), {
                    id: block.get('block_id')
                });
                
                if (!elem) {
                    layout([{
                        component: 'Mirasvit_Dashboard/js/view/block',
                        provider:  this.provider,
                        grid:      this.grid,
                        name:      block.get('block_id'),
                        id:        block.get('block_id'),
                        block:     block
                    }], this);
                }
            }, this);
            
        },
        
        switchMode: function () {
            if (!this.grid) {
                return;
            }
            
            if (this.isEditable) {
                this.grid.enable();
                this.grid.enable_resize();
            } else {
                this.grid.disable();
                this.grid.disable_resize();
            }
        },
        
        addBlock: function () {
            addBlock(this.provider);
        },
        
        synchronize: function () {
            // sort widgets by Y, X position
            this.board.set('blocks', _.sortBy(this.grid.serialize(), function (block) {
                return block.get('pos')[1] * 1000 + block.get('pos')[0];
            }));
        },
        
        destroy: function () {
            this.grid.destroy();
            this._super();
        }
    });
});
