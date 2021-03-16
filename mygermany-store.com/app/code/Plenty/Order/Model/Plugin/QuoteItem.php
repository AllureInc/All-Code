<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Model\Plugin;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class QuoteItem
 * @package Plenty\Order\Model\Plugin
 */
class QuoteItem
{
    /**
     * @param ToOrderItem $subject
     * @param OrderItemInterface $orderItem
     * @param AbstractItem $item
     * @param array $additional
     * @return OrderItemInterface
     */
    public function afterConvert(
        ToOrderItem $subject,
        OrderItemInterface $orderItem,
        AbstractItem $item,
        $additional = []
    ) {
        if (!$plentyVariationId = $item->getProduct()->getData('plenty_variation_id')) {
            return $orderItem;
        }
        $orderItem->setPlentyVariationId($plentyVariationId);
        return $orderItem;
    }
}
