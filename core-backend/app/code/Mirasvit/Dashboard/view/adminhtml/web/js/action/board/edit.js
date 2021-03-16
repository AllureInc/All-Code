define([
    'jquery',
    'underscore',
    'uiRegistry',
    'mage/translate',
    '../../model/board'
], function ($, _, Registry, t, Board) {
    'use strict';
    
    return function (provider, board) {
        provider = Registry.get(provider);
        
        var modalName = provider.parentName + '.dashboard.board_form_modal';
        var modal = Registry.get(modalName);
        var form = Registry.get(modalName + '.form');
        
        modal.setTitle(board.get('title') ? board.get('title') : t('New Board'));
        modal.toggleModal();
        
        form.destroyInserted();
        form.params.board_id = board.get('board_id');
        form.render();
        
        var responseSubscription = form.responseStatus.subscribe(function () {
            var data = form.responseData['board'];
            
            var model = _.find(provider.boards, function (itm) {
                return itm.get('board_id') === data.board_id;
            });
            
            if (model) {
                model.set(data);
            } else {
                model = new Board(data);
                provider.boards.push(model);
            }
            
            modal.state(false);
            responseSubscription.dispose();
        }.bind(this));
    };
});
