<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest;

use Magento\Framework\Data\CollectionFactory;
use Plenty\Core\Rest\Client;
use Plenty\Item\Rest\Response\CategoryDataBuilder;
use Plenty\Item\Helper\Data as Helper;
use Plenty\Item\Model\Logger;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Category
 * @package Plenty\Item\Rest
 */
class Category extends AbstractItem
    implements CategoryInterface
{
    /**
     * Attribute constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param Helper $helper
     * @param Logger $logger
     * @param CategoryDataBuilder $responseParser
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        Helper $helper,
        Logger $logger,
        CategoryDataBuilder $responseParser
    ) {
        $this->_responseParser = $responseParser;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger);
    }

    /**
     * @return CategoryDataBuilder
     */
    public function _responseParser()
    {
        return $this->_responseParser;
    }

    /**
     * @param int $page
     * @param null $categoryId
     * @param null $updatedAt
     * @param null $with
     * @param null $type
     * @param null $lang
     * @param null $parentId
     * @param null $name
     * @param null $level
     * @return Collection
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchCategory(
        $page = 1,
        $categoryId = null,
        $updatedAt = null,
        $with = null,
        $type = null,
        $lang = null,
        $parentId = null,
        $name = null,
        $level = null
    ) {
        $params = ['page' => $page];
        $categoryId ? $params['categoryId'] = $categoryId : null;
        $updatedAt ? $params['updatedAt'] = $updatedAt : null;
        $with ? $params['with'] = $with : null;
        $type ? $params['type'] = $type : null;
        $lang ? $params['lang'] = $lang : null;
        $parentId ? $params['parentId'] = $parentId : null;
        $name ? $params['name'] = $name : null;
        $level ? $params['level'] = $level : null;

        try {
            $this->_api()
                ->get($this->_helper()->getCategoryUrl($categoryId) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), Response\CategoryDataInterface::CATEGORY_TYPE_ITEM);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param $itemId
     * @param $variationId
     * @param null $categoryId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariationCategory($itemId, $variationId, $categoryId = null)
    {
        try {
            $response = $this->_api()
                ->get($this->_helper()->getVariationCategoryUrl($itemId, $variationId, $categoryId));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param $itemId
     * @param $variationId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariationDefaultCategory($itemId, $variationId)
    {
        try {
            $response = $this->_api()
                ->get($this->_helper()->getVariationDefaultCategoryUrl($itemId, $variationId));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param array $params
     * @param null $itemId
     * @param null $variationId
     * @param null $categoryId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function linkCategoryToVariation(array $params, $itemId = null, $variationId = null, $categoryId = null)
    {
        try {

            if (null === $itemId && null === $variationId) {
                if (null === $categoryId) {
                    $response = $this->_api()
                        ->post($this->_helper()->getVariationCategoryUrl(), $params);
                } else {
                    $response = $this->_api()
                        ->put($this->_helper()->getVariationCategoryUrl(), $params);
                }
            } else {
                if (null === $categoryId) {
                    $response = $this->_api()
                        ->post($this->_helper()->getVariationCategoryUrl($itemId, $variationId), $params);
                } else {
                    $response = $this->_api()
                        ->put($this->_helper()->getVariationCategoryUrl($itemId, $variationId, $categoryId), $params);
                }
            }
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }
}