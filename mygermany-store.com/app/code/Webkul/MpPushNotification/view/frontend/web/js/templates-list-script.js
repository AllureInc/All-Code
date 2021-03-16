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
    $.widget('mpPushNotification.templatesList', {
        _create: function () {
            globalThis = this;

            $("#templates-from-date").calendar({'dateFormat':'mm/dd/yy'});
            $("#templates-to-date").calendar({'dateFormat':'mm/dd/yy'});
            $('#mass-delete-butn').click(
                function (e) {
                    var flag =0;
                    e.preventDefault();
                    $('.mpcheckbox').each(
                        function () {
                            if (this.checked === true) {
                                flag =1;
                            }
                        }
                    );
                    if (flag === 0) {
                        alert({title : $t('Warning'),content : $t(' No Checkbox is checked ')});
                        return false;
                    } else {
                        confirmation({
                            title: $t(" Confirmation "),
                            content: $t(" Are you sure you want to delete these Template(s) ? "),
                            actions: {
                                confirm: function () {
                                    $('#form-templates-massdelete').submit();
                                }
                            }
                        });
                    }
                }
            );

            $('#mpselecctall').click(
                function (event) {
                    if (this.checked) {
                        $('.mpcheckbox').each(
                            function () {
                                this.checked = true;
                            }
                        );
                    } else {
                        $('.mpcheckbox').each(
                            function () {
                                this.checked = false;
                            }
                        );
                    }
                }
            );

            $('.mp-delete').click(
                function () {
                    var preEvent = $(this);
                    confirmation({
                        title: $t(" Confirmation "),
                        content: $t(" Are you sure you want to delete this template ? "),
                        actions: {
                            confirm: function () {
                                var url = preEvent.attr('data-url');
                                window.location = url;
                            }
                        }
                    });
                }
            );
            $('.mp-edit').click(
                function () {
                    var preEvent = $(this);
                    confirmation({
                        title: $t(" Confirmation "),
                        content: $t(" Are you sure you want to edit this template ? "),
                        actions: {
                            confirm: function () {
                                var url = preEvent.attr('data-url');
                                window.location = url;
                            }
                        }
                    });
                }
            );
        }
    });
    return $.mpPushNotification.templatesList;
    }
);