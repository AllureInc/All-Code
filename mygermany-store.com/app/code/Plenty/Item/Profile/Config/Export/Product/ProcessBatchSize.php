<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Export\Product;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ProcessBatchSize
 * @package Plenty\Item\Profile\Config\Export\Product
 * @deprecated since 0.1.1
 */
class ProcessBatchSize implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 10,  'label' => __('10 records')],
            ['value' => 20, 'label' => __('20 records')],
            ['value' => 30, 'label' => __('30 records')],
            ['value' => 40, 'label' => __('40 records')],
            ['value' => 50, 'label' => __('50 records')],
            ['value' => 60, 'label' => __('60 records')],
            ['value' => 70, 'label' => __('70 records')],
            ['value' => 80, 'label' => __('80 records')],
            ['value' => 90, 'label' => __('90 records')],
            ['value' => 100, 'label' => __('100 records')]
        ];
    }
}