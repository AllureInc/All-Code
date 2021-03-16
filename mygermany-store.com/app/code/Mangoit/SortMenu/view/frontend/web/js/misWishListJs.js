require([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function($){
 
    return function() {
        $.validator.addMethod(
            'mis-validate-emails', function (value) {
                var validRegexp, emails, i;

                if (utils.isEmpty(value)) {
                    return true;
                }
                validRegexp = /^[a-z0-9\._-]{1,30}@([a-z0-9_-]{1,30}\.){1,5}[a-z]{2,4}$/i;
                emails = value.split(/[\s\n\,]+/g);

                for (i = 0; i < emails.length; i++) {
                    if (!validRegexp.test(emails[i].strip())) {
                        return false;
                    }
                }

                return true;
            },
            $.mage.__('MIS Please enter valid email addresses, separated by commas. For example, johndoe@domain.com, johnsmith@domain.com.')
        );
    }
});