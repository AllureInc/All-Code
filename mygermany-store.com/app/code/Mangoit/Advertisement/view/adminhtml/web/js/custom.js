var blockValue = '';
require(
    [
      'jquery'
    ], function($) {
    	jQuery(document).on("change", 'select[name^=ads_pricing]', function() {
                blockValue = jQuery(this).val();
                console.log('blockValue '+blockValue);
            });
    }

);