<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Request;

use Plenty\Customer\Rest\AbstractData;
use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;

/**
 * Interface ContactDataInterface
 * @package Plenty\Order\Rest\Request
 */
interface ContactDataInterface extends AbstractData\ContactDataInterface
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
     * @param null $language
     * @param null $referrerId
     * @return $this
     */
    public function buildRequest(
        SalesOrderInterface $salesOrder,
        $language = null,
        $referrerId = null
    );

    /**
     * @return array
     */
    public function getErrors();
}