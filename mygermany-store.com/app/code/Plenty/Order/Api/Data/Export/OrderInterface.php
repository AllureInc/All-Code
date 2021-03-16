<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Api\Data\Export;

/**
 * Interface OrderInterface
 * @package Plenty\Order\Api\Data\Export
 */
interface OrderInterface
{
    const ENTITY_ID                             = 'entity_id';
    const PROFILE_ID                            = 'profile_id';
    const ORDER_ID                              = 'order_id';
    const ORDER_INCREMENT_ID                    = 'order_increment_id';
    const CUSTOMER_ID                           = 'customer_id';
    const STATUS                                = 'status';
    const PLENTY_ORDER_ID                       = 'plenty_order_id';
    const PLENTY_STATUS_ID                      = 'plenty_status_id';
    const PLENTY_STATUS_NAME                    = 'plenty_status_name';
    const PLENTY_STATUS_LOCK                    = 'plenty_status_lock';
    const PLENTY_REFERRER_ID                    = 'plenty_referrer_id';
    const PLENTY_CONTACT_ID                     = 'plenty_contact_id';
    const PLENTY_BILLING_ADDRESS_ID             = 'plenty_billing_address_id';
    const PLENTY_SHIPPING_ADDRESS_ID            = 'plenty_shipping_address_id';
    const PLENTY_PAYMENT_ID                     = 'plenty_payment_id';
    const PLENTY_PAYMENT_METHOD_ID              = 'plenty_payment_method_id';
    const PLENTY_PAYMENT_STATUS_ID              = 'plenty_payment_status_id';
    const PLENTY_PAYMENT_ORDER_ASSIGNMENT_ID    = 'plenty_payment_order_assignment_id';
    const PLENTY_PAYMENT_CONTACT_ASSIGNMENT_ID  = 'plenty_payment_contact_assignment_id';
    const PLENTY_LOCATION_ID                    = 'plenty_location_id';
    const MESSAGE                               = 'message';
    const CREATED_AT                            = 'created_at';
    const UPDATED_AT                            = 'updated_at';
    const PROCESSED_AT                          = 'processed_at';

    const IS_BILLING_SAME_AS_SHIPPING           = 'is_billing_same_as_shipping';

    // SALES/QUOTE ATTRIBUTES
    const PLENTY_ORDER_STATUS                   = 'plenty_order_status';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getProfileId();

    /**
     * @param $profileId
     * @return mixed
     */
    public function setProfileId($profileId);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param $orderId
     * @return mixed
     */
    public function setOrderId($orderId);

    /**
     * @return int|null
     */
    public function getOrderIncrementId();

    /**
     * @param $orderId
     * @return mixed
     */
    public function setOrderIncrementId($orderId);

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @param $customerId
     * @return mixed
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param $status
     * @return mixed
     */
    public function setStatus($status);

    /**
     * @return int|null
     */
    public function getPlentyOrderId();

    /**
     * @param $plentyOrderId
     * @return mixed
     */
    public function setPlentyOrderId($plentyOrderId);

    /**
     * @return int|null
     */
    public function getPlentyStatusId();

    /**
     * @param $plentyStatusId
     * @return mixed
     */
    public function setPlentyStatusId($plentyStatusId);

    /**
     * @return string|null
     */
    public function getPlentyStatusName();

    /**
     * @param $plentyStatusName
     * @return mixed
     */
    public function setPlentyStatusName($plentyStatusName);

    /**
     * @return string|null
     */
    public function getPlentyStatusLock();

    /**
     * @param $plentyStatusLock
     * @return mixed
     */
    public function setPlentyStatusLock($plentyStatusLock);

    /**
     * @return int|null
     */
    public function getPlentyReferrerId();

    /**
     * @param $plentyReferrerId
     * @return mixed
     */
    public function setPlentyReferrerId($plentyReferrerId);

    /**
     * @return int|null
     */
    public function getPlentyContactId();

    /**
     * @param $plentyContactId
     * @return mixed
     */
    public function setPlentyContactId($plentyContactId);

    /**
     * @return int|null
     */
    public function getPlentyBillingAddressId();

    /**
     * @param $plentyBillingAddressId
     * @return mixed
     */
    public function setPlentyBillingAddressId($plentyBillingAddressId);

    /**
     * @return int|null
     */
    public function getPlentyShippingAddressId();

    /**
     * @param $plentyShippingAddressId
     * @return mixed
     */
    public function setPlentyShippingAddressId($plentyShippingAddressId);

    /**
     * @return int|null
     */
    public function getPlentyPaymentId();

    /**
     * @param $plentyPaymentId
     * @return mixed
     */
    public function setPlentyPaymentId($plentyPaymentId);

    /**
     * @return int|null
     */
    public function getPlentyPaymentMethodId();

    /**
     * @param $plentyPaymentMethodId
     * @return mixed
     */
    public function setPlentyPaymentMethodId($plentyPaymentMethodId);

    /**
     * @return int|null
     */
    public function getPlentyPaymentStatusId();

    /**
     * @param $plentyPaymentStatusId
     * @return mixed
     */
    public function setPlentyPaymentStatusId($plentyPaymentStatusId);

    /**
     * @return int|null
     */
    public function getPlentyPaymentOrderAssignmentId();

    /**
     * @param $plentyPaymentAssignmentId
     * @return mixed
     */
    public function setPlentyPaymentOrderAssignmentId($plentyPaymentAssignmentId);

    /**
     * @return int|null
     */
    public function getPlentyPaymentContactAssignmentId();

    /**
     * @param $plentyPaymentAssignmentId
     * @return mixed
     */
    public function setPlentyPaymentContactAssignmentId($plentyPaymentAssignmentId);

    /**
     * @return int|null
     */
    public function getPlentyLocationId();

    /**
     * @param $plentyLocationId
     * @return mixed
     */
    public function setPlentyLocationId($plentyLocationId);

    /**
     * @return string|null
     */
    public function getMessage();

    /**
     * @param $message
     * @return mixed
     */
    public function setMessage($message);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param $updatedAt
     * @return mixed
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string|null
     */
    public function getProcessedAt();

    /**
     * @param $processed
     * @return mixed
     */
    public function setProcessedAt($processed);

    /**
     * @return bool
     */
    public function getIsBillingSameAsShipping();

    /**
     * @param $bool
     * @return $this
     */
    public function setIsBillingSameAsShipping($bool);
}