<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Request;

use Plenty\Order\Rest\AbstractData;
use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;


/**
 * Interface VariationDataInterface
 * @package Plenty\Item\Rest\Request
 */
interface PaymentDataInterface extends AbstractData\PaymentDataInterface
{
    /**
     * @return array
     */
    public function getRequest();

    /**
     * @param array $request
     * @return $this
     */
    public function setRequest(array $request);

    /**
     * @param SalesOrderInterface $salesOrder
     * @param int $paymentMethodId
     * @return $this
     */
    public function buildRequest(SalesOrderInterface $salesOrder, $paymentMethodId);
}