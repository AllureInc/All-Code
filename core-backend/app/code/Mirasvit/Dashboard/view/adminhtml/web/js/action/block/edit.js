define([
    'uiRegistry',
    'jquery',
    'mage/translate'
], function (Registry, $, t) {
    'use strict';
    
    return function (provider, board, block) {
        provider = Registry.get(provider);
        
        var dfd = $.Deferred();
        
        var modalName = provider.parentName + '.dashboard.block_form_modal';
        var modal = Registry.get(modalName);
        var form = Registry.get(modalName + '.form');
        
        modal.setTitle(t('Configuration'));
        modal.toggleModal();
        
        form.destroyInserted();
        form.params.block_id = block.get('block_id');
        form.params.board_id = board.get('board_id');
        form.render();
        
        var responseSubscription = form.responseStatus.subscribe(function () {
            var data = form.responseData['block'];

            var model = _.find(board.get('blocks'), function (itm) {
                return itm.get('block_id') === block.get('block_id');
            });

            model.initConfig(data);
            
            modal.state(false);
            responseSubscription.dispose();
    
            dfd.resolve();
        });
    
        return dfd.promise();
    };
});
