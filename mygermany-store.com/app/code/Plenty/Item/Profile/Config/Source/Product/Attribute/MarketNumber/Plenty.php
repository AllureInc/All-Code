<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Product\Attribute\MarketNumber;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Plenty
 * @package Plenty\Item\Profile\Config\Source\Product\Attribute\MarketNumber
 */
class Plenty implements OptionSourceInterface
{
    const ASIN  = 'ASIN';
    const EPID  = 'EPID';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ASIN,  'label' => 'ASIN'],
            ['value' => self::EPID,  'label' => 'EPID']
        ];
    }
}