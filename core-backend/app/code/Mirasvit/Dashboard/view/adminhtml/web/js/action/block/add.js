define([
    'uiRegistry',
    '../../model/block'
], function (Registry, Block) {
    'use strict';
    
    return function (provider) {
        provider = Registry.get(provider);
        
        var blocks = provider.board.get('blocks');
        var blockId = _.isEmpty(blocks) ? 1 : _.max(blocks, function (block) {
            return block.get('block_id');
        }).get('block_id') + 1;
        
        provider.board.get('blocks').push(new Block({
            block_id: blockId,
            pos:      [0, 0],
            size:     [5, 10],
            title:    'Block #' + blockId
        }));
        
        provider.board.notify();
    };
});
