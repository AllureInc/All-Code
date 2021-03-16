/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
var config = {
	'mixins': {
	    'Magento_Checkout/js/view/shipping': {
	        'Mangoit_Marketplace/js/view/shipping-payment-mixin': true
	    },
	    'Magento_Checkout/js/view/payment': {
	        'Mangoit_Marketplace/js/view/shipping-payment-mixin': true
	    }
	},
    
    map: {
        '*': {
            'Magento_Tax/js/view/checkout/summary/grand-total':'Mangoit_Marketplace/js/view/checkout/summary/grand-total',
            'Webkul_MarketplacePreorder/js/listpage':'Mangoit_Marketplace/js/preorder/listpage',
            profileLink: 'Mangoit_Marketplace/js/profile-link',
            productAttributeLink: 'Mangoit_Marketplace/js/product-attr-link',
            paymentOverviewLink: 'Mangoit_Marketplace/js/payment-overview-link'
        }
    }
};