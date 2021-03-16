<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest;

use Magento\Framework\Data\Collection;

/**
 * Interface PaymentInterface
 * @package Plenty\Order\Rest
 */
interface PaymentInterface
{
    /**
     * @param int $page
     * @param null $paymentId
     * @return Collection
     */
    public function getSearchPayments(
        $page = 1,
        $paymentId = null
    ) : Collection;

    /**
     * @param int $page
     * @param null $pluginKey
     * @return Collection
     */
    public function getSearchPaymentMethods(
        $page = 1,
        $pluginKey = null
    ) : Collection;

    /**
     * @param array $requestParams
     * @param null $paymentId
     * @return mixed
     */
    public function createPayment(array $requestParams, $paymentId = null);

    /**
     * @param array $requestParams
     * @param bool $update
     * @return mixed
     */
    public function createPaymentMethod(array $requestParams, $update = false);

    /**
     * @param array $requestParams
     * @param null $propertyId
     * @return mixed
     */
    public function createPaymentProperty(array $requestParams, $propertyId = null);

    /**
     * @param $paymentId
     * @param $orderId
     * @return mixed
     */
    public function createPaymentOrderRelation($paymentId, $orderId);

    /**
     * @param $paymentId
     * @param $contactId
     * @return mixed
     */
    public function createPaymentContactRelation($paymentId, $contactId);
}