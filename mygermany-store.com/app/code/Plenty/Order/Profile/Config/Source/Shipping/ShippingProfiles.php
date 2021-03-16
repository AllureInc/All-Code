<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Profile\Config\Source\Shipping;

use Magento\Framework\Data\OptionSourceInterface;
use Plenty\Order\Model\Config\Source as ConfigSource;

/**
 * Class ShippingProfiles
 * @package Plenty\Order\Profile\Config\Source\Shipping
 */
class ShippingProfiles implements OptionSourceInterface
{
    /**
     * @var ConfigSource
     */
    private $_configSourceFactory;

    /**
     * @var array
     */
    private $_optionConfigs;

    /**
     * ShippingProfiles constructor.
     * @param ConfigSource $configSourceFactory
     */
    public function __construct(
        ConfigSource $configSourceFactory
    ) {
        $this->_configSourceFactory = $configSourceFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        /** @var ConfigSource $method */
        foreach ($this->_getShippingMethods() as $method) {
            $options[] = [
                'value' => $method->getData('id'),
                'label' => $method->getData('backend_name'),
            ];
        }

        return $options;
    }

    /**
     * @return \Magento\Framework\Data\Collection|\Plenty\Core\Model\ResourceModel\Config\Source\Collection
     */
    private function _getShippingMethods()
    {
        if (null === $this->_optionConfigs) {
            $this->_optionConfigs = $this->_configSourceFactory->getShippingProfiles();
        }

        return $this->_optionConfigs;
    }
}