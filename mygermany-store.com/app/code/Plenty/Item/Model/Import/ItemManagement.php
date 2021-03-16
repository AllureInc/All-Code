<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import;

use Magento\Framework\DataObject;
use Plenty\Core\Model\Source\Status;
use Plenty\Item\Api\Data\Import\ItemInterface;
use Plenty\Item\Api\ItemManagementInterface;
use Plenty\Item\Api\ItemRepositoryInterface;
use Plenty\Item\Api\VariationRepositoryInterface;
use Plenty\Item\Rest\Item as ItemClient;
use Plenty\Item\Rest\Variation as VariationClient;

/**
 * Class ItemManagement
 * @package Plenty\Item\Model\Import
 */
class ItemManagement implements ItemManagementInterface
{
    /**
     * @var ItemClient
     */
    private $_itemClient;

    /**
     * @var VariationClient
     */
    private $_variationClient;

    /**
     * @var ItemRepositoryInterface
     */
    private $_itemRepository;

    /**
     * @var VariationRepositoryInterface
     */
    private $_variationRepository;

    /**
     * @var array
     */
    private $_response;

    /**
     * @var array
     */
    private $_collectionResult;

    /**
     * ItemManagement constructor.
     * @param ItemClient $itemClient
     * @param VariationClient $variationClient
     * @param ItemRepositoryInterface $itemRepository
     * @param VariationRepositoryInterface $variationRepository
     */
    public function __construct(
        ItemClient $itemClient,
        VariationClient $variationClient,
        ItemRepositoryInterface $itemRepository,
        VariationRepositoryInterface $variationRepository
    ) {
        $this->_itemClient = $itemClient;
        $this->_variationClient = $variationClient;
        $this->_itemRepository = $itemRepository;
        $this->_variationRepository = $variationRepository;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null)
    {
        $key
            ? $this->_response[$key] = $data
            : $this->_response = $data;
        return $this;
    }

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addResponse($data, $key = null)
    {
        $key
            ? $this->_response[$key][] = $data
            : $this->_response[] = $data;
        return $this;
    }

    /**
     * @param null $profileId
     * @return string|null
     * @throws \Exception
     */
    public function getItemsLastUpdatedAt($profileId = null)
    {
        return $this->_itemRepository->getLastUpdatedAt($profileId);
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getVariationsLastUpdatedAt()
    {
        return $this->_variationRepository->getLastUpdatedAt();
    }

    /**
     * @param $profileId
     * @param null $itemId
     * @param null $with
     * @param null $flagOne
     * @param null $flagTwo
     * @param null $lang
     * @param null $updatedBetween
     * @param null $variationUpdatedBetween
     * @return $this
     * @throws \Exception
     */
    public function collectItems(
        $profileId,
        $itemId = null,
        $with = null,
        $flagOne = null,
        $flagTwo = null,
        $lang = null,
        $updatedBetween = null,
        $variationUpdatedBetween = null
    ) {
        $this->_initResponseData();
        $page = 1;

        do {
            $response = $this->_itemClient
                ->getSearchItems(
                    $page,
                    $itemId,
                    $with,
                    $flagOne,
                    $flagTwo,
                    $lang,
                    $updatedBetween,
                    $variationUpdatedBetween
                );

            if (!$response->getSize()) {
                $this->addResponse(__('Items are up-to-date.'), Status::SUCCESS);
                return $this;
            }

            $result = $response->getColumnValues(ItemInterface::ITEM_ID);
            $this->_collectionResult = array_merge($this->_collectionResult, $result);

            $this->_itemRepository->saveItemCollection($profileId, $response);

            $page = $response->getFlag('page');
            $last = $response->getFlag('lastPageNumber');
            $page++;
        } while ($page <= $last);

        $this->addResponse(
            __('Items have been collected. Effected ID(s): %1', implode(', ', $this->_collectionResult)),
            Status::SUCCESS
        );

        return $this;
    }

    /**
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
     * @return $this|mixed
     * @throws \Exception
     */
    public function collectVariations(
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
    ) {
        $this->_initResponseData();
        $page = 1;

        do {
            $response = $this->_variationClient
                ->getSearchVariations(
                    $page,
                    $itemId,
                    $variationId,
                    $with,
                    $flagOne,
                    $flagTwo,
                    $lang,
                    $sku,
                    $itemName,
                    $isMain,
                    $updatedBetween,
                    $relatedUpdatedBetween,
                    $stockWarehouseId
                );

            if (!$response->getSize()) {
                $this->addResponse(__('Variations are up-to-date.'), Status::SUCCESS);
                return $this;
            }

            $result = $response->getColumnValues(ItemInterface::VARIATION_ID);
            $this->_collectionResult = array_merge($this->_collectionResult, $result);

            $this->_variationRepository->saveVariationCollection($response);

            $page = $response->getFlag('page');
            $last = $response->getFlag('lastPageNumber');
            $page++;
        } while ($page <= $last);

        $this->addResponse(
            __('Variations have been collected. Effected ID(s): %1', implode(', ', $this->_collectionResult)),
            Status::SUCCESS
        );

        return $this;
    }

    /**
     * @param $profileId
     * @param $sku
     * @param null $itemSearchFilter
     * @param null $variationSearchFilter
     * @return DataObject
     * @throws \Exception
     */
    public function collectBySku(
        $profileId,
        $sku,
        $itemSearchFilter = null,
        $variationSearchFilter = null
    ) {
        $response = new DataObject();

        $variationResponse = $this->_variationClient
            ->getSearchVariations(null, null, null, $variationSearchFilter, null, null, null, $sku);
        if (!$variationResponse->getSize()) {
            return $response;
        }

        $itemId = $variationResponse->getFirstItem()->getData('item_id');
        $itemResponse = $this->_itemClient
            ->getSearchItems(null, (int) $itemId, $itemSearchFilter);

        if (!$itemResponse->getSize()) {
            return $response;
        }

        $this->_itemRepository->saveItemCollection($profileId, $itemResponse);
        // $this->_variationRepository->saveVariationCollection($variationResponse);

        $response->setData(ItemInterface::ITEM_RESPONSE, $itemResponse->getFirstItem());
        $response->setData(ItemInterface::VARIATION_RESPONSE,  $variationResponse->getFirstItem());

        $this->collectVariations($itemId, null, $variationSearchFilter);

        return $response;
    }

    /**
     * @return $this
     */
    private function _initResponseData()
    {
        $this->_response =
        $this->_collectionResult = [];
        return $this;
    }
}
