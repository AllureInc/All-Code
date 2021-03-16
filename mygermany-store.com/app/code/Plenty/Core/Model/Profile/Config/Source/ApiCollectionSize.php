<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\Model\Profile\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AdaptorType
 * @package Plenty\Core\Model\Profile\Source
 */
class ApiCollectionSize implements OptionSourceInterface
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
            ['value' => 500, 'label' => __('500 records (max)')],
        ];
    }
}