/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/

 require(['jquery', 'mage/url', 'mage/storage','Magento_Ui/js/lib/view/utils/dom-observer', 'Magento_Ui/js/modal/modal', 'jquery/ui'],
    function($, Url, storage, domObserver, modal){ 
    $(document).on('click', '.mis_sensitive_attr', function(){
        var urlToPost  = $(this).attr('id');
        var self = this;
        console.log(urlToPost);
        $('<div id="mis_attributs_table" style="display:none;"></div>').appendTo(this);
        $.ajax({
            type: "POST",
            url: urlToPost,
            async : true,
            showLoader : true,
            data: {form_key: window.FORM_KEY},
            success: function(data)
            {
                $('#mis_attributs_table').append(data);
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    buttons: [{
                        text: $.mage.__('Okay'),
                        class: 'mymodal1',
                        click: function () {
                            this.closeModal();
                        }
                    }]
                };

                var popup = modal(options, $('#mis_attributs_table'));
                $("#mis_attributs_table").modal("openModal");
            }
        });
    });

    //Vendor delivery days code - Start
    var status = true;
   $(document).ready(function(){
       selector = 'select[name="product[delivery_days_from]"]' ;
       time = 100 ;
       waitForElementToDisplay(selector,time);
   });
   function waitForElementToDisplay(selector, time) {
       if(document.querySelector(selector)!=null) {
           if (status) {
               var deliveryFrom = $('select[name="product[delivery_days_from]"]').val();
               status = false;
               changeDeliveryToOptions(deliveryFrom);
           }
           return;
       }
       else {
           setTimeout(function() {
               waitForElementToDisplay(selector, time);
           }, time);
       }
   }

   function changeDeliveryToOptions(garmenttype){
        $('select[name="product[delivery_days_from]"]').on('change', function(){
            console.log('Changed');
            var deliveryFrom = parseInt($(this).val());
            $('select[name="product[delivery_days_to]"] option').each(function(){
                var deliveryTo = parseInt($(this).val());
                if (deliveryTo != '' && (deliveryTo <= deliveryFrom)) {
                    $(this).prop('disabled', 'disable');
                } else {
                    $(this).prop('disabled', '');
                }
                $('select[name="product[delivery_days_to]"]').val(deliveryTo).trigger('change');
            });
        });
   }
    //Vendor delivery days code - End
 });
