<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\Model\Profile\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Profile Entity source
 */
class EntityType implements OptionSourceInterface
{
    /**
     * Available profile types
     */
    const TYPE_ATTRIBUTE            = 'attribute';
    const TYPE_CATEGORY             = 'category';
    const TYPE_CUSTOMER             = 'customer';
    const TYPE_PRODUCT              = 'product';
    const TYPE_ORDER                = 'order';
    const TYPE_STOCK                = 'stock';

    /**
     * @return array
     */
    public function getAvailableProfiles()
    {
        return [
            // self::TYPE_ATTRIBUTE    => __('Attribute'),
            self::TYPE_CATEGORY     => __('Category'),
            // self::TYPE_CUSTOMER     => __('Customer'),
            self::TYPE_PRODUCT      => __('Product'),
            self::TYPE_ORDER        => __('Order'),
            self::TYPE_STOCK        => __('Stock')
        ];
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->getAvailableProfiles();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
