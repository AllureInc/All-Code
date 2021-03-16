/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_BusinessDirectory
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'mage/template',
    'mage/mage',
    "jquery/ui"
    ], function ($,$t,mageTemplate,mage,$g) {
    'use strict';
    $.widget('mage.showmap', {
        options: {
            backUrl: '',
            ajaxErrorMessage: $t('There is some error during executing this process, please try again later.'),
            chartErrorMessage: $t('No Data Found'),
            dateErrorMessage: $t('Start date must be prior than end date.')
        },
        _create: function () {
            var self = this;
            var mapdata = self.options.data;
            $(document).ready(function () {
                self.createMap(mapdata);                
            });

            $(self.options.geolocationfilterajax).on('change', function () {
                $('body').trigger('processStart');  
                self.geolocationAjaxRequest($(this));
            });
        },
        createMap:function (mapdata) {
            google.charts.load('current', { 'packages': ['map'] });
            google.charts.setOnLoadCallback(drawMap);

            function drawMap() {
                var data = google.visualization.arrayToDataTable(mapdata);

                var options = {
                    zoomLevel: 2,
                    showTooltip: true,
                    showInfoWindow: true,
                    mapType: 'styledMap',
                    maps: {
                        styledMap: {
                            name: 'Styled Map', 
                            styles: [
                            {featureType: 'poi.attraction',
                            stylers: [{color: '#fce8b2'}]
                            },
                            {featureType: 'road.highway',
                            stylers: [{hue: '#0277bd'}, {saturation: -50}]
                            },
                            {featureType: 'road.highway',
                            elementType: 'labels.icon',
                            stylers: [{hue: '#000'}, {saturation: 100}, {lightness: 50}]
                            },
                            {featureType: 'landscape',
                            stylers: [{hue: '#259b24'}, {saturation: 10}, {lightness: -22}]
                            }
                        ]}
                    }
                };

                var map = new google.visualization.Map(document.getElementById('chart_div'));

                map.draw(data, options);
            };
        },        
        geolocationAjaxRequest:function (element) {
            var self = this;
            var filter = element;
            $.ajax({
                url         :   self.options.geolocationfilterurl,
                data        :   {filter:filter.attr('value'),isAjax:true},
                type        :   "post",
                dataType    :   "json",
                cache       :   false,
                success     :   function (data) {
                    $('body').trigger('processStop');                                                                                  
                    self.createMap(data);      
                },
                error: function (data) {
                    alert({
                        content: self.options.ajaxErrorMessage
                    });
                    window.location.href = self.options.indexurl;
                    $('body').trigger('processStop');                
                }
            });
        },
    });
    return $.mage.showmap;
});
