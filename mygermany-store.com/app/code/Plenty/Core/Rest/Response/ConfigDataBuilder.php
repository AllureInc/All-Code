<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Rest\Response;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;

use Plenty\Core\Api\Data\Config\SourceInterface;

/**
 * Class ConfigDataBuilder
 * @package Plenty\Core\Rest\Response
 */
class ConfigDataBuilder implements ConfigDataInterface
{
    /**
     * @var string
     */
    protected $_configSource;

    /**
     * @var CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @var Collection
     */
    protected $_responseCollection;

    /**
     * ConfigDataBuilder constructor.
     * @param CollectionFactory $dataCollectionFactory
     */
    public function __construct(CollectionFactory $dataCollectionFactory)
    {
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->_responseCollection = $this->_dataCollectionFactory->create();
    }

    /**
     * @param array $response
     * @param $configSource
     * @return Collection
     * @throws \Exception
     */
    public function buildResponse(array $response, $configSource): Collection
    {
        if (empty($response)) {
            return $this->_responseCollection;
        }

        $this->_configSource = $configSource;
        $this->_responseCollection->setFlag('page', isset($response['page']) ? $response['page'] : null);
        $this->_responseCollection->setFlag('totalsCount', isset($response['totalsCount']) ? $response['totalsCount'] : null);
        $this->_responseCollection->setFlag('isLastPage', isset($response['isLastPage']) ? $response['isLastPage'] : null);
        $this->_responseCollection->setFlag('lastPageNumber', isset($response['lastPageNumber']) ? $response['lastPageNumber'] : null);

        $responseData = isset($response['entries'])
            ? $response['entries']
            : $response;

        $this->__buildResponseData($responseData);

        return $this->_responseCollection;
    }

    /**
     * @param array $responseData
     * @return Collection
     * @throws \Exception
     */
    protected function __buildResponseData(array $responseData): Collection
    {
        foreach ($responseData as $item) {

            if (isset($item[self::ID])) {
                $entryId = $item[self::ID];
            } elseif (isset($item[self::STATUS_ID])) {
                $entryId = $item[self::STATUS_ID] * 10;
            } else {
                $entryId = null;
            }

            $itemObj = new DataObject(
                [
                    SourceInterface::CONFIG_SOURCE => $this->_configSource,
                    SourceInterface::ENTRY_ID => $entryId,
                    SourceInterface::CREATED_AT => isset($item[self::CREATED_AT])
                        ? $item[self::CREATED_AT]
                        : null,
                    SourceInterface::UPDATED_AT => isset($item[self::UPDATED_AT])
                        ? $item[self::UPDATED_AT]
                        : null,
                    SourceInterface::CONFIG_ENTRIES => $item
                ]
            );

            $this->_responseCollection->addItem($itemObj);
        }

        return $this->_responseCollection;
    }

    /**
     * @param array $responseData
     * @return Collection
     * @throws \Exception
     */
    private function __buildWebStoresResponseData(
        array $responseData): Collection
    {
        foreach ($responseData as $item) {
            $itemObj = new DataObject(
                [
                    SourceInterface::CONFIG_SOURCE => SourceInterface::CONFIG_SOURCE_WEB_STORE,
                    SourceInterface::ENTRY_ID => isset($item[self::ID])
                        ? $item[self::ID]
                        : null,
                    SourceInterface::CONFIG_ENTRIES => $item
                ]
            );

            $this->_responseCollection->addItem($itemObj);
        }

        return $this->_responseCollection;
    }


    /**
     * @param array $responseData
     * @return Collection
     * @throws \Exception
     */
    private function __buildVatResponseData(
        array $responseData): Collection
    {
        foreach ($responseData as $item) {
            $itemObj = new DataObject(
                [
                    SourceInterface::CONFIG_SOURCE => SourceInterface::CONFIG_SOURCE_VAT,
                    SourceInterface::ENTRY_ID => isset($item[self::ID])
                        ? $item[self::ID]
                        : null,
                    SourceInterface::CREATED_AT => isset($item[self::CREATED_AT])
                        ? $item[self::CREATED_AT]
                        : null,
                    SourceInterface::UPDATED_AT => isset($item[self::UPDATED_AT])
                        ? $item[self::UPDATED_AT]
                        : null,
                    SourceInterface::CONFIG_ENTRIES => $item
                ]
            );

            $this->_responseCollection->addItem($itemObj);
        }


        return $this->_responseCollection;
    }


    /**
     * @param array $responseData
     * @return Collection
     * @throws \Exception
     */
    private function __buildWarehouseResponseData(
        array $responseData): Collection
    {
        foreach ($responseData as $item) {
            $itemObj = new DataObject(
                [
                    SourceInterface::CONFIG_SOURCE => SourceInterface::CONFIG_SOURCE_WAREHOUSE,
                    SourceInterface::ENTRY_ID => isset($item[self::ID])
                        ? $item[self::ID]
                        : null,
                    SourceInterface::CONFIG_ENTRIES => $item
                ]
            );

            $this->_responseCollection->addItem($itemObj);
        }

        return $this->_responseCollection;
    }
}