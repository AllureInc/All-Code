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
namespace Webkul\MarketplacePreorder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as Products;

class PreorderPrice implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Webkul\Preorder\Model\ItemFactory
     */
    protected $_item;

    /**
     * @var \Webkul\Preorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @var Items
     */
    protected $_itemCollection;

    /**
     * @var Products
     */
    protected $_productCollection;

    /**
     * @param RequestInterface $request
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\MarketplacePreorder\Model\ItemFactory $item
     * @param \Webkul\MarketplacePreorder\Helper\Data $preorderHelper
     * @param Items $itemCollection
     * @param CollectionFactory $preorderCollection
     * @param Products $productCollection
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\MarketplacePreorder\Model\PreorderItemsFactory $item,
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper,
        Products $productCollection
    ) {
        $this->_request = $request;
        $this->_customerSession = $customerSession;
        $this->_messageManager = $messageManager;
        $this->_item = $item;
        $this->_preorderHelper = $preorderHelper;
        $this->_productCollection = $productCollection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_preorderHelper;
        $item = $observer->getEvent()->getData('quote_item');
        $product = $observer->getEvent()->getData('product');
        $productId = $this->getFinalProductId($product);

        $this->setPreorderPrice($item, $product, $productId);
        $preorderCompleteProductId = $helper->getPreorderCompleteProductId();
        if ($productId == $preorderCompleteProductId) {
            $result = $this->processPreorderCompleteData($item);
            if ($result['error']) {
                $this->_messageManager->addNotice(__($result['msg']));
            }
        }
    }

    /**
     * Get Product Id
     *
     * @param object $product
     *
     * @return int
     */
    public function getFinalProductId($product)
    {
        $helper = $this->_preorderHelper;
        $productId = $product->getId();
        $data = $this->_request->getParams();
        if (array_key_exists('selected_configurable_option', $data)) {
            if ($data['selected_configurable_option'] != '') {
                $productId = $data['selected_configurable_option'];
            } else {
                if (array_key_exists('super_attribute', $data)) {
                    $info = $data['super_attribute'];
                    $productId = $helper->getAssociatedId($info, $product);
                }
            }
        }
        return $productId;
    }

    /**
     * Set Preorder Product Price
     *
     * @param object $item
     * @param object $product
     * @param int $productId
     *
     * @return int
     */
    public function setPreorderPrice($item, $product, $productId)
    {
        $msg = 'Requested quantity(s) of preorder this product is not available.';
        $helper = $this->_preorderHelper;
        $sellerId = $helper->getSellerIdByProductId($productId);
        $preorderPercent = (int) $helper->getPreorderPercent($sellerId);
        if ($helper->isPartialPreorder($productId)) {
            $id = (int) $item->getId();
            $item = ($item->getParentItem() ? $item->getParentItem() : $item);
            $price = $helper->getPreorderPrice($product, $productId);
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
        }
    }

    /**
     * Get Product Id
     *
     * @param object $quoteItem
     *
     * @return array
     */
    public function processPreorderCompleteData($quoteItem)
    {
        $itemId = (int) $quoteItem->getId();
        $helper = $this->_preorderHelper;
        if (!$this->_customerSession->isLoggedIn()) {
            $msg = 'There was some error while processing your request.';
            $result = ['error'=> true, 'msg'=> $msg];
            return $result;
        }
        $data = $this->_request->getParams();
        $qty = $data['qty'];
        $orderId = $data['order_id'];
        $orderItemId = $data['item_id'];
        $preorderProductId = $data['pro_id'];
        $stockStatus = 0;
        $preorderQty = 0;
        $collection = $this->_productCollection->create();
        $table = 'cataloginventory_stock_item';
        $bind = 'product_id = entity_id';
        $cond = '{{table}}.stock_id = 1';
        $type = 'left';
        $alias = 'is_in_stock';
        $field = 'is_in_stock';
        $collection->joinField($alias, $table, $field, $bind, $cond, $type);
        $alias = 'qty';
        $field = 'qty';
        $collection->joinField($alias, $table, $field, $bind, $cond, $type);
        $collection->addFieldToFilter('entity_id', $preorderProductId);
        foreach ($collection as $value) {
            $stockStatus = $value->getIsInStock();
            $preorderQty = $value->getQty();
        }
        if ($stockStatus == 0 || $qty > $preorderQty) {
            $msg = 'Product is not available.';
            $result = ['error'=> true, 'msg'=> $msg];
            return $result;
        }
        if ($itemId > 0) {
            $msg = 'Already added to cart.';
            $result = ['error'=> true, 'msg'=> $msg];
            return $result;
        }
        $values = [$orderItemId, $orderId];
        $fields = ['item_id', 'order_id'];
        $item = $helper->getPreorderItemCollectionData($fields, $values);
        if ($item) {
            $remainingAmount = $item->getRemainingAmount();
            $unitPrice = $remainingAmount;
            $quoteItem->setCustomPrice($unitPrice);
            $quoteItem->setOriginalCustomPrice($unitPrice);
            $quoteItem->getProduct()->setIsSuperMode(true);
            return ['error' => false];
        }
        $msg = 'Something went wrong.';
        $result = ['error'=> true, 'msg'=> $msg];
        return $result;
    }
}
