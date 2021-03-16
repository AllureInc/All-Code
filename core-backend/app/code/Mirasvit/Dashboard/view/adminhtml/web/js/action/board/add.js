define([
    'uiRegistry',
    './edit',
    '../../model/board'
], function (Registry, edit, Board) {
    'use strict';
    
    return function (provider) {
        provider = Registry.get(provider);
        
        edit(provider, new Board({}));
    };
});
