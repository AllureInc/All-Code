<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryTreeInterface;
use Magento\Catalog\Model\Category\Tree;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;

use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Item\Api\ProductExportManagementInterface;
use Plenty\Item\Api\ProductCategoryExportManagementInterface;
use Plenty\Item\Api\CategoryExportManagementInterface;
use Plenty\Item\Api\Data\Export\CategoryInterface as CategoryExportInterface;
use Plenty\Item\Api\CategoryExportRepositoryInterface;
use Plenty\Item\Model\Logger;
use Plenty\Item\Helper;

/**
 * Class ProductCategoryExportManagement
 * @package Plenty\Item\Profile
 */
class ProductCategoryExportManagement extends AbstractManagement
    implements ProductCategoryExportManagementInterface
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
     * @var Tree
     */
    private $_categoryTree;

    /**
     * @var CategoryRepository
     */
    protected $_categoryRepository;

    /**
     * @var CategoryExportRepositoryInterface
     */
    private $_categoryExportRepository;

    /**
     * @var CategoryExportManagementInterface
     */
    private $_categoryExportManagement;

    /**
     * ProductCategoryExportManagement constructor.
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Tree $categoryTree
     * @param CategoryRepository $categoryRepository
     * @param CategoryExportRepositoryInterface $categoryExportRepository
     * @param CategoryExportManagementInterface $categoryExportManagement
     * @param Helper\Data $helper
     * @param Logger $logger
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Tree $categoryTree,
        CategoryRepository $categoryRepository,
        CategoryExportRepositoryInterface $categoryExportRepository,
        CategoryExportManagementInterface $categoryExportManagement,
        Helper\Data $helper,
        Logger $logger,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_categoryTree = $categoryTree;
        $this->_categoryRepository = $categoryRepository;
        $this->_categoryExportRepository = $categoryExportRepository;
        $this->_categoryExportManagement = $categoryExportManagement;
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
     * @return $this|mixed
     */
    public function setProfileHistory(HistoryInterface $history)
    {
        $this->_profileHistory = $history;
        return $this;
    }

    /**
     * @param Product $product
     * @return $this|array|ProductCategoryExportManagementInterface
     * @throws \Exception
     */
    public function execute(Product $product)
    {
        $this->_initResponseData();

        if (!$fallBackCategory = $this->getProfileEntity()->getFallbackCategory()) {
            throw new \Exception(__('Fallback category is not set.'));
        }

        if (!$this->getProfileEntity()->getIsActiveCategoryExport()
            || !$productCategoryIds = $product->getCategoryIds()
        ) {
            $product->setData(ProductExportManagement::REQUEST_CATEGORY, [['categoryId' => $fallBackCategory]]);
            $this->_response['success'][] = __('Default category %1 has been assigned.', $fallBackCategory);
            return $this;
        }

        /**
         * @todo implement category export
         * Fallback to root until implemented.
         */
        $product->setData(ProductExportManagement::REQUEST_CATEGORY, [['categoryId' => $fallBackCategory]]);
        $this->_response['success'][] = __('Default category %1 has been assigned.', $fallBackCategory);
        return $this;

        $result = [];
        $profileId = $this->getProfile()->getId();

        $filters = [
            $this->_filterBuilder
                ->setField(CategoryExportInterface::PROFILE_ID)
                ->setValue($this->getProfile()->getId())
                ->setConditionType('eq')
                ->create(),
            $this->_filterBuilder
                ->setField(CategoryExportInterface::CATEGORY_ID)
                ->setValue($productCategoryIds)
                ->setConditionType('in')
                ->create()
        ];

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilters($filters)
            ->create();

        $collection = $this->_categoryExportRepository->getList($searchCriteria);

        /** @var CategoryExportInterface $category */
        foreach ($collection as $category) {
            $index = array_search($category->getCategoryId(), $productCategoryIds);
            if (false === $index || !isset($productCategoryIds[$index])) {
                continue;
            }

            unset($productCategoryIds[$index]);
        }

        if (!empty($productCategoryIds)) {
            $this->_categoryExportManagement->setProfile($this->getProfile())
                ->setDefaultLang($this->getProfileEntity()->getDefaultLang())
                ->setRootCategoryMapping($this->getProfileEntity()->getRootCategoryMapping())
                ->addToExport($productCategoryIds);

            $categoryRecord = $this->_categoryExportFactory()
                ->getCollection()
                ->addProfileFilter($profileId)
                ->addFieldToFilter('category_id', ['in' => $product->getCategoryIds()]);
            if (!$categoryRecord->getSize()) {
                throw new \Exception(__('Could not find categories to export. [Ids: %s]', $productCategoryIds));
            }
        }

        /** @var CategoryExportInterface $category */
        foreach ($categoryRecord as $category) {
            if ($plentyCategoryId = $category->getPlentyCategoryId()) {
                $result[] = ['categoryId' => $plentyCategoryId];
                $this->_response['success'][] = __('Category has been assigned. [Plenty ID: # %s, Mage ID: #%s]',
                    $plentyCategoryId, $category->getCategoryId());
                continue;
            }

            try {
                $category->setProfileEntity($this->getProfileEntity());
                $categoryId = $category->export();
                $result[] = ['categoryId' => $categoryId];
            } catch (\Exception $e) {
                $this->_response['error'][] = __($e->getMessage());
                continue;
            }

            $this->_response['success'][] = __('Category has been created. [Plenty ID: # %s, Mage ID: #%s]',
                $categoryId, $category->getCategoryId());
        }

        $product->setData('request_category', $result);

        return $result;
    }

    /**
     * @param $categoryId
     * @param null $depth
     * @return CategoryTreeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _getTree($categoryId, $depth = null)
    {
        /** @var CategoryInterface $category */
        $category = $this->_categoryRepository->get($categoryId);
        $result = $this->_categoryTree->getTree($this->_categoryTree->getRootNode($category), $depth);
        return $result;
    }


    /**
     * @return $this
     */
    private function _initResponseData()
    {
        $this->_response = [];
        return $this;
    }
}