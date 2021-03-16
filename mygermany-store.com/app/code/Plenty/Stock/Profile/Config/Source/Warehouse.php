<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Profile\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Plenty\Core\Model\Config\Source as ConfigSource;

/**
 * Class Warehouse
 * @package Plenty\Stock\Profile\Config\Source
 */
class Warehouse implements OptionSourceInterface
{
    /**
     * @var ConfigSource
     */
    protected $_configSourceFactory;

    /**
     * @var array
     */
    protected $_options;


    /**
     * Warehouse constructor.
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
        if (null === $this->_options) {
            $this->_options[] = ['value' => '', 'label' => __('--- select ---')];
            /** @var ConfigSource $vatConfig */
            foreach ($this->_configSourceFactory->getWarehouseConfigCollection() as $item) {
                if (!$item->getData('entry_id') || !$item->getData('name')
                ) {
                    continue;
                }
                $this->_options[] = [
                    'value' => $item->getData('entry_id'),
                    'label' => "{$item->getData('name')} [id: {$item->getData('entry_id')}]",
                ];
            }
        }

        return $this->_options;
    }
}