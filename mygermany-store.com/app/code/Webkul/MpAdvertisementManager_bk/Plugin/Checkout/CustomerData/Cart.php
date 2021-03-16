<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Plugin\Checkout\CustomerData;

class Cart
{
    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_helper;

    /**
     * __construct
     *
     * @param \Webkul\MpAdvertisementManager\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpAdvertisementManager\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
    ) {
        $this->_helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->catalogUrl = $catalogUrl;
    }

    /**
     * plugin to stop add to cart of ads plan product
     *
     * @param \Magento\Quote\Model\Quote
     * @param Closure                       $proceed
     * @param Magento\Catalog\Model\Product $product
     *
     * @return html
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        return $this->getRecentItems($subject, $result);
    }

    protected function getRecentItems($subject, $result)
    {
        $reflFoo = new \ReflectionClass('\Magento\Checkout\CustomerData\Cart');
        $reflBar = $reflFoo->getProperty('itemPoolInterface');
        $reflBar->setAccessible(true);
        foreach (array_reverse($this->getAllQuoteItems()) as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $product =  $item->getOptionByCode('product_type') !== null
                    ? $item->getOptionByCode('product_type')->getProduct()
                    : $item->getProduct();
                if ($product->getSku()== 'wk_mp_ads_plan') {
                    $products[$product->getId()] = ['store_id'=>$product->getStoreId(), 'visibility'=> $product->getVisibility(), 'url_rewrite'=>'#'];
                    $urlDataObject = new \Magento\Framework\DataObject($products[$product->getId()]);
                    $item->getProduct()->setUrlDataObject($urlDataObject);
                    $result['items'][]= $reflBar->getValue($subject)->getItemData($item);
                }
            }
        }
        return $result;
    }

    protected function getQuote()
    {
        $this->quote = $this->checkoutSession->getQuote();
        return $this->quote;
    }

    /**
     * Return customer quote items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getAllQuoteItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }
}
