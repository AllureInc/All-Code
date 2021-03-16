<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Customer\Rest\Response;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;

/**
 * Class ContactDataBuilder
 * @package Plenty\Customer\Rest\Response
 */
class ContactDataBuilder implements ContactDataInterface
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
     * @return Collection
     * @throws \Exception
     */
    public function buildResponse(array $response): Collection
    {
        /** @var \Magento\Framework\Data\Collection $collection */
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
            $itemObj = $this->__buildResponseData($item);
            $responseCollection->addItem($itemObj);
        }

        return $responseCollection;
    }

    /**
     * @param array $response
     * @return DataObject
     */
    private function __buildResponseData(array $response)
    {
        $object = new DataObject();
        return $object->setData($response);
    }
}