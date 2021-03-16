<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import;
use Plenty\Item\Model\Import\Category;

/**
 * Interface CategoryInterface
 * @package Plenty\Item\Api\Data\Import
 */
interface CategoryInterface
{
    const ENTITY_ID         = 'entity_id';
    const PROFILE_ID        = 'profile_id';
    const CATEGORY_ID       = 'category_id';
    const MAGE_ID           = 'mage_id';
    const PARENT_ID         = 'parent_id';
    const LEVEL             = 'level';
    const HAS_CHILDREN      = 'has_children';
    const TYPE              = 'type';
    const LINK_LIST         = 'link_list';
    const RIGHT             = 'right';
    const SITEMAP           = 'sitemap';
    const NAME              = 'name';
    const PATH              = 'path';
    const ORIGINAL_PATH     = 'original_path';
    const PREVIEW_URL       = 'preview_url';
    const STATUS            = 'status';
    const DETAILS           = 'details';
    const MESSAGE           = 'message';
    const CREATED_AT        = 'created_at';
    const UPDATED_AT        = 'updated_at';
    const UPDATED_BY        = 'updated_by';
    const COLLECTED_AT      = 'collected_at';
    const PROCESSED_AT      = 'processed_at';

    /**
     * @return int
     */
    public function getProfileId();

    /**
     * @param $profileId
     * @return Category
     */
    public function setProfileId($profileId);

    /**
     * @return null|int
     */
    public function getCategoryId();

    /**
     * @param $categoryId
     * @return Category
     */
    public function setCategoryId($categoryId);

    /**
     * @return null|int
     */
    public function getMageId();

    /**
     * @param $mageId
     * @return $this
     */
    public function setMageId($mageId);

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
     * @return null|int
     */
    public function getLevel();

    /**
     * @return bool
     */
    public function getHasChildren();

    /**
     * @return null|string
     */
    public function getType();

    /**
     * @return null|string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return null|string
     */
    public function getPath();

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path);

    /**
     * @return null|string
     */
    public function getOriginalPath();

    /**
     * @param string $originalPath
     * @return $this
     */
    public function setOriginalPath($originalPath);

    /**
     * @return null|string
     */
    public function getPreviewUrl();

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
    public function getDetails();

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
    public function getUpdatedBy();

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
    public function getCollectedAt();

    /**
     * @param $dateTime
     * @return $this
     */
    public function setCollectedAt($dateTime);

    /**
     * @return null|string
     */
    public function getProcessedAt();

    /**
     * @param $dateTime
     * @return $this
     */
    public function setProcessedAt($dateTime);
}