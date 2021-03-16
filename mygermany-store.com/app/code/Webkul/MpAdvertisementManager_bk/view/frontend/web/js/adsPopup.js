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
      'handlebars',
      'Magento_Ui/js/modal/alert',
      'Magento_Ui/js/modal/confirm',
      'Magento_Ui/js/modal/modal',
      "mage/template",
      'mage/mage',
      'jquery/ui'

    ],
    function ($, $t, $h, alert, confirm, modal, template) {
        'use strict';
 
        $.widget(
            'webkul.adsPopup',
            {
          
                _create: function () {
              
                    self = this;

                    var htmlTemplate = template("#ads-popup-html");
                    var html = htmlTemplate({});
          
                    if (html != '') {
                        self.createPopup(html);
                    }
                },

                createPopup: function (html) {
                    var options = {
                        type: "popup",
                        autoOpen:false,
                        innerScroll:false,
                        responsive: true
                    };

                    var popup = modal(options, html);
                    popup.openModal();
                }
            }
        );

        return $.webkul.adsPopup;
    }
);  