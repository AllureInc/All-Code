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
namespace Webkul\MarketplacePreorder\Plugin;

use Magento\Framework\Event\Observer;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;

class QuantityValidator
{
    /**
     * @var \Webkul\Preorder\Helper\Data
     */
    private $_preorderHelper;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var StockStateInterface
     */
    protected $stockState;

    protected $observer;

    /**
     * Initialize dependencies.
     *
     * @param \Webkul\Preorder\Helper\Data $preorderHelper
     */
    public function __construct(
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState,
        \Magento\Framework\Event\Observer $observer
    ) {
        $this->_preorderHelper = $preorderHelper;
        $this->stockRegistry = $stockRegistry;
        $this->stockState = $stockState;
        $this->observer = $observer;
    }

    public function aroundValidate(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
    
        $quoteItem = $observer->getEvent()->getItem();

        $productId = $quoteItem->getProduct()->getId();

        /**
         * Check if product in stock. For composite products check base (parent) item stock status
         */
        if ($quoteItem->getParentItem()) {
            $product = $quoteItem->getParentItem()->getProduct();
            $productId = $product->getId();
        }
        $qtyCheck = $this->_preorderHelper->getQtyCheck($quoteItem, $quoteItem->getProduct());

        if (!$qtyCheck && $this->_preorderHelper->isPreorder($productId)) {
            $quoteItem->addErrorInfo(
                'cataloginventory',
                \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                __('Requested Quantity(s) to preorder this product is not available')
            );
            return;
        }
        if ($this->_preorderHelper->isPreorder($productId) || $this->_preorderHelper->isConfigPreorder($productId)) {
            return true;
        } else {
            $result = $proceed($observer);
            return $result;
        }
    }
}
