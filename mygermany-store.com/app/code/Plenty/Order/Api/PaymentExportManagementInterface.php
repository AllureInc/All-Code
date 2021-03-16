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
interface PaymentExportManagementInterface
{
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
     * @return int|null
     */
    public function getPaymentId();

    /**
     * @param int $paymentId
     * @return $this
     */
    public function setPaymentId($paymentId);

    /**
     * @return int|null
     */
    public function getPaymentOrderAssignmentId();

    /**
     * @param $id
     * @return $this
     */
    public function setPaymentOrderAssignmentId($id);

    /**
     * @return int|null
     */
    public function getPaymentContactAssignmentId();

    /**
     * @param $id
     * @return $this
     */
    public function setPaymentContactAssignmentId($id);

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
        SalesOrderInterface $salesOrder,
        PlentyOrderInterface $plentyOrder
    );

    /**
     * @param SalesOrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @return mixed
     */
    public function createPaymentRelations(
        SalesOrderInterface $salesOrder,
        PlentyOrderInterface $plentyOrder
    );
}