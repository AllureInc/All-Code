<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCoupons
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCoupons\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpSellerCoupons\Helper\Data;

/**
 * Webkul MpSellerCoupons SalesQuoteRemoveItemObserver Observer.
 */
class SalesQuoteRemoveItemObserver implements ObserverInterface
{
    /**
     * @var Webkul\MpSellerCoupons\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpDataHelper;

    /**
     * @param Data                            $helperData
     * @param \Webkul\Marketplace\Helper\Data $mpDataHelper
     */
    public function __construct(
        Data $helperData,
        \Magento\Checkout\Model\Cart $cart,
        \Webkul\Marketplace\Helper\Data $mpDataHelper
    ) {
        $this->_helperData = $helperData;
        $this->_cart = $cart;
        $this->_mpDataHelper = $mpDataHelper;
    }

    /**
     * sales_quote_remove_item event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getQuoteItem();
                      
        $productId = $item->getProduct()->getId();
        $mpProductCollection = $this->_mpDataHelper
                                    ->getSellerProductDataByProductId($productId);
        $sellerId="";
        foreach ($mpProductCollection as $sellerProduct) {
            $sellerId = $sellerProduct->getSellerId();
        }
        if (!empty($sellerId) && isset($couponInfo[$sellerId])) {
            $couponInfo = $this->_helperData->getAppliedCoupons();
            if ($couponInfo) {
                $price = 0;
                $updateCoupon = 0;
                
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $cart = $this->_cart;
                $items = $cart->getQuote()->getAllVisibleItems();
                foreach ($items as $item) {
                    $productId = $item->getProductId();
                    $sellers = [];
                    $isMpPro = $this->_mpDataHelper->getSellerProductDataByProductId($productId)->getItems();
                    foreach ($mpProductCollection as $sellerProduct) {
                        $seller = $sellerProduct->getSellerId();
                        if ($seller == $sellerId) {
                            $price = $price + ($item->getPrice() * $item->getQty());
                            $updateCoupon = 1;
                        }
                    }
                }
                if ($updateCoupon) {
                    if ($price < abs($couponInfo[$sellerId]['amount'])) {
                        $couponInfo[$sellerId]['amount'] = -$price;
                        $this->_helperData->setCouponCheckoutSession($couponInfo);
                        $this->_helperData->refreshPricesAtCartPage();
                    }
                } else {
                    unset($couponInfo[$sellerId]);
                    $this->_helperData->setCouponCheckoutSession($couponInfo);
                    $this->_helperData->refreshPricesAtCartPage();
                }
            }
        }
        return;
    }
}
