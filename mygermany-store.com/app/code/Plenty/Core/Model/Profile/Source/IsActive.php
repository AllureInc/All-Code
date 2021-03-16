<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
{
    /**
     * @var \Plenty\Core\Model\Profile
     */
    protected $_profile;

    /**
     * IsActive constructor.
     * @param \Plenty\Core\Model\Profile $_profile
     */
    public function __construct(\Plenty\Core\Model\Profile $_profile)
    {
        $this->_profile = $_profile;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->_profile->getAvailableStatuses();
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
