<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Profile;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Stock\Api\Data\Import\InventoryInterface;
use Plenty\Stock\Api\Data\Profile\StockImportInterface;
use Plenty\Stock\Api\StockCollectManagementInterface;
use Plenty\Stock\Api\StockImportManagementInterface;
use Plenty\Stock\Api\StockImportRepositoryInterface;
use Plenty\Stock\Helper;
use Plenty\Stock\Model\Logger;
use Plenty\Core\Model\Source\Status;

/**
 * Class Contact
 * @package Plenty\Order\Model\Export\Service
 */
class StockImportManagement extends AbstractManagement
    implements StockImportManagementInterface
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
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var array
     */
    private $_importResult;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var StockRegistryInterface|null
     */
    protected $_stockRegistry;

    /**
     * StockImportManagement constructor.
     * @param ProductFactory $productFactory
     * @param StockImportRepositoryInterface $stockImportRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StockRegistryInterface $stockRegistry
     * @param Helper\Data $helper
     * @param Logger $logger
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        ProductFactory $productFactory,
        StockImportRepositoryInterface $stockImportRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StockRegistryInterface $stockRegistry,
        Helper\Data $helper,
        Logger $logger,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_stockRepository = $stockImportRepository;
        $this->_productFactory = $productFactory;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_stockRegistry = $stockRegistry;
        parent::__construct($helper, $logger, $dateTime, $serializer, $data);
    }

    /**
     * @return StockImportInterface
     */
    public function getProfileEntity(): StockImportInterface
    {
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
     * @return $this|StockImportManagementInterface
     * @throws \Exception
     */
    public function execute()
    {
        $this->_initialize();

        if (!$this->getProfileEntity()
            || !$profileId = $this->getProfileEntity()->getProfile()->getId()
        ) {
            throw new \Exception(__('Profile entity is not set. [Trace: %1]', __METHOD__));
        }

        if (!$this->getProfileEntity()->getIsActiveStockImport()) {
            $this->setResponse(
                [
                    __('Stock import is inactive. [Profile: %1]',
                        $this->getProfileEntity()->getProfile()->getId())
                ], 'notice'
            );
            return $this;
        }

        if (!$searchCriteria = $this->getProfileEntity()->getImportSearchCriteria()) {
            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter(InventoryInterface::STATUS, Status::PENDING, 'eq')
                ->create();
        }

        $searchCriteria->setPageSize($this->getProfileEntity()->getImportBatchSize() ?? 100);

        $collection = $this->_stockRepository->getList($searchCriteria);

        /** @var InventoryInterface $inventory */
        foreach ($collection->getItems() as $inventory) {
            try {
                $inventory->setProfileId($profileId);
                $this->_import($inventory);
            } catch (\Exception $e) {
                $errors = [
                    InventoryInterface::PROFILE_ID => $profileId,
                    InventoryInterface::VARIATION_ID => $inventory->getVariationId(),
                    InventoryInterface::PRODUCT_ID => $inventory->getProductId(),
                    InventoryInterface::SKU => $inventory->getSku(),
                    InventoryInterface::WAREHOUSE_ID => $inventory->getWarehouseId(),
                    InventoryInterface::STATUS => Status::ERROR,
                    InventoryInterface::PROCESSED_AT => $this->_dateTime->gmtDate(),
                    InventoryInterface::MESSAGE => __('Could not update stock item. [Reason: %1]', $e->getMessage())
                ];
                $this->_importResult[] = $errors;
                $this->addResponse($errors, Status::ERROR);
            }
        }

        $effectedSku = !empty($this->_importResult)
            ? array_column($this->_importResult, InventoryInterface::SKU)
            : [];

        $this->addResponse(
            [
                __('Stock import has been processed. Effected Sku(s): %1.', implode(', ', array_filter($effectedSku)))
            ], Status::SUCCESS
        );

        $this->_finalize();

        return $this;
    }

    /**
     * @param InventoryInterface $inventory
     * @return $this
     * @throws \Exception
     */
    protected function _import(InventoryInterface $inventory)
    {
        if (!$profileId = $inventory->getProfileId()) {
            throw new \Exception(__('Profile entity is not set. [%1]', __METHOD__));
        }

        /** @var ProductInterface $product */
        $product = $this->_getProductByVariationId($inventory->getVariationId());
        if (!$product instanceof ProductInterface
            || !$product->getId()
        ) {
            $this->_importResult[] = [
                InventoryInterface::PROFILE_ID => $profileId,
                InventoryInterface::VARIATION_ID => $inventory->getVariationId(),
                InventoryInterface::PRODUCT_ID => $inventory->getProductId(),
                InventoryInterface::SKU => $inventory->getSku(),
                InventoryInterface::WAREHOUSE_ID => $inventory->getWarehouseId(),
                InventoryInterface::STATUS => Status::SKIPPED,
                InventoryInterface::PROCESSED_AT => $this->_dateTime->gmtDate(),
                InventoryInterface::MESSAGE => __('Stock item could not be found. [Variation ID: %1]', $inventory->getVariationId())
            ];
            return $this;
        }

        $inventory->setProductId($product->getId())
            ->setSku($product->getSku());

        /** @var StockItemInterface $stockItem */
        $stockItem = $this->_stockRegistry->getStockItem($product->getId());

        if (!$stockItem->getManageStock()) {
            $this->_importResult[] = [
                InventoryInterface::PROFILE_ID => $profileId,
                InventoryInterface::VARIATION_ID => $inventory->getVariationId(),
                InventoryInterface::PRODUCT_ID => $inventory->getProductId(),
                InventoryInterface::SKU => $inventory->getSku(),
                InventoryInterface::WAREHOUSE_ID => $inventory->getWarehouseId(),
                InventoryInterface::STATUS => Status::SKIPPED,
                InventoryInterface::PROCESSED_AT => $this->_dateTime->gmtDate(),
                InventoryInterface::MESSAGE => __('Stock management is inactive. [SKU: %1, Variation ID: %2]',
                    $inventory->getVariationId())
            ];
            return $this;
        }

        $oldQty = $stockItem->getQty();
        $stockItem->setQty($inventory->getStockNet());
        $stockItem->setIsInStock(
            $stockItem->getBackorders()
                ? true
                : $inventory->getStockNet() > 0
        );

        $this->_stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
        $this->_importResult[] = [
            InventoryInterface::PROFILE_ID => $profileId,
            InventoryInterface::VARIATION_ID => $inventory->getVariationId(),
            InventoryInterface::PRODUCT_ID => $inventory->getProductId(),
            InventoryInterface::SKU => $inventory->getSku(),
            InventoryInterface::WAREHOUSE_ID => $inventory->getWarehouseId(),
            InventoryInterface::STATUS => Status::COMPLETE,
            InventoryInterface::PROCESSED_AT => $this->_dateTime->gmtDate(),
            InventoryInterface::MESSAGE => __('Stock item has been updated. [SKU: %1, Variation ID: %2, New Qty: %3, Old Qty: %4]',
                $product->getSku(),
                $inventory->getVariationId(),
                $stockItem->getQty(),
                $oldQty
            )
        ];

        return $this;
    }

    /**
     * @param $variationId
     * @return ProductInterface
     */
    private function _getProductByVariationId($variationId)
    {
        $model = $this->_productFactory->create();
        /** @var ProductInterface $product */
        $product = $model->loadByAttribute(InventoryInterface::PLENTY_VARIATION_ID, (int) $variationId, InventoryInterface::SKU);
        return $product;
    }

    /**
     * @return $this
     */
    private function _initialize()
    {
        $this->_response =
        $this->_importResult =[];
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _finalize()
    {
        if (empty($this->_importResult)) {
            return $this;
        }

        $this->_stockRepository
            ->saveMultiple(
                $this->_importResult,
                [
                    InventoryInterface::PRODUCT_ID,
                    InventoryInterface::SKU,
                    InventoryInterface::STATUS,
                    InventoryInterface::PROCESSED_AT,
                    InventoryInterface::MESSAGE
                ]
            );

        return $this;
    }
}