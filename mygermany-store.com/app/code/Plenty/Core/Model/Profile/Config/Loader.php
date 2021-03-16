<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile\Config;

use Plenty\Core\Model\ResourceModel\Profile\Config\CollectionFactory;

/**
 * Class Loader
 * @package Plenty\Core\Model\Profile\Config
 */
class Loader
{
    /**
     * Config data factory
     *
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var \Plenty\Core\Model\ResourceModel\Profile\Config\Collection
     */
    protected $_collection;

    /**
     * Loader constructor.
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Plenty\Core\App\Config\ValueFactory $configValueFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->_collection = $collectionFactory->create();
        $this->_configValueFactory = $configValueFactory;
    }

    /**
     * Get configuration value by path
     *
     * @param $path
     * @param $scope
     * @param $scopeId
     * @param $profileId
     * @param bool $full
     * @return array
     */
    public function getConfigByPath($path, $scope, $scopeId, $profileId, $full = true)
    {
        $configDataCollection = $this->_collection->addScopeFilter($scope, $scopeId, $path, $profileId);

        $config = [];
        $configDataCollection->load();
        foreach ($configDataCollection->getItems() as $data) {
            if ($full) {
                $config[$data->getPath()] = [
                    'path' => $data->getPath(),
                    'value' => $data->getValue(),
                    'config_id' => $data->getConfigId(),
                ];
            } else {
                $config[$data->getPath()] = $data->getValue();
            }
        }
        return $config;
    }
}