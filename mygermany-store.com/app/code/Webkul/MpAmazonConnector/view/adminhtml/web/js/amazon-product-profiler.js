/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert'
], function ($,$t,alert) {
    'use strict';
    var skipCount,total;
    $.widget('mpamazonconnect.productProfiler', {
        _create: function () {
            var self = this;
            skipCount = 0;
            var total = self.options.productCount;
            if (total > 0) {
                importProduct(1);
            }
            function importProduct(count)
            {
                count = count;
                $.ajax({
                    type: 'get',
                    url:self.options.createProductUrl,
                    async: true,
                    dataType: 'json',
                    data : {
                        count:count,
                        'entity_id' : self.options.accountId
                    },
                    success:function (data) {
                        if (data['error'] == 1) {
                            $('.wk-mu-error-msg-container').append($('<div />')
                                                    .addClass('message message-error error')
                                                    .text(data['msg']));
                            skipCount++;
                        }
                        var width = (100/total)*count;
                        $(self.options.progressBarSelector).animate({width: width+"%"},'slow', function () {
                            if (count == total) {
                                finishImporting(count, skipCount);
                                $(self.options.infoBarSelector).text("Completed");
                            } else {
                                count++;
                                $(self.options.currentSelector).text(count);
                                importProduct(count);
                            }
                        });
                    }
                });
            }
            function finishImporting(count, skipCount)
            {
                $.ajax({
                    type: 'get',
                    url:self.options.createProductUrl,
                    async: true,
                    dataType: 'json',
                    data : {count:count,skip:skipCount },
                    success:function (data) {
                        $(self.options.fieldsetSelector).append(data['msg']);
                    }
                });
            }
        }
    });
    return $.mpamazonconnect.productProfiler;
});
