define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({
        initialize: function () {
            _.bindAll(this, 'reset');
            var _self = this;
            this._super()
                .setInitialValue()
                ._setClasses()
                .initSwitcher();

            var divCheckingInterval = setInterval(function(){
                // Find it with a selector
                if(window.FAQCategories && jQuery('select[name="group"] option').length){
                    clearInterval(divCheckingInterval);

                    var initialValue = _self.getInitialValue();
                    var arr = jQuery.map(window.FAQCategories, function(el) { return el });
                    var filtered = arr.filter(function(item){
                        return (initialValue != 'both') ? (item.group_type == initialValue || item.group_type == 'both') : true;
                    });

                    jQuery('select[name="group"] option').attr('disabled', true);
                    jQuery(filtered).each(function(){
                        jQuery('select[name="group"] option[value="'+this.value+'"]').attr('disabled', false);
                    });
                }
            }, 500);

            return this;
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            var arr = jQuery.map(window.FAQCategories, function(el) { return el });
            var filtered = arr.filter(function(item){
                return (value != 'both') ? (item.group_type == value || item.group_type == 'both') : true;
            });

            jQuery('select[name="group"] option').attr('disabled', true);
            jQuery(filtered).each(function(){
                jQuery('select[name="group"] option[value="'+this.value+'"]').attr('disabled', false);
            });
            jQuery('select[name="group"]').val('').trigger('change');

            return this._super();
        },
    });
});