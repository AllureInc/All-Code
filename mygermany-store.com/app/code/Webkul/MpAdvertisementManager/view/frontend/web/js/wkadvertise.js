/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerTaxManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define(
    [
      'jquery',
      'mage/translate',
      'Magento_Ui/js/modal/alert',
      'Magento_Ui/js/modal/confirm',
      'mage/mage',
      'jquery/ui'

    ],
    function ($,$t,alert,confirm) {
        'use strict';
        $.widget(
            'webkul.wkadvertise',
            {
                _create: function () {
                    var self = this;

                    $("body").on(
                        "click",
                        ".enable-ads-demo",
                        function () {
                            self.setDemoStatus($(this));
                        }
                    );


                },

                setDemoStatus: function (element) {
                    var self = this;
                    var status = element.data('status');
                    $.ajax(
                        {
                            url: self.options.advertiseData.data.enableDemoUrl,
                            method:"post",
                            dataType:'json',
                            data:{
                                isEnable:element.data("status")
                            },
                            beforeSend: function () {
                                $("body").trigger('processStart');
                            },
                            complete: function () {
                                $("body").trigger('processStop');
                            },
                            success: function (res) {

                                if (res.status == 1) {
                                    element.val($t("Disable Ads Demo"));
                                    element.html($t("Disable Ads Demo"));
                                    element.data('status', 1);
                                } else {
                                    element.val($t("Enable Ads Demo"));
                                    element.html($t("Enable Ads Demo"));
                                    element.data('status', 0);
                                }
                                alert({
                                    content:res.message,
                                    actions: {
                                        always: function () {
                                            if ($('.modals-overlay').length) {
                                                $('.modals-overlay').remove();
                                            }
                                        }
                                        }
                                    });

                            },
                            error:function () {
                                alert({content:$t("not able to switch status")});
                            }
                        }
                    );
                }
            }
        );

        return $.webkul.wkadvertise;
    }
);
