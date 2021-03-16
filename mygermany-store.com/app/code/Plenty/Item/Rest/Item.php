<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest;

use Magento\Framework\Data\CollectionFactory;
use Plenty\Core\Rest\Client;
use Plenty\Item\Model\Logger;
use Plenty\Item\Rest\Response\ItemDataBuilder;
use Plenty\Item\Helper\Data as Helper;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Item
 * @package Plenty\Item\Rest
 */
class Item extends AbstractItem implements ItemInterface
{
    /**
     * Item constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param Helper $helper
     * @param Logger $logger
     * @param ItemDataBuilder $itemDataBuilder
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        Helper $helper,
        Logger $logger,
        ItemDataBuilder $itemDataBuilder
    ) {
        $this->_responseParser = $itemDataBuilder;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger);
    }

    /**
     * @return ItemDataBuilder
     */
    public function _responseParser()
    {
        return $this->_responseParser;
    }

    /**
     * @param int $page
     * @param null $itemId
     * @param null $with
     * @param null $flagOne
     * @param null $flagTwo
     * @param null $lang
     * @param null $updatedBetween
     * @param null $variationUpdatedBetween
     * @return Collection|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchItems(
        $page = 1,
        $itemId = null,
        $with = null,
        $flagOne = null,
        $flagTwo = null,
        $lang = null,
        $updatedBetween = null,
        $variationUpdatedBetween = null
    ) {
        $params = array('page' => $page);
        $with ? $params['with'] = $with : null;
        $flagOne ? $params['flagOne'] = $flagOne : null;
        $flagTwo ? $params['flagTwo'] = $flagTwo : null;
        $lang ? $params['lang'] = $lang : null;
        $updatedBetween ? $params['updatedBetween'] = $updatedBetween : null;
        $variationUpdatedBetween ? $params['variationUpdatedBetween'] = $variationUpdatedBetween : null;

        try {
            $this->_api()
                ->get($this->_helper()->getItemUrl($itemId) . '?' . http_build_query($params));
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
     * @param $itemId
     * @return bool|Collection
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemImages($itemId)
    {
        try {
            $this->_api()
                ->get($this->_helper()->getItemImagesUrl($itemId));
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
     * @param array $params
     * @param null $itemId
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createItem(array $params, $itemId = null)
    {
        try {
            if (null === $itemId) {
                $this->_api()->post($this->_helper()->getItemUrl(), $params);
            } else {
                $this->_api()->put($this->_helper()->getItemUrl($itemId), $params);
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
     * @param array $params
     * @param null $imageId
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createItemImages($itemId, array $params, $imageId = null)
    {
        try {
            if (null === $imageId) {
                $this->_api()
                    ->post($this->_helper()->getItemImageUploadUrl($itemId), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getItemImageUploadUrl($itemId, $imageId), $params);
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
     * @param $imageId
     * @param array $params
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteItemImages($itemId, $imageId, array $params)
    {
        try {
            $this->_api()
                ->delete($this->_helper()->getItemImageUploadUrl($itemId, $imageId), $params);
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }
}