define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    '../../model/board'
], function ($, _, Registry, alert, t, Board) {
    'use strict';
    return function (provider, board) {
        provider = Registry.get(provider);
        
        provider.set('isLoading', true);
        
        var data = board.asArray();
        data.test = 1;
  
        $.ajax({
            url:      provider.saveBoardUrl,
            dataType: 'json',
            type:     'POST',
            data:     {
                board: JSON.stringify(data)
            },
            
            complete: function () {
                provider.set('isLoading', false);
            },
            
            success: function (response) {
                var model = _.find(provider.boards, function (itm) {
                    return itm.get('board_id') === board.get('board_id');
                });

                if (!model) {
                    model = new Board(response.board);
                    provider.boards.push(model);
                } else {
                    model.initConfig(response.board);
                }
            },
            
            error: function (response) {
                alert({
                    title:   t('Something went wrong.'),
                    content: response.responseText
                });
            }
        });
    };
});
