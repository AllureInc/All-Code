<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Api\Data\Config;

use Plenty\Core\Api\Data\Config\SourceInterface as CoreSourceInterface;
use Plenty\Core\Model\ResourceModel\Config\Source\Collection;

/**
 * Interface SourceInterface
 * @package Plenty\Item\Api\Data\Config
 */
interface SourceInterface extends CoreSourceInterface
{
    const CONFIG_SOURCE_ORDER_STATUS            = 'order_status';
    const CONFIG_SOURCE_PAYMENT_METHOD          = 'payment_method';
    const CONFIG_SOURCE_SHIPPING_PROFILE       = 'shipping_profile';

    /**
     * @param null $statusId
     * @return $this
     */
    public function collectOrderStatuses($statusId = null);

    /**
     * @param null $priceId
     * @return $this
     */
    public function collectPaymentMethods($priceId = null);

    /**
     * @param null $profileId
     * @return $this
     */
    public function collectShippingProfiles($profileId = null);

    /**
     * @return Collection
     */
    public function getOrderStatuses(): Collection;

    /**
     * @return Collection
     */
    public function getPaymentMethods(): Collection;

    /**
     * @return Collection
     */
    public function getShippingProfiles(): Collection;
}