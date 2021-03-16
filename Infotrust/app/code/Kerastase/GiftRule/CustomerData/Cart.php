<?php

namespace Kerastase\GiftRule\CustomerData;

class Cart extends \Magento\Checkout\CustomerData\Cart
{
    /**
     * Fix: NVI product not displaying in mini-cart
     *
     * Get array of last added items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getRecentItems()
    {
        $items = [];
        if (!$this->getSummaryCount()) {
            return $items;
        }

        foreach (array_reverse($this->getAllQuoteItems()) as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $product =  $item->getOptionByCode('product_type') !== null
                    ? $item->getOptionByCode('product_type')->getProduct()
                    : $item->getProduct();

                $products = $this->catalogUrl->getRewriteByProductStore([$product->getId() => $item->getStoreId()]);
                if (!isset($products[$product->getId()]) && !$item->getIsFreeProduct()) {
                    continue;
                }
                if ($item->getIsFreeProduct()) {
                    $urlDataObject = new \Magento\Framework\DataObject();

                } else {
                    $urlDataObject = new \Magento\Framework\DataObject($products[$product->getId()]);
                }

                $item->getProduct()->setUrlDataObject($urlDataObject);
            }
            $items[] = $this->itemPoolInterface->getItemData($item);
        }
        return $items;
    }
}
