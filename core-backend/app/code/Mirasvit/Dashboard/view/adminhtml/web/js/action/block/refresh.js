define([
    'jquery',
    'underscore',
    'uiRegistry',
    'mage/translate'
], function ($, _, Registry, t) {
    'use strict';
    
    return function (provider, board, block, view) {
        provider = Registry.get(provider);
        
        provider.set('isLoading', true);
        view.set('isLoading', true);
        
        var data = _.extend({block: block.asArray()}, provider.params);
        
        $.ajax({
            url:      provider.queryUrl,
            method:   'POST',
            dataType: 'json',
            data:     data,
            
            complete: function () {
                provider.set('isLoading', false);
                view.set('isLoading', false);
            },
            
            success: function (response) {
                if (response.success) {
                    block.set('value', response.data);
                    block.set('error', null);
                } else {
                    block.set('value', null);
                    block.set('error', response.message);
                }
            },
            
            error: function (response) {
                block.set('value', null);
                block.set('error', response.responseText);
            }
        });
    };
});
