<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Config;

use Magento\Framework\Data\Collection;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Plenty\Core\Model\Profile\Config\Source\Countries;
use Plenty\Item\Api\Data\Config\SourceInterface;
use Plenty\Core\Model\Config\Source as ConfigSource;
use Plenty\Core\Model\ResourceModel\Config\Source\Collection as ConfigSourceCollection;
use Plenty\Item\Rest\Config;

/**
 * Class Source
 * @package Plenty\Item\Model\Config
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
     * @return \Plenty\Core\Rest\Config|Config|null
     */
    protected function _api()
    {
        return $this->_api;
    }

    /**
     * @param null $barcodeId
     * @param null $updatedAt
     * @return $this|bool|mixed
     * @throws \Exception
     */
    public function collectItemBarcodeConfigs($barcodeId = null, $updatedAt = null)
    {
        $page = 1;
        do {
            $response = $this->_api()
                ->getSearchItemBarcodes($page, $barcodeId);
            if (!$response->getSize()) {
                return false;
            }

            $this->_saveConfigResponseData($response);

            $page = $response->getFlag('page');
            $last = $response->getFlag('lastPageNumber');
            $page++;
        } while ($page <= $last);

        return $this;
    }

    /**
     * @param null $priceId
     * @return $this|bool
     * @throws \Exception
     */
    public function collectItemSalesPrices($priceId = null)
    {
        $page = 1;
        do {
            $response = $this->_api()
                ->getSearchItemSalesPrices($page, $priceId);
            if (!$response->getSize()) {
                return false;
            }

            $this->_saveConfigResponseData($response);

            $page = $response->getFlag('page');
            $last = $response->getFlag('lastPageNumber');
            $page++;
        } while ($page <= $last);

        return $this;
    }

    public function getBarcodeCollection(): ConfigSourceCollection
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(self::CONFIG_SOURCE, self::CONFIG_SOURCE_ITEM_BARCODE)
            ->load();

        /** @var Source $item */
        foreach ($collection as $item) {
            if (!$entries = $item->getConfigEntries()) {
                continue;
            }

            $item->setData('name', isset($entries['name'])
                ? $entries['name']
                : null);
            $item->setData('type', isset($entries['type'])
                ? $entries['type']
                : null);
            $item->setData('referrers', isset($entries['referrers'])
                ? $entries['referrers']
                : null);

            $item->unsetData('config_entries');
        }

        return $collection;
    }

    /**
     * @return ConfigSourceCollection
     */
    public function getSalesPriceCollection(): ConfigSourceCollection
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(self::CONFIG_SOURCE, self::CONFIG_SOURCE_ITEM_SALES_PRICE)
            ->load();

        /** @var Source $item */
        foreach ($collection as $item) {
            if (!$entries = $item->getConfigEntries()) {
                continue;
            }

            $item->setData('position', isset($entries['position'])
                ? $entries['position']
                : null);
            $item->setData('min_order_qty', isset($entries['minimumOrderQuantity'])
                ? $entries['minimumOrderQuantity']
                : null);
            $item->setData('type', isset($entries['type'])
                ? $entries['type']
                : null);
            $item->setData('is_customer_price', isset($entries['isCustomerPrice'])
                ? $entries['isCustomerPrice']
                : null);
            $item->setData('is_displayed_by_default', isset($entries['isDisplayedByDefault'])
                ? $entries['isDisplayedByDefault']
                : null);
            $item->setData('is_live_conversion', isset($entries['isLiveConversion'])
                ? $entries['isLiveConversion']
                : null);
            $item->setData('created_at', isset($entries['createdAt'])
                ? $entries['createdAt']
                : null);
            $item->setData('updated_at', isset($entries['updatedAt'])
                ? $entries['updatedAt']
                : null);
            $item->setData('interval', isset($entries['interval'])
                ? $entries['interval']
                : null);
            $item->setData('names', isset($entries['names'])
                ? $entries['names']
                : null);

            $item->unsetData('config_entries');
        }

        return $collection;
    }
}