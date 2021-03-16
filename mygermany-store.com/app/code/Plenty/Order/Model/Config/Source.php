<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Model\Config;

use Magento\Framework\Data\Collection;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

use Plenty\Order\Api\Data\Config\SourceInterface;
use Plenty\Core\Model\Config\Source as ConfigSource;
use Plenty\Core\Model\ResourceModel\Config\Source\Collection as ConfigSourceCollection;
use Plenty\Core\Model\Profile\Config\Source\Countries;
use Plenty\Order\Rest\Config;

/**
 * Class Source
 * @package Plenty\Order\Model\Config
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
     * @param null $statusId
     * @return $this
     * @throws \Exception
     */
    public function collectOrderStatuses($statusId = null)
    {
        $page = 1;
        do {
            $response = $this->_api()
                ->getSearchOrderStatuses($page, $statusId);
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
     * @param null $statusId
     * @return $this
     * @throws \Exception
     */
    public function collectPaymentMethods($statusId = null)
    {
        $page = 1;
        do {
            $response = $this->_api()
                ->getSearchPaymentMethods($page, $statusId);
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
     * @param null $profileId
     * @return $this
     * @throws \Exception
     */
    public function collectShippingProfiles($profileId = null)
    {
        $page = 1;
        do {
            $response = $this->_api()
                ->getSearchShippingProfiles($page, $profileId);
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
    public function getOrderStatuses(): ConfigSourceCollection
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(self::CONFIG_SOURCE, self::CONFIG_SOURCE_ORDER_STATUS)
            ->load();

        $defaultLang = $this->getDefaultLangConfig();
        /** @var Source $item */
        foreach ($collection as $item) {
            if (!$entries = $item->getConfigEntries()) {
                continue;
            }

            $item->setData('status_id', isset($entries['statusId'])
                ? $entries['statusId']
                : null);
            $item->setData('names', isset($entries['names'])
                ? $entries['names']
                : null);
            $item->setData('default_name', $defaultLang && isset($entries['names'][$defaultLang])
                ? $entries['names'][$defaultLang]
                : null);
            $item->setData('created_at', isset($entries['createdAt'])
                ? $entries['createdAt']
                : null);

            $item->unsetData('config_entries');
        }

        return $collection;
    }

    /**
     * @return ConfigSourceCollection
     */
    public function getPaymentMethods(): ConfigSourceCollection
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(self::CONFIG_SOURCE, self::CONFIG_SOURCE_PAYMENT_METHOD)
            ->load();

        /** @var Source $item */
        foreach ($collection as $item) {
            if (!$entries = $item->getConfigEntries()) {
                continue;
            }

            $item->setData('id', isset($entries['id'])
                ? $entries['id']
                : null);
            $item->setData('name', isset($entries['name'])
                ? $entries['name']
                : null);
            $item->setData('plugin_key', isset($entries['pluginKey'])
                ? $entries['pluginKey']
                : null);
            $item->setData('payment_key', isset($entries['paymentKey'])
                ? $entries['paymentKey']
                : null);

            $item->unsetData('config_entries');
        }

        return $collection;
    }

    /**
     * @return Collection
     */
    public function getShippingProfiles(): ConfigSourceCollection
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(self::CONFIG_SOURCE, self::CONFIG_SOURCE_SHIPPING_PROFILE)
            ->load();

        /** @var Source $item */
        foreach ($collection as $item) {
            if (!$entries = $item->getConfigEntries()) {
                continue;
            }

            $item->setData('id', isset($entries['id'])
                ? $entries['id']
                : null);
            $item->setData('parcel_service_id', isset($entries['parcelServiceId'])
                ? $entries['parcelServiceId']
                : null);
            $item->setData('backend_name', isset($entries['backendName'])
                ? $entries['backendName']
                : null);
            $item->setData('flag', isset($entries['flag'])
                ? $entries['flag']
                : null);
            $item->setData('priority', isset($entries['priority'])
                ? $entries['priority']
                : null);
            $item->setData('category', isset($entries['category'])
                ? $entries['category']
                : null);
            $item->setData('flag', isset($entries['flag'])
                ? $entries['flag']
                : null);
            $item->setData('is_insured', isset($entries['isInsured'])
                ? $entries['isInsured']
                : null);
            $item->setData('is_express', isset($entries['isExpress'])
                ? $entries['isExpress']
                : null);
            $item->setData('shipping_group', isset($entries['shippingGroup'])
                ? $entries['shippingGroup']
                : null);
            $item->setData('dispatch_identifier', isset($entries['dispatchIdentifier'])
                ? $entries['dispatchIdentifier']
                : null);
            $item->setData('auction_type', isset($entries['auctionType'])
                ? $entries['auctionType']
                : null);

            $item->unsetData('config_entries');
        }

        return $collection;
    }
}