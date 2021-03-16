define([
    'underscore',
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/ui-select',
    'uiRegistry'
], function (_, $, ko, uiSelect, Registry) {
    'use strict';

    return uiSelect.extend({
        defaults: {
            switchFieldsets: [],
            listens: {
                value: 'handleValueChange'
            }
        },

        initialize: function () {
            this._super();

            setTimeout(function () {
                this.handleValueChange(this.value());
            }.bind(this), 100);
        },

        handleValueChange: function (value) {
            // Change visibility of dependent fieldsets
            _.each(this.switchFieldsets, function (components, type) {
                _.each(components, function (componentName) {
                    var component = Registry.get(componentName);
                    if (component) {
                        this._changeVisibility(component, type == value);
                    }
                }.bind(this));
            }.bind(this));

            _.each(this.options(), function (option) {
                this.changeVisibility(option.value, false);

                if (option.optgroup) {
                    _.each(option.optgroup, function (opt) {
                        this.changeVisibility(opt.value, false);
                    }.bind(this));
                }
            }.bind(this));

            this.changeVisibility(value, true)
        },

        changeVisibility: function (name, visibility) {
            if (_.isEmpty(name)) {
                return;
            }

            name = this.parentName + '.container.' + name.toLowerCase();

            var element = Registry.get(name);

            this._changeVisibility(element, visibility);
        },

        _changeVisibility: function (element, visibility) {
            if (!element) {
                return
            }

            if ('visible' in element && !element.forceVisibility) {
                element.visible(visibility);
            }

            if (element.elems) {
                _.each(element.elems(), function (elem) {
                    this._changeVisibility(elem, visibility)
                }.bind(this))
            }
        }
    })
});