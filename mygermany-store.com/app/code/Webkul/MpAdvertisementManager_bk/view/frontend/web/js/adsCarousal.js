/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
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
      'Webkul_MpAdvertisementManager/js/owlJs',
      'mage/mage',
      'jquery/ui'

    ],
    function ($,$t,$h,alert,confirm) {
        'use strict';
        $.widget(
            'webkul.adsCarousal',
            {
                _create: function () {
                    var self = this;
                    var position = self.options.carousalData.position;
                    var adscount = self.options.carousalData.adscount;
                    var carousalId = self.options.carousalData.carousalId;
                    var autoplaytime = self.options.carousalData.autoplaytime;
                    var items = 1;
                    var loop = true;
                    if ((position == 4 || position == 6 || position == 12 || position == 14) && adscount>=3) {
                        items = 3;
                    }
                    if (adscount < 2 ) {
                        loop = false;
                    }
                    var owl = $(carousalId);
                    owl.owlCarousel({
                    items:items,
                    loop:loop,
                    margin:10,
                    autoplay:true,
                    autoplayTimeout:autoplaytime,
                    autoplayHoverPause:false
                    });
                }
            }
        );

        return $.webkul.adsCarousal;
    }
);  