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

use Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems as ResourcePreorderItems;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Webkul\MarketplacePreorder\Model\PreorderItemsRepository as ItemsRepository;
use Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Proerder Item Model
 *
 */
class PreorderItems extends AbstractModel implements PreorderItemsInterface, IdentityInterface
{
    /**
     * Proerder Item cache tag
     */
    const CACHE_TAG = 'preorder_items';

    /**#@+
     * Proerder Item's statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = 'preorder_items';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'preorder_items';

     /**
     * @var Items
     */
    protected $_itemsRepository;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /** @var PreorderItemsInterfaceFactory  */

    protected $_preorderItemsFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ItemsRepository $itemsRepository,
        PreorderItemsInterfaceFactory $preorderItemsFactory,
        DataObjectHelper $dataObjectHelper,
        \Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems $resource,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_itemsRepository = $itemsRepository;
        $this->_preorderItemsFactory = $preorderItemsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems');
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
     * Retrieve item model with preorder item data
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getDataModel()
    {
        $itemData = $this->getData();
        $itemsDataObject = $this->_preorderItemsFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $itemsDataObject,
            $itemData,
            '\Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface'
        );
        $itemsDataObject->setId($this->getId());

        return $itemsDataObject;
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
     * Retrieve Seller ID
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * Retrieve Order ID
     *
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Retrieve Item ID
     *
     * @return int|null
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
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
     * Get Parent ID
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
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
     * Get CustomerEmail
     *
     * @return string|null
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * Get PreorderPrecent
     *
     * @return int|null
     */
    public function getPreorderPrecent()
    {
        return $this->getData(self::PREORDER_PERCENT);
    }

    /**
     * Get PaidAmount
     *
     * @return int|null
     */
    public function getPaidAmount()
    {
        return $this->getData(self::PAID_AMOUNT);
    }

    /**
     * Get RemainingAmount
     *
     * @return int|null
     */
    public function getRemainingAmount()
    {
        return $this->getData(self::REMAINING_AMOUNT);
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
     * Get Type
     *
     * @return int|null
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get Notify
     *
     * @return int|null
     */
    public function getNotify()
    {
        return $this->getData(self::NOTIFY);
    }

    /**
     * Get Time
     *
     * @return string|null
     */
    public function getTime()
    {
        return $this->getData(self::TIME);
    }

    /**
     * Get OrderMode
     *
     * @return string|null
     */
    public function getOrderMode()
    {
        return $this->getData(self::ORDER_MODE);
    }

    /**
     * Get completed preorder qty
     *
     * @return int|null
     */
    public function getCompletedPreorderQty()
    {
        return $this->getData(self::COMPLETED_PREORDER_QTY);
    }

     /**
     * Get Tax Class
     *
     * @return string|null
     */
    public function getTaxClassId()
    {
        return $this->getData(self::TAX_CLASS_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return PreorderItemsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set Seller ID
     *
     * @return PreorderItemsInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Set Order ID
     *
     * @return PreorderItemsInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set Item ID
     *
     * @return PreorderItemsInterface
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * Set Produt ID
     *
     * @return PreorderItemsInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Set Parent ID
     *
     * @return PreorderItemsInterface
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    /**
     * Set Customer ID
     *
     * @return PreorderItemsInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set CustomerEmail
     *
     * @return PreorderItemsInterface
     */
    public function setCustomerEmail($email)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $email);
    }

    /**
     * Set PreorderPrecent
     *
     * @return PreorderItemsInterface
     */
    public function setPreorderPrecent($percent)
    {
        return $this->setData(self::PREORDER_PERCENT, $percent);
    }

    /**
     * Set PaidAmount
     *
     * @return PreorderItemsInterface
     */
    public function setPaidAmount($paidAmount)
    {
        return $this->setData(self::PAID_AMOUNT, $paidAmount);
    }

    /**
     * Set RemainingAmount
     *
     * @return PreorderItemsInterface
     */
    public function setRemainingAmount($remaining)
    {
        return $this->setData(self::REMAINING_AMOUNT, $remaining);
    }

    /**
     * Set qty
     *
     * @return PreorderItemsInterface
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * Set Type
     *
     * @return PreorderItemsInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Set Status
     *
     * @return PreorderItemsInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set Notify
     *
     * @return PreorderItemsInterface
     */
    public function setNotify($status)
    {
        return $this->setData(self::NOTIFY, $status);
    }

    /**
     * Set Time
     *
     * @return PreorderItemsInterface
     */
    public function setTime($time)
    {
        return $this->setData(self::TIME, $time);
    }

    /**
     * Set OrderMode
     *
     * @return PreorderItemsInterface
     */
    public function setOrderMode($mode)
    {
        return $this->setData(self::ORDER_MODE, $mode);
    }

    /**
     * Set completed preorder qty
     *
     * @return PreorderItemsInterface
     */
    public function setCompletedPreorderQty($completedQty)
    {
        return $this->setData(self::COMPLETED_PREORDER_QTY, $completedQty);
    }

     /**
     * Set Tax Class
     *
     * @return PreorderItemsInterface
     */
    public function setTaxClassId($taxId)
    {
        return $this->setData(self::TAX_CLASS_ID, $taxId);
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
