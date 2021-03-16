<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Product\Attribute\Name;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Plenty
 * @package Plenty\Item\Profile\Config\Source\Product\Attribute\Name
 */
class Plenty implements OptionSourceInterface
{
    const NAME =	'name';
    const NAME2 =	'name2';
    const NAME3 =	'name3';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NAME,  'label' => __('Name')],
            ['value' => self::NAME2,  'label' => __('Name 2')],
            ['value' => self::NAME3,  'label' => __('Name 3')]
        ];
    }
}