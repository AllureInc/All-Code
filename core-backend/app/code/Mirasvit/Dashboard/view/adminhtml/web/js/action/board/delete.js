define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, _, Registry, alert, t) {
    'use strict';
    
    return function (provider, board) {
        provider = Registry.get(provider);
        
        provider.set('isLoading', true);
        
        $.ajax({
            url:      provider.deleteBoardUrl,
            dataType: 'json',
            data:     {
                board_id: board.get('board_id')
            },
            
            complete: function () {
                provider.set('isLoading', false);
            }.bind(this),
            
            success: function () {
                provider.set('boards', _.reject(provider.boards, function (itm) {
                    return itm.get('board_id') === board.get('board_id');
                }.bind(this)));
            }.bind(this),
            
            error: function (response) {
                alert({
                    title:   t('Something went wrong.'),
                    content: response.responseText
                });
            }
        })
    };
});
