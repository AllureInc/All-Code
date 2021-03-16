/* global $, $H */

define([
    'mage/adminhtml/grid'
], function () {
    'use strict';

    return function (config) {
        var walletCustomers = $H({}),
            gridJsObject = window[config.gridJsObjectName],
            length = walletCustomers.keys().length;

        $('wkcustomerids').value = Object.toJSON(walletCustomers);
        /**
         * Register customer
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function walletCustomerCheckBoxClick(grid, element, checked) {
            if (element.className != "admin__control-checkbox") {
                if (checked) {
                    walletCustomers.set(element.value, length+1);
                } else {
                    walletCustomers.unset(element.value);
                }
                length = walletCustomers.keys().length;
                $('wkcustomerids').value = Object.toJSON(walletCustomers);
                grid.reloadParams = {
                    'selected_products[]': walletCustomers.keys()
                };
            }
        }

        /**
         * Click on customer row
         * @param {Object} grid
         * @param {String} event
         */
        function walletCustomerRowClick(grid, event) {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;
            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');
                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }
        gridJsObject.rowClickCallback = walletCustomerRowClick;
        gridJsObject.checkboxCheckCallback = walletCustomerCheckBoxClick;
    };
});
