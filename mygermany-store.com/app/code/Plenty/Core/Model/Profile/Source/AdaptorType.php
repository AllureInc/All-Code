<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\Model\Profile\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AdaptorType
 * @package Plenty\Core\Model\Profile\Source
 */
class AdaptorType implements OptionSourceInterface
{
    const IMPORT    = 'import';
    const EXPORT    = 'export';

    /**
     * @return array
     */
    public function getAvailableAdaptors()
    {
        return [self::IMPORT => __('Import'), self::EXPORT => __('Export')];
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->getAvailableAdaptors();
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
