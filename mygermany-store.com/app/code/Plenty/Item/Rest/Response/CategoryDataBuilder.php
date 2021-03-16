<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\Response;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Plenty\Item\Api\Data\Import\CategoryInterface;

/**
 * Class CategoryDataBuilder
 * @package Plenty\Item\Rest\Response
 */
class CategoryDataBuilder implements CategoryDataInterface
{
    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @param CollectionFactory $dataCollectionFactory
     */
    public function __construct(CollectionFactory $dataCollectionFactory)
    {
        $this->_dataCollectionFactory = $dataCollectionFactory;
    }

    /**
     * @param array $response
     * @return DataObject
     */
    private function __buildItemTypeResponseData(array $response)
    {
        return new DataObject(
            [
                CategoryInterface::CATEGORY_ID => isset($response[self::ID])
                    ? $response[self::ID]
                    : null,
                CategoryInterface::PARENT_ID => isset($response[self::PARENT_CATEGORY_ID])
                    ? $response[self::PARENT_CATEGORY_ID]
                    : null,
                CategoryInterface::LEVEL => isset($response[self::LEVEL])
                    ? $response[self::LEVEL]
                    : null,
                CategoryInterface::TYPE => isset($response[self::TYPE])
                    ? $response[self::TYPE]
                    : null,
                CategoryInterface::HAS_CHILDREN => isset($response[self::HAS_CHILDREN])
                    ? $response[self::HAS_CHILDREN]
                    : null,
                CategoryInterface::LINK_LIST => isset($response[self::LINK_LIST])
                    ? $response[self::LINK_LIST]
                    : null,
                CategoryInterface::RIGHT => isset($response[self::RIGHT])
                    ? $response[self::RIGHT]
                    : null,
                CategoryInterface::SITEMAP => isset($response[self::SITEMAP])
                    ? $response[self::SITEMAP]
                    : null,
                CategoryInterface::NAME => isset($response[self::DETAILS][0][self::NAME])
                    ? $response[self::DETAILS][0][self::NAME]
                    : null,
                CategoryInterface::PREVIEW_URL => isset($response[self::DETAILS][0][self::PREVIEW_URL])
                    ? $response[self::DETAILS][0][self::PREVIEW_URL]
                    : null,
                CategoryInterface::UPDATED_AT => isset($response[self::DETAILS][0][self::UPDATED_AT])
                    ? $response[self::DETAILS][0][self::UPDATED_AT]
                    : null,
                CategoryInterface::UPDATED_BY => isset($response[self::DETAILS][0][self::UPDATED_BY])
                    ? $response[self::DETAILS][0][self::UPDATED_BY]
                    : null,
                CategoryInterface::DETAILS => isset($response[self::DETAILS])
                    ? $response[self::DETAILS]
                    : null
            ]
        );
    }

    /**
     * @param array $response
     * @param string $entityType
     * @return Collection
     * @throws \Exception
     */
    public function buildResponse(array $response, $entityType = self::CATEGORY_TYPE_ITEM): Collection
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

        $responseData = isset($response['entries'])
            ? $response['entries']
            : [$response];
        foreach ($responseData as $item) {
            switch ($entityType) {
                case self::CATEGORY_TYPE_CONTENT:
                    break;
                case self::CATEGORY_TYPE_CONTAINER:
                    break;
                case self::CATEGORY_TYPE_BLOG:
                    break;
                default :
                    $itemObj = $this->__buildItemTypeResponseData($item);
                    $responseCollection->addItem($itemObj);
                    break;
            }
        }

        return $responseCollection;
    }
}