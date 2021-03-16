define(['jquery','mage/template','jquery/ui','mage/translate','mage/validation','jquery/ui'], function ($) {
    "use strict";
    $.fn.SBDeleteIndex = function (options) {
        this.each(function () {
            $(this).click(function () {
                //Alert are you sure to delete?
                var deleteUrl = $(this).data('ui-url');
                //alert(deleteUrl+'xxxx');
                
                $.ajax({
                    type: "GET",
                    showLoader: true,
                    url: deleteUrl,
                    success: function (response) {
                        //alert('done');
                        window.location.href = BASE_URL;
                    }
                });
            })
        })
    }
    
    function _SBRunReindex(reindexUrl, params)
    {
        $.ajax({
            type: "GET",
            showLoader: true,
            url: reindexUrl,
            data: params,
            success: function (response) {
                if (undefined != response.status) {
                    alert(response.status);
                    location.reload();
                    return;
                }
                if (undefined != response.page) {
                    _SBRunReindex(response.action_url, {page:response.page});
                }
                //alert('done');
                //window.location.href = BASE_URL;
            }
        });
    }
    
    $.fn.SBReIndex = function (options) {
        this.each(function () {
            $(this).click(function () {
                //Alert are you sure to delete?
                var reindexUrl = $(this).data('ui-url');
                
                _SBRunReindex(reindexUrl, {});
            })
        })
    }
    
    $('[data-ui-id="delete-index-button"]').SBDeleteIndex({});
    
    $('[data-ui-id="re-index-button"]').SBReIndex({});
    
    $('#add-index-mapping').click(function () {
        //alert('Do adding new index mapping action');
        $('#solrbridge-index-mapping').validation().submit();
    });
});