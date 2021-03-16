/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */

define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($,$t,alert) {
    "use strict";
    var popup;
    $.widget('rakutenconnect.productsyncscript', {
        _create: function () {
            var self = this;
            var options = this.options;
            // import amazon product
            $("#rakuten-pro-import").on("click", function () {
                var alerttext = '';
                $.ajax({
                    url: options.importUrl,
                    data: {
                        form_key: window.FORM_KEY
                    },
                    type: 'POST',
                    dataType:'JSON',
                    showLoader: true,
                    success: function (amazonProd) {
                        if (amazonProd.error === 'false') {
                            if (amazonProd.data) {
                                var countArray = amazonProd.data;
                                document.getElementById('run-profiler').removeAttribute('disabled');
                                var msg='Total '+countArray.length +$.mage.__(' product(s) imported in your store from rakuten, Click on Create Imported Product In Store to create these products in your store');
                            } else {
                                var msg='There is no product(s)';
                            }
                            alert({
                                   title: $.mage.__('Imported Product'),
                                   content: msg,
                                   actions: {
                                       always: function (){}
                                   }
                             });
                        } else {
                            alert({
                                   title: $.mage.__('Imported Product'),
                                   content: amazonProd.error_msg,
                                   actions: {
                                       always: function (){}
                                   }
                             });
                        }
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });

            $('#mpsyncproselecctall').change(function () {
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

            // export related event
            $('#mass-export-butn').on('click', function (event) {
                event.preventDefault();
                var status = false;
                $('.mpcheckbox').each(function () {
                    if ($(this).prop('checked') == true) {
                        status = true;
                    }
                });
                if (!status) {
                    alert({
                           title: $.mage.__('Attention'),
                           content: $.mage.__('No item selected.'),
                           actions: {
                               always: function (){}
                           }
                     });
                    return false;
                }
                $('#form-productlist-massdisable').submit();
            });
            $('.mpcheckbox').change(function () {
                if ($(this).is(":checked")) {
                    var totalCheck = $('.wk-row-view  .mpcheckbox').length,
                        totalCkecked = $('.wk-row-view  .mpcheckbox:checked').length;
                    if (totalCheck == totalCkecked) {
                        $('#mpsyncproselecctall').prop('checked', true);
                    }
                } else {
                    $('#mpsyncproselecctall').prop('checked', false);
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
            
            // generate report
            $('#rakuten-generate-report').on('click', function () {
                var alerttext = '';
                $.ajax({
                    url: options.reportUrl,
                    data: {
                        form_key: window.FORM_KEY
                    },
                    type: 'POST',
                    dataType:'JSON',
                    showLoader: true,
                    success: function (response) {
                        if (response.error_msg==false) {
                            document.getElementById('rakuten-pro-import').removeAttribute('disabled');
                            $('.wk-msg-box').text(response.data).removeClass('message-error').addClass('wk-mu-success');
                            $('<div />').html(response.pop_msg)
                            .modal({
                                title: $.mage.__('Attention'),
                                autoOpen: true,
                                buttons: [{
                                 text: 'OK',
                                    attr: {
                                        'data-action': 'cancel'
                                    },
                                    'class': 'action-primary',
                                    click: function () {
                                            this.closeModal();
                                        }
                                }]
                            });
                        } else {
                            $('<div />').html(response.error_msg)
                            .modal({
                                title: $.mage.__('Attention'),
                                autoOpen: true,
                                buttons: [{
                                 text: 'OK',
                                    attr: {
                                        'data-action': 'cancel'
                                    },
                                    'class': 'action-primary',
                                    click: function () {
                                            this.closeModal();
                                        }
                                }]
                            });
                        }
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });

            // error display
            $('#error-check-butn').on('click', function () {
                var errorMsg = $(this).data('error');
                alert({
                       title: $.mage.__('Error Message'),
                       content: errorMsg,
                       actions: {
                           always: function (){}
                       }
                 });
            });

            // run profiler event
            $('#run-profiler').on('click',function (e) {
                var width = '800';
                var height = '400';
                var scroller = 1;
                var screenX = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft;
                var screenY = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop;
                var outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth;
                var outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22);
                var left = parseInt(screenX + ((outerWidth - width) / 2), 10);
                var top = parseInt(screenY + ((outerHeight - height) / 2.5), 10);
                
                var settings = (
                    'width=' + width +
                    ',height=' + height +
                    ',left=' + left +
                    ',top=' + top +
                    ',scrollbars=' + scroller
                    );
               popup = window.open(options.profilerUrl,'',settings);
               popup.onunload = self.afterChildClose;
            });
        },
        afterChildClose:function () {
            if (popup.location != "about:blank") {
                location.reload();
            }
        }
    });
    return $.rakutenconnect.productsyncscript;
});