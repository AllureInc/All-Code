var config = {
    paths: {
            'owlcarousel' : 'Cor_Customizations/js/owl.carousel.min',
            'queueconfigloader' : 'Cor_Customizations/js/queueconfigloader.min',
            'queueclient' : 'Cor_Customizations/js/queueclient.min'
        },   
    shim: {
        'owlcarousel': {
            deps: ['jquery']
        }
    },
    // map: {
    //     '*': {
    //         'Magento_Catalog/js/catalog-add-to-cart': 'Cor_Customizations/js/catalog-add-to-cart',
    //     }
    // },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping-information': {
                'Cor_Customizations/js/view/shipping-information': true
            },
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Cor_Customizations/js/catalog-add-to-cart': true
            }
        }
    }
};
