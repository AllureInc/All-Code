<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\DataObject\IdentityInterface;

use Plenty\Item\Api\Data\Import\CategoryInterface;
use Plenty\Core\Model\Source\Status;
use Plenty\Item\Helper\Data as Helper;
use Plenty\Item\Model\Logger;
use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Rest\Category as CategoryClient;
use Plenty\Core\Model\Profile\Config\Source\Behaviour;
use Plenty\Core\Plugin\ImportExport\Model\ImportFactory;

/**
 * Class Category
 * @package Plenty\Item\Model\Import
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Category getResource()
 * @method \Plenty\Item\Model\ResourceModel\Import\Category\Collection getCollection()
 *
 * @method boolean getBehaviourUpdate()
 * @method Category setBehaviourUpdate(boolean $value)
 * @method string getBehaviour()
 * @method Category setBehaviour(string $value)
 * @method \Plenty\Item\Profile\Import\Entity\Product getProfileEntity()
 * @method \Plenty\Item\Profile\Import\Entity\Product setProfileEntity(object $value)
 */
class Category extends ImportExportAbstract implements CategoryInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_category';

    protected $_cacheTag        = 'plenty_item_import_category';
    protected $_eventPrefix     = 'plenty_item_import_category';

    protected $_importFactory;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $_dataCollectionFactory;

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Category::class);
    }

    /**
     * Category constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param CategoryClient $restCategoryClient
     * @param CollectionFactory $dataCollectionFactory
     * @param ImportFactory $importFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        CategoryClient $restCategoryClient,
        CollectionFactory $dataCollectionFactory,
        ImportFactory $importFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_api = $restCategoryClient;
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->_importFactory = $importFactory;
        parent::__construct($context, $registry, $dateTime, $helper, $logger, $resource, $resourceCollection, $serializer, $data);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return CategoryClient
     */
    protected function _api()
    {
        if (!$this->_api->getProfileEntity()) {
            $this->_api->setProfileEntity($this->getProfileEntity());
        }
        return $this->_api;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    protected function _getLastUpdatedAt()
    {
        $updatedBetween = null;
        if (false === $this->_behaviourUpdate) {
            return $updatedBetween;
        }

        /** @var Category $lastUpdatedEntry */
        $lastUpdatedEntry = $this->getCollection()
            ->addFieldToFilter('profile_id', $this->getProfileEntity()->getProfile()->getId())
            ->addFieldToSelect('collected_at')
            ->setOrder('collected_at', 'desc')
            ->setPageSize(1)
            ->getFirstItem();

        if (strtotime($lastUpdatedEntry->getCollectedAt()) > 0) {
            $updatedBetween = $this->_helper()
                ->getDateTimeLocale($lastUpdatedEntry->getCollectedAt());
        }

        return $updatedBetween;
    }

    /**
     * @param Category $category
     * @param array $categoryCollection
     * @param null $parentCategoryId
     * @return Category
     */
    protected function _setCategoryPath(
        Category $category, array $categoryCollection, $parentCategoryId = null
    ) {
        if (!$category->getPath()) {
            $category->setData('path', $category->getName());
        }

        if (!$parentId = $category->getParentId()) {
            return $category;
        }

        if (null !== $parentCategoryId) {
            $parentId = $parentCategoryId;
        }

        if (!isset($categoryCollection['items'])) {
            return $category;
        }

        $categories = $categoryCollection['items'];

        $parentCategoryIndex = $this->getSearchArrayMatch($parentId, $categories, 'category_id');
        if (false === $parentCategoryIndex && !isset($categories[$parentCategoryIndex]['name'])) {
            return $category;
        }

        $path = $categories[$parentCategoryIndex]['name'] .'/'. $category->getPath();
        $category->setData('path', $path);

        if (!isset($categories[$parentCategoryIndex]['parent_id'])) {
            return $category;
        }

        $this->_setCategoryPath($category, $categoryCollection, $categories[$parentCategoryIndex]['parent_id']);

        return $category;
    }

    /**
     * @throws \Exception
     */
    protected function _buildMagentoCategoryPath()
    {
        $categoryCollection = $this->getCollection()
            ->addFieldToFilter('profile_id', $this->getProfileEntity()->getProfile()->getId())
            ->addFieldToSelect(['category_id', 'parent_id', 'name', 'path']);

        if (!$categoryCollection->getSize()) {
            throw new \Exception(__('Could not find any categories. [%s]', __METHOD__));
        }

        $categories = $categoryCollection->toArray();
        /** @var Category $category */
        foreach ($categoryCollection as $category) {
            $this->_setCategoryPath($category, $categories);
        }

        $this->_saveCategoryResponseData($categoryCollection, ['profile_id', 'category_id', 'path']);

        return $this;
    }

    /**
     * @param $categoryData
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @deprecated
     */
    protected function _importCategories($categoryData)
    {
        $model = $this->_importFactory->create();
        $model->setEntityCode('catalog_category');
        $model->setBehavior('append');
        // $model->setProfile($this->getProfileEntity()->getProfile());
        $model->execute($categoryData);
        return $this;
    }

    /**
     * @return int
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @param $profileId
     * @return $this
     */
    public function setProfileId($profileId)
    {
        $this->setData(self::PROFILE_ID, $profileId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * @param $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        $this->setData(self::CATEGORY_ID, $categoryId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMageId()
    {
        return $this->getData(self::MAGE_ID);
    }

    /**
     * @param $mageId
     * @return $this
     */
    public function setMageId($mageId)
    {
        $this->setData(self::MAGE_ID, $mageId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * @param $parentId
     * @return $this
     */
    public function setParentId($parentId)
    {
        $this->setData(self::PARENT_ID, $parentId);
        return $this;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->getData(self::LEVEL);
    }

    /**
     * @return bool
     */
    public function getHasChildren()
    {
        return $this->getData(self::HAS_CHILDREN);
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->setData(self::NAME, $name);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->setData(self::PATH, $path);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getOriginalPath()
    {
        return $this->getData(self::ORIGINAL_PATH);
    }

    /**
     * @param string $originalPath
     * @return $this
     */
    public function setOriginalPath($originalPath)
    {
        $this->setData(self::ORIGINAL_PATH, $originalPath);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPreviewUrl()
    {
        return $this->getData(self::PREVIEW_URL);
    }

    /**
     * @return null|string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getDetails()
    {
        if (!$data = $this->getData(self::DETAILS)) {
            return [];
        }
        return $this->_serializer->unserialize($data);
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->setData(self::MESSAGE, $message);
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->getData(self::UPDATED_BY);
    }

    /**
     * @return null|string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $dateTime
     * @return $this
     */
    public function setCreatedAt($dateTime)
    {
        $this->setData(self::CREATED_AT, $dateTime);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $dateTime
     * @return $this
     */
    public function setUpdatedAt($dateTime)
    {
        $this->setData(self::UPDATED_AT, $dateTime);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCollectedAt()
    {
        return $this->getData(self::COLLECTED_AT);
    }

    /**
     * @param $dateTime
     * @return $this
     */
    public function setCollectedAt($dateTime)
    {
        $this->setData(self::COLLECTED_AT, $dateTime);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getProcessedAt()
    {
        return $this->getData(self::PROCESSED_AT);
    }

    /**
     * @param $dateTime
     * @return $this
     */
    public function setProcessedAt($dateTime)
    {
        $this->setData(self::PROCESSED_AT, $dateTime);
        return $this;
    }

    /**
     * Get category entries
     *
     * @return array
     */
    public function getCategoryDetails()
    {
        if (!$data = $this->getData(self::DETAILS)) {
            return [];
        }
        return $this->_serializer->unserialize($data);
    }

    /**
     * @param null $id
     * @return $this
     * @throws \Exception
     */
    public function collect($id = null)
    {
        if (!$this->getProfileEntity()) {
            throw new \Exception(__('Profile entity is not set.'));
        }

        $this->_behaviourUpdate = false;
        if ($this->getBehaviour() === Behaviour::APPEND) {
            $this->_behaviourUpdate = true;
        }

        $lastUpdatedAt = $this->_getLastUpdatedAt();
        $page = 1;
        $with = 'details,clients'; // Available values: details, clients, elmarCategories

        do {
            try {
                $response = $this->_api()
                    ->getSearchCategory($page, $id, $lastUpdatedAt, $with, 'item');
                if (!$response->getSize()) {
                    return $this;
                }
                $this->_saveCategoryResponseData($response);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            $page = $response->getFlag('page');
            $last = $response->getFlag('lastPageNumber');
            $page++;
        } while ($page <= $last);

        $this->_buildMagentoCategoryPath();

        $this->_response['success'] = __('Categories have been collected.');

        return $this;
    }

    /**
     * @param Collection $data
     * @param array $fields
     * @return $this
     * @throws \Exception
     */
    private function _saveCategoryResponseData(Collection $data, array $fields = [])
    {
        $this->getResource()
            ->saveCategories($this->getProfileEntity()->getProfile(), $data, $fields);
        return $this;
    }

    /**
     * @param null $categoryId
     * @return $this
     * @throws \Exception
     */
    public function import($categoryId = null)
    {
        if (!$this->getProfileEntity()) {
            throw new \Exception(__('Profile entity is not set.'));
        }

        if (!$this->getProfileEntity()->getIsActiveCategoryImport()) {
            // throw new \Exception(__('Category import is disabled.'));
        }

        if (!$rootCategory = $this->getProfileEntity()->getRootCategory()) {
            // throw new \Exception(__('Root category is not set.'));
        }

        $categoryCollection = $this->getCollection();
        if (null !== $categoryId) {
            $categoryCollection->addFieldToFilter('mage_id', (int) $categoryId);
        } else {
            $categoryCollection->addFieldToFilter('status', Status::PENDING);
        }

        if (!$categoryCollection->getSize()) {
            // throw new \Exception(__('Categories could not be found.'));
        }

        $plentyRootCategoryId = 600; // $this->getProfileEntity()->getPlentyRootCategoryId();
        $categories = [];
        foreach ($categoryCollection as $category) {
            $categoryPath = $category->getPath();
            if ($plentyRootCategoryId
                || ($plentyRootCategoryId && ($plentyRootCategoryId == $category->getParentId()))
            ) {
                $categoryPath = explode('/', $category->getPath());
                array_shift($categoryPath);
                $categoryPath = implode('/', $categoryPath);
            }

            if (!$categoryPath) {
                continue;
            }

            $categoryDetails = $category->getDetails();
            array_push($categories, [
                '_root' => 'Default Category', // $rootCategory,
                '_category' => $categoryPath,
                'name' => $category->getName(),
                'description' => isset($categoryDetails[0]['description']) ? $categoryDetails[0]['description'] : '',
                'is_active' => '1',
                'include_in_menu' => '1',
                'meta_description' => isset($categoryDetails[0]['metaDescription']) ? $categoryDetails[0]['metaDescription'] : '',
                'available_sort_by' => 'position',
                'default_sort_by' => 'position',
                // 'url_key' => $categoryEntries['details'][0]['nameUrl']
            ]);
        }

        try {
            $this->_importCategories($categories);
        } catch (\Exception $e) {
            // throw new \Exception(__('Could not import categories. [Reason: %s]', $e->getMessage()));
        }

        $this->_response['success'] = __('Categories have been imported successfully.');

        return $this;
    }
}