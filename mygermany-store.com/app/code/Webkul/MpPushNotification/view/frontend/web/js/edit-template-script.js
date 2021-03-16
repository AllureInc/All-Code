/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

/*jshint jquery:true*/
define(
    [
    'jquery',
    'mage/translate',
    'mage/mage',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'mage/calendar',
    ],
    function ($,$t,mage,alert,confirmation) {
    'use strict';
    var globalThis;
    $.widget('mpPushNotification.editTemplate', {
        _create: function () {
            globalThis = this;
            $('.wk-editform-del').click(
                function () {
                    var preEvent = $(this);
                    confirmation({
                        title: $t(" Confirmation "),
                        content: $t(" Are you sure you want to delete this logo ? "),
                        actions: {
                            confirm: function () {
                                $('.wk-ap-ap-img-box').remove();
                                if (!$('.wk-ap-ap-img-box').length) {
                                    $('#logo').addClass('required-entry');
                                }
                            }
                        }
                    });
                }
            );
            $('.wk-mp-btn').click(
                function (e) {
                    var preEvent = $(this);
                }
            );

        }
    });
    return $.mpPushNotification.editTemplate;
    }
);