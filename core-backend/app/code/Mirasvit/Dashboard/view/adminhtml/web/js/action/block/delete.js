define([
    'uiRegistry',
    'jquery',
    'mage/translate'
], function (Registry, $, t) {
    'use strict';
    
    return function (provider, board, block) {
        provider = Registry.get(provider);
        
        var blocks = _.reject(board.get('blocks'), function (itm) {
            return itm.get('block_id') === block.get('block_id');
        }.bind(this));
    
        board.set('blocks', blocks);
        
        provider.board.notify();
    };
});
