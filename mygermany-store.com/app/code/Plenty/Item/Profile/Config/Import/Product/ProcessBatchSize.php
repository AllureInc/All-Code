<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Import\Product;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ProcessBatchSize
 * @package Plenty\Item\Profile\Config\Import\Product
 */
class ProcessBatchSize implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 50,  'label' => __('50 records')],
            ['value' => 100, 'label' => __('100 records')],
            ['value' => 150, 'label' => __('150 records')],
            ['value' => 200, 'label' => __('200 records')],
            ['value' => 250, 'label' => __('250 records')],
            ['value' => 300, 'label' => __('300 records')],
            ['value' => 350, 'label' => __('350 records')],
            ['value' => 400, 'label' => __('400 records')],
            ['value' => 450, 'label' => __('450 records')],
            ['value' => 500, 'label' => __('500 records')],
            ['value' => 750, 'label' => __('750 records')],
            ['value' => 1000, 'label' => __('1000 records')],
            ['value' => 1500, 'label' => __('1500 records')],
            ['value' => 2000, 'label' => __('2000 records')],
            ['value' => 2500, 'label' => __('2500 records')],
            ['value' => 3000, 'label' => __('3000 records')],
            ['value' => 3500, 'label' => __('3500 records')],
            ['value' => 4000, 'label' => __('4000 records')],
            ['value' => 5000, 'label' => __('5000 records')],
            ['value' => 6000, 'label' => __('6000 records')],
            ['value' => 7000, 'label' => __('7000 records')],
            ['value' => 8000, 'label' => __('8000 records')],
            ['value' => 9000, 'label' => __('9000 records')],
            ['value' => 10000, 'label' => __('10000 records')]
        ];
    }
}