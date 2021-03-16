<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Profile;

use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Core\Model\Source\Status;
use Plenty\Stock\Api\Data\Import\InventoryInterface;
use Plenty\Stock\Api\StockImportRepositoryInterface;
use Plenty\Stock\Api\Data\Profile\StockImportInterface;
use Plenty\Stock\Api\StockCollectManagementInterface;
use Plenty\Stock\Rest\Stock as StockClient;
use Plenty\Stock\Helper;
use Plenty\Stock\Model\Logger;

/**
 * Class StockCollectManagement
 * @package Plenty\Stock\Profile
 */
class StockCollectManagement extends AbstractManagement
    implements StockCollectManagementInterface
{
    /**
     * @var StockImportInterface
     */
    private $_profileEntity;

    /**
     * @var StockImportRepositoryInterface
     */
    private $_stockRepository;

    /**
     * @var array
     */
    private $_collectionResult;

    /**
     * StockCollectManagement constructor.
     * @param StockImportRepositoryInterface $stockImportRepository
     * @param StockClient $stockClient
     * @param Helper\Data $helper
     * @param Logger $logger
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        StockImportRepositoryInterface $stockImportRepository,
        StockClient $stockClient,
        Helper\Data $helper,
        Logger $logger,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_stockRepository = $stockImportRepository;
        $this->_api = $stockClient;
        $this->_helper = $helper;
        parent::__construct($helper, $logger, $dateTime, $serializer, $data);
    }

    /**
     * @return StockImportInterface
     * @throws \Exception
     */
    public function getProfileEntity(): StockImportInterface
    {
        if (!$this->_profileEntity) {
            throw new \Exception(__('Profile entity is not set.'));
        }
        return $this->_profileEntity;
    }

    /**
     * @param StockImportInterface $profileEntity
     * @return $this|StockCollectManagementInterface
     * @throws \Exception
     */
    public function setProfileEntity(StockImportInterface $profileEntity)
    {
        if (!$profileEntity instanceof StockImportInterface) {
            throw new \Exception(__('Wrong profile instance type provided.'));
        }
        $this->_profileEntity = $profileEntity;
        return $this;
    }

    /**
     * @param null $variationId
     * @param null $warehouseId
     * @param null $lastUpdatedAt
     * @return $this|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function execute(
        $variationId = null,
        $warehouseId = null,
        $lastUpdatedAt = null
    ) {
        $this->_initResponseData();

        if (!$profileId = $this->getProfileEntity()->getProfile()->getId()) {
            throw new \Exception(__('Profile entity is not set. [Trace: %1]', __METHOD__));
        }

        $page = 1;
        $itemsPerPage = $this->getProfileEntity()->getApiCollectionSize();

        do {
            $response = $this->_api->getSearchStock(
                $page,
                $variationId,
                $warehouseId,
                $lastUpdatedAt,
                null,
                $itemsPerPage
            );

            if ($response->getFlag('totalsCount') < 1) {
                $this->addResponse(__('Stock is up-to-date.'), Status::SUCCESS);
                return $this;
            }

            $stockData = $this->_prepareResponseDataForSave($response, $profileId);

            if (!empty($stockData)) {
                $this->_stockRepository->saveMultiple($stockData);
            }

            $page = $response->getFlag('page');
            $last = $response->getFlag('lastPageNumber');
            $page++;
        } while ($page <= $last);

        $this->addResponse(
            __('Stock has been collected. Effected Variation(s): %1', implode(', ', $this->_collectionResult)),
            Status::SUCCESS
        );

        return $this;
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

    /**
     * @param Collection $collection
     * @param $profileId
     * @return array
     */
    private function _prepareResponseDataForSave(Collection $collection, $profileId)
    {
        $stockData = [];
        /** @var DataObject $item */
        foreach ($collection as $item) {
            $this->_collectionResult[] = $item->getData(InventoryInterface::VARIATION_ID);
            $item->setData(InventoryInterface::PROFILE_ID, $profileId);
            $item->setData(InventoryInterface::STATUS, Status::PENDING);
            $item->setData(InventoryInterface::MESSAGE, __('Collected.'));
            $item->setData(InventoryInterface::CREATED_AT, $this->_dateTime->gmtDate());
            $item->setData(InventoryInterface::COLLECTED_AT, $this->_dateTime->gmtDate());
            $item->setData(InventoryInterface::PROCESSED_AT, $this->_dateTime->gmtDate());
            $stockData[] = $item->toArray();
        }

        return $stockData;
    }
}