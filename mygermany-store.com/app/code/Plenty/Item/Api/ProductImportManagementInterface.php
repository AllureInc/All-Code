<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

use Plenty\Core\Api\ProfileEntityManagementInterface;
use Plenty\Item\Api\Data\Profile\ProductImportInterface;
use Plenty\Core\Api\Data\Profile\HistoryInterface;

/**
 * Interface ProductImportManagementInterface
 * @package Plenty\Item\Api
 */
interface ProductImportManagementInterface extends ProfileEntityManagementInterface
{
    const ROOT_CATEGORY_MAPPING = 'root_category_mapping';
    const DEFAULT_STORE_ID = 'default_store_id';

    /**
     * @return ProductImportInterface
     */
    public function getProfileEntity() : ProductImportInterface;

    /**
     * @param ProductImportInterface $profileEntity
     * @return $this
     */
    public function setProfileEntity(ProductImportInterface $profileEntity);

    /**
     * @return HistoryInterface
     */
    public function getProfileHistory() : HistoryInterface;

    /**
     * @param HistoryInterface $history
     * @return $this
     */
    public function setProfileHistory(HistoryInterface $history);

    /**
     * @return $this
     */
    public function execute();
}