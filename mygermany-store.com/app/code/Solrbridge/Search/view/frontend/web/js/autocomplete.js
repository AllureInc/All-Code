define(['jquery', 'mage/translate', 'mage/loader'], function ($, $t, loader) {
    "use strict";
    var SolrBridgeSearch = function (el, config) {
        this.element = el;
        this.id = this.element.getAttribute('id');
        this.element.setAttribute('autocomplete','off');
        this.element.setAttribute('data-solrbridge-autocomplete', 'true');
        this.responseData = [];
        this.selectedIndex = 0;
        this.loadedProductIds = [];
        this.currentValue = this.element.value;
        this.page = 1;
        this.requests = [];
        this.cachedResponse = {};
        this.onChangeTimeout = null;
        var defaultConfig = {
            'searchLabel': '[data-role=minisearch-label]',
            'deferRequestBy': 300
        };
        
        if (config) {
            this.config = $.extend({}, defaultConfig, config);
        } else {
            this.config = defaultConfig;
        }
        this.init();
    }
    
    SolrBridgeSearch.instances = [];
    
    SolrBridgeSearch.getInstance = function (id) {
        var instances = SolrBridgeSearch.instances;
        var i = instances.length;
        while (i--) {
            if (instances[i].id === id) {
                return instances[i];
            }
        }
    };

    SolrBridgeSearch.highlight = function (value, re) {
        value = value.toString();
        return value.replace(re, function (match) {
            return '<strong>' + match + '<\/strong>';
        });
    };
    
    SolrBridgeSearch.prototype = {
        enabled: true,
        killerFn: null,
        loadedFilter: false,
        className: 'solrbridge-autocomplete solrbridge-autocomplete-search',
        searchLabel: null,
        filterQuery: {},
        keyborad: {KEY_ESC: 27,KEY_TAB: 9,KEY_RETURN: 13,KEY_LEFT: 37,KEY_UP: 38,KEY_RIGHT: 39,KEY_DOWN: 40},
        init: function () {
            var me = this;
            //killer function definition
            this.killerFn = function (e) {
                if ($(e.target).closest('.solrbridge-autocomplete').length === 0 && !$(e.target).data('solrbridge-autocomplete')) {
                    me.killSuggestions();
                    me.disableKillerFn();
                }
            }
            
            //Create a div element
            this.box = $('<div/>').css({
                'position':'absolute',
                'display':'none',
                'z-index':'9998'
            }).addClass(this.className);
            
            //Put some children div into parent div
            var divInner = $('<div/>', {id: 'sbs_'+this.id+'_autocomplete_box'}).addClass('sbs_autocomplete_inner');
            
            var divInnerRight = $('<div/>', {id: 'sbs_'+this.id+'_autocomplete_right'}).addClass('sbs_autocomplete_inner_right');
            divInner.append(divInnerRight);
    
            var divInnerLeft = $('<div/>', {id: 'sbs_'+this.id+'_autocomplete_left'}).addClass('sbs_autocomplete_inner_left');
            divInner.append(divInnerLeft);
            
            var divOuter = $('<div/>', {id: 'sbs_'+this.id+'_autocomplete_outer'}).addClass('sbs_autocomplete_outer');
            $(divOuter).append(divInner);
            
            var tailIcon = $('<span/>', {id: 'sbs_'+this.id+'_tail_icon'}).addClass('sbs_autocomplete_tail_icon');
            divOuter.append(tailIcon);
            
            var tailIconInner = $('<span/>', {id: 'sbs_'+this.id+'_tail_icon_inner'}).addClass('sbs_autocomplete_tail_icon_inner');
            divOuter.append(tailIconInner);
            
            var divCloseButton = $('<div/>', {id: 'sbs_'+this.id+'_closed_button'}).addClass('sbs_autocomplete_close_button');
            divOuter.append(divCloseButton);
            
            divOuter.append('<br style="clear: both" />');
    
            this.box.append(divOuter);
    
            //Append all div to body tag
            this.config.container = $(this.config.container);
            $(document.body).append(this.box);
            //Get the div ID
            
            $(document).on('click', '.solrbridge-autocomplete-search .suggested-item', function(event){
                $('body').loader('show');
            });
            
            this.divId = this.box.id;
            this.rightSideBar = $('#sbs_'+this.id+'_autocomplete_right');
            this.container = $('#sbs_'+this.id+'_autocomplete_box');
            this.closebutton = $('#sbs_'+this.id+'_closed_button');
            this.leftSideBar = $('#sbs_'+this.id+'_autocomplete_left');
            this.innerContainer = $('#sbs_'+this.id+'_autocomplete_box');
            this.outerContainer = $('#sbs_'+this.id+'_autocomplete_outer');
            this.tailIcon = $('#sbs_'+this.id+'_tail_icon');
            this.tailIconInner = $('#sbs_'+this.id+'_tail_icon_inner');
    
            if (this.config.sideBarWidth) {
                this.leftSideBar.css({width:'100%'});
            }
            if (this.config.boxWidth) {
                this.container.css({width:(this.config.boxWidth)+'px'});
            }
            this.container.css({padding:'0'});
    
            if (this.config.advanced_mode == 1) {
                this.leftSideBar.show();
            } else {
                this.leftSideBar.remove();
            }
            
            //resize window RWD
            this.fixPosition();
            
            this.initSearchBox();
            
            $(this.element).bind('keydown', this.onKeyPress.bind(this));
            $(this.element).bind('keyup', this.onKeyUp.bind(this));
            $(this.element).bind('click', this.onClick.bind(this));
            $(this.element).bind('blur', this.enableKillerFn.bind(this));
            $(this.element).bind('focus', this.fixPosition.bind(this));
            $(this.closebutton).bind('click', this.closeAll.bind(this));
            if (undefined !== this.config.searchLabel) {
                this.searchLabel = $(this.config.searchLabel);
                $(this.searchLabel).bind('click', this.onSearchLabelClick.bind(this));
            }
            $(window).bind('resize', this.fixPosition.bind(this));
            
            this.instanceId = SolrBridgeSearch.instances.push(this) - 1;
        },
        closeAll: function () {
            this.hide();
        },
        hide: function () {
            this.box.hide();
        },
        show: function () {
            if (!$(this.box).is(":visible")) {
                this.box.show();
            }
        },
        enableKillerFn: function () {
            $(document.body).bind('click', this.killerFn.bind(this));
        },
        disableKillerFn: function () {
            //Event.stopObserving(document.body, 'click', this.killerFn);
            //Not sure the purpose of this...
        },
        killSuggestions: function () {
            this.stopKillSuggestions();
            this.intervalId = window.setInterval(
                function () {
                    this.hide();
                    this.stopKillSuggestions();
                }.bind(this),
                1
            );
        },
        stopKillSuggestions: function () {
            window.clearInterval(this.intervalId);
        },
        fixPosition: function () {
            var offset = $(this.element).offset();
            var top = offset.top + $(this.element).outerHeight();

            //var windowSize = document.viewport.getDimensions();
            var windowWidth = window.innerWidth;

            //var boxWidth = $(this.element).width();
            var boxWidth = $(this.element).outerWidth();

            if (parseInt(this.config.boxWidth) > 0 && parseInt(windowWidth) >= parseInt(this.config.boxWidth)) {
                boxWidth = this.config.boxWidth;
                var left = offset.left - parseInt(this.config.boxWidth) + $(this.element).outerWidth();
            } else {
                boxWidth = $(this.element).outerWidth();
                var left = offset.left;
            }
            var x = windowWidth - boxWidth;

            if (x < 0) {
                boxWidth = $(this.element).outerWidth();
                var left = offset.left;
            }
            this.container.css({width:(boxWidth)+'px'});
            $(this.box).css({ top: (top) + 'px', left: (left) + 'px' });
            this.closebutton.css({ top: '-10px', left: (boxWidth - 12)+ 'px' });
        },
        onSearchLabelClick: function (e) {
            var label = e.target;
            $(label).toggleClass('active');
        },
        onKeyPress: function (e) {
            if (!this.enabled) {
                return;
            }
            var keyCode = e.keyCode || e.which;
            var keyboard = this.keyborad;
            switch (keyCode) {
                case keyboard.KEY_ESC:
                    this.element.value = this.currentValue;
                    this.hide();
                    break;
                case keyboard.KEY_TAB:
                case keyboard.KEY_RETURN:
                    //if ($(e.target).is(this.element)) {
                    if (this.selectedIndex == -1) {
                        //This is to allow user hit enter to submit search form
                        return;
                    }
                    this.enterSelect();
                    break;
                case keyboard.KEY_UP:
                    this.moveUp();
                    break;
                case keyboard.KEY_DOWN:
                    this.moveDown();
                    break;
                default:
                    return;
            }
            e.preventDefault();
            e.stopPropagation();
        },
        onKeyUp: function (e) {
            var me = this;
            var keyboard = this.keyborad;
            switch (e.keyCode) {
                case keyboard.KEY_UP:
                case keyboard.KEY_DOWN:
                    return;
            }
            //clearInterval(this.onChangeInterval);
            
            clearTimeout(me.onChangeTimeout);
            
            if (this.currentValue !== this.element.value) {
                if (this.config.deferRequestBy > 0) {
                    // Defer lookup in case when value changes very quickly:
                    me.onChangeTimeout = setTimeout(function () {
                        me.onValueChange();
                    }, me.config.deferRequestBy);
                    
                    /*this.onChangeInterval = setInterval((function () {
                        this.onValueChange();
                    }).bind(this), this.config.deferRequestBy);
                        */
                } else {
                    this.onValueChange();
                }
            }
        },
        onValueChange: function () {
            this.page = 1;
            clearInterval(this.onChangeInterval);
            if (this.element.value !== this.currentValue) {
                this.loadedFilter = false;
                this.filterQuery = {};
            }
            this.currentValue = this.element.value;
            this.selectedIndex = -1;
            if (this.ignoreValueChange) {
                this.ignoreValueChange = false;
                return;
            }
            this.suggestions = [];
            if (this.currentValue === '' || this.currentValue.length < this.config.minChars) {
                this.hide();
            } else {
                this.getSuggestions();
            }
        },
        onClick: function () {
            this.page = 1;
            this.suggestions = [];
            if (this.currentValue === '' || this.currentValue.length < this.config.minChars) {
                this.hide();
            } else {
                this.getSuggestions();
            }
        },
        moveUp: function () {
            this.adjustScroll(-1);
        },
        moveDown: function () {
            this.adjustScroll(1);
        },
        adjustScroll: function (direction) {
            //calculate item index
            var items = this.rightSideBar.children();
            var index = this.selectedIndex;

            //until reach the first / last one
            if ((direction < 0 && this.selectedIndex == -1) || (direction > 0 && this.selectedIndex == (items.length -1))) {
                //Reach top or reach bottom - do no thing
            } else {
                if (direction > 0) {
                    this.selectedIndex++;
                } else {//Up
                    this.selectedIndex--;
                }
            }
            var activeItem = this.activate(this.selectedIndex);
            
            if (!activeItem) {
                activeItem = this.element;
            }
            
            var offsetTop,
                upperBound,
                lowerBound,
                heightDelta = $(activeItem).outerHeight();
                
            offsetTop = $(this.container).offset().top + activeItem.offsetTop + heightDelta;
            
            if ($(activeItem).is(this.element)) {
                $(window).scrollTop($(this.element).offset().top);
                return;
            }
            
            upperBound = $(window).scrollTop();
            lowerBound = upperBound + $(this.container).outerHeight() - heightDelta;
            lowerBound = $(window).scrollTop()+$(window).height();

            if (offsetTop < upperBound) {
                //$(this.container).scrollTop(offsetTop);
                var windowTop = $(window).scrollTop();
                $(window).scrollTop(windowTop-heightDelta);
            } else if (offsetTop > lowerBound) {
                var windowTop = $(window).scrollTop();
                $(window).scrollTop(windowTop+heightDelta);
            }
        },
        activate: function (index) {
            var divs = this.rightSideBar.children();
            divs.each(function (key, item) {
                $(item).removeClass('selected');
            });
            var activeItem = divs[index];
            $(activeItem).addClass('selected');
            return activeItem;
        },
        enterSelect: function () {
            var items = this.rightSideBar.children();
            var selectItem = items[this.selectedIndex];
            var selectedItemNodeName = $(selectItem).prop('nodeName');
            
            if (selectedItemNodeName.toLowerCase() == 'a'){
                //return $(selectItem).trigger('click');
                if ($(selectItem).attr('href') !== '#') {
                    window.location.href = $(selectItem).attr('href');
                    return;
                } else {
                    $(selectItem).trigger('click');
                    return;
                }
            } else {
                var firstChild = $(selectItem).children().first()
                if (undefined !== firstChild) {
                    if ($(firstChild).prop('nodeName').toLowerCase() == 'a') {
                        $(firstChild).trigger('click');
                    }
                }
            }
            
            if ($(selectItem).attr('item-type') == 'product') {
                //Redirect to product detail
                //alert('redirect to product');
            } else if ( $(selectItem).attr('item-type') == 'keyword' ) {
                //alert('redirect to keyword');
                this.redirectKeyword(selectItem);
            }
        },
        filterQueryToString: function () {
            return JSON.stringify(this.filterQuery);
        },
        getSuggestions: function (params = {}) {
            if (this.currentValue == this.config.searchTextPlaceHolder) {
                return false;
            }
            var timestamp = new Date().getTime();
            
            //For category filter
            if (document.getElementById('sb-search-category-select-insivisible')) {
                var value = $('#sb-search-category-select-insivisible').val();
                
                var key = 'cat';
                
                if (undefined !== this.filterQuery[key] && this.filterQuery[key].indexOf(value) == -1) {
                    if (undefined !== key && undefined !== value) {
                        this.filterQuery[key].push(value);
                    }
                } else {
                    if (undefined !== key && undefined !== value) {
                        this.filterQuery[key] = [value];
                    }
                }
            }
            
            var requestParams = {
                'q':this.currentValue,
                'storeid':this.config.store_id,
                'customergroupid':this.config.customergroupid,
                'storetimestamp':this.config.storetimestamp,
                'currencycode':this.config.currencycode,
                'fq': this.filterQueryToString(),
                'timestamp': timestamp,
                'p': this.page
            };
            this.timestamp = timestamp;
            $.extend({}, requestParams, params);
            //this.doRequest('/sb.php',requestParams);
            this.doRequest('/solrbridge/',requestParams);
        },
        doRequest: function (ajaxUrl, params) {
            var me = this;
            
            var cacheParams = {
                'storeid': params.storeid,
                'q': params.q,
                'customergroupid': params.customergroupid,
                'currencycode': params.currencycode,
                'fq': params.fq,
                'p': this.page
            };
            var cacheKey = ajaxUrl + '?' + $.param(cacheParams || {});
            
            if (undefined !== me.cachedResponse[cacheKey]) {
                var data = me.cachedResponse[cacheKey];
                me.processResponse(data);
                return;
            }
            
            //abort all previous request
            if (this.requests.length > 0) {
                $.each(this.requests, function(index, ajaxObject) {
                    //$(ajaxObject).abort();
                    if (undefined !== ajaxObject) {
                        ajaxObject.abort();
                    }
                });
            }
            
            var requestQueueIndex = (this.requests.length + 1);
            this.requests[requestQueueIndex] = $.ajax({
                'url': ajaxUrl,
                'type': 'GET',
                'data': params,
                'dataType': 'json',
                'success': function(data) {
                    me.processResponse(data);
                    me.requests.splice(requestQueueIndex, 1);
                    if (undefined != data.status && data.status != 'ERROR') {
                        me.cachedResponse[cacheKey] = data;
                    }
                }
            });
        },
        processResponse: function (response) {
            this.responseData = [];
            if (typeof response === 'undefined' && this.currentValue.length == 0) {
                this.hide();
                return;
            }
            if (response && (response.status == 'ERROR' || this.currentValue.length == 0 || response.timestamp != this.timestamp)) {
                if (this.filterQuery.length < 0) {
                    this.hide();
                    return;
                }
            }
            //Loop to push doc name into suggestions array
            var i = 0;
            this.responseData = response;
            if(undefined !== this.responseData.status && this.responseData.status !== 'ERROR') {
                this.renderSuggestion();
            }
        },
        _getSearchRedirectUrl: function (query) {
            if (!query) {
                query = this.responseData.q;
            }
            return this.config.result_redirect_url+'?q='+encodeURIComponent(query);
        },
        _getSearchResultUrl: function (query) {
            if (!query) {
                query = this.responseData.q;
            }
            return this.config.result_url+'?q='+encodeURIComponent(query);
        },
        _renderKeywordSuggestion: function () {
            if (undefined !== this.responseData.keywords) {
                for (var key_word in this.responseData.keywords) {
                    if (!isNaN(key_word)) {
                        var keywordString = this.responseData.rawkeywords[key_word];
                        var keywordItem = $('<a/>', {id:'sbs_'+this.id+'_keyword_index_'+key_word});
                        keywordItem.attr('href', this._getSearchResultUrl(keywordString));
                        keywordItem.addClass('keywords suggested-item suggested-item-keyword');
                        keywordItem.attr('item-type', 'keyword');
                        keywordItem.attr('item-data-index', key_word);
                        $(keywordItem).html('<span class="sbs_search_suggest_item_title">'+keywordString+'</span>');
                        this.rightSideBar.append(keywordItem);
                    }
                }
            }
        },
        _getRedirectToProductUrl: function (productData) {
            if (undefined !== productData.producturl) {
                return this.config.base_url + productData.producturl;
            }
            return this.config.base_url+'catalog/product/view/id'+productData.productid;
        },
        _renderProductSuggestion: function () {
            this.loadedProductIds = [];
            if (undefined !== this.responseData.products && this.responseData.products.length > 0) {
                this._createDivider($t('Products'), 'divider-products');

                var thumbWidth = this.config.thumbWidth;
                var thumbHeight = this.config.thumbHeight;
                //spinner image .gif size is 24x24
                var spinnerTop = (parseInt(thumbHeight) - 24) / 2;
                var spinnerLeft = (parseInt(thumbWidth) - 24) / 2;

                for (var key_product in this.responseData.products) {
                    if (!isNaN(key_product)) {
                        var productData = this.responseData.products[key_product];

                        var productItem = $('<a/>', {id:'sbs_'+this.id+'_product_index_'+key_product});
                        $(productItem).attr('href', this._getRedirectToProductUrl(productData));
                        $(productItem).attr('item-type', 'product');
                        $(productItem).attr('item-data-index', productData.productid);
                        productItem.addClass('product suggested-item suggested-item-product');

                        var productImageUrl = this.config.spinner;
                        var spinnerClass = 'spinner';
                        if (undefined !== productData.image && productData.image.length > 0) {
                            productImageUrl = this.config.base_media_url;
                            if ('/' == productData.image.substring(0, 1)) {
                                productImageUrl += productData.image;
                            } else {
                                productImageUrl += '/'+productData.image;
                            }
                            spinnerClass = '';
                        }
                        var itemThumb = '<div class="sbs_search_suggest_item_thumb" style="width:'+thumbWidth+'px; height:'+(parseInt(thumbHeight) + 4)+'px">';
                        itemThumb += '<div class="sb_thumb_box" style="width:'+thumbWidth+'px; height:'+thumbHeight+'px;margin-top:-'+(parseInt(thumbHeight) / 2)+'px">';
                        itemThumb += '<div style="position: relative;width:'+thumbWidth+'px; height:'+thumbHeight+'px;"><img style="top:'+spinnerTop+'px; left:'+spinnerLeft+'px" class="'+spinnerClass+'" id="sbs_product_thumb_'+productData.productid+'" src="'+productImageUrl+'" /></div></div>';
                        itemThumb += '</div>';
                        productItem.append(itemThumb);
                        var itemTitle = '<div class="sbs_search_suggest_item_title">'+productData.name+'</div>';
                        productItem.append(itemTitle);
                        var itemPrice = '<div id="sbs_product_price_'+productData.productid+'" class="sbs_search_suggest_item_subtitle">'+productData.formatted_price+'</div>';
                        productItem.append(itemPrice);

                        //update loaded product ids
                        this.loadedProductIds.push(productData.productid);

                        this.rightSideBar.append(productItem);
                    }
                }
            }
            //load more button
            if (undefined != this.responseData.last_page_num) {
                
                var nextLink = '<a href="#" class="next">'+$t('Next')+'</a>';
                var prevLink = '<a href="#" class="prev">'+$t('Prev')+'</a>';
                if (this.page < 2) {
                    prevLink = '<a href="#" class="prev" style="cursor: not-allowed; opacity: 0.5">'+$t('Prev')+'</a>';
                }
                if (this.page >= this.responseData.last_page_num) {
                    nextLink = '<a href="#" class="next" style="cursor: not-allowed; opacity: 0.5">'+$t('Next')+'</a>';
                }
                if (this.responseData.last_page_num > 1) {
                    this.rightSideBar.append('<div class="suggest_divider load-more-button">'+prevLink+'|'+nextLink+'</div>');
                }
            }
        },
        loadMore: function(element) {
            if ($(element).hasClass('next')) {
                this.page++;
            } else {
                this.page--;
            }
            
            if (this.page < 1) {
                this.page = 1;
                return;
            }
            
            if (this.page > this.responseData.last_page_num) {
                this.page = this.responseData.last_page_num;
                return;
            }
            
            this.getSuggestions();
        },
        _getCategoryIdFromString: function (category) {
            var start = category.lastIndexOf('/') + 1;
            var end = category.lastIndexOf(':');
            var currentCatId = category.substring(start, end);
            return currentCatId;
        },
        _createCategoryItem: function (index, categoryData) {
            var catPathArray = [];
            var me = this;
            var categoryLink = '#';
            var productCount = 0;
            $.each(categoryData, function (k, data) {
                categoryLink = me._getSearchRedirectUrl()+'&cat='+data.id;
                catPathArray.push(data.name);
                productCount = data.count;
            });

            var productCountFormatted = productCount+'&nbsp;'+$t('product');
            if (parseInt(productCount) > 1) {
                productCountFormatted = productCount+'&nbsp;'+$t('products');
            }

            var catPath = catPathArray.join('&nbsp;>&nbsp;');
            var catPathFormatted = catPath.replace(/_._._/g,"/");
            var categoryItem = $('<a/>', {id:'sbs_'+this.id+'_category_index_'+index});
            $(categoryItem).attr('href', categoryLink);
            categoryItem.addClass('category suggested-item');
            var itemTitle = '<div class="sbs_search_suggest_item_title">'+catPathFormatted+'</div>';
            var itemSubTitle = '<div class="sbs_search_suggest_item_subtitle">'+productCountFormatted+'</div>';
            categoryItem.append(itemTitle);
            categoryItem.append(itemSubTitle);
            this.rightSideBar.append(categoryItem);
        },
        _renderCategorySuggestion: function () {
            if (typeof this.responseData.categories !== 'undefined' && this.responseData.categories.length > 0) {
                this._createDivider($t('Categories'), 'divider-category');
                this.itemCount++;
                var catIndex = 1;
                var categoryFacet = this.responseData.categories;
                for (var key_cat in categoryFacet) {
                    if (isNaN(key_cat)) {
                        continue;
                    }
                    var categoryData = categoryFacet[key_cat];
                    this._createCategoryItem(key_cat, categoryData);
                    catIndex++;
                }
            }
        },
        _createBrandItem: function (index, brandData) {
            var brandItem = $('<a/>', {id:'sbs_'+this.id+'_brand_index_'+index});
            var categoryLink = this._getSearchRedirectUrl()+'&'+this.config.brand_attr_code+'='+brandData.id;;
            $(brandItem).attr('href', categoryLink);
            brandItem.addClass('category suggested-item');
            var itemTitle = '<div class="sbs_search_suggest_item_title">'+brandData.name+'&nbsp;('+brandData.count+')</div>';
            brandItem.append(itemTitle);
            this.rightSideBar.append(brandItem);
        },
        _createDivider: function (dividerLabel, className = '') {
            if (className.length > 0) {
                var divider = '<div class="suggest_divider '+className+'"><span>'+dividerLabel+'</span></div>';
            } else {
                var divider = '<div class="suggest_divider"><span>'+dividerLabel+'</span></div>';
            }
            this.rightSideBar.append(divider);
        },
        _renderBrandSuggestion: function () {
            if (typeof this.responseData.brands !== undefined && this.responseData.brands.length > 0) {
                this._createDivider($t('Brands'), 'divider-brand');
                this.itemCount++;
                var catIndex = 1;
                var brandFacet = this.responseData.brands;
                var me = this;
                $.each(brandFacet, function (k, brandData) {
                    me._createBrandItem(k, brandData);
                });
            }
        },
        _ajaxLoadAdditionalData: function () {
            var productIds = this.loadedProductIds.join(',');
            
            var me = this;
            
            var requestQueueIndex = (this.requests.length + 1);
            var queryText = this.currentValue;
            
            this.requests[requestQueueIndex] = $.ajax({
                'url': this.config.load_product_data_url,
                'type': 'GET',
                'data': {'productids': productIds, 'query_text': queryText},
                'dataType': 'json',
                'success': function(productData) {
                    $.each(productData, function (k, product) {
                        //update image
                        if (document.getElementById('sbs_product_thumb_'+k)) {
                            $('#sbs_product_thumb_'+k).attr('src', product.thumb);
                            $('#sbs_product_thumb_'+k).removeClass('spinner');
                        }
                        //update prices
                        if (document.getElementById('sbs_product_price_'+k)) {
                            if (parseInt(product.show_special_price) > 0) {
                                var priceHtml = '<div class="sbs_search_suggest_item_price_box"><span class="special_price">'+(product.final_price)+'</span>';
                                priceHtml += '<span class="price">'+(product.price)+'</span></div>';
                                $('#sbs_product_price_'+k).html(priceHtml);
                            } else {
                                $('#sbs_product_price_'+k).html(product.final_price);
                            }
                        }
                    });
                    me.requests.splice(requestQueueIndex, 1);
                }
            });
            /*
            this.requests[requestQueueIndex] = $.get(this.config.load_product_data_url, {'productids': productIds}, function (productData,status,xhr) {
                $.each(productData, function (k, product) {
                    //update image
                    if (document.getElementById('sbs_product_thumb_'+k)) {
                        $('#sbs_product_thumb_'+k).attr('src', product.thumb);
                        $('#sbs_product_thumb_'+k).removeClass('spinner');
                    }
                    //update prices
                    if (document.getElementById('sbs_product_price_'+k)) {
                        if (parseInt(product.show_special_price) > 0) {
                            var priceHtml = '<div class="sbs_search_suggest_item_price_box"><span class="special_price">'+(product.final_price)+'</span>';
                            priceHtml += '<span class="price">'+(product.price)+'</span></div>';
                            $('#sbs_product_price_'+k).html(priceHtml);
                        } else {
                            $('#sbs_product_price_'+k).html(product.final_price);
                        }
                    }
                });
                this.requests.splice(requestQueueIndex, 1);
            }, 'json');*/
        },
        _renderTitle: function () {
            var suggestionMessage = $t('Search result for <em>%s</em>');
            if (this.responseData.didyoumean !== null) {
                suggestionMessage = $t('Search result for <em>%s</em> instead.');
            }
            suggestionMessage = suggestionMessage.replace('%s', this.responseData.q);
            this._createDivider(suggestionMessage, 'result-message');
        },
        _renderFilters: function () {
            //Render filters logic here
        },
        renderSuggestion: function () {
            //Clear right side bar div
            this.rightSideBar.html('');
            //Render title
            this._renderTitle();

            //Render keywords
            this._renderKeywordSuggestion();

            //Render Products
            this._renderProductSuggestion();

            //Render brands
            this._renderBrandSuggestion();

            //Render Categories
            this._renderCategorySuggestion();
            
            //Render filters
            this._renderFilters();

            //Display result not found message
            this.show();
            if (this.rightSideBar.children().length < 1) {
                //display will be useful for advanced mode only
                var emptyResultMessage = $('<div>').html($t('Sorry, Your search or filter returned no results.'));
                var resetFilterButton = $('<a>', {'href': '#'}).html($t('Reset Filter'));
                $(resetFilterButton).bind('click', this.onResetFilter.bind(this));
                var resetFilterWrapper = $('<div>').append(resetFilterButton);
                var emptyResultWrapper = $('<div>').addClass('empty-result-wrapper');
                $(emptyResultWrapper).append(emptyResultMessage);
                $(emptyResultWrapper).append(resetFilterWrapper);
                this.rightSideBar.append(emptyResultWrapper);
            } else {
                //Render viewall link - redirect to search result page
                this._createViewAllLink();
                this._ajaxLoadAdditionalData();
            }
        },
        _createViewAllLink: function () {
            //Render viewall link - redirect to search result page
            var viewAllLink = '<a href="'+this._getSearchResultUrl()+'">'+$t('View all search result for <strong>%s</strong>')+'</a>';
            viewAllLink = viewAllLink.replace('%s', this.responseData.q);
            this._createDivider(viewAllLink, 'sbs_search_view_all_link');
        },
        onResetFilter: function (e) {
            e.preventDefault();
            this.filterQuery = {};
            this.getSuggestions();
            this._scrollTop();
        },
        _scrollTop: function () {
            var me = this;
            $('html, body').stop().animate({
                scrollTop: $(me.element).offset().top
            }, 1000);
        },
        redirectKeyword: function (selectItem) {
            var index = $(selectItem).attr('item-data-index');
            var selectedKeyword = this.responseData.rawkeywords[index];
            $(this.element).val(selectedKeyword);
            $(this.element).closest("form").submit();
        },
        redirectToProduct: function (selectItem) {
            var productId = $(selectItem).attr('item-data-index');
            window.location = this.config.load_product_url+'?productid='+productId;
        },
        changeCategoryStyle: function() {
            var elementId = '#sb-search-category-select-insivisible';
            var categoryName = $(elementId + ' option:selected').text();
            $('#sb-category-fake-text').text(categoryName);
            
            var elementWidth = $('#sb-category-fake-text').width();
            var height = $(this.element).height();
            $(this.element).css({'padding-left': (elementWidth + 50) +'px'});
            $('#sb-search-box-category').css({'width': (elementWidth + 45) +'px'});
            $(elementId).css({
                'width': (elementWidth + 40) +'px',
                'height': height+'px',
                'opacity': '1'
            });
        },
        initSearchBox: function() {
            //initialize config
            var requestParams = {
                'storeid':this.config.store_id,
                'customergroupid':this.config.customergroupid,
                'storetimestamp':this.config.storetimestamp,
                'currencycode':this.config.currencycode,
                'fq': this.filterQueryToString(),
                'action': 'LOADCONFIG'
            };
            var me = this;
            $.ajax({
                'url': '/solrbridge/',
                'type': 'GET',
                'data': requestParams,
                'dataType': 'json',
                'success': function(data) {
                    me.initCategoryDropdown(data);
                    me.initTopResultsCache(data);
                }
            });
            
            var me = this;
            $(document).on('click', '.solrbridge-autocomplete-search .load-more-button a', function(event){
                event.preventDefault();
                me.loadMore(this);
            });
        },
        initCategoryDropdown: function(config) {
            var categories = [];
            if (undefined != config.categories) {
                categories = config.categories;
            }
            if (undefined !== this.config.show_cat_dropdown && this.config.show_cat_dropdown > 0 && categories.length > 0) {
                var selectedCategoryText = 'All';
                
                var categorySelectWrapper = $('<div />', {'class': 'sb-search-box-category', 'id': 'sb-search-box-category'});
                var categorySelect = '<select class="category-select" name="fq[cat][0]" id="sb-search-category-select-insivisible">';
                
                var selectedCategoryIds = [];
                if (undefined !== this.config.selected_cat_id && this.config.selected_cat_id.length > 0) {
                    selectedCategoryIds = this.config.selected_cat_id;
                }
                
                $.each(categories, function(index, category) {
                    if (selectedCategoryIds.indexOf(category.id) !== -1) {
                        categorySelect += '<option value="'+category.id+'" selected="selected">'+category.name+'</option>';
                    } else {
                        categorySelect += '<option value="'+category.id+'">'+category.name+'</option>';
                    }
                });
                categorySelect += '</select>';
                var fakeText = '<span id="sb-category-fake-text" class="sb-category-fake-text">'+selectedCategoryText+'</span>';
                $(categorySelectWrapper).html(fakeText+categorySelect);
                $(categorySelectWrapper).insertBefore(this.element);
                
                var me = this;
                me.changeCategoryStyle();
                $(document).on('change', '.sb-search-box-category select[class^="category-select"]', function(event){
                    me.changeCategoryStyle();
                });
            }
        },
        initTopResultsCache: function(data) {
            var me = this;
            if (undefined != data.cachedPopularResults) {
                var ajaxUrl = '/solrbridge/';
                $.each(data.cachedPopularResults, function(queryText, result){
                    if (undefined != result.status && result.status != 'ERROR') {
                        var cacheParams = {
                            'storeid': me.config.store_id,
                            'q': queryText,
                            'customergroupid': me.config.customergroupid,
                            'currencycode': me.config.currencycode,
                            'fq': me.filterQueryToString(),
                            'p': 1
                        };
                        var cacheKey = ajaxUrl + '?' + $.param(cacheParams || {});
                    
                        me.cachedResponse[cacheKey] = result;
                    }
                });
            }
        }
    };
    
    $.fn.SolrBridgeSearch = function (options) {
        
        if (options.advanced_mode == 1) {
            var SolrBridgeSearchAdvanced = SolrBridgeSearch;
            $.extend(true, SolrBridgeSearchAdvanced.prototype, {
                className: 'solrbridge-autocomplete adv-solrbridge-autocomplete-search',
                originFacetData: {},
                facetData: {},
                fixPosition: function () {
                    var offset = $(this.element).offset();
                    var top = offset.top + $(this.element).outerHeight();
                    $(this.box).css({ top: (top + 15) + 'px', left: '0' });
                    var inputWidth = $(this.element).outerWidth();
                    this.tailIcon.css({'left': offset.left + (inputWidth/2)+'px'});
                    this.tailIconInner.css({'left': offset.left + (inputWidth/2)+'px'});
                },
                _renderTitle: function () {
                    var suggestionMessage = $t('Search result for <em>%s</em>');
                    if (this.responseData.didyoumean !== null) {
                        suggestionMessage = $t('Search result for <em>%s</em> instead.');
                    }
                    suggestionMessage = suggestionMessage.replace('%s', this.responseData.q);
        
                    var dividerId = 'sbs_'+this.id+'_autocomplete_message';
                    if (document.getElementById(dividerId)) {
                        $('#'+dividerId).html(suggestionMessage);
                    } else {
                        var divider = $('<div>', {id: dividerId}).html(suggestionMessage);
                        $(divider).addClass('suggest_product_items suggest_divider sbs_autocomplete_message sbs_autocomplete_result_title');
                        $(divider).insertBefore(this.innerContainer);
                    }
                    this.rightSideBar.html('');
                },
                _renderCategorySuggestion: function () {
                    //Do no thing - this is required function, please do not remove
                },
                _renderFilterState: function () {
                    var filterStateId = 'sbs_'+this.id+'_autocomplete_filter_state';
                    var filterContainerId = 'sbs_'+this.id+'_autocomplete_filter_container';
                    if (!$.isEmptyObject(this.filterQuery)) {
                        if (!document.getElementById(filterStateId)) {
                            var state = $('<div>', {id: filterStateId}).addClass('filter-state-wrapper');
                            $(state).insertBefore($('#'+filterContainerId));
                        }
                        $('#'+filterStateId).html('');
                        var nowShoppingBy = $('<div>').html($t('Now shopping by')).addClass('now-shopping-by');
                        $('#'+filterStateId).append(nowShoppingBy);
                        
                        var hasItem = false;
                        $('.adv-solrbridge-autocomplete-search .sbs_autocomplete_inner_left .facet-item').each(function () {
                            if ($(this).hasClass('selected')) {
                                var dataKey = $(this).data('key');
                                var dataLabel = $(this).data('label');
                                var dataValue = $(this).data('value');
                                var dataTitle = $(this).data('title');
                                
                                var filterStateItem = $('<span>');
                                $(filterStateItem).html('<strong>'+dataTitle+'</strong>: '+dataLabel);
                                
                                var filterStateItemClose = $('<a>', {'href': '#'}).addClass('remove-icon');
                                $(filterStateItemClose).attr('data-key', dataKey);
                                $(filterStateItemClose).attr('data-label', dataLabel);
                                $(filterStateItemClose).attr('data-value', dataValue);
                                $(filterStateItemClose).attr('data-title', dataTitle);
                                $(filterStateItemClose).html('');
                                
                                var filterStateItemWrapper = $('<div>').addClass('state-item');
                                filterStateItemWrapper.append(filterStateItem);
                                filterStateItemWrapper.append(filterStateItemClose);
                                
                                $('#'+filterStateId).append(filterStateItemWrapper);
                                hasItem = true;
                            }
                        });
                        if (hasItem) {
                            $('.filter-state-wrapper .state-item a.remove-icon').bind('click', this.onFilterStateRemoveItem.bind(this));
                            var resetFilterLink = $('<a>', {'href': '#'}).html($t('Reset Filter'));
                            var resetFilterLinkWrapper = $('<div>').addClass('reset-filter-link').append(resetFilterLink);
                            $(resetFilterLink).bind('click', this.onResetFilter.bind(this));
                            $('#'+filterStateId).append(resetFilterLinkWrapper);
                        } else {
                            if (document.getElementById(filterStateId)) {
                                $('#'+filterStateId).remove();
                            }
                        }
                    } else {
                        if (document.getElementById(filterStateId)) {
                            $('#'+filterStateId).remove();
                        }
                    }
                },
                onFilterStateRemoveItem: function (event) {
                    event.preventDefault();
                    var stateItem = event.target;
                    var key = $(stateItem).data('key');
                    var value = $(stateItem).data('value');
                    if (undefined !== this.filterQuery[key] && this.filterQuery[key].indexOf(value) != -1) {
                        var _index = this.filterQuery[key].indexOf(value);
                        this.filterQuery[key].splice(_index, 1);
                        
                        if (this.filterQuery[key].length < 1) {
                            var newFilterQuery = {};
                            $.each(this.filterQuery, function (_k, _v) {
                                if (_k != key) {
                                    newFilterQuery[_k] = _v;
                                }
                            });
                            this.filterQuery = newFilterQuery;
                        }
                        
                        this._scrollTop();
                        this.getSuggestions();
                    }
                },
                _prepareFilterData: function () {
                    if ($.isEmptyObject(this.filterQuery)) {
                        if (typeof this.responseData.categories !== 'undefined' && this.responseData.categories.length > 0) {
                            this.originFacetData['category'] = this.responseData.categories;
                        }
                        if (typeof this.responseData.filters !== undefined) {
                            this.originFacetData['filters'] = this.responseData.filters;
                        }
                    }
                    
                    this.facetData['category'] = '';
                    if (undefined !== this.originFacetData['category']) {
                        this.facetData['category'] = this.originFacetData['category'];
                    }
                    this.facetData['filters'] = {};
                    if (undefined !== this.originFacetData['filters']) {
                        //this.facetData['filters'] = this.originFacetData['filters'];
                    }
                    
                    var me = this;
                    
                    $.each(this.originFacetData, function (key, value) {
                        if (key == 'category') {
                            if (!('cat' in me.filterQuery)) {
                                me.facetData['category'] = me.responseData.categories;
                                me.originFacetData['category'] = me.responseData.categories;
                            } else {
                                me.facetData['category'] = me.originFacetData['category'];
                            }
                        } else {
                            $.each(me.originFacetData.filters, function (_key, _value) {
                                if (undefined !== _value.attribute_code) {
                                    //filters
                                    if (_value.attribute_code in me.filterQuery) {
                                        me.facetData['filters'][_value.attribute_code] = me.originFacetData['filters'][_key];
                                    } else {
                                    //no filters
                                        $.each(me.responseData.filters, function (__k, __v) {
                                            if (__k == _value.attribute_code) {
                                                me.facetData['filters'][__k] = me.responseData.filters[__k];
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    });
                },
                _renderFilters: function () {
                    this.leftSideBar.html('');
                    var me = this;
                    this._prepareFilterData();
                    
                    var filterContainerId = 'sbs_'+this.id+'_autocomplete_filter_container';
                    var filterContainer = $('<div>', {id: filterContainerId});
                    
                    if (typeof this.facetData['category'] !== 'undefined' && this.facetData['category'].length > 0) {
                        var filterSection = $('<div>').addClass('filter-wrapper');
                        $(filterSection).append('<div class="title"><strong>'+$t('Category')+'</strong></div>');
                        $(filterSection).append(this.facetData.category);
                        filterContainer.append(filterSection);
                    }
                    
                    if (typeof this.facetData['filters'] !== undefined) {
                        $.each(this.facetData['filters'], function (i, filter) {
                            if (undefined !== filter.items && undefined !== filter.items.length && filter.items.length > 0) {
                                var filterSection = $('<div>').addClass('filter-wrapper');
                                $(filterSection).append('<div class="title"><strong>'+filter.frontend_label+'</strong></div>');
                                var filterUL = $('<ul>').addClass('items');
                                $.each(filter.items, function () {
                                    var filterItem = $('<li>');
                                    var label = this.label+' ('+this.count+')';
                                    var filterItemLink = $('<a>', {'href': '#'}).addClass('facet-item').html(label);
                                    $(filterItemLink).attr('data-value', this.value);
                                    $(filterItemLink).attr('data-key', this.attribute_code);
                                    $(filterItemLink).attr('data-label', this.label);
                                    $(filterItemLink).attr('data-title', filter.frontend_label);
                                    $(filterItem).append(filterItemLink);
                                    $(filterUL).append(filterItem);
                                });
                                $(filterSection).append(filterUL);
                                filterContainer.append(filterSection);
                            }
                        });
                    }
                    
                    this.leftSideBar.append(filterContainer);
                    
                    $('.adv-solrbridge-autocomplete-search .sbs_autocomplete_inner_left .facet-item').each(function () {
                        me._scanAndCheckForActiveFacetItem(this);
                    });
                    
                    $('.sbs_autocomplete_inner_left a.facet-item').bind('click', this.onFilterItemClick.bind(this));
                    this._renderFilterState();
                    this.loadedFilter = true;
                },
                onFilterItemClick: function (event) {
                    event.preventDefault();
                    var me = this;
                    var filterElement = event.target;
                    var value = $(filterElement).data('value');
                    var key = $(filterElement).data('key');
                    
                    if ($(filterElement).hasClass('selected')) {
                        if (undefined !== this.filterQuery[key] && this.filterQuery[key].indexOf(value) != -1) {
                            var _index = this.filterQuery[key].indexOf(value);
                            this.filterQuery[key].splice(_index, 1);
                            $(filterElement).removeClass('selected');
                            //if no more element
                            if (this.filterQuery[key].length < 1) {
                                var newFilterQuery = {};
                                $.each(this.filterQuery, function (_k, _v) {
                                    if (_k != key) {
                                        newFilterQuery[_k] = _v;
                                    }
                                });
                                this.filterQuery = newFilterQuery;
                            }
                        }
                    } else {
                        
                        if (undefined !== this.filterQuery[key] && this.filterQuery[key].indexOf(value) == -1) {
                            if (undefined !== key && undefined !== value) {
                                this.filterQuery[key].push(value);
                            }
                        } else {
                            if (undefined !== key && undefined !== value) {
                                this.filterQuery[key] = [value];
                            }
                        }
                    }
                    this._scrollTop();
                            
                    this.getSuggestions();
                    //this._scanAndCheckForActiveFacetItem(filterElement);
                },
                _scanAndCheckForActiveFacetItem: function (filterElement) {
                    var value = $(filterElement).data('value');
                    var key = $(filterElement).data('key');
                    if (undefined != this.filterQuery[key] && this.filterQuery[key].indexOf(value) != -1) {
                        //active
                        if (!$(filterElement).hasClass('selected')) {
                            $(filterElement).addClass('selected');
                        }
                    }
                }
            });
        }
        
        $(document).on('change', 'select.solrbridge-search-layer-nav-filter-dropdown', function(event){
            var directUrl = $(this).val();
            window.location.href = directUrl;
        });
        
        //loop elements matched selectors
        return this.each(function () {
            // If options exist, lets merge them
            // with our default settings
            if ( options ) {
                $.extend({}, options);
            }
            var instance  = $.data(this, 'SolrBridgeSearch');
            if (!instance) {
                if (options.advanced_mode == 1) {
                    $.data(this, 'SolrBridgeSearch', new SolrBridgeSearchAdvanced(this, options));
                } else {
                    $.data(this, 'SolrBridgeSearch', new SolrBridgeSearch(this, options));
                }
            }
        });
    };
});