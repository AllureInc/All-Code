define([
    'underscore',
    'uiRegistry'
], function (_, Registry) {
    'use strict';
    return function (provider, boardId) {
        provider = Registry.get(provider);
        
        var board = _.find(provider.boards, function (board) {
            return board.get('board_id') === boardId;
        });
        
        provider.set('board', board);
    };
});
