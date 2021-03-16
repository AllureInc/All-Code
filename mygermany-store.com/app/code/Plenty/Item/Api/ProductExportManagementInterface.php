<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

use Plenty\Core\Api\ProfileEntityManagementInterface;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Core\Api\Data\Profile\HistoryInterface;

/**
 * Interface ProductImportManagementInterface
 * @package Plenty\Item\Api
 */
interface ProductExportManagementInterface extends ProfileEntityManagementInterface
{
    const ROOT_CATEGORY_MAPPING = 'root_category_mapping';
    const DEFAULT_STORE_ID = 'default_store_id';

    const CONFIGURABLE_VARIATIONS = 'configurable_variations';
    const IS_CONFIGURABLE_VARIATION = 'is_configurable_variation';

    const IS_NEW_EXPORT = '_is_new_export';
    const CONFIG_ATTRIBUTES = '_config_attributes';
    const REQUEST_CATEGORY = '_request_category';
    const STOCK_ITEM_OBJ = '_stock_item_obj';

    /**
     * @return ProductExportInterface
     */
    public function getProfileEntity() : ProductExportInterface;

    /**
     * @param ProductExportInterface $profileEntity
     * @return $this
     */
    public function setProfileEntity(ProductExportInterface $profileEntity);

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