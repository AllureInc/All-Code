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
    $.widget('mpPushNotification.userslist', {
        _create: function () {
            globalThis = this;

            $("#users-from-date").calendar({'dateFormat':'mm/dd/yy'});
            $("#users-to-date").calendar({'dateFormat':'mm/dd/yy'});
          
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
                        content: $t(" Are you sure you want to delete this user ? "),
                        actions: {
                            confirm: function () {
                                var url = preEvent.attr('data-url');
                                window.location = url;
                            }
                        }
                    });
                }
            );

            $('#mass-delete-butn').click(
                function (e) {
                    var flag = 0;
                    $('select[name=template]').removeClass('required-entry');
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
                    }
                    confirmation({
                        title: $t(" Confirmation "),
                        content: $t(" Are you sure you want to delete these user(s) ? "),
                        actions: {
                            confirm: function () {
                                $('#form-userslist-massdelete').submit();
                            }
                        }
                    });
                }
            );

            $('#send-notification').click(
                function (e) {
                var flag = 0;
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
                }
                var preEvent = $(this);
                $('select[name=template]').addClass('required-entry');

                    if ($('#form-userslist-massdelete').valid()) {
                        confirmation({
                            title: $t("Confirmation"),
                            content: $t("Are you sure you want to send notification ?"),
                            actions: {
                                confirm: function () {
                                    $('#form-userslist-massdelete').attr('action', globalThis.options.sendNotificationUrl);
                                    $('#form-userslist-massdelete').submit();
                                }
                            }
                        });
                    } else {
                        return false;
                    }
                }
            );
        }
    });
    return $.mpPushNotification.userslist;
    }
);