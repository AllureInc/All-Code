require([
    'jquery',
    'mage/url'
], function($, url){
    window.mainArray = [];
    var textProductIdArray = '';
    var visualProductIdArray = '';
    var selectProductIdArray = '';
    var textClick = 0;
    var visualClick = 0;
    var selectClick = 0;
    /* Function for inserting data when click on text swatch options of product */
    $("body").on('click', ".swatch-option", function(){

        var isTextOption = $(this).hasClass('text');
        var jsonConfigData = JSON.parse(window.jsonConfigArray);
        var selectedSwatchValue = $(this).attr('data-option-label');
        var dataOptionId = parseInt($(this).attr('data-option-id'));
        var attributeId = parseInt($(this).parent('.swatch-attribute-options').parent('.swatch-attribute').attr('data-attribute-id'));
        var optionsArray = jsonConfigData['attributes'][attributeId]['options'];
        var childProductId = '';
        var product = '';
        var commonProductId = '';
        $.each(optionsArray, function( key, value ) {
            if(value['id'] == dataOptionId) {
               if(isTextOption == true) {
                   
                textProductIdArray = optionsArray[key]['products'];

                /* Code for pushing array of product*/
                var data = {
                    'text' : textProductIdArray
                }
                if(textClick>0){
                  $(mainArray).each(function (index){
                  if(mainArray[index] != undefined) {
                    if(mainArray[index]['text']){
                        mainArray.splice(index,1);
                        return true;
                        }
                     }
                   });
                  mainArray.push(data);
                } else {
                  mainArray.push(data);
                }
                textClick++;
                /* Code for pushing array of product*/
             } else {
                visualProductIdArray = optionsArray[key]['products'];

                /* Code for pushing array of product*/
                var data = {
                    'visual' : visualProductIdArray
                }
                 if(visualClick>0){
                  $(mainArray).each(function (index){
                  if(mainArray[index] != undefined) {
                    if(mainArray[index]['visual']){
                        mainArray.splice(index,1);
                        return true;
                        }
                     }
                   });
                  mainArray.push(data);
                } else {
                  mainArray.push(data);
                }
                visualClick++;
                /* Code for pushing array of product*/
             }
            }
        });
        var data = window.category_data;
        var hasTextOptSelected = $("div").hasClass("swatch-option text selected");
        var hasVisualOptSelected = $("div").hasClass("swatch-option selected");
        if(hasTextOptSelected && hasVisualOptSelected == true) {
            /* Comparing common product id */
            var arrays =[textProductIdArray,visualProductIdArray];
                   var result = arrays.shift().filter(function(v) {
                    return arrays.every(function(a) {
                        return a.indexOf(v) !== -1;
                });
            });
            commonProductId = result[0];
            var simpleProduct = window.child_product_data[0][commonProductId];
            result = addVariantInDataLayer(selectedSwatchValue, data.eventLabel, simpleProduct);
        } else if($("div").hasClass("text") == false || $("div").hasClass("text") == true){
            var simpleProductIdToAdd = '';
            var simpleProductToAdd = '';
            $.each(optionsArray, function( key, value ) {
                if(value['id'] == dataOptionId) {
                    simpleProductIdToAdd = optionsArray[key]['products'];
                    simpleProductToAdd = window.child_product_data[0][simpleProductIdToAdd]
                    result = addVariantInDataLayer(selectedSwatchValue, data.eventLabel, simpleProductToAdd);
                }
            }); 
        } 
    });
    /* End function */

    /* Function for inserting data when change on select swatch options of product */
    $("body").on('change', ".swatch-select", function(){
        var jsonConfigData = JSON.parse(window.jsonConfigArray);
        var selectedSwatchValue = $(".swatch-select option:selected").html();
        var dataOptionId = parseInt(this.value);
        var attributeId = parseInt($(this).parent('.swatch-attribute-options').parent('.swatch-attribute').attr('data-attribute-id'));
        var optionsArray = jsonConfigData['attributes'][attributeId]['options'];
        var childProductId = '';
        var product = '';
        $.each(optionsArray, function( key, value ) {
            if(value['id'] == dataOptionId) {
               selectProductIdArray = optionsArray[key]['products'];
            }
        });
        /*Code for pushing product data in array*/
        var data = {
            'select' : selectProductIdArray
        }
        if(visualClick>0){
          $(mainArray).each(function (index){
          if(mainArray[index] != undefined) {
            if(mainArray[index]['select']){
                mainArray.splice(index,1);
                return true;
                }
             }
           });
          mainArray.push(data);
        } else {
          mainArray.push(data);
        }
        selectClick++;
        /*Code for pushing product data in array*/
        var hasTextOptSelected = $("div").hasClass("swatch-option text selected");
        var hasVisualOptSelected = $("div").hasClass("swatch-option selected");
        var data = window.category_data;
        result = addVariantInDataLayer(selectedSwatchValue, data.eventLabel, product);
    });
    /* End function */

    /* Function for adding add to cart data when click from product detail page */
    $("body").on('click', "#product-addtocart-button", function(){
        var d = new Date();
        console.log(' Button Clicked on : '+d);
        var productType = window.product_type;
        if(productType == 'configurable'){
            var commonProductId = 0;
            var arrays =[];
            $.each(window.mainArray, function( key, value ) {
                
                if(value['select'] != undefined){
                    arrays.push(value['select']);
                } else if(value['text'] != undefined){
                    arrays.push(value['text']);
                } else if (value['visual'] != undefined) {
                    arrays.push(value['visual']);
                }
                var result = arrays.shift().filter(function(v) {
                    return arrays.every(function(a) {
                        return a.indexOf(v) !== -1;
                });
               });
               commonProductId = result[0];
            });
            setTimeout(getCartProductsData({'event_call': 0, 'product_type': window.product_type, 'product_id': window.commonProductId}), 3000);
        } else {
            setTimeout(getCartProductsData({'event_call': 0, 'product_type': window.product_type, 'product_id': window.product_id}), 3000);
        }
    });

    /* Function for remove data from cart */
    $('.showcart').click(function() { 
        setTimeout(addClassInDeleteButton(), 2000);
    });
    /* Function for remove data from cart ends */
    
    /* Function for removing product and updating cart */
    function addClassInDeleteButton() {
        $('.action.delete').click(function() {
            $(document).ajaxComplete(function(event,xhr,settings){
                var ajaxUrl = url.build('checkout/sidebar/removeItem/');
                if(ajaxUrl == settings.url){
                    setTimeout(updateCartData({'event_call': 1}), 800);
                }
            });
        });

        $('.cart-item-qty').on('click change', function(){
            var counter = 1;
            if (update_cart == true && counter == 1) {
                $('.update-cart-item').on('click', function(){
                    updateQuantityOfProducts();
                    counter++;
                    setTimeout(
                        function(){
                            counter = 1;
                        }, 
                        3000
                    );
                });

            }
        });
    }
    /* Function for removing product and updating cart ends */

    /* Function for updating product quantity of the cart */
    function updateQuantityOfProducts()
    {
        $(document).ajaxComplete(function(event,xhr,settings){
            var ajaxUrl = url.build('checkout/sidebar/updateItemQty/');
            if(ajaxUrl == settings.url){
                updateCartData({'event_call': 2});
            }
        });
    }
    /* Function for updating product quantity of the cart ends */
});