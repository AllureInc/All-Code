define(['jquery', 'mage/translate', 'mage/loader'], function ($, $t, loader) {
    "use strict";
    var SolrBridgeSearchAjaxResult = function (el, config) {
        this.element = el;
        this.id = this.element.getAttribute('id');
        var defaultConfig = {};
        
        if (config) {
            this.config = $.extend({}, defaultConfig, config);
        } else {
            this.config = defaultConfig;
        }
        this.init();
    }
    
    SolrBridgeSearchAjaxResult.instances = [];
    
    SolrBridgeSearchAjaxResult.getInstance = function (id) {
        var instances = SolrBridgeSearchAjaxResult.instances;
        var i = instances.length;
        while (i--) {
            if (instances[i].id === id) {
                return instances[i];
            }
        }
    };
    
    SolrBridgeSearchAjaxResult.prototype = {
        init: function () {
            //$('body').loader('show');
            
            this.instanceId = SolrBridgeSearchAjaxResult.instances.push(this) - 1;
            
            this.doRequest();
        },
        
        doRequest: function() {
            var me = this;
            $.ajax({url: me.config.search_url, cache: false, success: function(result){
                //$("#div1").html(result);
                
                var html = $.parseHTML(result);
                
                var searchResultElement = $(html).find('#solrbridge-ajax-search-result-wrapper').first();
                //console.log(searchResultElement.html());
                
                $(me.element).html(searchResultElement.html());
                
                var layerNavElement = $(html).find('#solrbridge-ajax-search-layer-nav-wrapper').first();
                
                $('#sb-ajax-layer-nav-wrapper').html(layerNavElement.html());
                
                //$('body').loader('hide');
                
                $('body').trigger('contentUpdated');
            }});
        }
    };
    
    $.fn.SolrBridgeSearchAjaxResult = function (options) {
        //loop elements matched selectors
        return this.each(function () {
            // If options exist, lets merge them
            // with our default settings
            if ( options ) {
                $.extend({}, options);
            }
            var instance  = $.data(this, 'SolrBridgeSearchAjaxResult');
            if (!instance) {
                $.data(this, 'SolrBridgeSearchAjaxResult', new SolrBridgeSearchAjaxResult(this, options));
            }
        });
    };
});