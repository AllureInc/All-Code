var config = {
    paths: {
        "intlTelInput": 'Cnnb_WhatsappApi/js/intlTelInput',
        "intlTelInputUtils": 'Cnnb_WhatsappApi/js/utils',
        "internationalTelephoneInput": 'Cnnb_WhatsappApi/js/internationalTelephoneInput'
    },

    shim: {
        'intlTelInput': {
            'deps':['jquery', 'knockout']
        },
        'internationalTelephoneInput': {
            'deps':['jquery', 'intlTelInput']
        }
    }
};