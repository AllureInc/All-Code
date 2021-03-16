define([
    'underscore',
    'jquery'
], function (_, $) {
    'use strict';

    return {
        waitElement: function (selector, callback) {
            var interval = setInterval(function () {

                if ($(selector).length) {
                    clearInterval(interval);

                    callback($(selector));
                }
            }, 100);
        },

        waitObject: function (objectCallback, callback) {
            var interval = setInterval(function () {
                if (objectCallback()) {
                    clearInterval(interval);

                    callback(objectCallback());
                }
            }, 100);
        },

        ts: function () {
            return parseInt(new Date().getTime());
        }
    };
});