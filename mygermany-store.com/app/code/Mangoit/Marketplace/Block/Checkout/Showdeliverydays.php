<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\Marketplace\Block\Checkout;

/**
 * Mangoit Marketplace Sellerlist Block.
 */
class Showdeliverydays extends \Magento\Framework\View\Element\Template
{

    protected $cart;

    protected $_productloader;  
    protected $_sellerProducts; 
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    protected $pricingHelper;

    protected $_webkulHelper;
    protected $_checkoutSession;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Webkul\Marketplace\Model\Product $_sellerProducts,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Webkul\Marketplace\Helper\Data $webkulHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->cart = $cart;
        $this->_productloader = $_productloader;
        $this->_sellerProducts = $_sellerProducts;
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->pricingHelper = $pricingHelper;
        $this->_webkulHelper = $webkulHelper;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }


    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getVendorDeliveryDays()
    {
        $productInfo = $this->cart->getQuote()->getAllVisibleItems();
        if (!empty($productInfo)) {
            $vendorDeliveryDays = array();
            $vendorHighestProduct = [];
            foreach ($productInfo as $item){
               $productObj = $this->_productloader->create()->load($item->getProductId());
               $mageProduct  = $this->_sellerProducts->load($item->getProductId(),'mageproduct_id');

               $sellerId = $mageProduct->getSellerId();
               $sellerDetails = $this->_webkulHelper->getSellerDataBySellerId($sellerId);
               $shopTitle = $sellerDetails->getFirstItem()->getShopTitle();

               $deliveryFrom = $productObj->getDeliveryDaysFrom();
               $deliveryTo = $productObj->getDeliveryDaysTo();
                if ($deliveryFrom && ($deliveryTo)) {
                   if (array_key_exists($sellerId,$vendorDeliveryDays))
                    {
                        if ($vendorDeliveryDays[$sellerId]['delivery_to'] > $deliveryTo || ($vendorDeliveryDays[$sellerId]['delivery_to'] == $deliveryTo)) {
                            continue;
                        } else if($vendorDeliveryDays[$sellerId]['delivery_to'] < $deliveryTo) {
                            $vendorDeliveryDays[$sellerId]['final_days'] = $deliveryFrom.'-'.$deliveryTo;
                            $vendorDeliveryDays[$sellerId]['delivery_to'] = $deliveryTo;
                            $vendorDeliveryDays[$sellerId]['shop_title'] = $shopTitle;
                        } 
                    } else {
                        $vendorDeliveryDays[$sellerId]['final_days'] = $deliveryFrom.'-'.$deliveryTo;
                        $vendorDeliveryDays[$sellerId]['delivery_to'] = $deliveryTo;
                        $vendorDeliveryDays[$sellerId]['shop_title'] = $shopTitle;
                    }
                }
                $checkoutSession = $this->_checkoutSession->getQuote();
                $checkoutSession->setVendorDeliveryDays(serialize($vendorDeliveryDays))->save();
            }
            return $vendorDeliveryDays;  
        }
        
    }
}
