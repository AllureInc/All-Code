<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Product\Attribute\Weight\Unit;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Plenty
 * @package Plenty\Item\Profile\Config\Source\Product\Attribute\Weight\Unit
 */
class Plenty implements OptionSourceInterface
{
    const WEIGHT_G          = 'weight_g';
    const WEIGHT_NET_G      = 'weight_net_g';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => false,  'label' => __('Do not export')],
            ['value' => self::WEIGHT_G,  'label' => __('Gross weight')],
            ['value' => self::WEIGHT_NET_G,  'label' => __('Net weight')]
        ];
    }
}