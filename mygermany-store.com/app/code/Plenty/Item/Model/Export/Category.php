<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Export;

use Magento\Framework\DataObject\IdentityInterface;
use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Api\Data\Export\CategoryInterface;
use Plenty\Item\Model\ResourceModel;

/**
 * Class Category
 * @package Plenty\Item\Model\Export
 *
 * @method ResourceModel\Export\Category getResource()
 * @method ResourceModel\Export\Category\Collection getCollection()
 */
class Category extends ImportExportAbstract
    implements CategoryInterface, IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_category';
    protected $_cacheTag        = 'plenty_item_category';
    protected $_eventPrefix     = 'plenty_item_category';

    protected $_exportResponse;

    protected function _construct()
    {
        $this->_init(ResourceModel\Export\Category::class);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @return int
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @param int $profileId
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
     * @return null|int
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
     * @return null|string
     */
    public function getSystemPath()
    {
        return $this->getData(self::SYSTEM_PATH);
    }

    /**
     * @param $path
     * @return $this
     */
    public function setSystemPath($path)
    {
        $this->setData(self::SYSTEM_PATH, $path);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCategoryPath()
    {
        return $this->getData(self::CATEGORY_PATH);
    }

    /**
     * @param $path
     * @return $this
     */
    public function setCategoryPath($path)
    {
        $this->setData(self::CATEGORY_PATH, $path);
        return $this;
    }

    /**
     * @return null|int
     */
    public function getCategoryLevel()
    {
        return $this->getData(self::CATEGORY_LEVEL);
    }

    /**
     * @param $level
     * @return $this
     */
    public function setCategoryLevel($level)
    {
        $this->setData(self::CATEGORY_LEVEL, $level);
        return $this;
    }

    /**
     * @return null|int
     */
    public function getChildrenCount()
    {
        return $this->getData(self::CHILDREN_COUNT);
    }

    /**
     * @return null|string
     */
    public function getCategoryName()
    {
        return $this->getData(self::CATEGORY_NAME);
    }

    /**
     * @param $name
     * @return $this
     */
    public function setCategoryName($name)
    {
        $this->setData(self::CATEGORY_NAME, $name);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getCategoryEntries()
    {
        return $this->hasData(self::CATEGORY_ENTRIES)
            ? $this->_serializer->unserialize($this->getData(self::CATEGORY_ENTRIES))
            : [];
    }

    /**
     * @param array $entries
     * @return $this
     */
    public function setCategoryEntries(array $entries)
    {
        $this->setData(self::CATEGORY_ENTRIES, $this->_serializer->serialize($entries));
        return $this;
    }

    /**
     * @return null|int
     */
    public function getPlentyCategoryId()
    {
        return $this->getData(self::PLENTY_CATEGORY_ID);
    }

    /**
     * @param $categoryId
     * @return $this
     */
    public function setPlentyCategoryId($categoryId)
    {
        $this->setData(self::PLENTY_CATEGORY_ID, $categoryId);
        return $this;
    }

    /**
     * @return null|int
     */
    public function getPlentyCategoryParentId()
    {
        return $this->getData(self::PLENTY_CATEGORY_PARENT_ID);
    }

    /**
     * @param $parentId
     * @return $this
     */
    public function setPlentyCategoryParentId($parentId)
    {
        $this->setData(self::PLENTY_CATEGORY_PARENT_ID, $parentId);
        return $this;
    }

    /**
     * @return null|int
     */
    public function getPlentyCategoryLevel()
    {
        return $this->getData(self::PLENTY_CATEGORY_LEVEL);
    }

    /**
     * @param $level
     * @return $this
     */
    public function setPlentyCategoryLevel($level)
    {
        $this->setData(self::PLENTY_CATEGORY_LEVEL, $level);
        return $this;
    }

    /**
     * @return bool
     */
    public function getPlentyCategoryHasChildren()
    {
        return $this->getData(self::PLENTY_CATEGORY_HAS_CHILDREN);
    }

    /**
     * @return null|string
     */
    public function getPlentyCategoryType()
    {
        return $this->getData(self::PLENTY_CATEGORY_TYPE);
    }

    /**
     * @param $type
     * @return $this
     */
    public function setPlentyCategoryType($type)
    {
        $this->setData(self::PLENTY_CATEGORY_TYPE, $type);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPlentyCategoryName()
    {
        return $this->getData(self::PLENTY_CATEGORY_NAME);
    }

    /**
     * @param $name
     * @return $this
     */
    public function setPlentyCategoryName($name)
    {
        $this->setData(self::PLENTY_CATEGORY_NAME, $name);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPlentyCategoryPath()
    {
        return $this->getData(self::PLENTY_CATEGORY_PATH);
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPlentyCategoryPath($path)
    {
        $this->setData(self::PLENTY_CATEGORY_PATH, $path);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlentyCategoryEntries()
    {
        return $this->getData(self::PLENTY_CATEGORY_ENTRIES);
    }

    /**
     * @param array $entries
     * @return $this
     */
    public function setPlentyCategoryEntries(array $entries)
    {
        $this->setData(self::PLENTY_CATEGORY_ENTRIES, $this->_serializer->serialize($entries));
        return $this;
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
}