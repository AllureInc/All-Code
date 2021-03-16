<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model\Quote;

use Magento\Framework\Registry;

class Item extends \Magento\Quote\Model\Quote\Item
{
    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if (!$product || $itemProduct->getId() != $product->getId()) {
            return false;
        }
        $stickWithinParent = $product->getStickWithinParent();
        if ($stickWithinParent) {
            if ($this->getParentItem() !== $stickWithinParent) {
                return false;
            }
        }
        $itemOptions = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();

        if (!$this->compareOptions($itemOptions, $productOptions)) {
            return false;
        }
        if (!$this->compareOptions($productOptions, $itemOptions)) {
            return false;
        }
        $quote = $this->_quote->getAllItems();
        $walletProductId = $this->getHelper()->getWalletProductId();
        $params = $this->getRequest()->getParams();
        if (array_key_exists('product', $params)) {
            if ($walletProductId == $params['product']) {
                return false;
            }
        } elseif (array_key_exists('order_id', $params)) {
            $order = $this->getorderRegistry()->registry('current_order');
            $items = $order->getItemsCollection();
            return $this->checkWalletProduct($items);
        }
        return true;
    }

    public function getHelper()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('Webkul\Walletsystem\Helper\Data');

        return $helper;
    }

    public function getRequest()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('Magento\Framework\App\Request\Http');

        return $request;
    }

    public function getorderRegistry()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $registry = $objectManager->get('Magento\Framework\Registry');

        return $registry;
    }

    public function checkWalletProduct($items)
    {
        $walletProductId = $this->getHelper()->getWalletProductId();
        foreach ($items as $item) {
            $productId = $item->getproduct()->getId();
            if ($productId == $walletProductId) {
                return false;
            }
        }
        return true;
    }
}
