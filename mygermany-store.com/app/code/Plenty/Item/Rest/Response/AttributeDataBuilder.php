<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Response;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Plenty\Item\Api\Data\Import\AttributeInterface;
use Plenty\Item\Model\Logger;
use Plenty\Item\Rest\AbstractItem;
use Plenty\Core\Rest\Client;
use Plenty\Item\Helper\Data as Helper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AttributeDataBuilder
 * @package Plenty\Item\Rest\Response
 */
class AttributeDataBuilder extends AbstractItem implements AttributeDataInterface
{
    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * AttributeDataBuilder constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param Helper $helper
     * @param Logger $logger
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        Helper $helper,
        Logger $logger
    ) {
        $this->_dataCollectionFactory = $dataCollectionFactory;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger);
    }

    /**
     * @param array $response
     * @param string $entityType
     * @return Collection
     * @throws \Exception
     */
    public function buildResponse(array $response, $entityType = AttributeInterface::ENTITY_ATTRIBUTE): Collection
    {
        /** @var Collection $collection */
        $responseCollection = $this->_dataCollectionFactory->create();
        if (empty($response)) {
            return $responseCollection;
        }

        $responseCollection->setFlag('page', isset($response['page']) ? $response['page'] : null);
        $responseCollection->setFlag('totalsCount', isset($response['totalsCount']) ? $response['totalsCount'] : null);
        $responseCollection->setFlag('isLastPage', isset($response['isLastPage']) ? $response['isLastPage'] : null);
        $responseCollection->setFlag('lastPageNumber', isset($response['lastPageNumber']) ? $response['lastPageNumber'] : null);

        $data = isset($response['entries'])
            ? $response['entries']
            : $response;
        foreach ($data as $item) {
            switch ($entityType) {
                case AttributeInterface::ENTITY_PROPERTY :
                    $itemObj = $this->__buildPropertyResponseData($item);
                    $responseCollection->addItem($itemObj);
                    break;
                case AttributeInterface::ENTITY_PROPERTY_SELECTION :
                    $itemObj = $this->__buildPropertySelectionResponseData($item);
                    $responseCollection->addItem($itemObj);
                    break;
                case AttributeInterface::ENTITY_MANUFACTURER :
                    $itemObj = $this->__buildManufacturerResponseData($item);
                    $responseCollection->addItem($itemObj);
                    break;
                default :
                    $itemObj = $this->__buildAttributeResponseData($item);
                    $responseCollection->addItem($itemObj);
                    break;
            }
        }

        return $responseCollection;
    }

    /**
     * @param array $item
     * @return DataObject
     */
    private function __buildAttributeResponseData(array $item)
    {
        $attributeValueNames = [];
        if (isset($item[self::VALUES])) {
            foreach ($item[self::VALUES] as $attributeValue) {
                if (!isset($attributeValue['id'])) {
                    continue;
                }
                try {
                    $attributeValueNames[$attributeValue['id']] = $this->_getSearchAttributeValueNames($attributeValue['id']);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return new DataObject(
            [
                AttributeInterface::ATTRIBUTE_ID => isset($item[self::ID])
                    ? $item[self::ID]
                    : null,
                AttributeInterface::ATTRIBUTE_CODE => isset($item[self::BACKEND_NAME])
                    ? $item[self::BACKEND_NAME]
                    : null,
                AttributeInterface::POSITION => isset($item[self::POSITION])
                    ? $item[self::POSITION]
                    : null,
                AttributeInterface::IS_SURCHARGE_PERCENTAGE => isset($item[self::IS_SURCHARGE_PERCENTAGE])
                    ? $item[self::IS_SURCHARGE_PERCENTAGE]
                    : null,
                AttributeInterface::IS_LINKABLE_TO_IMAGE => isset($item[self::IS_LINKABLE_TO_IMAGE])
                    ? $item[self::IS_LINKABLE_TO_IMAGE]
                    : null,
                AttributeInterface::AMAZON_ATTRIBUTE => isset($item[self::AMAZON_ATTRIBUTE])
                    ? $item[self::AMAZON_ATTRIBUTE]
                    : null,
                AttributeInterface::FRUUGO_ATTRIBUTE => isset($item[self::FRUUGO_ATTRIBUTE])
                    ? $item[self::FRUUGO_ATTRIBUTE]
                    : null,
                AttributeInterface::PIXMANIA_ATTRIBUTE => isset($item[self::PIXMANIA_ATTRIBUTE])
                    ? $item[self::PIXMANIA_ATTRIBUTE]
                    : null,
                AttributeInterface::OTO_ATTRIBUTE => isset($item[self::OTO_ATTRIBUTE])
                    ? $item[self::OTO_ATTRIBUTE]
                    : null,
                AttributeInterface::GOOGLE_SHOPPING_ATTRIBUTE => isset($item[self::GOOGLE_SHOPPING_ATTRIBUTE])
                    ? $item[self::GOOGLE_SHOPPING_ATTRIBUTE]
                    : null,
                AttributeInterface::NECKERMANN_ATTRIBUTE => isset($item[self::NECKERMANN_ATTRIBUTE])
                    ? $item[self::NECKERMANN_ATTRIBUTE]
                    : null,
                AttributeInterface::TYPE_OF_SELECTION_IN_ONLINE_STORE => isset($item[self::TYPE_OF_SELECTION_IN_ONLINE_STORE])
                    ? $item[self::TYPE_OF_SELECTION_IN_ONLINE_STORE]
                    : null,
                AttributeInterface::IS_GROUPABLE => isset($item[self::IS_GROUPABLE])
                    ? $item[self::IS_GROUPABLE]
                    : null,
                AttributeInterface::IS_GROUPABLE => isset($item[self::IS_GROUPABLE])
                    ? $item[self::IS_GROUPABLE]
                    : null,
                AttributeInterface::NAMES => isset($item[self::ATTRIBUTE_NAMES])
                    ? $item[self::ATTRIBUTE_NAMES]
                    : null,
                AttributeInterface::VALUES => isset($item[self::VALUES])
                    ? $item[self::VALUES]
                    : null,
                AttributeInterface::MAPS => isset($item[self::MAPS])
                    ? $item[self::MAPS]
                    : null,
                AttributeInterface::VALUE_NAMES => $attributeValueNames,
                AttributeInterface::UPDATED_AT => isset($item[self::UPDATED_AT])
                    ? $item[self::UPDATED_AT]
                    : null,
                AttributeInterface::ENTRIES => $item
            ]
        );
    }

    /**
     * @param array $item
     * @return DataObject
     */
    private function __buildPropertyResponseData(array $item)
    {
        return new DataObject(
            [
                AttributeInterface::PROPERTY_ID => isset($item[self::ID])
                    ? $item[self::ID]
                    : null,
                AttributeInterface::POSITION => isset($item[self::POSITION])
                    ? $item[self::POSITION]
                    : null,
                AttributeInterface::PROPERTY_GROUP_ID => isset($item[self::PROPERTY_GROUP_ID])
                    ? $item[self::PROPERTY_GROUP_ID]
                    : null,
                AttributeInterface::PROPERTY_CODE => isset($item[self::BACKEND_NAME])
                    ? $item[self::BACKEND_NAME]
                    : null,
                AttributeInterface::VALUE_TYPE => isset($item[self::VALUE_TYPE])
                    ? $item[self::VALUE_TYPE]
                    : null,
                AttributeInterface::NAMES => isset($item[self::NAMES])
                    ? $item[self::NAMES]
                    : null,
                AttributeInterface::GROUP => isset($item[self::GROUP])
                    ? $item[self::GROUP]
                    : null,
                AttributeInterface::MARKET_COMPONENTS => isset($item[self::MARKET_COMPONENTS])
                    ? $item[self::MARKET_COMPONENTS]
                    : null,
                AttributeInterface::COMMENT => isset($item[self::COMMENT])
                    ? $item[self::COMMENT]
                    : null,
                AttributeInterface::UPDATED_AT => isset($item[self::UPDATED_AT])
                    ? $item[self::UPDATED_AT]
                    : null,
                AttributeInterface::ENTRIES => $item
            ]
        );
    }

    /**
     * @param array $item
     * @return DataObject
     */
    private function __buildPropertySelectionResponseData(array $item)
    {
        return new DataObject(
            [
                AttributeInterface::PROPERTY_SELECTION_ID => isset($item[self::ID])
                    ? $item[self::ID]
                    : null,
                AttributeInterface::PROPERTY_ID => isset($item[self::PROPERTY_ID])
                    ? $item[self::PROPERTY_ID]
                    : null,
                AttributeInterface::LANG => isset($item[self::LANG])
                    ? $item[self::LANG]
                    : null,
                self::NAME => isset($item[self::NAME])
                    ? $item[self::NAME]
                    : null,
                AttributeInterface::DESCRIPTION => isset($item[self::DESCRIPTION])
                    ? $item[self::DESCRIPTION]
                    : null
            ]
        );
    }

    /**
     * @param array $item
     * @return DataObject
     */
    private function __buildManufacturerResponseData(array $item)
    {
        return new DataObject(
            [
                AttributeInterface::MANUFACTURER_ID => isset($item[self::ID])
                    ? $item[self::ID]
                    : null,
                AttributeInterface::NAMES => isset($item[self::NAME])
                    ? $item[self::NAME]
                    : null,
                AttributeInterface::LOGO => isset($item[self::LOGO])
                    ? $item[self::LOGO]
                    : null,
                AttributeInterface::URL => isset($item[self::URL])
                    ? $item[self::URL]
                    : null,
                AttributeInterface::PIXMANIA_BRAND_ID => isset($item[self::PIXMANIA_BRAND_ID])
                    ? $item[self::PIXMANIA_BRAND_ID]
                    : null,
                AttributeInterface::NECKERMANN_BRAND_ID => isset($item[self::NECKERMANN_BRAND_ID])
                    ? $item[self::NECKERMANN_BRAND_ID]
                    : null,
                AttributeInterface::NECKERMANN_AT_EP__BRAND_ID => isset($item[self::NECKERMANN_AT_EP__BRAND_ID])
                    ? $item[self::NECKERMANN_AT_EP__BRAND_ID]
                    : null,
                AttributeInterface::LA_REDOUTE_BRAND_ID => isset($item[self::LA_REDOUTE_BRAND_ID])
                    ? $item[self::LA_REDOUTE_BRAND_ID]
                    : null,
                AttributeInterface::EXTERNAL_NAME => isset($item[self::EXTERNAL_NAME])
                    ? $item[self::EXTERNAL_NAME]
                    : null,
                AttributeInterface::STREET => isset($item[self::STREET])
                    ? $item[self::STREET]
                    : null,
                AttributeInterface::HOUSE_NO => isset($item[self::HOUSE_NO])
                    ? $item[self::HOUSE_NO]
                    : null,
                AttributeInterface::POST_CODE => isset($item[self::POST_CODE])
                    ? $item[self::POST_CODE]
                    : null,
                AttributeInterface::TOWN => isset($item[self::TOWN])
                    ? $item[self::TOWN]
                    : null,
                AttributeInterface::COUNTRY_ID => isset($item[self::COUNTRY_ID])
                    ? $item[self::COUNTRY_ID]
                    : null,
                AttributeInterface::PHONE_NUMBER => isset($item[self::PHONE_NUMBER])
                    ? $item[self::PHONE_NUMBER]
                    : null,
                AttributeInterface::FAX_NUMBER => isset($item[self::FAX_NUMBER])
                    ? $item[self::FAX_NUMBER]
                    : null,
                AttributeInterface::EMAIL => isset($item[self::EMAIL])
                    ? $item[self::EMAIL]
                    : null,
                AttributeInterface::COMMENT => isset($item[self::COMMENT])
                    ? $item[self::COMMENT]
                    : null,
                AttributeInterface::POSITION => isset($item[self::POSITION])
                    ? $item[self::POSITION]
                    : null,
                AttributeInterface::UPDATED_AT => isset($item[self::UPDATED_AT])
                    ? $item[self::UPDATED_AT]
                    : null,
                AttributeInterface::ENTRIES => $item
            ]
        );
    }

    /**
     * @param $valueId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _getSearchAttributeValueNames($valueId)
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
}