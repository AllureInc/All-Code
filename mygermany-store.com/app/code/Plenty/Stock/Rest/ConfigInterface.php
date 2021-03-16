<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Rest;

/**
 * Interface ConfigInterface
 * @package Plenty\Stock\Rest
 */
interface ConfigInterface
{
    /**
     * @param null $warehouseId
     * @return mixed
     */
    public function getSearchWarehouseConfigs(
        $warehouseId = null
    );
}