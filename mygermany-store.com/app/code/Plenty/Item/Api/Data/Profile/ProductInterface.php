<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Profile;

use Plenty\Core\Api\Data\ProfileTypeInterface;
use Magento\Framework\DataObject;

/**
 * Interface ProductExportInterface
 * @package Plenty\Item\Api\Data\Profile
 */
interface ProductInterface extends ProfileTypeInterface
{
    const TYPE = 'type';

    // Stages
    const STAGE_COLLECT_ATTRIBUTE = 'collect_attribute';
    const STAGE_COLLECT_CATEGORY = 'collect_category';
    const STAGE_COLLECT_ITEM = 'collect_item';
    const STAGE_COLLECT_VARIATION = 'collect_variation';

    // custom methods
    const CONFIG_FLAG_ONE = 'flag_one';
    const CONFIG_FLAG_TWO = 'flag_two';
    const CONFIG_IS_ACTIVE_REQUEST_LOG = 'is_active_request_log';
    const CONFIG_ITEM_COLLECT_SEARCH_CRITERIA = 'item_collect_search_filter';

    /**
     * @return $this
     * @throws \Exception
     */
    public function collectAttributes();

    /**
     * @return $this
     * @throws \Exception
     */
    public function collectCategories();

    /**
     * @return $this
     * @throws \Exception
     */
    public function collectItems();

    /**
     * @return bool
     */
    public function getIsActiveRequestLog();

    /**
     * @return string|null
     */
    public function getApiItemSearchFilters();

    /**
     * @return string|null
     */
    public function getApiVariationSearchFilters();

    /**
     * @return null|DataObject
     * @throws \Exception
     */
    public function getDefaultStoreMapping() : DataObject;

    /**
     * @return int
     */
    public function getFlagOne();

    /**
     * @return int
     */
    public function getFlagTwo();

    /**
     * @param null $store
     * @return int
     */
    public function getDefaultTaxClass($store = null);

    /**
     * @param null $store
     * @return array
     */
    public function getTaxMapping($store = null);

    /**
     * @param null $store
     * @return array
     */
    public function getPriceMapping($store = null);

    /**
     * @return string
     */
    public function getPurchasePriceMapping();

    /**
     * @param null $storeId
     * @return int
     */
    public function getMainWarehouseId($storeId = null);

    /**
     * @param null $store
     * @return string
     */
    public function getDefaultAttributeSet($store = null);

    /**
     * @return array
     */
    public function getNameMapping();

    /**
     * @param null $store
     * @return string
     */
    public function getShortDescriptionMapping($store = null);

    /**
     * @param null $store
     * @return string
     */
    public function getDescriptionMapping($store = null);

    /**
     * @return string
     */
    public function getTechnicalDataMapping();

    /**
     * @return string
     */
    public function getSupplierNameMapping();

    /**
     * @return string
     */
    public function getSupplierItemNumberMapping();

    /**
     * @return string
     */
    public function getManufacturerMapping();

    /**
     * @return string
     */
    public function getDefaultWeightUnit();

    /**
     * @return string|null
     */
    public function getItemWidthMapping();

    /**
     * @return string|null
     */
    public function getItemLengthMapping();

    /**
     * @return string|null
     */
    public function getItemHeightMapping();

    /**
     * @return array
     */
    public function getPropertyMapping();

    /**
     * @return array
     */
    public function getBarcodeMapping();

    /**
     * @return array
     */
    public function getMarketNumberMapping();

    /**
     * @return array
     */
    public function getRootCategoryMapping();

    /**
     * @return string
     */
    public function getMediaFilter();

    /**
     * @return bool
     */
    public function getIsMultiStore();

    /**
     * @return array
     */
    public function getItemCollectSearchCriteria();

    /**
     * @var array $filter
     *
     * Column-value pairs:
     * [100, 101, 102]
     *
     * @return $this
     */
    public function setItemCollectSearchCriteria(array $filter);
}
