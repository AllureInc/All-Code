<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Rest;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

use Plenty\Core\Model\Logger;
use Plenty\Core\Helper\Data as Helper;
use Plenty\Core\Rest\Response\ConfigDataBuilder;
use Plenty\Core\Api\Data\Config\SourceInterface;

/**
 * Class Item
 * @package Plenty\Item\Rest
 */
class Config extends AbstractCore implements ConfigInterface
{
    /**
     * Config constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param Helper $helper
     * @param Logger $logger
     * @param ConfigDataBuilder $configDataBuilder
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        Helper $helper,
        Logger $logger,
        ConfigDataBuilder $configDataBuilder
    ) {
        $this->_responseParser = $configDataBuilder;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger);
    }

    /**
     * @return ConfigDataBuilder
     */
    protected function _responseParser()
    {
        return $this->_responseParser;
    }

    /**
     * @return Collection|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSearchWebStoreConfigs()
    {
        try {
            $this->_api()
                ->get($this->_helper()->getWebStoresUrl());
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), SourceInterface::CONFIG_SOURCE_WEB_STORE);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param int $page
     * @param null $vatId
     * @param null $with
     * @param array $columns
     * @return Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSearchVatConfigs(
        $page = 1,
        $vatId = null,
        $with = null,
        $columns = []
    ) {
        $params = array('page' => $page);
        $with ? $params['with'] = $with : null;

        try {
            $this->_api()
                ->get($this->_helper()->getVatConfigUrl($vatId) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), SourceInterface::CONFIG_SOURCE_VAT);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param null $warehouseId
     * @return Collection|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSearchWarehouseConfigs($warehouseId = null)
    {
        try {
            $this->_api()
                ->get($this->_helper()->getWarehousesUrl($warehouseId));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), SourceInterface::CONFIG_SOURCE_WAREHOUSE);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }
}