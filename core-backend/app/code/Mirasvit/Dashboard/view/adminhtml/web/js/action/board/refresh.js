define([
    'underscore',
    'uiRegistry'
], function (_, Registry) {
    'use strict';
    
    return function (provider) {
        provider = Registry.get(provider);
        
        var params = _.clone(provider.params);
        
        params['refresh'] = params['refresh'] === undefined ? 0 : params['refresh'] + 1;
        
        provider.set('params', params);
    };
});
