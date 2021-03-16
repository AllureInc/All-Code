define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/form/components/fieldset'
], function (_, utils, Fieldset) {
    'use strict';
    
    return Fieldset.extend({
        defaults: {
            template: 'Mirasvit_ReportBuilder/form/container'
        }
    });
});