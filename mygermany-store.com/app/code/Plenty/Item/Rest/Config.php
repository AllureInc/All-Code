<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest;

use Plenty\Core\Rest\Client;
use Plenty\Core\Rest\Config as CoreConfig;
use Plenty\Core\Model\Logger;
// use Plenty\Item\Rest\Response\ConfigDataBuilder;
use Plenty\Core\Rest\Response\ConfigDataBuilder;
use Plenty\Item\Helper\Data as Helper;
use Plenty\Item\Api\Data\Config\SourceInterface;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;


/**
 * Class Item
 * @package Plenty\Item\Rest
 */
class Config extends CoreConfig implements ConfigInterface
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
        $this->_helper = $helper;
        $this->_responseParser = $configDataBuilder;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger, $configDataBuilder);
    }

    /**
     * @return Helper
     */
    protected function _helper()
    {
        return $this->_helper;
    }

    /**
     * @param int $page
     * @param null $barcodeId
     * @param null $updatedAt
     * @return Collection|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSearchItemBarcodes(
        $page = 1,
        $barcodeId = null,
        $updatedAt = null
    ) {
        $params = array('page' => $page);
        $updatedAt ? $params['updatedAt'] = $updatedAt : null;

        try {
            $this->_api()
                ->get($this->_helper()->getItemBarcodeUrl($barcodeId) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), SourceInterface::CONFIG_SOURCE_ITEM_BARCODE);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param int $page
     * @param null $priceId
     * @param null $updatedAt
     * @return Collection|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSearchItemSalesPrices(
        $page = 1,
        $priceId = null,
        $updatedAt = null
    ) {
        $params = array('page' => $page);
        $updatedAt ? $params['updatedAt'] = $updatedAt : null;

        try {
            $this->_api()
                ->get($this->_helper()->getItemSalesPriceUrl($priceId) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), SourceInterface::CONFIG_SOURCE_ITEM_SALES_PRICE);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }
}