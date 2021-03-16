<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Product\Shipping;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class OrderPicking
 * @package Plenty\Item\Profile\Config\Source\Product\Shipping
 */
class OrderPicking implements OptionSourceInterface
{
    const SINGLE_PICKING                = 'single_picking';
    const NO_SINGLE_PICKING             = 'no_single_picking';
    const EXCLUDE_FROM_PICKING_LIST     = 'exclude_from_picklist';
    const NO_ORDER_PICKING              = 'exclude_from_picklist';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SINGLE_PICKING, 'label' => __('Single picking')],
            ['value' => self::NO_SINGLE_PICKING, 'label' => __('No single picking')],
            ['value' => self::EXCLUDE_FROM_PICKING_LIST, 'label' => __('Exclude from picking list')],
            ['value' => self::NO_ORDER_PICKING, 'label' => __('No order picking')]
        ];
    }
}