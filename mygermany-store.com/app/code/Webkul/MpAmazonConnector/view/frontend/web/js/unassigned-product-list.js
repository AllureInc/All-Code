/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
define([
    "jquery",
    "mage/translate"
], function ($, $t) {
    "use strict";
    $.widget('mpamazonconnect.unassignProList', {
        _create: function () {
            $('#mpunassignselecctall').change(function () {
                if ($(this).is(":checked")) {
                    $('.wk-row-view  .mpcheckbox').each(function () {
                        $(this).prop('checked', true);
                    });
                } else {
                    $('.wk-row-view  .mpcheckbox').each(function () {
                        $(this).prop('checked', false);
                    });
                }
            });

            $('body').delegate('.mp-delete','click',function () {
                var dicision=confirm($t(" Are you sure you want to delete map record ? "));
                if (dicision === true) {
                    var $url=$(this).attr('data-url');
                    window.location = $url;
                }
            });

            $('body').delegate('.mp-edit','click',function () {
                var dicision=confirm($t(" Are you sure you want to edit this product ? "));
                if (dicision === true) {
                    var $url=$(this).attr('data-url');
                    window.location = $url;
                }
            });

            $('.mpcheckbox').change(function () {
                if ($(this).is(":checked")) {
                    var totalCheck = $('.wk-row-view  .mpcheckbox').length,
                        totalCkecked = $('.wk-row-view  .mpcheckbox:checked').length;
                    if (totalCheck == totalCkecked) {
                        $('#mpunassignselecctall').prop('checked', true);
                    }
                } else {
                    $('#mpunassignselecctall').prop('checked', false);
                }
            });
        }
    });
    return $.mpamazonconnect.unassignProList;
});
