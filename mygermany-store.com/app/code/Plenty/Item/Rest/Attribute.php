<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest;

use Magento\Framework\Data\CollectionFactory;
use Plenty\Core\Rest\Client;
use Plenty\Item\Rest\Response\AttributeDataBuilder;
use Plenty\Item\Helper\Data as Helper;
use Plenty\Item\Model\Logger;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Attribute
 * @package Plenty\Item\Rest
 */
class Attribute extends AbstractItem implements AttributeInterface
{
    /**
     * Attribute constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param Helper $helper
     * @param Logger $logger
     * @param AttributeDataBuilder $responseParser
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        Helper $helper,
        Logger $logger,
        AttributeDataBuilder $responseParser
    ) {
        $this->_responseParser = $responseParser;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger);
    }

    /**
     * @return AttributeDataBuilder
     */
    protected function _responseParser()
    {
        return $this->_responseParser;
    }

    /**
     * @param $attributeId
     * @return array|mixed
     * @throws LocalizedException
     */
    public function getAttributeById($attributeId)
    {
        try {
            $this->_api()
                ->get($this->_helper()->getAttributeUrl($attributeId));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param $attributeId
     * @param $valueId
     * @return array|mixed
     * @throws LocalizedException
     */
    public function getAttributeValue($attributeId, $valueId)
    {
        try {
            $this->_api()
                ->get($this->_helper()->getAttributeValueUrl($attributeId, $valueId));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param int $page
     * @param null $updatedAt
     * @param null $with
     * @param bool $addValueNames
     * @return Collection|mixed
     * @throws LocalizedException
     */
    public function getSearchAttributes(
        $page = 1,
        $updatedAt = null,
        $with = null,
        $addValueNames = false
    ) {
        $params = ['page' => $page];
        $with ? $params['with'] = $with : null;
        $updatedAt ? $params['updatedAt'] = $updatedAt : null;

        try {
            $this->_api()
                ->get($this->_helper()->getAttributeUrl() . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), Response\AttributeDataInterface::ENTITY_ATTRIBUTE);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param $attributeId
     * @return array|mixed
     * @throws LocalizedException
     */
    public function getSearchAttributeNames($attributeId)
    {
        try {
            $this->_api()
                ->get($this->_helper()->getAttributeNamesUrl($attributeId));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param $attributeId
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchAttributeValues($attributeId)
    {
        $currentPage = 1;
        $attributeValues = [];
        do {
            try {
                $this->_api()
                    ->get($this->_helper()->getAttributeValueUrl($attributeId) . '?page=' . $currentPage);
                $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            } catch (\Exception $e) {
                $this->_logError($e->getMessage(), __METHOD__);
                throw new LocalizedException(__($e->getMessage()));
            }

            $this->_helper()->debug($this->getLog($this->_logLevel, __METHOD__));

            $response = $this->_api()->getResponse();
            if ($values = $response['entries']) {
                foreach ($values as $value) {
                    $attributeValues[$value['id']]                      = $value;
                    $attributeValues[$value['id']]['page']              = $response['page'];
                    $attributeValues[$value['id']]['items_per_page']    = $response['itemsPerPage'];
                    $attributeValues[$value['id']]['last_on_page']      = $response['lastOnPage'];
                    $attributeValues[$value['id']]['last_page']         = $response['lastPageNumber'];
                }
            }

            $currentPage = $response['page'];
            $lastPage = $response['lastPageNumber'];
            $currentPage++;
        } while ($currentPage <= $lastPage);

        return $attributeValues;
    }

    /**
     * @param $valueId
     * @return array|mixed
     * @throws LocalizedException
     */
    public function getSearchAttributeValueNames($valueId)
    {
        try {
            $this->_api()
                ->get($this->_helper()->getAttributeValueNameUrl($valueId));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param int $page
     * @param null $updatedAt
     * @param null $id
     * @return Collection|mixed
     * @throws LocalizedException
     */
    public function getSearchManufacturers(
        $page = 1, $updatedAt = null, $id = null
    ) {
        $params = ['page' => $page];
        $updatedAt ? $params['updatedAt'] = $updatedAt : null;

        try {
            $this->_api()
                ->get($this->_helper()->getManufacturersUrl($id) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), Response\AttributeDataInterface::ENTITY_MANUFACTURER);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param $params
     * @param null $id
     * @return array|mixed
     * @throws LocalizedException
     */
    public function createAttribute($params, $id = null)
    {
        try {
            if (null === $id) {
                $this->_api()
                    ->post($this->_helper()->getAttributeUrl(), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getAttributeUrl(), $params);
            }
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param $params
     * @param $attributeId
     * @param null $lang
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createAttributeNames($params, $attributeId, $lang = null)
    {
        try {
            if (null === $lang) {
                $this->_api()
                    ->post($this->_helper()->getAttributeNamesUrl($attributeId), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getAttributeNamesUrl($attributeId).'/'.$lang, $params);
            }
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param $params
     * @param $attributeId
     * @param null $valueId
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createAttributeValues($params, $attributeId, $valueId = null)
    {
        try {
            $this->_api()
                ->post($this->_helper()->getAttributeValueUrl($attributeId, $valueId), $params);
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param $params
     * @param $valueId
     * @param null $lang
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createAttributeValuesNames($params, $valueId, $lang = null)
    {
        try {
            if (null === $lang) {
                $this->_api()
                    ->post($this->_helper()->getAttributeValueNameUrl($valueId), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getAttributeValueNameUrl($valueId, $lang), $params);
            }
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param int $page
     * @param null $with
     * @param null $id
     * @param null $updatedAt
     * @param null $groupId
     * @return Collection
     * @throws LocalizedException
     */
    public function getSearchProperties(
        $page = 1,
        $with = null,
        $id = null,
        $updatedAt = null,
        $groupId = null
    ) {
        $params = ['page' => $page];
        $with ? $params['with'] = $with : null;
        $updatedAt ? $params['updatedAt'] = $updatedAt : null;
        $groupId ? $params['groupId'] = $groupId : null;

        try {
            $this->_api()
                ->get($this->_helper()->getPropertiesUrl($id) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), Response\AttributeDataInterface::ENTITY_PROPERTY);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

       return $response;
    }

    /**
     * @param $propertyId
     * @param null $lang
     * @param null $selectionId
     * @return Collection
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchPropertySelections($propertyId, $selectionId = null, $lang = null)
    {
        try {
            $this->_api()
                ->get($this->_helper()->getPropertySelectionsUrl($propertyId, $selectionId, $lang));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), Response\AttributeDataInterface::ENTITY_PROPERTY_SELECTION);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param $params
     * @param null $propertyId
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createProperty($params, $propertyId = null)
    {
        try {
            if (null === $propertyId) {
                $this->_api()
                    ->post($this->_helper()->getPropertiesUrl($params), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getPropertiesUrl($propertyId), $params);
            }
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param $propertyId
     * @param $params
     * @param null $lang
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createPropertyName($propertyId, $params, $lang = null)
    {
        try {
            if (null === $lang) {
                $this->_api()
                    ->post($this->_helper()->getPropertyNamesUrl($propertyId), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getPropertyNamesUrl($propertyId, $lang), $params);
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
     * @param array $params
     * @param null $propertyId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createPropertyValueLink($itemId, $variationId, array $params, $propertyId = null)
    {
        try {
            if (null === $propertyId) {
                $this->_api()
                    ->post($this->_helper()->getVariationPropertyValueUrl($itemId, $variationId), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getVariationPropertyValueUrl($itemId, $variationId, $propertyId), $params);
            }
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        }  catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param $itemId
     * @param $variationId
     * @param $propertyId
     * @param array $params
     * @param null $lang
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createPropertyValueText($itemId, $variationId, $propertyId, array $params, $lang = null)
    {
        try {
            if (null === $lang) {
                $this->_api()
                    ->post($this->_helper()->getPropertyValueTextUrl($itemId, $variationId, $propertyId), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getPropertyValueTextUrl($itemId, $variationId, $propertyId, $lang), $params);
            }
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        }  catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param $propertyId
     * @param array $params
     * @param null $selectionId
     * @return array|bool
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createPropertySelections($propertyId, array $params, $selectionId = null)
    {
        if (empty($params) || !$propertyId) {
            return false;
        }

        try {
            if (null === $selectionId) {
                $this->_api()
                    ->post($this->_helper()->getPropertySelectionsUrl($propertyId), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getPropertySelectionsUrl($propertyId, $selectionId), $params);
            }

            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        }  catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }
}