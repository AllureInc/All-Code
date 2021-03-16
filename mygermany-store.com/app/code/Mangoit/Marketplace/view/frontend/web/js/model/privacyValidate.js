define(
    ['jquery', 'Magento_Ui/js/modal/alert'],
    function ($, alert) {
        'use strict';
        return {
            /**
             * Validate something
             *
             * @returns {boolean}
             */
            validate: function() {
                //Privacy validation logic here
                var agreeToPrivacy = 'input[name="scommerce_gdpr_privacy_consent"]';
                if (!$(agreeToPrivacy).attr('checked')) {
                    alert({
                        title: 'Privacy Policy',
                        content: 'Please select Privacy Policy!'
                    });
                    return false;
                }
                return true;
            }
        }
    }
);