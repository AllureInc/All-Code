require(['jquery', 'jquery/ui'], function($){
    jQuery(document).on('change', '#page_category_name', function(){
        /*if (jQuery(this).val().trim().length < 1) {}*/
        var postUrl = jQuery('.posturl').val();
        var postid = jQuery('.postid').val();

        saveJquery(jQuery(this).val(), postUrl, postid);
        console.log('postUrl '+postUrl);
    });

    function saveJquery(category, postUrl, postid){
        var flag = 0;
        // var ajaxurl = "<?php echo $this->getUrl('artistcategory/category/checkcategory');?>";  
        var ajaxurl = postUrl;  
        var requestCategory = category;     
        jQuery.ajax({
            type: "POST",
            data : {'category': requestCategory, 'id': postid},
            async : true,
            url: ajaxurl,
            success: function (response) {
                console.log(response);
                if (response == 'exist') 
                {
                    jQuery('.exist-err').remove(); 
                    jQuery('#page_category_name').after("<small id= 'exist-err'>This category is already exist.</small>");                        
               
                } else {
                    jQuery('#exist-err').remove();
                }
            },
                error: function (response) {
                   console.log('err'+response);
            }
        });

    } 
});