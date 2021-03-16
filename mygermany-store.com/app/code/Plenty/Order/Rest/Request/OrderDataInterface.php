<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Request;

use Plenty\Order\Rest\AbstractData;
use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;
use Plenty\Order\Api\Data\Export\OrderInterface as PlentyOrderInterface;

/**
 * Interface VariationDataInterface
 * @package Plenty\Item\Rest\Request
 */
interface OrderDataInterface extends AbstractData\OrderDataInterface
{
    /**
     * @return array
     */
    public function getRequest();

    /**
     * @param array $request
     * @return mixed
     */
    public function setRequest(array $request);

    /**
     * @param SalesOrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @param $statusId
     * @return mixed
     */
    public function buildRequest(
        SalesOrderInterface $salesOrder,
        PlentyOrderInterface $plentyOrder,
        $statusId
    );
}