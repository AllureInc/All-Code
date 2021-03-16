<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Api\Data\Profile;

use Magento\Framework\Api\SearchCriteriaInterface;
use Plenty\Core\Api\Data\ProfileTypeInterface;
use Magento\Framework\DataObject;

/**
 * Interface OrderExportInterface
 * @package Plenty\Order\Api\Data\Profile
 */
interface OrderExportInterface extends ProfileTypeInterface
{
    const TYPE = 'type';

    // Stages
    const STAGE_COLLECT_ORDER = 'collect_order';
    const STAGE_EXPORT_ORDER = 'export_order';

    const DEFAULT_EXPORT_BATCH_SIZE = 10;

    // Export config scope
    const XML_PATH_IS_ACTIVE_ORDER_EXPORT = 'order_export/export_config/is_active_order_export';
    const XML_PATH_EXPORT_BATCH_SIZE = 'order_export/export_config/export_batch_size';

    // Api config scope
    const XML_PATH_API_BEHAVIOUR = 'order_export/api_config/api_behaviour';
    const XML_PATH_API_COLLECTION_SIZE = 'order_export/api_config/api_collection_size';
    const XML_PATH_API_ORDER_SEARCH_FILTERS = 'order_export/api_config/order_search_filters';

    // Store config scope
    const XML_PATH_STORE_MAPPING = 'order_export/store_config/store_mapping';
    const XML_PATH_ORDER_REFERER_ID = 'order_export/store_config/order_referer_id';

    // Status config scope
    const XML_PATH_STATUS_FILTER = 'order_export/status_config/status_filter';
    const XML_PATH_STATUS_MAPPING = 'order_export/status_config/status_mapping';

    // Payment config scope
    const XML_PATH_ENABLE_PAYMENT_EXPORT = 'order_export/payment_config/enable_payment_export';
    const XML_PATH_PAYMENT_METHOD_MAPPING = 'order_export/payment_config/payment_mapping';

    // Shipping config scope
    const XML_PATH_ENABLE_SHIPPING_EXPORT = 'order_export/shipping_config/enable_shipping_export';
    const XML_PATH_DEFAULT_SHIPPING_PROFILE = 'order_export/shipping_config/default_shipping_profile';
    const XML_PATH_SHIPPING_MAPPING = 'order_export/shipping_config/shipping_mapping';

    // Customer config scope
    const XML_PATH_ENABLE_CUSTOMER_EXPORT = 'order_export/customer_config/enable_customer_export';

    // WAREHOUSE CONFIG
    const XML_PATH_MAIN_WAREHOUSE_ID = 'order_export/warehouse_config/main_warehouse_id';

    const CONFIG_EXPORT_SEARCH_CRITERIA = 'export_search_criteria';

    /**
     * @return mixed
     */
    public function exportOrders();

    /**
     * @return mixed
     */
    public function getIsActiveOrderExport();

    /**
     * @return int
     */
    public function getExportBatchSize();

    /**
     * @return string|null
     */
    public function getApiOrderSearchFilters();

    /**
     * @return null|DataObject
     * @throws \Exception
     */
    public function getDefaultStoreMapping() : DataObject;

    /**
     * @param null $storeId
     * @return int|null
     */
    public function getOrderReferrerId($storeId = null);

    /**
     * @return array
     */
    public function getOrderStatusFilter();

    /**
     * @return array
     */
    public function getOrderStatusMapping();

    /**
     * @param $salesOrderStatus
     * @return int|null
     */
    public function getPlentyStatusIdByOrderStatusCode($salesOrderStatus);

    /**
     * @return bool
     */
    public function getIsActivePaymentExport();

    /**
     * @return array
     */
    public function getPaymentMapping();

    /**
     * @param $paymentMethodCode
     * @return int|null
     */
    public function getMopIdByOrderPaymentMethodCode($paymentMethodCode);

    /**
     * @return bool
     */
    public function getIsActiveExportShipping();

    /**
     * @param null $store
     * @return int|null
     */
    public function getDefaultShippingProfileId($store = null);

    /**
     * @return array
     */
    public function getShippingMapping();

    /**
     * @param $method
     * @param null $store
     * @return int|null
     */
    public function getShippingProfileId($method, $store = null);

    /**
     * @return bool
     */
    public function getIsActiveCustomerExport();

    /**
     * @param null $storeId
     * @return int
     */
    public function getMainWarehouseId($storeId = null);

    /**
     * @return bool
     */
    public function getIsMultiStore();

    /**
     * @return SearchCriteriaInterface|null
     */
    public function getImportSearchCriteria();

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setImportSearchCriteria(SearchCriteriaInterface $searchCriteria);
}
