<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile;

use Magento\Framework\Data\Collection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

use Plenty\Item\Api\CategoryCollectManagementInterface;
use Plenty\Item\Api\CategoryImportRepositoryInterface;
use Plenty\Item\Rest\CategoryInterface as ClientCategoryInterface;
use Plenty\Item\Model\Import\Category as CategoryImportModel;
use Plenty\Item\Api\Data\Import\CategoryInterface;
use Plenty\Item\Helper;
use Plenty\Item\Model\Logger;
use Plenty\Core\Model\Source\Status;

/**
 * Class CategoryCollectManagement
 * @package Plenty\Item\Profile
 */
class CategoryCollectManagement extends AbstractManagement
    implements CategoryCollectManagementInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $_categoryRepository;

    /**
     * @var CategoryImportRepositoryInterface
     */
    private $_categoryImportRepository;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var array
     */
    private $_collectionResult;

    /**
     * CategoryCollectManagement constructor.
     * @param ClientCategoryInterface $clientCategoryInterface
     * @param CategoryImportRepositoryInterface $categoryImportRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param Helper\Data $helper
     * @param Logger $logger
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        ClientCategoryInterface $clientCategoryInterface,
        CategoryImportRepositoryInterface $categoryImportRepository,
        CategoryRepositoryInterface $categoryRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        Helper\Data $helper,
        Logger $logger,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_client = $clientCategoryInterface;
        $this->_categoryRepository = $categoryRepository;
        $this->_categoryImportRepository = $categoryImportRepository;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_storeManager = $storeManager;
        parent::__construct($helper, $logger, $dateTime, $serializer, $data);
    }

    /**
     * @return null|array
     */
    public function getRootCategoryMapping()
    {
        return $this->getData(self::ROOT_CATEGORY_MAPPING);
    }

    /**
     * @param array $categories
     * @return $this
     */
    public function setRootCategoryMapping(array $categories)
    {
        $this->setData(self::ROOT_CATEGORY_MAPPING, $categories);
        return $this;
    }

    /**
     * @return null|int
     * @throws \Exception
     */
    public function getDefaultStoreId()
    {
        if (!$this->hasData(self::DEFAULT_STORE_ID)) {
            throw new \Exception(__('Default store ID is not set.'));
        }

        return $this->getData(self::DEFAULT_STORE_ID);
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setDefaultStoreId(int $storeId)
    {
        $this->setData(self::DEFAULT_STORE_ID, $storeId);
        return $this;
    }

    /**
     * @param null $categoryId
     * @param null $updatedAt
     * @param null $with
     * @param null $type
     * @param null $lang
     * @param null $parentId
     * @param null $name
     * @param null $level
     * @return $this|mixed
     * @throws \Exception
     */
    public function execute(
        $categoryId = null,
        $updatedAt = null,
        $with = null,
        $type = null,
        $lang = null,
        $parentId = null,
        $name = null,
        $level = null
    ) {
        $this->_initResponseData();
        $page = 1;
        $this->_collectionResult = [];

        do {
            $response = $this->_client->getSearchCategory(
                $page,
                $categoryId,
                $updatedAt,
                $with,
                $type,
                $lang,
                $parentId,
                $name,
                $level
            );

            if (!$response->getSize()) {
                return $this;
            }

            $this->_saveCategoryResponse($response);

            $result = $response->getColumnValues(CategoryInterface::CATEGORY_ID);
            $this->_collectionResult = array_merge($this->_collectionResult, $result);

            $page = $response->getFlag('page');
            $last = $response->getFlag('lastPageNumber');
            $page++;
        } while ($page <= $last);

        $this->_categoryCollectAfter();

        $this->addResponse(
            __('Categories have been collected. Effected ID(s): %1', implode(', ', $this->_collectionResult)),
            Status::SUCCESS
        );

        return $this;
    }

    /**
     * @return $this
     */
    private function _initResponseData()
    {
        $this->_response = [];
        return $this;
    }

    /**
     * @param Collection $data
     * @param array $fields
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Exception
     */
    private function _saveCategoryResponse(Collection $data, array $fields = [])
    {
        $categories = [];
        /** @var DataObject $item */
        foreach ($data as $item) {
            $categories[] = $this->_prepareCategoryResponseForSave($item);
        }

        $this->_categoryImportRepository->saveMultiple($categories, $fields);
        return $this;
    }

    /**
     * @param DataObject $category
     * @return array
     * @throws \Exception
     */
    private function _prepareCategoryResponseForSave(DataObject $category) : array
    {
        return [
            CategoryInterface::PROFILE_ID => $this->getProfile()->getId(),
            CategoryInterface::CATEGORY_ID => $category->getData(CategoryInterface::CATEGORY_ID),
            CategoryInterface::PARENT_ID => $category->getData(CategoryInterface::PARENT_ID),
            CategoryInterface::LEVEL => $category->getData(CategoryInterface::LEVEL),
            CategoryInterface::TYPE => $category->getData(CategoryInterface::TYPE),
            CategoryInterface::NAME => $category->getData(CategoryInterface::NAME),
            CategoryInterface::PATH => $category->getData(CategoryInterface::PATH),
            CategoryInterface::ORIGINAL_PATH => $category->getData(CategoryInterface::ORIGINAL_PATH),
            CategoryInterface::PREVIEW_URL => $category->getData(CategoryInterface::PREVIEW_URL),
            CategoryInterface::HAS_CHILDREN => $category->getData(CategoryInterface::HAS_CHILDREN),
            CategoryInterface::DETAILS => $this->_serializer->serialize($category->getData(CategoryInterface::DETAILS)),
            CategoryInterface::STATUS => Status::PENDING,
            CategoryInterface::CREATED_AT => $this->_dateTime->gmtDate(),
            CategoryInterface::UPDATED_AT => $category->getData(CategoryInterface::UPDATED_AT),
            CategoryInterface::COLLECTED_AT => $this->_dateTime->gmtDate(),
            CategoryInterface::MESSAGE => __('Collected.')
        ];
    }

    /**
     * @throws \Exception
     */
    private function _categoryCollectAfter()
    {
        $profileFilter = $this->_filterBuilder->setField(CategoryInterface::PROFILE_ID)
            ->setValue($this->getProfile()->getId())
            ->setConditionType('eq')
            ->create();
        $pendingFilter = $this->_filterBuilder->setField(CategoryInterface::STATUS)
            ->setValue(Status::PENDING)
            ->setConditionType('eq')
            ->create();

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilters([$profileFilter, $pendingFilter])
            ->create();

        $collection = $this->_categoryImportRepository->getList($searchCriteria);

        if (!$collection->getTotalCount()) {
            return $this;
        }

        $categories = [];
        /** @var CategoryImportModel $category */
        foreach ($collection->getItems() as $category) {
            try {
                $this->buildCategoryPath($category);
                $categories[] = $category->toArray();
            } catch (\Exception $e) {
                $this->addResponse($e->getMessage(), Status::ERROR);
            }
        }

        if (empty($categories)) {
            return $this;
        }

        $this->_categoryImportRepository->saveMultiple(
            $categories,
            [
                CategoryInterface::PROFILE_ID,
                CategoryInterface::CATEGORY_ID,
                CategoryInterface::PATH,
                CategoryInterface::ORIGINAL_PATH
            ]
        );

        return $this;
    }

    /**
     * @param CategoryInterface $category
     * @param null $parentId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function buildCategoryPath(
        CategoryInterface $category,
        $parentId = null
    ) {
        $originalPath = [];
        $mapCategoryId = null;

        if (!$this->getRootCategoryMapping() && !$this->getData('_root_category')) {
            $defaultStore = $this->_storeManager->getStore($this->getDefaultStoreId());
            $defaultRootCategoryId = $defaultStore->getRootCategoryId();
            $mageCategory = $this->_categoryRepository->get($defaultRootCategoryId);
            $this->setData('_root_category', $mageCategory->getName());
        }

        if (null === $parentId) {
            $category->setPath($category->getName());
            $category->setOriginalPath(null);
            $this->setData('_root_category_mapping', null);
            $this->setData('_allowed_nested_level', null);
        }

        if (null === $parentId && !$category->getParentId()) {
            if ($mapCategoryId = $this->_getMageRootCategoryIdMapping($category->getCategoryId())) {
                $mageCategory = $this->_categoryRepository->get($mapCategoryId);
                $category->setPath($mageCategory->getName());
            } elseif ($rootCategory = $this->getData('_root_category')) {
                $category->setPath($rootCategory . '/' . $category->getName());
            } else {
                $category->setPath(null);
            }
            $category->setOriginalPath($category->getName());
            return $this;
        } elseif (null === $parentId && $category->getParentId()) {
            $parentId = $category->getParentId();
        }

        if (null === $parentId) {
            return $this;
        }

        $parentCategory = $this->_categoryImportRepository->getById($parentId);
        if (null === $parentCategory) {
            return $this;
        }

        if ($mapCategoryId = $this->_getMageRootCategoryIdMapping($parentCategory->getCategoryId())) {
            $mageCategory = $this->_categoryRepository->get($mapCategoryId);
            $this->setData('_root_category_mapping', $mageCategory->getName());
            $category->setPath($this->getData('_root_category_mapping') . '/' . $category->getPath());
        } elseif ($this->getRootCategoryMapping()) {
            $category->setPath($parentCategory->getName() . '/' . $category->getPath());
        } elseif ($rootCategory = $this->getData('_root_category')) {
            if (!$parentCategory->getParentId()) {
                $category->setPath($rootCategory . '/' . $parentCategory->getName() . '/' . $category->getPath());
            } else {
                $category->setPath($parentCategory->getName() . '/' . $category->getPath());
            }
        } else {
            $category->setPath(null);
        }

        $originalPath[] = $parentCategory->getName();
        $originalPath[] = $category->getOriginalPath()
            ? $category->getOriginalPath()
            : $category->getName();
        $category->setOriginalPath(implode('/', $originalPath));

        if (!$parentId = $parentCategory->getParentId()) {
            return $this;
        }

        $this->buildCategoryPath($category, $parentId);

        return $this;
    }

    /**
     * @param $plentyCategoryId
     * @return int|null
     */
    private function _getMageRootCategoryIdMapping($plentyCategoryId)
    {
        if (!$categories = array_values($this->getRootCategoryMapping())) {
            return null;
        }

        $index = $this->getSearchArrayMatch($plentyCategoryId, $categories, 'plenty_category');
        if (false === $index || !isset($categories[$index]['mage_category'])) {
            return null;
        }

        return $categories[$index]['mage_category'];
    }
}