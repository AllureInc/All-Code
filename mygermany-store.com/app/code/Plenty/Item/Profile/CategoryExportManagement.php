<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryTreeInterface;
use Magento\Catalog\Model\Category\Tree;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Model\Category;

use Plenty\Core\Model\Source\Status;
use Plenty\Item\Api\Data\Export\CategoryInterface as CategoryExportInterface;
use Plenty\Item\Api\CategoryExportManagementInterface;
use Plenty\Item\Api\CategoryExportRepositoryInterface;
use Plenty\Item\Model\Logger;
use Plenty\Item\Helper;
use Plenty\Item\Rest\CategoryInterface as ClientCategoryInterface;

/**
 * Class CategoryExportManagement
 * @package Plenty\Item\Profile
 */
class CategoryExportManagement extends AbstractManagement
    implements CategoryExportManagementInterface
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
     * @var CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    /**
     * @var CategoryListInterface
     */
    protected $_categoryListRepository;

    /**
     * @var CategoryExportRepositoryInterface
     */
    private $_categoryExportRepository;

    /**
     * CategoryExportManagement constructor.
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Tree $categoryTree
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryListInterface $categoryListRepository
     * @param CategoryExportRepositoryInterface $categoryExportRepository
     * @param ClientCategoryInterface $clientCategoryInterface
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
        CategoryRepositoryInterface $categoryRepository,
        CategoryListInterface $categoryListRepository,
        CategoryExportRepositoryInterface $categoryExportRepository,
        ClientCategoryInterface $clientCategoryInterface,
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
        $this->_categoryListRepository = $categoryListRepository;
        $this->_categoryExportRepository = $categoryExportRepository;
        $this->_client = $clientCategoryInterface;
        parent::__construct($helper, $logger, $dateTime, $serializer, $data);
    }

    /**
     * @return null|int
     * @throws \Exception
     */
    public function getDefaultLang()
    {
        if (!$this->hasData(self::DEFAULT_LANG)) {
            throw new \Exception(__('Default profile language is not set.'));
        }

        return $this->getData(self::DEFAULT_LANG);
    }

    /**
     * @param string $lang
     * @return $this
     */
    public function setDefaultLang(string $lang)
    {
        $this->setData(self::DEFAULT_LANG, $lang);
        return $this;
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
     * @param array $categoryIds
     * @return $this|CategoryExportManagementInterface|\Plenty\Item\Api\CategoryImportManagementInterface
     */
    public function export(
        array $categoryIds = []
    ) {
        return $this;
    }

    /**
     * @param array $categoryIds
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addToExport(array $categoryIds)
    {
        $this->_initResponseData();

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('entity_id', $categoryIds, 'in')
            ->create();

        $collection = $category = $this->_categoryListRepository->getList($searchCriteria);
        if (!$collection->getTotalCount()) {
            throw new \Exception(__('Could not find categories to add to export.'));
        }

        $rootCategoryMapping = $this->getRootCategoryMapping();
        $plentyRootCategoryId = null;
        $plentyRootCategoryPath = null;
        $attributes = [];
        /** @var Category $category */
        foreach ($collection->getItems() as $category) {
            $path = [];
            $plentyPath = [];
            $structure = explode('/', $category->getPath());
            array_shift($structure);
            $pathSize  = count($structure);

            if ($pathSize <= 1 || $category->getLevel() == 1) {
                $plentyRootCategoryId = $this->_getPlentyRootCategoryId($category->getId());
                if ($rootCategoryMapping
                    && $plentyRootCategoryId
                    && $plentyRootCategoryPath = $this->_getPlentyCategoryPath($category, $plentyRootCategoryId)
                ) {
                    $path[] = $category->getName();
                    $plentyPath[] = $plentyRootCategoryPath;
                }
                continue;
            }

            for ($i = $rootCategoryMapping ? 0 : 1; $i < $pathSize; $i++) {
                if (!isset($structure[$i])) {
                    continue;
                }

                if ($category->getId() != $structure[$i]) {
                    $subCategory = $this->_categoryRepository->get($structure[$i]);
                } else {
                    $subCategory = $category;
                }

                if ($subCategory->getLevel() == 1) {
                    $plentyRootCategoryId = $this->_getPlentyRootCategoryId($subCategory->getId());
                    if ($rootCategoryMapping
                        && $plentyRootCategoryId
                        && $plentyRootCategoryPath = $this->_getPlentyCategoryPath($subCategory, $plentyRootCategoryId)
                    ) {
                        $path[] = $subCategory->getName();
                        $plentyPath[] = $plentyRootCategoryPath;
                        continue;
                    }
                }

                $path[] = $subCategory->getName();
                $plentyPath[] = $subCategory->getName();
            }

            if ($pathSize > 1) {
                $category->setData('category_path', implode('/', $path));
                $category->setData('plenty_category_path', implode('/', $plentyPath));
                $category->setData('category_entries', $this->_serializer->serialize($plentyPath));
            }

            if (!$plentyRootCategoryId) {
                $category->setData('level', $category->getLevel() - 1);
            }

            $attributes[] = $this->_prepareCategoryDataForSave($category);
        }

        $this->_saveCategories($attributes);

        return $this;
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

    /**
     * @param $mageCategoryId
     * @return int|null
     */
    public function _getPlentyRootCategoryId($mageCategoryId)
    {
        if (!$categories = $this->getRootCategoryMapping()) {
            return null;
        }
        return $categories[$mageCategoryId] ?? null;
    }

    /**
     * @param Category $category
     * @param $plentyCategoryId
     * @return mixed|string
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getPlentyCategoryPath(Category $category, $plentyCategoryId)
    {
        $plentyRootCategoryPath = $this->_categoryExportRepository
            ->getPathByCategoryId(
                $plentyCategoryId,
                CategoryExportInterface::PLENTY_CATEGORY_ID,
                CategoryExportInterface::PLENTY_CATEGORY_PATH
            );

        if ($plentyRootCategoryPath) {
            return $plentyRootCategoryPath;
        }

        $response = $this->_client->getSearchCategory(1, $plentyCategoryId);
        if (!$response->getSize()
            || !$plentyCategory = $response->getFirstItem()
        ) {
            return $plentyRootCategoryPath;
        }

        $this->_saveCategoryResponse($category, $plentyCategory);

        return $plentyCategory->getData('name');
    }

    /**
     * @param Category $category
     * @param DataObject $response
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function _saveCategoryResponse(Category $category, DataObject $response)
    {
        $data = [
            CategoryExportInterface::PROFILE_ID => $this->getProfile()->getId(),
            CategoryExportInterface::CATEGORY_ID => $category->getId(),
            CategoryExportInterface::PARENT_ID => $category->getParentId(),
            CategoryExportInterface::SYSTEM_PATH => $category->getPath(),
            CategoryExportInterface::CATEGORY_LEVEL => $category->getLevel(),
            CategoryExportInterface::CHILDREN_COUNT => $category->getChildrenCount(),
            CategoryExportInterface::CATEGORY_NAME => $category->getName(),
            CategoryExportInterface::CATEGORY_PATH => $category->getName(),
            CategoryExportInterface::CATEGORY_ENTRIES => $this->_serializer->serialize([$response->getData('name')]),
            CategoryExportInterface::PLENTY_CATEGORY_ID => $response->getData(CategoryExportInterface::CATEGORY_ID),
            CategoryExportInterface::PLENTY_CATEGORY_PARENT_ID => $response->getData(CategoryExportInterface::PARENT_ID),
            CategoryExportInterface::PLENTY_CATEGORY_LEVEL => $response->getData('level'),
            CategoryExportInterface::PLENTY_CATEGORY_TYPE => $response->getData('type'),
            CategoryExportInterface::PLENTY_CATEGORY_NAME => $response->getData('name'),
            CategoryExportInterface::PLENTY_CATEGORY_PATH => $response->getData('name'),
            CategoryExportInterface::PLENTY_CATEGORY_HAS_CHILDREN => $response->getData('has_children'),
            CategoryExportInterface::PLENTY_CATEGORY_ENTRIES => $this->_serializer->serialize($response->getData('details')),
            CategoryExportInterface::MESSAGE => __('Root category has been added.'),
            CategoryExportInterface::STATUS => Status::COMPLETE,
            CategoryExportInterface::CREATED_AT => $this->_dateTime->gmtDate(),
            CategoryExportInterface::UPDATED_AT => $this->_dateTime->gmtDate()
        ];

        $this->_categoryExportRepository->saveMultiple($data);
        return $this;
    }

    /**
     * @param Category $category
     * @return array
     * @throws \Exception
     */
    private function _prepareCategoryDataForSave(Category $category)
    {
        return [
            CategoryExportInterface::PROFILE_ID => $this->getProfile()->getId(),
            CategoryExportInterface::CATEGORY_ID => $category->getId(),
            CategoryExportInterface::PARENT_ID => $category->getParentId(),
            CategoryExportInterface::SYSTEM_PATH => $category->getPath(),
            CategoryExportInterface::CATEGORY_PATH => $category->getCategoryPath(),
            CategoryExportInterface::CATEGORY_LEVEL => $category->getLevel(),
            CategoryExportInterface::CHILDREN_COUNT => $category->getChildrenCount(),
            CategoryExportInterface::CATEGORY_NAME => $category->getName(),
            CategoryExportInterface::CATEGORY_ENTRIES => $category->getCategoryEntries(),
            CategoryExportInterface::PLENTY_CATEGORY_ID => $category->getPlentyCategoryId(),
            CategoryExportInterface::PLENTY_CATEGORY_PARENT_ID => $category->getPlentyCategoryParentId(),
            CategoryExportInterface::PLENTY_CATEGORY_PATH => $category->getPlentyCategoryPath(),
            CategoryExportInterface::PLENTY_CATEGORY_TYPE => 'item',
            CategoryExportInterface::MESSAGE => __('Category has been added to export.'),
            CategoryExportInterface::STATUS => Status::PENDING,
            CategoryExportInterface::CREATED_AT => $this->_dateTime->gmtDate(),
            CategoryExportInterface::UPDATED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $attributes
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function _saveCategories(array $attributes)
    {
        $this->_categoryExportRepository->saveMultiple($attributes);
        return $this;
    }
}