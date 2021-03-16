/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    'mage/loader',
    'mage/template',
    'mage/calendar',
], function ($, alert, loader, template, calendar) {
    "use strict";
    var popup,modelWrapper;
    $.widget('MpAmazonConnector.orderimport', {
        _create: function () {
            var self = this;
            var options = this.options;
            $("body").find('#order_from').calendar({
                dateFormat: "mm/dd/yy",
                showsTime: true,
                timeFormat: "HH:mm:ss",
                sideBySide: true,
                closeText: "Done",
                selectOtherMonths: true,
                onClose: function ( selectedDate ) {
                //   $("#order_to").datepicker("option", "minDate", selectedDate);
                }
            });
            $("body").find('#order_to').calendar({
                dateFormat: "mm/dd/yy",
                showsTime: true,
                timeFormat: "HH:mm:ss",
                sideBySide: true,
                closeText: "Done",
                selectOtherMonths: true,
                onClose: function ( selectedDate ) {
                //   $("#order_from").datepicker("option", "minDate", selectedDate);
                }
            });

            modelWrapper = $('#amazon-order-popup');
            var orderRangeForm = $('#order-date-range-form');
            orderRangeForm.mage('validation', {});
            var self = this;
            var options = this.options;
            $("body").on("click",'#mpamazonconnect-accounts-order-import', function (event) {
                $('.wk-mp-model-popup').addClass('_show');
                $('#amazon-order-popup').show();
            });


            $('body').on('click','.wk-close',function () {
                $('.page-wrapper').css('opacity','1');
                $('#resetbtn').trigger('click');
                $('#amazon-order-popup').hide();
                $('#order-date-range-form .validation-failed').each(function () {
                    $(this).removeClass('validation-failed');
                });
                $('#order-date-range-form .validation-advice').each(function () {
                    $(this).remove();
                });
            });

            $('body').on('click','#range-button', function (event) {
                event.stopPropagation();
                event.preventDefault();
                var params = [];
                var dateFrom = $("input[name='order_from']").val();
                var dateTo = $("input[name='order_to']").val();
                params['order_from'] = dateFrom;
                params['order_to'] = dateTo;
                params['url'] = options.importUrl;
                $('.wk-close,#resetbtn').trigger('click');
                $('#amazon-order-popup').remove();
                if (dateFrom && dateTo) {
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
                                    $('.loading-mask').hide();
                                    if(parseInt(response.data)) {
                                        var msg='Total '+response.data +' order(s) imported in your store from amazon, Click on Create Imported Order In Store to create these order(s) in your store.';    
                                    } else {
                                        var msg='No Amazon order found in given date range.';
                                    }
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
                                $('.loading-mask').hide();
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
                            $('.amazon-order-popup-container').append(modelWrapper);
                            self.initializeCalender();

                        },
                        error: function (error) {
                            console.log(error);
                        }
                    });
                } else {
                    $('#date_from, #date_to').css('border', '1px solid rgb(255, 0, 0)');
                }
            });            
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

            $('#mpamazonconnect-import-order-create').on('click',function (e) {
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
        },initializeCalender : function () {
            $("body").find('#order_from').calendar({
                dateFormat: "mm/dd/yy",
                showsTime: true,
                timeFormat: "HH:mm:ss",
                sideBySide: true,
                closeText: "Done",
                selectOtherMonths: true,
                onClose: function ( selectedDate ) {
                //   $("#period_date_end").datepicker("option", "minDate", selectedDate);
                }
            });
            $("body").find('#order_to').calendar({
                dateFormat: "mm/dd/yy",
                showsTime: true,
                timeFormat: "HH:mm:ss",
                sideBySide: true,
                closeText: "Done",
                selectOtherMonths: true,
                onClose: function ( selectedDate ) {
                //   $("#period_date_end").datepicker("option", "minDate", selectedDate);
                }
            });
        },
        afterChildClose:function () {
            if (popup.location != "about:blank") {
                $('#amazon_order_map_grid button[title="Reset Filter"]').trigger('click');
            }
        }
    });
    return $.MpAmazonConnector.orderimport;
});