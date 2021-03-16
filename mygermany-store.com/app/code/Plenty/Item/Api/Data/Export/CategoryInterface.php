<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Export;

/**
 * Interface CategoryInterface
 * @package Plenty\Item\Api\Data\Export
 */
interface CategoryInterface
{
    const ENTITY_ID                     = 'entity_id';
    const PROFILE_ID                    = 'profile_id';
    const CATEGORY_ID                   = 'category_id';
    const PARENT_ID                     = 'parent_id';
    const SYSTEM_PATH                   = 'system_path';
    const CATEGORY_PATH                 = 'category_path';
    const CATEGORY_LEVEL                = 'category_level';
    const CHILDREN_COUNT                = 'children_count';
    const CATEGORY_NAME                 = 'category_name';
    const CATEGORY_ENTRIES              = 'category_entries';
    const PLENTY_CATEGORY_ID            = 'plenty_category_id';
    const PLENTY_CATEGORY_PARENT_ID     = 'plenty_category_parent_id';
    const PLENTY_CATEGORY_LEVEL         = 'plenty_category_level';
    const PLENTY_CATEGORY_HAS_CHILDREN  = 'plenty_category_has_children';
    const PLENTY_CATEGORY_TYPE          = 'plenty_category_type';
    const PLENTY_CATEGORY_NAME          = 'plenty_category_name';
    const PLENTY_CATEGORY_PATH          = 'plenty_category_path';
    const PLENTY_CATEGORY_ENTRIES       = 'plenty_category_entries';
    const STATUS                        = 'status';
    const MESSAGE                       = 'message';
    const CREATED_AT                    = 'created_at';
    const UPDATED_AT                    = 'updated_at';
    const PROCESSED_AT                  = 'processed_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getProfileId();

    /**
     * @param int $profileId
     * @return $this
     */
    public function setProfileId($profileId);

    /**
     * @return null|int
     */
    public function getCategoryId();

    /**
     * @param $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId);

    /**
     * @return null|int
     */
    public function getParentId();

    /**
     * @param $parentId
     * @return $this
     */
    public function setParentId($parentId);

    /**
     * @return null|string
     */
    public function getSystemPath();

    /**
     * @param $path
     * @return $this
     */
    public function setSystemPath($path);

    /**
     * @return null|string
     */
    public function getCategoryPath();

    /**
     * @param $path
     * @return $this
     */
    public function setCategoryPath($path);

    /**
     * @return null|int
     */
    public function getCategoryLevel();

    /**
     * @param $level
     * @return $this
     */
    public function setCategoryLevel($level);

    /**
     * @return null|int
     */
    public function getChildrenCount();

    /**
     * @return null|string
     */
    public function getCategoryName();

    /**
     * @param $name
     * @return $this
     */
    public function setCategoryName($name);

    /**
     * @return array|null
     */
    public function getCategoryEntries();

    /**
     * @param array $entries
     * @return $this
     */
    public function setCategoryEntries(array $entries);

    /**
     * @return null|int
     */
    public function getPlentyCategoryId();

    /**
     * @param $categoryId
     * @return $this
     */
    public function setPlentyCategoryId($categoryId);

    /**
     * @return null|int
     */
    public function getPlentyCategoryParentId();

    /**
     * @param $parentId
     * @return $this
     */
    public function setPlentyCategoryParentId($parentId);

    /**
     * @return null|int
     */
    public function getPlentyCategoryLevel();

    /**
     * @param $level
     * @return $this
     */
    public function setPlentyCategoryLevel($level);

    /**
     * @return bool
     */
    public function getPlentyCategoryHasChildren();

    /**
     * @return null|string
     */
    public function getPlentyCategoryType();

    /**
     * @param $type
     * @return $this
     */
    public function setPlentyCategoryType($type);

    /**
     * @return null|string
     */
    public function getPlentyCategoryName();

    /**
     * @param $name
     * @return $this
     */
    public function setPlentyCategoryName($name);

    /**
     * @return null|string
     */
    public function getPlentyCategoryPath();

    /**
     * @param $path
     * @return $this
     */
    public function setPlentyCategoryPath($path);

    /**
     * @return null|string
     */
    public function getPlentyCategoryEntries();

    /**
     * @return null|string
     */
    public function getStatus();

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return null|string
     */
    public function getMessage();

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return null|string
     */
    public function getCreatedAt();

    /**
     * @param $dateTime
     * @return $this
     */
    public function setCreatedAt($dateTime);

    /**
     * @return null|string
     */
    public function getUpdatedAt();

    /**
     * @param $dateTime
     * @return $this
     */
    public function setUpdatedAt($dateTime);

    /**
     * @return null|string
     */
    public function getProcessedAt();
}