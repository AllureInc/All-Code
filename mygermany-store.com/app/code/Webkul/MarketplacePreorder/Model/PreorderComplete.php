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
namespace Webkul\MarketplacePreorder\Model;

use Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterface;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller as ResourcePreorderComplete;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Proerder Complete Item Model
 *
 */
class PreorderComplete extends AbstractModel implements PreorderCompleteInterface, IdentityInterface
{
    /**
     * Proerder Item cache tag
     */
    const CACHE_TAG = 'preorder_complete';

    /**#@+
     * Proerder Item's statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = 'preorder_complete';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'preorder_complete';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\MarketplacePreorder\Model\ResourceModel\PreorderComplete');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Retrieve item id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderItemId()
    {
        return $this->getData(self::ORDER_ITEM_ID);
    }

    /**
     * Get Quote ID
     *
     * @return int|null
     */
    public function getQuoteItemId()
    {
        return $this->getData(self::QUOTE_ITEM_ID);
    }

    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Get Customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Get Produt ID
     *
     * @return int|null
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Get qty
     *
     * @return int|null
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return PreorderCompleteInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set Order Item ID
     *
     * @return PreorderCompleteInterface
     */
    public function setOrderItemId($itemId)
    {
        return $this->setData(self::ORDER_ITEM_ID, $itemId);
    }

    /**
     * Set Quote ID
     *
     * @return PreorderCompleteInterface
     */
    public function setQuoteItemId($itemId)
    {
        return $this->setData(self::QUOTE_ITEM_ID, $itemId);
    }

    /**
     * Set Order ID
     *
     * @return PreorderCompleteInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set Customer ID
     *
     * @return PreorderCompleteInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set Produt ID
     *
     * @return PreorderCompleteInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Set qty
     *
     * @return PreorderCompleteInterface
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Prepare block's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
