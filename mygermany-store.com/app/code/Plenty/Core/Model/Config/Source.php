<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Config;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject\IdentityInterface;

use Plenty\Core\Rest\Config as ConfigClient;
use Plenty\Core\Model\Profile\Config\Source\Countries;
use Plenty\Core\Api\Data\Config\SourceInterface;
use Plenty\Core\Model\ResourceModel\Config\Source\Collection as ConfigSourceCollection;

/**
 * Class Source
 * @package Plenty\Core\Model\Config
 *
 * @method \Plenty\Core\Model\ResourceModel\Config\Source getResource()
 * @method \Plenty\Core\Model\ResourceModel\Config\Source\Collection getCollection()
 */
class Source extends AbstractModel
    implements SourceInterface, IdentityInterface
{
    const CACHE_TAG             = 'plenty_core_config_source';
    protected $_cacheTag        = 'plenty_core_config_source';
    protected $_eventPrefix     = 'plenty_core_config_source';

    protected $_countrySource;

    /**
     * @var null
     */
    protected $_api;

    protected function _construct()
    {
        $this->_init(\Plenty\Core\Model\ResourceModel\Config\Source::class);
    }

    /**
     * @return ConfigClient|null
     */
    protected function _api()
    {
        return $this->_api;
    }

    /**
     * @param Collection $responseData
     * @return $this
     * @throws \Exception
     */
    protected function _saveConfigResponseData(Collection $responseData)
    {
        $this->getResource()->saveConfigs($responseData);
        return $this;
    }

    /**
     * Source constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ConfigClient $configClient
     * @param Countries $countries
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ConfigClient $configClient,
        Countries $countries,
        AbstractResource $resource = null,
        Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_api = $configClient;
        $this->_countrySource = $countries;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return mixed
     */
    public function getConfigSource()
    {
        return $this->getData(self::CONFIG_SOURCE);
    }

    /**
     * @return array|mixed
     */
    public function getConfigEntries()
    {
        return $this->getData(self::CONFIG_ENTRIES)
            ? unserialize($this->getData(self::CONFIG_ENTRIES))
            : [];
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function collectWebStoreConfigs()
    {
        $response = $this->_api()->getSearchWebStoreConfigs();
        if (!$response->getSize()) {
            return $this;
        }
        $this->_saveConfigResponseData($response);
        return $this;
    }

    /**
     * @param null $vatId
     * @return $this
     * @throws \Exception
     */
    public function collectVatConfigs($vatId = null)
    {
        $page = 1;
        do {
            $response = $this->_api()->getSearchVatConfigs($page, $vatId);
            if (!$response->getSize()) {
                return $this;
            }
            $this->_saveConfigResponseData($response);

            $page = $response->getFlag('page');
            $last = $response->getFlag('lastPageNumber');
            $page++;
        } while ($page <= $last);

        return $this;
    }

    /**
     * @return ConfigSourceCollection
     */
    public function getWebStoreConfigCollection(): ConfigSourceCollection
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(self::CONFIG_SOURCE, self::CONFIG_SOURCE_WEB_STORE)
            ->load();
        /** @var Source $item */
        foreach ($collection as $item) {
            if (!$entries = $item->getConfigEntries()) {
                continue;
            }

            if (!isset($entries['configuration'])
                || !$entries = $entries['configuration']
            ) {
                continue;
            }

            $item->setData('name', isset($entries['name'])
                ? $entries['name']
                : null);

            $item->setData('default_language', isset($entries['defaultLanguage'])
                ? $entries['defaultLanguage']
                : null);
            $item->setData('default_shipping_country', isset($entries['defaultShippingCountryId'])
                ? $entries['defaultShippingCountryId']
                : null);
            $item->setData('domain', isset($entries['domain'])
                ? $entries['domain']
                : null);
            $item->setData('domain_ssl', isset($entries['domainSsl'])
                ? $entries['domainSsl']
                : null);
            $item->setData('root_dir', isset($entries['rootDir'])
                ? $entries['rootDir']
                : null);
            $item->setData('default_currency', isset($entries['defaultCurrency'])
                ? $entries['defaultCurrency']
                : null);
            $item->setData('store_id', isset($entries['webstoreId'])
                ? $entries['webstoreId']
                : null);
            $item->setData('default_parcel_service_id', isset($entries['defaultParcelServiceId'])
                ? $entries['defaultParcelServiceId']
                : null);
            $item->setData('default_payment_method_id', isset($entries['defaultMethodOfPaymentId'])
                ? $entries['defaultMethodOfPaymentId']
                : null);
            $item->setData('default_currency_list', isset($entries['defaultCurrencyList'])
                ? $entries['defaultCurrencyList']
                : null);
            $item->unsetData('config_entries');
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function getDefaultLangConfig()
    {
        $webStoreConfig = $this->getWebStoreConfigCollection()->getFirstItem();
        return $webStoreConfig->getData('default_language');
    }

    /**
     * @return ConfigSourceCollection
     */
    public function getVatConfigCollection(): ConfigSourceCollection
    {
        $countries = $this->_countrySource->getCountryIds();
        $collection = $this->getCollection()
            ->addFieldToFilter(self::CONFIG_SOURCE, self::CONFIG_SOURCE_VAT)
            ->load();

        /** @var Source $item */
        foreach ($collection as $item) {
            if (!$entries = $item->getConfigEntries()) {
                continue;
            }

            $item->setData('country_id', isset($entries['countryId'])
                ? $entries['countryId']
                : null);
            $item->setData('country_name', isset($entries['countryId']) && isset($countries[$entries['countryId']])
                ? $countries[$entries['countryId']]
                : null);
            $item->setData('tax_id_number', isset($entries['taxIdNumber'])
                ? $entries['taxIdNumber']
                : null);
            $item->setData('started_at', isset($entries['startedAt'])
                ? $entries['startedAt']
                : null);
            $item->setData('location_id', isset($entries['locationId'])
                ? $entries['locationId']
                : null);
            $item->setData('margin_scheme', isset($entries['marginScheme'])
                ? $entries['marginScheme']
                : null);
            $item->setData('is_restricted_to_digital_items', isset($entries['isRestrictedToDigitalItems'])
                ? $entries['isRestrictedToDigitalItems']
                : null);
            $item->setData('invalid_from', isset($entries['invalidFrom'])
                ? $entries['invalidFrom']
                : null);
            $item->setData('created_at', isset($entries['createdAt'])
                ? $entries['createdAt']
                : null);
            $item->setData('updated_at', isset($entries['updatedAt'])
                ? $entries['updatedAt']
                : null);
            $item->setData('vat_rates', isset($entries['vatRates'])
                ? $entries['vatRates']
                : null);
            $item->unsetData('config_entries');
        }

        return $collection;
    }

    /**
     * @param null $warehouseId
     * @return $this
     * @throws \Exception
     */
    public function collectWarehouseConfigs($warehouseId = null)
    {
        $response = $this->_api()
            ->getSearchWarehouseConfigs($warehouseId);
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
        /** @var Source $item */
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