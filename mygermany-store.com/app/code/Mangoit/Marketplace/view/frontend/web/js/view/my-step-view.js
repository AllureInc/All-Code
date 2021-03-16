define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'uiRegistry',
        "Magento_Ui/js/lib/view/utils/dom-observer",
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Customer/js/model/customer',
        'Mangoit_Marketplace/js/model/changeshippinrate',
        'Mangoit_Marketplace/js/model/showDeliveryDays',
        'Mangoit_Marketplace/js/model/changeshippingmethod',
        'Mangoit_Marketplace/js/view/checkout/summary/grand-total',
        "Magento_Checkout/js/model/shipping-save-processor",
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        //'Magento_Checkout/js/view/shipping-address/address-renderer/default',
        'mage/url',
        'mage/translate'
    ],
    function (
        ko,
        $,
        Component,
        uiRegistry,
        domObserver,
        _,
        stepNavigator,
        customer,
        changeshippinrate,
        showDeliveryDays,
        changeshippingmethod,
        grandTotal,
        shippingSaveProcessor,
        quote,
        checkoutData,
        //default,
        Url,
        $t
    ) {
        'use strict';
        var selectedMethod = ko.observable();

        var misFirstname = ko.observable();
        var reload = ko.observable(false);
        var misLastname = ko.observable('myGermany');
        var misStreet = ko.observable('Nordstr. 5');
        var misCity = ko.observable('Weimar');
        var misPostal = ko.observable('99427');
        var misCountry = ko.observable('DE');
        var misRegion = ko.observable('82');
        var misTelephone = ko.observable('123456789');
        var shippingDescUrl = Url.build('shipping-methods/');
        var billingAddTitle = ko.observable('<span class="title-number"><span>2</span></span>Billing Address');
        var warehouseSelectedMethodsTitle = ko.observable('<li class="mis-warehouse-selected"><label><span>Warehouse</span></label></li>');

        var shippingMethods = ko.observableArray([
            { "Id": "0", "Title": "Drop Shipment", "value": "dropship" },
             { "Id": "1", "Title": "Ship To Warehouse", "value": "warehouse" }
           ,
        ]);

        /**
        *
        * mystep - is the name of the component's .html template,
        * <Vendor>_<Module>  - is the name of the your module directory.
        *
        */
        return Component.extend({
            defaults: {
                template: 'Mangoit_Marketplace/mystep'
            },

            //add here your logic to display step,
            isVisible: ko.observable(true),
            dropship: ko.observable(true),
            orderTotalWIthTax: window.taxLabel,
            orderTotalWIthoutTax: $t(''),
            // orderTotalWIthoutTax: $t('Order Total'),

            /**
            *
            * @returns {*}
            */
            initialize: function () {
                this._super();
                // register your step
                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'mis_shippings',
                    //step alias
                    null,
                    //step title value
                    ''+$.mage.__('Shipping Methods'),
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    /**
                    * sort order value
                    * 'sort order value' < 10: step displays before shipping step;
                    * 10 < 'sort order value' < 20 : step displays between shipping and payment step
                    * 'sort order value' > 20 : step displays after payment step
                    */
                    9
                );
                var self = this;
                $('#newaddress-heading').on('click', function(){
                    $('.address .input-text').val('');
                });

                if (!quote.isVirtual()) {
                    jQuery('.address .input-text').val('');
                    $(document).on('change','.form-shipping-address [name=country_id]', function() {
                        if ($(".mis_shippings_select").val() != 'warehouse') {
                            var postVal = $('.form-shipping-address [name=postcode]').val();
                            jQuery('body').trigger('processStart');
                            changeshippinrate(this.value, postVal);
                            jQuery('body').trigger('processStop');
                        }
                        // jQuery('body').trigger('processStop');
                    });

                    $(document).on('blur','.form-shipping-address [name=postcode]', function() { 
                        if ($(".mis_shippings_select").val() != 'warehouse') {
                            var countryCode = $('.form-shipping-address [name=country_id]').find(":selected").val();
                            $('body').trigger('processStart');
                            changeshippinrate(countryCode, this.value);
                            jQuery('body').trigger('processStop');
                        } // jQuery('body').trigger('processStop');
                    });
                    $(document).on('click','[name=mis_shippings_radio]',function(){
                        $('body').trigger('processStart');
                        var shippingVal = this.value;
                        var vendorTomyGermany = $(this).attr('data-attr');
                        changeshippingmethod(encodeURI(this.value), encodeURI(vendorTomyGermany));
                    });

                    var self = this;
                    $(document).on('click','.shipping-address-item',function(){
                        $('body').trigger('processStart');
                        var quoteObj = quote.shippingAddress();
                        var countryCode = quoteObj.countryId;
                        var postcode = quoteObj.postcode;
                        changeshippinrate(countryCode, postcode);
                        self.setOrderTotalLabel();
                        jQuery('body').trigger('processStop');
                    });
                    
                    changeshippinrate('reset', 'reset');
                    this.addTitleNumber();
                } else {
                    $('#newaddress-heading').on('click', function(){
                        $('.address .input-text').val('');
                    });

                    self.isVisible(false);
                    domObserver.get("#shipping", function (elem) {
                        //$(elem).remove();
                        $(elem).css('visibility','hidden');
                        $(elem).addClass('shiptowarehouse-mit');
                    });
                    domObserver.get("#opc-shipping_method", function (elem) {
                        $(elem).remove();
                    });
                    domObserver.get("#iosc-billing-container", function (elem) {
                        $(elem).css('display','block');
                    });
                    domObserver.get("#iosc-billing-container div.step-title span.title-number", function (elem) {
                        $(elem).html('<span>1</span>');
                    });
                    domObserver.get("#mis_shippings div.step-title", function (elem) {
                        $(elem).prepend($("<span class='title-number'><span>" + $.mage.__("1") + "</span></span>").get(0));
                    });
                    domObserver.get("#iosc-billing-container div.step-title", function (elem) {
                        $(elem).prepend($("<span class='title-number'><span>" + $.mage.__("2") + "</span></span>").get(0));
                    });
                    domObserver.get("#checkout-payment-method-load div.step-title span.title-number", function (elem) {
                        $(elem).html('<span>2</span>');
                    });
                    self.setPaymentMethodImages();
                }
                return this;
            },
            isStepDisplayed: function () {
                return true;
            },
            /**
            * The navigate() method is responsible for navigation between checkout step
            * during checkout. You can add custom logic, for example some conditions
            * for switching to your custom step
            */
            navigate: function () {

            },
            shippingPageUrl: function () {
                return shippingDescUrl;
            },
            shippingMethods: function () {
                return shippingMethods;
            },
            selectedMethod: function () {
                return selectedMethod;
            },

            addTitleNumber: function () {
                var self = this;

                var customName = customer.customerData.firstname+' '+customer.customerData.lastname;
                misFirstname(customName);

                uiRegistry.async("checkout.steps.my-new-step")(
                    function () {
                        domObserver.get("#mis_shippings div.step-title", function (elem) {
                            $('#mis_shippings div.step-title').prepend($("<span class='title-number'><span>" + $.mage.__("1") + "</span></span>").get(0));
                             setTimeout(function(){
                               if (customer.customerData.addresses.length) {
                                    
                                    // jQuery('.mis_shippings_select').change();
                                    $('select.mis_shippings_select').trigger( "change" );
                               } else {
                                    //jQuery('.mis_shippings_select').change();
                                    $('select.mis_shippings_select').trigger( "change" );
                                
                               }
                               if (quote.isVirtual()) {
                                    // This code is used to remove extra things from the checkout when advertisement product is there
                                    $('.no-quotes-block').css('display','none');
                                    $('#iosc-billing-container').css('display','none');
                                    $('.payment-methods .title-number > span').text(4);
                                    $('.mis_shippings_select').attr('disabled', 'disabled');
                                    
                                }
                            }, 7000);
                        });
                       self.setPaymentMethodImages();
                    }
                );

               
            },

            /**
            * @returns void
            */
            setPaymentMethodImages: function () {
                var mediaUrl = window.getMediaUrl;
                domObserver.get('input[name="payment[method]"]', function (elem) {
                    var imageName = $(elem).attr('value');
                    var imgUrl = mediaUrl+"payment_methods/"+imageName+".png";
                    var imgObj = $(elem).next('.label').find('img');
                    $(elem).parent().find('img').remove();
                    $.ajax({
                        url: imgUrl,
                        type:'HEAD',
                        success: function(){
                            $(elem).next('.label').find('span:first').prepend("<img src='"+imgUrl+"' class='payment_gateway_logos' alt='Payment gateways' />");
                            $('.payment_gateway_logos').css('width','80px');
                        }
                    });
                });
            },

            /**
            * @returns void
            */
            navigateToNextStep: function () {
                stepNavigator.next();
            },
            /**
            * @returns void
            */
            changeMethod: function (el) {
                var self = this;

                var coShippingForm =  $('#co-shipping-form');
                var sameBillingAdd = $('#iosc_billingaddress');
                var shippingForm = '.form-shipping-address';
                var shippingFormTitle = "#shipping .step-title";
                var billingFormTitle = ".field-select-billing .step-title";
                var opcNewAddress = "#opc-new-shipping-address";
                var billingAddSameAsShipping = '[name=billing-address-same-as-shipping]';
                var warehouseObj = $('input[aria-labelledby="label_method_warehouse_warehouse label_carrier_warehouse_warehouse"]');
                var dropshipObj = $('input[aria-labelledby="label_method_dropship_dropship label_carrier_dropship_dropship"]');
                
                //For drop shipment shipping method
                /*$('.shipping-address-item').trigger('click');*/
                $('#newaddress-heading').on('click', function(){
                    $('.address .input-text').val(''); 
                });

                if (self.selectedMethod == 'dropship') {
                    $('.shipping-address-item').trigger('click');
                    self.dropship(true);
                    dropshipObj.parent().parent().removeClass('mis-remove');
                    warehouseObj.parent().parent().addClass('mis-remove');
                    // $('.checkout-shipping-address').removeClass('shiptowarehouse-mit');
                    $('#co-shipping-form').removeClass('shiptowarehouse-mit');
                    //changeshippinrate('dropshipment');
                    if (customer.customerData.addresses.length) {
                        var quoteObj = quote.shippingAddress();
                        var countryCode = quoteObj.countryId;
                        var postcode = quoteObj.postcode;
                        //When there is a saved address in customer address book

                        $('.shipping-address-items').css({'visibility':'unset','height':'inherit'});
                        $('.shipping-address-items').closest('.addresses').css({'visibility':'unset','height':'inherit'})
                        $('.newaddress-button-title').css('display','block');
                        $('#iosc_billingaddress').css({'visibility':'unset','height':'inherit','margin':'auto'});
                        //jQuery(opcNewAddress).css('visibility','unset');
                        //jQuery(opcNewAddress).css('display','block');
                        self.resetFormFields(opcNewAddress);
                        //self.changeFormTitles(billingFormTitle,shippingFormTitle);
                        changeshippinrate(countryCode, postcode);
                        jQuery('body').trigger('processStop');

                    } else {
                        $(coShippingForm).css({'visibility':'unset','height':'inherit','margin':'auto'});
                        $(sameBillingAdd).css({'visibility':'unset','height':'inherit','margin':'auto'});
                        if (reload()) {
                            self.resetFormFields(shippingForm);
                            $(shippingForm+' [name=country_id] ').val('').trigger('change');
                           
                        }

                        $(coShippingForm).removeClass('mis-hide-form');
                    }
                    reload(true);
                    $('.addresses').css('display','block');
                    $('.newaddress-button').css('display','block');
                    self.resetFormTitles(billingFormTitle, shippingFormTitle);
                    if($("[name=billing-address-same-as-shipping]").prop('checked') == true){
                        $(billingAddSameAsShipping).trigger('click');
                    }

                   // shippingSaveProcessor.saveShippingInformation();
                    dropshipObj.trigger( "click" );
                    $('.mis-warehouse-selected').remove();
                    $('.vendor_product_delivery_ul').remove();
                    $('#opc-shipping_method div.step-title').html("<span class='title-number'><span>3</span></span>"+$.mage.__('Shipping Method'));

                } else {
                    
                    //For Warehouse shipping method
                    $('.billing-address-form .input-text').val('');
                    warehouseObj.trigger( "click" );
                    self.dropship(false);
                    warehouseObj.parent().parent().removeClass('mis-remove');
                    dropshipObj.parent().parent().addClass('mis-remove');
                    // $('.checkout-shipping-address').addClass('shiptowarehouse-mit');
                    
                    $('#co-shipping-form').addClass('shiptowarehouse-mit');
                    if (customer.customerData.addresses.length) {
                        //When there is a saved address in customer address book
                       // jQuery('.newaddress-button').trigger('click');
                        $(shippingForm).removeClass('mis-hide-form');
                        $(shippingForm).css('visibility','unset');
                        $('.shipping-address-items').css({'visibility':'hidden','height':'0px'});
                        $('.shipping-address-items').closest('.addresses').css({'visibility':'hidden','height':'0px'});
                        $('#shipping-save-in-address-book').parent().css({'visibility':'hidden','height':'0px'});
                        $('#shipping-save-in-address-book').prop( "checked", true );
                        $('.newaddress-save-button-container').css('display','none');
                       // self.setValuesToForm(shippingForm);
                        //jQuery(opcNewAddress).css('visibility','hidden');
                        $('#iosc_billingaddress').css({'visibility':'hidden','height':'0px','margin':'0px'});
                        $(billingAddSameAsShipping).trigger('click');
                        $('.newaddress-button-title').css('display','none');
                        self.changeFormTitles(billingFormTitle,shippingFormTitle);
                        //self.resetFormTitles(billingFormTitle,shippingFormTitle);
                    } else {
                        console.log('it triggered');
                        //When there is no address either shipping or billing START
                        $(coShippingForm).addClass('mis-hide-form');
                        
                        self.setValuesToForm(shippingForm);
                        $(coShippingForm).css({'visibility':'hidden','height':'0px','margin':'0px'});
                        $(sameBillingAdd).css({'visibility':'hidden','height':'0px','margin':'0px'});

                        $(billingAddSameAsShipping).trigger('click');

                        //When there is no address either shipping or billing END
                        self.changeFormTitles(billingFormTitle,shippingFormTitle);
                    }

                    if ($('select[name ="billing_address_id"]').length != 0) {
                        $('.billing-address-form').css('display','none');
                    }

                    $('.addresses').css('display','none');
                    $('.newaddress-button').css('display','none');


                    $('.scc_shipping_methods').remove();
                    $('.scc_shipping_ul').remove();
                    $('.mis-warehouse-selected').remove();
                    $('.vendor_product_delivery_ul').remove();
                    if (!quote.isVirtual()) {
                        showDeliveryDays();
                        $('.billing-address-form .input-text').val('');
                        $('.billing-address-form .select').prop('selectedIndex', 0);
                        // jQuery('#opc-shipping_method').append('<li class="mis-warehouse-selected"><label><span>Ship To Warehouse</span></label></li>');
                    }
                    $('#opc-shipping_method div.step-title').html("<span class='title-number'><span>3</span></span>Expected Delivery");

                }
                self.setOrderTotalLabel();

                if ($('.mis_shippings_select').val() == 'warehouse') {
                    setTimeout(function(){
                        $('.scc_shipping_ul').remove();
                        $('.billing-address-form .input-text').val('');
                        console.log('== Removed ===');
                    }, 6000);
                
                }
            },

            setOrderTotalLabel: function () {
                var self = this;
                // For Order total label based on country
                var shipCountryCode = $('.form-shipping-address [name=country_id]');
                var billcode = $('.billing-address-form [name=postcode]');
                var sameBillingAdd = $('#iosc_billingaddress');
                var countryArr = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB'];
                var shippingAddObj = quote.shippingAddress();
                var billingAddObj = quote.billingAddress();
                var countryCode = shippingAddObj.countryId;
                var billingCountryCode = billingAddObj.countryId;

                //if new address link is clicked then Priority 1
                $(document).on('click','.newaddress-button', function() {
                    var checkboxVal = $("[name=billing-address-same-as-shipping]").prop('checked');
                    if(!checkboxVal){
                        setTimeout(function(){
                            if($('.newaddress-button').hasClass('_active')){
                                countryCode = shipCountryCode.val();
                            }else{
                                countryCode = shippingAddObj.countryId;
                            }
                            if (($.inArray(countryCode, countryArr)> 0))
                            {
                                grandTotal().orderlabelValue(self.orderTotalWIthTax);
                            } else {
                                grandTotal().orderlabelValue(self.orderTotalWIthoutTax);
                            };
                        }, 1000);
                    }
                }); 
                shipCountryCode.on('change',function() {
                    var checkboxVal = $("[name=billing-address-same-as-shipping]").prop('checked');
                    if(!checkboxVal){
                        countryCode = this.value;
                        if (($.inArray(countryCode, countryArr)> 0))
                        {
                            grandTotal().orderlabelValue(self.orderTotalWIthTax);
                        } else {
                            grandTotal().orderlabelValue(self.orderTotalWIthoutTax);
                        }
                    }
                });
                if (customer.customerData.addresses.length) {
                    if (($.inArray(countryCode, countryArr)> 0))
                    {
                        grandTotal().orderlabelValue(self.orderTotalWIthTax);
                    } else {
                        grandTotal().orderlabelValue(self.orderTotalWIthoutTax);
                    }
                } 
                //When address is not available Start
                //Billing address form is open
                $(document).on('change', "[name=billing_address_id]", function() { 
                    setTimeout(function(){
                        if($('.billing-address-form:visible').is(':visible'))
                        {
                            var countryCode = billcode.val();
                        } else {
                            /*if (billingAddObj.countryId) {
                                var countryCode = billingAddObj.countryId;
                            }*/
                            if (billingCountryCode != 'undefined') {
                                var countryCode = billingCountryCode;
                            }
                        }
                        if (($.inArray(countryCode, countryArr)> 0))
                        {
                            grandTotal().orderlabelValue(self.orderTotalWIthTax);
                        } else {
                            grandTotal().orderlabelValue(self.orderTotalWIthoutTax);
                        }
                    }, 1000);

                });

                $(document).on('change', "[name=billing_address_id]" , function(){
                    var selected_text = $("[name=billing_address_id] option:selected:first").text();
                    if(selected_text == 'Neue Adresse' || selected_text == 'New Address') {
                        console.log('new');
                        $('.billing-address-form').css('display','block');
                    } else {
                        console.log('listed');
                        $('.billing-address-form').css('display','none');

                    }
                });

                $("[name=billing-address-same-as-shipping]").change(function() {
                    if(this.checked) {
                        var countryCode = billcode.val();
                        if($('.billing-address-form:visible').length == 0)
                        {
                            if($.isEmptyObject(billingAddObj)){
                                var countryCode = shippingAddObj.countryId;
                            } else {
                                /*if (billingAddObj.countryId) {
                                    var countryCode = billingAddObj.countryId;
                                }*/
                                if (billingCountryCode) {
                                    var countryCode = billingCountryCode;
                                }
                            }
                        }
                    } else {
                        var countryCode = shippingAddObj.countryId;
                        if($('.newaddress-button').hasClass('_active')){
                            countryCode = shipCountryCode.val();
                        }
                    }
                    if (($.inArray(countryCode, countryArr)> 0))
                    {
                        grandTotal().orderlabelValue(self.orderTotalWIthTax);
                    } else {
                        grandTotal().orderlabelValue(self.orderTotalWIthoutTax);
                    }
                });

                $(document).on('change','.billing-address-form  [name=country_id]', function() {
                    var countryCode = this.value;
                    if (($.inArray(countryCode, countryArr)> 0))
                    {
                        grandTotal().orderlabelValue(self.orderTotalWIthTax);
                    } else {
                        grandTotal().orderlabelValue(self.orderTotalWIthoutTax);
                    }
                });

                $(document).on('load', function(){
                    $('.checkout-shipping-method .step-title').append($.mage.__('Shipping Methods'));
                });
            },

            setValuesToForm: function (shippingFormSelector) {
                $(shippingFormSelector+' [name=firstname] ').trigger('focus').val(misFirstname());
                $(shippingFormSelector+' [name=lastname] ').trigger('focus').val(misLastname());
                $(shippingFormSelector+' [name="street[0]"] ').trigger('focus').val(misStreet());
                $(shippingFormSelector+' [name=city] ').trigger('focus').val(misCity());
                $(shippingFormSelector+' [name=postcode] ').trigger('focus').val(misPostal());

                $(shippingFormSelector+' [name=telephone] ').trigger('focus').val(misTelephone());
                setTimeout(function(){
                    $(shippingFormSelector+' [name=country_id] ').val(misCountry()).trigger('change');
                }, 3000);

                setTimeout(function(){
                    $(shippingFormSelector+' [name=region_id] ').val(misRegion()).trigger('change');
                }, 3500);
            },
            resetFormFields: function (resetFormSelector) {
                $(':input',resetFormSelector)
                 .not(':button, :submit, :reset, :hidden')
                 .val('')
                 .removeAttr('checked')
                 .removeAttr('selected');
                $(resetFormSelector+' *').each(function(){
                   $(this).trigger('focus');
                });
            },
            changeFormTitles: function (billingFormTitle, shippingFormTitle) {
                $(billingFormTitle).html('<span class="title-number"><span>2</span></span>'+$.mage.__('Billing Address'));
                $(shippingFormTitle).first().css('display','none');
            },
            resetFormTitles: function (billingFormTitle, shippingFormTitle) {
                $(billingFormTitle).html('Billing Address');
                $(shippingFormTitle).first().css('display','block'); 
            }
        });
    }
);