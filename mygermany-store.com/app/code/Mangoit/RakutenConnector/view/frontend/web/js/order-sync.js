/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    'mage/loader',
    'mage/template',
    'mage/calendar',
    'text!Mangoit_RakutenConnector/templates/OrderMap/date-for-import-ebay-order.html',
], function ($, alert, loader, template, calendar, orderFormTemplate) {
    "use strict";
    var popup,modelWrapper;
    $.widget('MpAmazonConnector.rktnOrderSync', {
        _create: function () {
            var self = this;
            var options = this.options;
            var modalHtml = template(
                orderFormTemplate,
                {}
            );
            $("body").on("click",'#amazon-order-import', function (event) {

                if (!$('#date_range').parents('.modal-popup').length) {
                    $('<div />').html(modalHtml)
                    .modal({
                        title: $.mage.__('Enter Date Range For Import Order From Rakuten'),
                        autoOpen: true,
                        opened: function () {
                            console.log(' opoend ');
                            var dateRange = $("#date_range").dateRange({
                                buttonText:"<?php echo __('Select Date') ?>",
                                showsTime: true,
                                timeFormat: "HH:mm:ss",
                                sideBySide: true,
                                from:{
                                    id:"date_from",
                                },
                                to:{
                                    id:"date_to"
                                }
                            });
                        },
                        buttons: [{
                         text: 'Import Order',
                            attr: {
                                'data-action': 'button'
                            },
                            'class': 'action-primary',
                            click: function () {
                                var dateFrom = $('#date_from').val();
                                var dateTo = $('#date_to').val();
                                if (dateFrom && dateTo) {
                                    $('#date_from, #date_to').removeAttr('style');
                                    importProducts(dateFrom, dateTo);
                                    this.closeModal();
                                } else {
                                    $('#date_from, #date_to').css('border', '1px solid rgb(255, 0, 0)');
                                    return false;
                                }
                            }
                        }]
                    });
                } else {
                    $('#date_range').parents('.modal-popup').addClass('_show');
                }
            });
            var importProducts = function (dateFrom, dateTo) {
                var params = [];
                params['order_from'] = dateFrom;
                params['order_to'] = dateTo;
                params['url'] = options.importUrl;
                $.ajax({
                    url: options.importUrl,
                    data: {
                        'order_from': dateFrom,
                        'order_to':dateTo,
                        'next_token' :''
                    },
                    type: 'POST',
                    dataType:'JSON',
                    showLoader: true,
                    success: function (response) {
                        // $('body').find('.popup-inner').append($('<span/>').addClass('wk-amazon-count'));
                        var notification = '';
                        if (response.error_msg==false) {
                            if (response.next_token) {
                                callAjax(params, response.next_token, response.data);
                            } else {
                                var msg='Total '+response.data +' order(s) imported in your store from Rakuten, Click on Create Imported Order In Store to create these order(s) in your store.';
                                if (response.notification) {
                                    notification= '<div style="color:red;">'+response.notification+'</div>';
                                }

                                $('<div />').html(msg+notification)
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
            }

           
            function callAjax(params, nextToken,count)
            {
                $.ajax({
                    url: params['url'],
                    data: {
                        'order_from': params['order_from'],
                        'order_to':params['order_to'],
                        'next_token' :nextToken
                    },
                    type: 'POST',
                    dataType:'JSON',
                    showLoader: true,
                    success: function (response) {
                        count = count+response.data;
                        // $('body').find('.wk-amazon-count').text(count+' Order(s) Imported');
                       if (response.next_token) {
                           callAjax(params, response.next_token, count);
                       } else {
                        //     alert({
                        //        title: 'Order Imported',
                        //        content: 'Total '+count +' order(s) imported in your store from amazon, Click on Create Imported Order In Store to create these order(s) in your store.<div style="color:red;">'+response.notification+'</div>',
                        //        actions: {
                        //            always: function (){}
                        //        }
                        //    });
                       }
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            }

            $('#run-order-profiler').on('click',function (e) {
                var width = '1100';
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
            location.reload(true);
        }
    });
    return $.MpAmazonConnector.rktnOrderSync;
});