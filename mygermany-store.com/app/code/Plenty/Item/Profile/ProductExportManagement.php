<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile;

use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Bundle\Model\Product as BundleProduct;
use Magento\Bundle\Model\ResourceModel\Selection as BundleProductSelection;

use Plenty\Item\Api\ProductExportManagementInterface;
use Plenty\Item\Api\ItemManagementInterface;
use Plenty\Item\Api\ProductAttributeExportManagementInterface;
use Plenty\Item\Api\ProductCategoryExportManagementInterface;
use Plenty\Item\Api\ProductItemExportManagementInterface;
use Plenty\Item\Api\ProductVariationExportManagementInterface;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Item\Api\ProductExportRepositoryInterface;
use Plenty\Item\Api\Data\Import\ItemInterface;
use Plenty\Item\Api\Data\Export\ProductInterface as ExportEntityInterface;
use Plenty\Item\Model\Export\Product as ProductExportModel;
use Plenty\Item\Model\ResourceModel\Export\Product\CollectionFactory as ProductExportCollectionFactory;
use Plenty\Item\Model\Import\ItemRepository;



use Plenty\Item\Helper;
use Plenty\Item\Model\Logger;
use Plenty\Item\Profile\Config\Source;
use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Core\Model\Source\Status;

/**
 * Class ProductExportManagement
 * @package Plenty\Item\Profile
 */
class ProductExportManagement extends AbstractManagement
    implements ProductExportManagementInterface
{
    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var BundleProductSelection
     */
    private $_bundleSelection;

    /**
     * @var ProductExportModel
     */
    private $_productExportModel;

    /**
     * @var ProductExportCollectionFactory
     */
    private $_productExportCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $_productRepository;

    /**
     * @var ProductExportRepositoryInterface
     */
    private $_productExportRepository;


    private $_itemImportRepository;

    /**
     * @var ItemManagementInterface
     */
    private $_itemManagement;

    /**
     * @var ProductAttributeExportManagementInterface
     */
    private $_productAttributeExportManagement;

    /**
     * @var ProductCategoryExportManagementInterface
     */
    private $_productCategoryExportManagement;

    /**
     * @var ProductItemExportManagementInterface
     */
    private $_productItemExportManagement;


    private $_productVariationExportManagement;

    /**
     * @var Product
     */
    private $_product;

    /**
     * @var ExportEntityInterface
     */
    private $_exportEntity;

    /**
     * @var array
     */
    private $_exportResponse;




    public function __construct(
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BundleProductSelection $bundleSelection,
        ProductRepositoryInterface $productRepository,
        ProductExportModel $productExportModel,
        ProductExportCollectionFactory $productExportCollectionFactory,
        ProductExportRepositoryInterface $productExportRepository,
        ItemRepository $itemImportRepository,
        ItemManagementInterface $itemManagement,
        ProductAttributeExportManagementInterface $productAttributeExportManagement,
        ProductCategoryExportManagementInterface $productCategoryExportManagement,
        ProductItemExportManagementInterface $productItemExportManagement,
        ProductVariationExportManagementInterface $productVariationExportManagement,
        Helper\Data $helper,
        Logger $logger,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_bundleSelection = $bundleSelection;
        $this->_productExportModel = $productExportModel;
        $this->_productExportCollectionFactory = $productExportCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->_productExportRepository = $productExportRepository;
        $this->_itemImportRepository = $itemImportRepository;
        $this->_itemManagement = $itemManagement;
        $this->_productAttributeExportManagement = $productAttributeExportManagement;
        $this->_productCategoryExportManagement = $productCategoryExportManagement;
        $this->_productItemExportManagement = $productItemExportManagement;
        $this->_productVariationExportManagement = $productVariationExportManagement;

        parent::__construct($helper, $logger, $dateTime, $serializer, $data);
    }

    /**
     * @return ProductExportInterface
     * @throws \Exception
     */
    public function getProfileEntity(): ProductExportInterface
    {
        if (!$this->_profileEntity) {
            throw new \Exception(__('Profile entity is not set.'));
        }

        return $this->_profileEntity;
    }

    /**
     * @param ProductExportInterface $profileEntity
     * @return $this
     */
    public function setProfileEntity(ProductExportInterface $profileEntity)
    {
        $this->_profileEntity = $profileEntity;
        return $this;
    }

    /**
     * @return HistoryInterface
     * @throws \Exception
     */
    public function getProfileHistory() : HistoryInterface
    {
        if (!$this->_profileHistory) {
            throw new \Exception(__('Profile history is not set.'));
        }

        return $this->_profileHistory;
    }

    /**
     * @param HistoryInterface $history
     * @return $this
     */
    public function setProfileHistory(HistoryInterface $history)
    {
        $this->_profileHistory = $history;
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function execute()
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(ExportEntityInterface::PROFILE_ID, $this->getProfile()->getId(), 'eq');

        if ($filters = $this->getProfileEntity()->getProductExportSearchCriteria()) {
            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilters($filters);
        } else {
            $searchCriteria->addFilter(ExportEntityInterface::STATUS, Status::PENDING, 'eq');
        }

        $collection = $this->_productExportRepository->getList($searchCriteria->create());

        if (!$collection->getTotalCount()) {
            $this->addResponse(__('Products are up to date.'), Status::SUCCESS);
            return $this;
        }

        $this->_exportResponse = [];

        /** @var ExportEntityInterface $exportEntity */
        foreach ($collection->getItems() as $exportEntity) {
            if ($exportEntity->getProductType() === BundleProduct\Type::TYPE_CODE) {
                $this->_exportBundleComponents($exportEntity);
            }

            try {
                $this->_export($exportEntity);
                $this->_exportResponse['success'][] = $exportEntity->getVariationId();
            } catch (\Exception $e) {
                $this->_exportResponse['error'][] = [
                    'status'        => Status::ERROR,
                    'profile_id'    => $this->getProfile()->getId(),
                    'product_id'    => $exportEntity->getProductId(),
                    'message'      => serialize($e->getMessage()),
                    'updated_at'    => $this->_dateTime->gmtDate()
                ];
            }
        }

        $this->_handleExportResult();

        return $this;
    }

    /**
     * @param ExportEntityInterface $exportEntity
     * @return $this
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    protected function _export(ExportEntityInterface $exportEntity)
    {
        $this->_initialize($exportEntity)
            ->_createCategory()
            ->_createItem()
            ->_createConfigAttributes()
            ->_createVariation()
            ->_finalize();

        return $this;
    }

    /**
     * @param ExportEntityInterface $exportEntity
     * @return $this
     * @throws \Exception
     */
    protected function _exportBundleComponents(ExportEntityInterface $exportEntity)
    {
        if ($exportEntity->getProductType() !== Product\Type::TYPE_BUNDLE) {
            return $this;
        }

        $bundleComponentIds = $this->_bundleSelection->getChildrenIds($exportEntity->getProductId(), false);
        foreach ($bundleComponentIds as $bundleComponentId) {
            $bundleComponentIds['ids'][] = current($bundleComponentId);
        }

        $bundleComponentIds = $bundleComponentIds['ids'] ?? [];
        if (empty($bundleComponentIds)) {
            return $this;
        }

        $collection = $this->_productExportCollectionFactory->create();
        $newRecordIds = $collection->addProfileFilter($this->getProfile()->getId())
            ->addFieldToFilter(ExportEntityInterface::PRODUCT_ID, ['in' => $bundleComponentIds])
            ->getColumnValues(ExportEntityInterface::PRODUCT_ID);

        $newRecordIds = array_diff_assoc($bundleComponentIds, $newRecordIds);

        if (!empty($newRecordIds)) {
            try {
                $exportEntity->addProductsToExport($this->getProfile()->getId(), $newRecordIds);
            } catch (\Exception $e) {
                $this->_exportResponse['error'][] = [
                    'status' => Status::ERROR,
                    'profile_id' => $this->getProfile()->getId(),
                    'product_id' => $exportEntity->getProductId(),
                    'message' => serialize($e->getMessage()),
                    'updated_at' => $this->_dateTime->gmtDate()
                ];
            }
        }

        $collection = $this->_productExportCollectionFactory->create();
        $bundleComponents = $collection->addProfileFilter($this->getProfile()->getId())
            ->addFieldToFilter(ExportEntityInterface::PRODUCT_ID, ['in' => $bundleComponentIds]);

        if (!$bundleComponents->getSize()) {
            return $this;
        }

        /** @var ExportEntityInterface $bundleComponent */
        foreach ($bundleComponents as $bundleComponent) {
            try {
                $this->_export($bundleComponent);
                $this->_exportResponse['success'][] = $exportEntity->getVariationId();
            } catch (\Exception $e) {
                $this->_exportResponse['error'][] = [
                    'status' => Status::ERROR,
                    'profile_id' => $this->getProfile()->getId(),
                    'product_id' => $exportEntity->getProductId(),
                    'message' => serialize($e->getMessage()),
                    'updated_at' => $this->_dateTime->gmtDate()
                ];
            }
        }

        return $this;
    }

    /**
     * @param ExportEntityInterface $exportEntity
     * @return $this
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws \Exception
     */
    private function _initialize(ExportEntityInterface $exportEntity)
    {
        $this->_initResponse()
            ->_initProduct($exportEntity)
            ->_initDefaultLang()
            ->_initItemVariation();

        $this->_productExportRepository->update(
            [
                ExportEntityInterface::STATUS => Status::PROCESSING,
                ExportEntityInterface::UPDATED_AT => $this->_dateTime->gmtDate()
            ], [
                ExportEntityInterface::ENTITY_ID.' = ?' => (int) $this->_getExportEntity()->getVariationId()
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    private function _initResponse()
    {
        $this->_error =
        $this->_response = null;
        return $this;
    }

    /**
     * @param ExportEntityInterface $exportEntity
     * @return $this
     * @throws NoSuchEntityException
     */
    private function _initProduct(ExportEntityInterface $exportEntity)
    {
        $this->_exportEntity = $exportEntity;
        $this->_product = $this->_productRepository->getById($exportEntity->getProductId());
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _initDefaultLang()
    {
        if (!$this->_getProduct()->hasData('default_lang')) {
            $this->_getProduct()->setData('default_lang', $this->getProfileEntity()->getDefaultLang());
        }

        if (!$this->_getProduct()->hasData('default_store')) {
            $this->_getProduct()->setData('default_store', $this->getProfileEntity()->getDefaultStoreMapping());
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _initItemVariation()
    {
        $response = $this->_itemManagement->collectBySku(
            $this->getProfile()->getId(),
            $this->_getProduct()->getSku(),
            $this->getProfileEntity()->getApiItemSearchFilters(),
            $this->getProfileEntity()->getApiVariationSearchFilters()
        );

        /** @var DataObject $variationResponse */
        $variationResponse = $response->getData(ItemInterface::VARIATION_RESPONSE);
        if ($variationResponse
            && $variationResponse->getData(ItemInterface::ITEM_ID) == $this->_getProduct()->getData(ExportEntityInterface::PLENTY_ITEM_ID)
            && $variationResponse[ItemInterface::VARIATION_ID] == $this->_getProduct()->getData(ExportEntityInterface::PLENTY_VARIATION_ID)
        ) {
            $this->_getProduct()->setData(self::IS_NEW_EXPORT, false)
                ->setData(ExportEntityInterface::PLENTY_ITEM_ID, $variationResponse->getData(ItemInterface::ITEM_ID))
                ->setData(ExportEntityInterface::PLENTY_VARIATION_ID, $variationResponse->getData(ItemInterface::VARIATION_ID));
        } else {
            $this->_getProduct()->setData(self::IS_NEW_EXPORT, true)
                ->setData(ExportEntityInterface::PLENTY_ITEM_ID, null)
                ->setData(ExportEntityInterface::PLENTY_VARIATION_ID, null);
        }

        $this->_getProduct()->setData(ExportEntityInterface::IS_MAIN_PRODUCT, $this->_getProduct()->isVisibleInSiteVisibility())
            ->setData(ItemInterface::ITEM_RESPONSE, $response->getData(ItemInterface::ITEM_RESPONSE));

        return $this;
    }

    /**
     * @return Product
     * @throws \Exception
     */
    private function _getProduct()
    {
        if (!$this->_product) {
            throw new \Exception(__('Catalog product is not set.'));
        }
        return $this->_product;
    }

    /**
     * @return ExportEntityInterface
     * @throws \Exception
     */
    private function _getExportEntity()
    {
        if (!$this->_exportEntity) {
            throw new \Exception(__('Export product entity is not set.'));
        }
        return $this->_exportEntity;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _createCategory()
    {
        try {
            $this->_productCategoryExportManagement->setProfile($this->getProfile())
                ->setProfileEntity($this->getProfileEntity())
                ->execute($this->_getProduct());
        } catch (\Exception $e) {
            $this->_error['category'] = $e->getMessage();
            throw new \Exception($e->getMessage());
        }
        $this->_response['category'] = $this->_productCategoryExportManagement->getResponse();

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _createItem()
    {
        try {
            $this->_productItemExportManagement->setProfile($this->getProfile())
                ->setProfileEntity($this->getProfileEntity())
                ->setProfileHistory($this->getProfileHistory())
                ->execute($this->_getProduct());
        } catch (\Exception $e) {
            $this->_error[] = $e->getMessage();
            throw new \Exception($e->getMessage());
        }

        $this->_response['item'] = $this->_productItemExportManagement->getResponse();

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _createConfigAttributes()
    {
        try {
            $this->_productAttributeExportManagement->setProfileEntity($this->getProfileEntity())
            ->execute($this->_getProduct());
        } catch (\Exception $e) {
            $this->_error[] = $e->getMessage();
            throw new \Exception($e->getMessage());
        }
        $this->_response['config_attribute'] = $this->_productAttributeExportManagement->getResponse();

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _createVariation()
    {
        try {
            $this->_productVariationExportManagement
                ->setProfile($this->getProfile())
                ->setProfileEntity($this->getProfileEntity())
                ->setProfileHistory($this->getProfileHistory())
                ->execute($this->_getProduct());
        } catch (\Exception $e) {
            $this->_error[] = $e->getMessage();
            throw new \Exception($e->getMessage());
        }
        $this->_response['variation'] = $this->_productVariationExportManagement->getResponse();

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _finalize()
    {
        $itemId = $this->_getProduct()->getData(ExportEntityInterface::PLENTY_ITEM_ID);
        $variationId = $this->_getProduct()->getData(ExportEntityInterface::PLENTY_VARIATION_ID);
        $mainVariationId = $this->_getProduct()->getData(ExportEntityInterface::MAIN_VARIATION_ID);
        $this->_productExportRepository
            ->update(
                [
                    ExportEntityInterface::ITEM_ID => $itemId,
                    ExportEntityInterface::VARIATION_ID => $variationId,
                    ExportEntityInterface::MAIN_VARIATION_ID => $mainVariationId,
                    ExportEntityInterface::STATUS => Status::COMPLETE,
                    ExportEntityInterface::MESSAGE => empty($this->_errors)
                        ? $this->_serializer->serialize($this->_response)
                        : $this->_serializer->serialize($this->_errors),
                    ExportEntityInterface::UPDATED_AT => $this->_dateTime->gmtDate(),
                    ExportEntityInterface::PROCESSED_AT => $this->_dateTime->gmtDate()
                ], [
                    ExportEntityInterface::ENTITY_ID. ' = ?' => $this->_getExportEntity()->getId()
                ]
            );

        $this->_getExportEntity()
            ->setItemId($itemId)
            ->setVariationId($variationId)
            ->setMainVariationId($mainVariationId);

        if (!empty($this->getErrors())) {
            $this->_logResponse(__METHOD__, $this->getErrors());
        }

        if ($this->getProfileEntity()->getIsActiveRequestLog()) {
            $this->_logResponse(__METHOD__, $this->getResponse());
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _handleExportResult()
    {
        if (isset($this->_exportResponse['error']) && !empty($this->_exportResponse['error'])) {
            $this->_productExportRepository
                ->saveMultiple($this->_exportResponse['error'], ['status', 'message', 'updated_at']);
        }

        if (isset($this->_exportResponse['success']) && !empty($this->_exportResponse['success'])) {
            $this->_updateItemStatus($this->_exportResponse['success']);
        }

        return $this;
    }

    /**
     * @param array $variationIds
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function _updateItemStatus(array $variationIds)
    {
        $bind = [
            'status' => Status::COMPLETE,
            'processed_at' => $this->_dateTime->gmtDate()
        ];

        $where = [
            'variation_id IN(?)' => $variationIds,
            'profile_id = ?' => (int) $this->getProfile()->getId()
        ];

        $this->_itemImportRepository->update($bind, $where);

        return $this;
    }
}