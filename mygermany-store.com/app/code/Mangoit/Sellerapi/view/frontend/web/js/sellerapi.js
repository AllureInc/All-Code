/*require([
    "jquery",
    'mage/url',
    'jquery/ui'
], function($, url){
    $(document).ready(function() {
        var base_url = $('.site-baseurl').val();
        $('.btn-copy').on('click', function(){
            var copy_data_id = $(this).data('class');
            var copied_data = $('.'+copy_data_id).text();
            var $temp = $("<input claas='copy-data'>");
            $("body").append($temp);
            $temp.val($('#'+copy_data_id).val()).select();
            document.execCommand("copy");
            $temp.remove();
            alert('Copied'); 
        });

        $('.btn-generate-token').on('click', function(){
            $.ajax({
                showLoader: true,
                url : base_url+'sellerapi/seller/generatetoken/',
                type : 'GET',
                success : function(data) {
                    if (data) {
                        $('.token_id_data').text(data);
                        $('#token_id_data').text(data);
                        console.log(data);
                    } else {
                        window.location.href = base_url+"/customer/account/login/";
                    }
                },
                error : function(request,error)
                {
                    alert("Request: "+JSON.stringify(request));
                }
            });
        });
    });
});
*/