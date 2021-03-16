<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Model\Config;

use Magento\Framework\Data\Collection;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

use Plenty\Stock\Api\Data\Config\SourceInterface;
use Plenty\Core\Model\Config\Source as ConfigSource;
use Plenty\Core\Model\ResourceModel\Config\Source\Collection as ConfigSourceCollection;
use Plenty\Core\Model\Profile\Config\Source\Countries;
use Plenty\Stock\Rest\Config;

/**
 * Class Source
 * @package Plenty\Stock\Model\Config
 */
class Source extends ConfigSource
    implements SourceInterface
{
    /**
     * Source constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Config $configClient
     * @param Countries $countries
     * @param AbstractResource|null $resource
     * @param Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Config $configClient,
        Countries $countries,
        ?AbstractResource $resource = null,
        ?Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_api = $configClient;
        parent::__construct($context, $registry, $configClient, $countries, $resource, $resourceCollection, $data);
    }

    /**
     * @param null $warehouseId
     * @return $this
     * @throws \Exception
     */
    public function collectWarehouseConfigs($warehouseId = null)
    {
        $response = $this->_api->getSearchWarehouseConfigs($warehouseId);
        if (!$response->getSize()) {
            return $this;
        }

        $this->_saveConfigResponseData($response);

        return $this;
    }

    /**
     * @return ConfigSourceCollection
     */
    public function getWarehouseConfigCollection(): ConfigSourceCollection
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(self::CONFIG_SOURCE, self::CONFIG_SOURCE_WAREHOUSE)
            ->load();
        /** @var \Plenty\Core\Model\Config\Source $item */
        foreach ($collection as $item) {
            if (!$entries = $item->getConfigEntries()) {
                continue;
            }

            $item->setData('name', isset($entries['name'])
                ? $entries['name']
                : null);
            $item->setData('type_id', isset($entries['typeId'])
                ? $entries['typeId']
                : null);
            $item->setData('storage_location_zone', isset($entries['storageLocationZone'])
                ? $entries['storageLocationZone']
                : null);
            $item->setData('repair_warehouse_id', isset($entries['repairWarehouseId'])
                ? $entries['repairWarehouseId']
                : null);
            $item->setData('address', isset($entries['address'])
                ? $entries['address']
                : null);

            $item->unsetData('config_entries');
        }

        return $collection;
    }
}