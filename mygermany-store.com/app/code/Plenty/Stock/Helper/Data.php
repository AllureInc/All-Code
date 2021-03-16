<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Stock\Helper;

use Magento\Framework\Exception\NoSuchEntityException;
use Plenty\Stock\Helper\Rest\RouteInterface;
use Plenty\Core\Helper\Data as CoreHelper;

/**
 * Class Data
 * @package Plenty\Core\Helper
 */
class Data extends CoreHelper implements RouteInterface
{
    // GENERAL
    const STOCK_CONFIG_UPDATE_ON_ADDTOCART          = 'plenty_stock/general/update_on_addtocart';
    const STOCK_CONFIG_UPDATE_ON_SAVE               = 'plenty_stock/general/update_on_save';

    // DEVELOPER
    const STOCK_CONFIG_DEV_LOG_DIRECTORY             = 'log/plenty/';
    const STOCK_CONFIG_DEV_DEBUG                     = 'plenty_stock/dev/debug_enabled';
    const STOCK_CONFIG_DEV_DEBUG_DIRECTORY_NAME      = 'plenty_stock/dev/debug_directory';
    const STOCK_CONFIG_DEV_DEBUG_LEVEL               = 'plenty_stock/dev/debug_level';

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getUpdateOnAddtocart()
    {
        return (bool) $this->_getConfig(self::STOCK_CONFIG_UPDATE_ON_ADDTOCART);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getUpdateOnSave()
    {
        return (bool) $this->_getConfig(self::STOCK_CONFIG_UPDATE_ON_SAVE);
    }

    /**
     * @param $warehouseId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getListStockPerWarehouseUrl($warehouseId)
    {
        return $this->getAppUrl(self::STOCK_MANAGEMENT_WAREHOUSE_URL.'/'.$warehouseId.'/stock');
    }

    /**
     * @param null $warehouseId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getListStockUrl($warehouseId = null)
    {
        if (null === $warehouseId) {
            return $this->getAppUrl(self::STOCK_MANAGEMENT_URL);
        }
        return $this->getListStockPerWarehouseUrl($warehouseId);
    }

    /**
     * @param $variationId
     * @param $warehouseId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getListStockPerItemUrl($variationId, $warehouseId)
    {
        return $this->getAppUrl(self::STOCK_MANAGEMENT_WAREHOUSE_URL.'/'.$warehouseId.'/stock?variationId='.$variationId);
    }

    /**
     * @param $warehouseId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getStockCorrectionByWarehouseIdUrl($warehouseId)
    {
        return $this->getAppUrl(self::STOCK_MANAGEMENT_WAREHOUSE_URL.'/'.$warehouseId.'/stock/correction');
    }
}
