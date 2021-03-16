<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Model\Stock;

class Item extends \Magento\CatalogInventory\Model\Stock\Item
{
    /**
     * Retrieve Stock Availability.
     *
     * @return bool|int
     */
    public function getIsInStock()
    {
        $isInStock = $this->_getData(static::IS_IN_STOCK);
        $productId = $this->getProductId();

        $helper = $this->helper();
        if ($helper->isPreorder($productId)) {
            return true;
        } elseif ($helper->isConfigPreorder($productId)) {
            return true;
        } else {
            if (!$this->getManageStock()) {
                return true;
            }

            return (bool) $this->_getData(static::IS_IN_STOCK);
        }
    }

    public function getQty()
    {
        $isInStock = $this->_getData(static::IS_IN_STOCK);
        $productId = $this->getProductId();
        $helper = $this->helper();
        if ($helper->isPreorder($productId)) {
            return 999999999;
        } elseif ($helper->isConfigPreorder($productId)) {
            return 999999999;
        } else {
            return parent::getQty();
        }
    }

    public function helper()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Webkul\MarketplacePreorder\Helper\Data');
        return $helper;
    }
}
