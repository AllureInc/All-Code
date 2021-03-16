<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Product\Attribute\Url;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * @package Plenty\Item\Profile\Config\Source\Product\Attribute\Url
 */
class Options implements OptionSourceInterface
{
    const USE_EXISTING  = 1;
    const CREATE_NEW    = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::USE_EXISTING,  'label' => __('Use existing url')],
            ['value' => self::CREATE_NEW,  'label' => __('Create new based on product name')]
        ];
    }
}