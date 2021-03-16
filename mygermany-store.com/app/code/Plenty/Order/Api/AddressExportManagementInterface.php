<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Api;

use Plenty\Order\Api\Data\Profile\OrderExportInterface;
use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;
use Plenty\Order\Api\Data\Export\OrderInterface as PlentyOrderInterface;

/**
 * Interface AddressExportManagementInterface
 * @package Plenty\Order\Api
 */
interface AddressExportManagementInterface
{
    const ADDRESS_TYPE_BILLING          = 'billing';
    const ADDRESS_TYPE_SHIPPING         = 'shipping';

    const ADDRESS_ENTITY_ACCOUNT        = 'address_entity_account';
    const ADDRESS_ENTITY_CONTACT        = 'address_entity_contact';
    const ADDRESS_ENTITY_ORDER          = 'address_entity_order';

    /**
     * @return OrderExportInterface
     */
    public function getProfileEntity();

    /**
     * @param OrderExportInterface $profileEntity
     * @return $this
     */
    public function setProfileEntity($profileEntity);

    /**
     * @return string|null
     */
    public function getAddressEntity();

    /**
     * @param string $addressEntity
     * @return $this
     */
    public function setAddressEntity(string $addressEntity);

    /**
     * @return int|null
     */
    public function getBillingAddressId();

    /**
     * @param int $addressId
     * @return $this
     */
    public function setBillingAddressId($addressId);

    /**
     * @return int|null
     */
    public function getShippingAddressId();

    /**
     * @param int $addressId
     * @return $this
     */
    public function setShippingAddressId($addressId);

    /**
     * @return array
     */
    public function getResponse();

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null);

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addResponse($data, $key = null);

    /**
     * @param SalesOrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @return $this
     */
    public function execute(
        SalesOrderInterface $salesOrder, PlentyOrderInterface $plentyOrder
    );
}