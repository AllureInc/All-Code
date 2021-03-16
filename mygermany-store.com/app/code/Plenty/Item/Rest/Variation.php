<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest;

use Plenty\Core\Rest\Client;
use Plenty\Item\Model\Logger;
use Plenty\Item\Rest\Response\VariationDataBuilder;
use Plenty\Item\Helper\Data as Helper;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Variation
 * @package Plenty\Item\Rest
 */
class Variation extends AbstractItem implements VariationInterface
{
    /**
     * Variation constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param Helper $helper
     * @param Logger $logger
     * @param VariationDataBuilder $variationDataBuilder
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        Helper $helper,
        Logger $logger,
        VariationDataBuilder $variationDataBuilder
    ) {
        $this->_responseParser = $variationDataBuilder;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger);
    }

    /**
     * @return VariationDataBuilder
     */
    public function _responseParser()
    {
        return $this->_responseParser;
    }

    /**
     * @param int $page
     * @param null $itemId
     * @param null $variationId
     * @param null $with
     * @param null $flagOne
     * @param null $flagTwo
     * @param null $lang
     * @param null $sku
     * @param null $itemName
     * @param bool $isMain
     * @param null $updatedBetween
     * @param null $relatedUpdatedBetween
     * @param null $stockWarehouseId
     * @return Collection
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchVariations(
        $page = 1,
        $itemId = null,
        $variationId = null,
        $with = null,
        $flagOne = null,
        $flagTwo = null,
        $lang = null,
        $sku = null,
        $itemName = null,
        $isMain = false,
        $updatedBetween = null,
        $relatedUpdatedBetween = null,
        $stockWarehouseId = null
    ) : Collection {
        $params = array('page' => $page);
        $with ? $params['with'] = $with : null;
        $flagOne ? $params['flagOne'] = $flagOne : null;
        $flagTwo ? $params['flagTwo'] = $flagTwo : null;
        $lang ? $params['lang'] = $lang : null;
        $sku ? $params['numberExact'] = $sku : null;
        $itemName ? $params['itemName'] = $itemName : null;
        $isMain ? $params['isMain'] = $isMain : null;
        $updatedBetween ? $params['updatedBetween'] = $updatedBetween : null;
        $relatedUpdatedBetween ? $params['relatedUpdatedBetween'] = $relatedUpdatedBetween : null;
        $stockWarehouseId ? $params['stockWarehouseId'] = $stockWarehouseId : null;
        // $params['itemsPerPage'] = 100;

        try {
            $this->_api()
                ->get($this->_helper()->getVariationUrl($itemId, $variationId) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse());
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param $sku
     * @return DataObject
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariationBySku($sku) : DataObject
    {
        $params['numberExact'] = $sku;

        try {
            $this->_api()
                ->get($this->_helper()->getVariationUrl() . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse());
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response->getFirstItem();
    }

    /**
     * @param array $params
     * @param $itemId
     * @param null $variationId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createVariation(array $params, $itemId, $variationId = null)
    {
        try {
            if (null === $variationId) {
                $this->_api()->post($this->_helper()->getVariationUrl($itemId), $params);
            } else {
                $this->_api()->put($this->_helper()->getVariationUrl($itemId, $variationId), $params);
            }
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param $itemId
     * @param $variationId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariationBundleComponents($itemId, $variationId)
    {
        return $this->_api()->get($this->_helper()->getVariationBundleUrl($itemId, $variationId));
    }

    /**
     * @param $itemId
     * @param $variationId
     * @param null $lang
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariationDescriptions($itemId, $variationId, $lang = null)
    {
        return $this->_api()->get($this->_helper()->getVariationDescriptionUrl($itemId, $variationId, $lang));
    }

    /**
     * @param array $params
     * @param $itemId
     * @param $variationId
     * @param null $bundleId
     * @param bool $delete
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createVariationBundle(
        array $params,
        $itemId,
        $variationId,
        $bundleId = null,
        $delete = false
    ) {
        try {
            if (is_null($bundleId)) {
                $this->_api()->post($this->_helper()->getVariationBundleUrl($itemId, $variationId), $params);
            } elseif ($delete) {
                $this->_api()->delete($this->_helper()->getVariationBundleUrl($itemId, $variationId, $bundleId));
            } else {
                $this->_api()->put($this->_helper()->getVariationBundleUrl($itemId, $variationId, $bundleId), $params);
            }
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param array $params
     * @param $itemId
     * @param $variationId
     * @param null $marketIdentNumberId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createVariationMarketNumbers(array $params, $itemId, $variationId, $marketIdentNumberId = null)
    {
        try {
            if (is_null($marketIdentNumberId)) {
                $this->_api()->post($this->_helper()->getVariationMarketNumbersUrl($itemId, $variationId), $params);
            } else {
                $this->_api()->put($this->_helper()->getVariationMarketNumbersUrl($itemId, $variationId, $marketIdentNumberId), $params);
            }
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param array $params
     * @param $itemId
     * @param $variationId
     * @param null $priceId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createVariationSalesPrices(array $params, $itemId, $variationId, $priceId = null)
    {
        try {
            if (is_null($priceId)) {
                $this->_api()->post($this->_helper()->getVariationSalesPricesUrl($itemId, $variationId), $params);
            } else {
                $this->_api()->put($this->_helper()->getVariationSalesPricesUrl($itemId, $variationId, $priceId), $params);
            }
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param array $params
     * @param $itemId
     * @param $variationId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createVariationStock(array $params, $itemId, $variationId)
    {
        try {
            $this->_api()->put($this->_helper()->getVariationStockCorrectionUrl($itemId, $variationId), $params);
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }


    /**
     * @param $itemId
     * @param $variationId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariationProperties($itemId, $variationId)
    {
        try {
            $this->_api()->get($this->_helper->getVariationPropertyValueUrl($itemId, $variationId));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }
}