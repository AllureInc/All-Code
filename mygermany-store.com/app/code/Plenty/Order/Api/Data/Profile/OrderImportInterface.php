<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Api\Data\Profile;

use Plenty\Core\Api\Data\ProfileTypeInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;


/**
 * Interface OrderImportInterface
 * @package Plenty\Order\Api\Data\Profile
 */
interface OrderImportInterface extends ProfileTypeInterface
{
    const TYPE                                  = 'type';

    // Stages
    const STAGE_COLLECT_ORDER                   = 'collect_order';
    const STAGE_IMPORT_ORDER                    = 'import_order';

    const DEFAULT_IMPORT_BATCH_SIZE             = 10;

    // Import config scope
    const XML_PATH_ENABLE_ORDER_IMPORT          = 'import_order/import_config/enable_import_order';
    const XML_PATH_IMPORT_BATCH_SIZE            = 'import_order/import_config/import_batch_size';

    // Api config scope
    const XML_PATH_API_BEHAVIOUR                = 'import_order/api_config/api_behaviour';
    const XML_PATH_API_COLLECTION_SIZE          = 'import_order/api_config/api_collection_size';
    const XML_PATH_API_ORDER_SEARCH_FILTERS     = 'import_order/api_config/order_search_filters';

    // Store config scope
    const XML_PATH_STORE_MAPPING                = 'import_order/store_config/store_mapping';
    const XML_PATH_ORDER_REFERER_ID             = 'import_order/store_config/order_referer_id';

    // Status config scope
    const XML_PATH_STATUS_FILTER                = 'import_order/status_config/status_filter';
    const XML_PATH_STATUS_MAPPING               = 'import_order/status_config/status_mapping';

    // Payment config scope
    const XML_PATH_ENABLE_PAYMENT_IMPORT        = 'import_order/payment_config/enable_payment_import';
    const XML_PATH_PAYMENT_METHOD_MAPPING       = 'import_order/payment_config/payment_mapping';

    // Shipping config scope
    const XML_PATH_ENABLE_SHIPPING_IMPORT       = 'import_order/shipping_config/enable_shipping_import';
    const XML_PATH_DEFAULT_SHIPPING_PROFILE     = 'import_order/shipping_config/default_shipping_profile';
    const XML_PATH_SHIPPING_MAPPING             = 'import_order/shipping_config/shipping_mapping';

    // Customer config scope
    const XML_PATH_ENABLE_CUSTOMER_IMPORT       = 'import_order/customer_config/enable_customer_import';

    // WAREHOUSE CONFIG
    const XML_PATH_MAIN_WAREHOUSE_ID            = 'import_order/warehouse_config/main_warehouse_id';

    /**
     * @return mixed
     */
    public function importOrders();

}
