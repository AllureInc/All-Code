<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Model\Export;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

use Plenty\Order\Api\Data\Export\OrderInterface;
use Plenty\Order\Model\ResourceModel;

/**
 * Class Order
 * @package Plenty\Order\Model\Export
 *
 * @method ResourceModel\Export\Order getResource()
 * @method ResourceModel\Export\Order\Collection getCollection()
 */
class Order extends AbstractModel
    implements OrderInterface, IdentityInterface
{
    const CACHE_TAG             = 'plenty_order_export_order';

    /**
     * @var string
     */
    protected $_cacheTag        = 'plenty_order_export_order';

    /**
     * @var string
     */
    protected $_eventPrefix     = 'plenty_order_export_order';

    /**
     * Resource constructor.
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Export\Order::class);
    }

    /**
     * Order constructor.
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context, 
        Registry $registry, 
        AbstractResource $resource = null, 
        AbstractDb $resourceCollection = null, 
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @return int
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @param $profileId
     * @return mixed|Order
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @param $orderId
     * @return mixed|Order
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return int|null
     */
    public function getOrderIncrementId()
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * @param $orderId
     * @return mixed|Order
     */
    public function setOrderIncrementId($orderId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderId);
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @param $customerId
     * @return Order
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param $status
     * @return Order
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return int|null
     */
    public function getPlentyOrderId()
    {
        return $this->getData(self::PLENTY_ORDER_ID);
    }

    /**
     * @param $plentyOrderId
     * @return Order
     */
    public function setPlentyOrderId($plentyOrderId)
    {
        return $this->setData(self::PLENTY_ORDER_ID, $plentyOrderId);
    }

    /**
     * @return int|null
     */
    public function getPlentyStatusId()
    {
        return $this->getData(self::PLENTY_STATUS_ID);
    }

    /**
     * @param $plentyStatusId
     * @return mixed|Order
     */
    public function setPlentyStatusId($plentyStatusId)
    {
        return $this->setData(self::PLENTY_STATUS_ID, $plentyStatusId);
    }

    /**
     * @return string|null
     */
    public function getPlentyStatusName()
    {
        return $this->getData(self::PLENTY_STATUS_NAME);
    }

    /**
     * @param $plentyStatusName
     * @return Order
     */
    public function setPlentyStatusName($plentyStatusName)
    {
        return $this->setData(self::PLENTY_STATUS_NAME, $plentyStatusName);
    }

    /**
     * @return string|null
     */
    public function getPlentyStatusLock()
    {
        return $this->getData(self::PLENTY_STATUS_LOCK);
    }

    /**
     * @param $plentyStatusLock
     * @return Order
     */
    public function setPlentyStatusLock($plentyStatusLock)
    {
        return $this->setData(self::PLENTY_STATUS_LOCK, $plentyStatusLock);
    }

    /**
     * @return int|null
     */
    public function getPlentyReferrerId()
    {
        return $this->getData(self::PLENTY_REFERRER_ID);
    }

    /**
     * @param $plentyReferrerId
     * @return Order
     */
    public function setPlentyReferrerId($plentyReferrerId)
    {
        return $this->setData(self::PLENTY_REFERRER_ID, $plentyReferrerId);
    }

    /**
     * @return int|null
     */
    public function getPlentyContactId()
    {
        return $this->getData(self::PLENTY_CONTACT_ID);
    }

    /**
     * @param $plentyContactId
     * @return mixed|Order
     */
    public function setPlentyContactId($plentyContactId)
    {
        return $this->setData(self::PLENTY_CONTACT_ID, $plentyContactId);
    }

    /**
     * @return int|null
     */
    public function getPlentyBillingAddressId()
    {
        return $this->getData(self::PLENTY_BILLING_ADDRESS_ID);
    }

    /**
     * @param $plentyBillingAddressId
     * @return Order
     */
    public function setPlentyBillingAddressId($plentyBillingAddressId)
    {
        return $this->setData(self::PLENTY_BILLING_ADDRESS_ID, $plentyBillingAddressId);
    }

    /**
     * @return int|null
     */
    public function getPlentyShippingAddressId()
    {
        return $this->getData(self::PLENTY_SHIPPING_ADDRESS_ID);
    }

    /**
     * @param $plentyShippingAddressId
     * @return Order
     */
    public function setPlentyShippingAddressId($plentyShippingAddressId)
    {
        return $this->setData(self::PLENTY_SHIPPING_ADDRESS_ID, $plentyShippingAddressId);
    }

    /**
     * @return int|null
     */
    public function getPlentyPaymentId()
    {
        return $this->getData(self::PLENTY_PAYMENT_ID);
    }

    /**
     * @param $plentyPaymentId
     * @return mixed|Order
     */
    public function setPlentyPaymentId($plentyPaymentId)
    {
        return $this->setData(self::PLENTY_PAYMENT_ID, $plentyPaymentId);
    }

    /**
     * @return int|null
     */
    public function getPlentyPaymentMethodId()
    {
        return $this->getData(self::PLENTY_PAYMENT_METHOD_ID);
    }

    /**
     * @param $plentyPaymentMethodId
     * @return Order
     */
    public function setPlentyPaymentMethodId($plentyPaymentMethodId)
    {
        return $this->setData(self::PLENTY_PAYMENT_METHOD_ID, $plentyPaymentMethodId);
    }

    /**
     * @return int|null
     */
    public function getPlentyPaymentStatusId()
    {
        return $this->getData(self::PLENTY_PAYMENT_STATUS_ID);
    }

    /**
     * @param $plentyPaymentStatusId
     * @return Order
     */
    public function setPlentyPaymentStatusId($plentyPaymentStatusId)
    {
        return $this->setData(self::PLENTY_PAYMENT_STATUS_ID, $plentyPaymentStatusId);
    }

    /**
     * @return int|null
     */
    public function getPlentyPaymentOrderAssignmentId()
    {
        return $this->getData(self::PLENTY_PAYMENT_ORDER_ASSIGNMENT_ID);
    }

    /**
     * @param $plentyPaymentAssignmentId
     * @return Order
     */
    public function setPlentyPaymentOrderAssignmentId($plentyPaymentAssignmentId)
    {
        return $this->setData(self::PLENTY_PAYMENT_ORDER_ASSIGNMENT_ID, $plentyPaymentAssignmentId);
    }

    /**
     * @return int|null
     */
    public function getPlentyPaymentContactAssignmentId()
    {
        return $this->getData(self::PLENTY_PAYMENT_CONTACT_ASSIGNMENT_ID);
    }

    /**
     * @param $plentyPaymentAssignmentId
     * @return mixed|Order
     */
    public function setPlentyPaymentContactAssignmentId($plentyPaymentAssignmentId)
    {
        return $this->setData(self::PLENTY_PAYMENT_CONTACT_ASSIGNMENT_ID, $plentyPaymentAssignmentId);
    }

    /**
     * @return int|null
     */
    public function getPlentyLocationId()
    {
        return $this->getData(self::PLENTY_LOCATION_ID);
    }

    /**
     * @param $plentyLocationId
     * @return Order
     */
    public function setPlentyLocationId($plentyLocationId)
    {
        return $this->setData(self::PLENTY_LOCATION_ID, $plentyLocationId);
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @param $message
     * @return mixed|Order
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $createdAt
     * @return mixed|Order
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $updatedAt
     * @return mixed|Order
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @return string|null
     */
    public function getProcessedAt()
    {
        return $this->getData(self::PROCESSED_AT);
    }

    /**
     * @param $processed
     * @return Order
     */
    public function setProcessedAt($processed)
    {
        return $this->setData(self::PROCESSED_AT, $processed);
    }

    /**
     * @return bool|mixed
     */
    public function getIsBillingSameAsShipping()
    {
        return $this->getData(self::IS_BILLING_SAME_AS_SHIPPING);
    }

    /**
     * @param $bool
     * @return $this|OrderInterface
     */
    public function setIsBillingSameAsShipping($bool)
    {
        $this->setData(self::IS_BILLING_SAME_AS_SHIPPING, $bool);
        return $this;
    }

    public function addOrdersToExport(array $orderIds)
    {
        /** @todo Implement manual order export */
    }
}