<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Rest;

/**
 * Interface ItemInterface
 * @package Plenty\Item\Rest
 */
interface ConfigInterface
{
    /**
     * @return mixed
     */
    public function getSearchWebStoreConfigs();

    /**
     * @param int $page
     * @param null $vatId
     * @param null $with
     * @param array $columns
     * @return mixed
     */
    public function getSearchVatConfigs(
        $page = 1,
        $vatId = null,
        $with = null,
        $columns = []
    );

    /**
     * @param null $warehouseId
     * @return mixed
     */
    public function getSearchWarehouseConfigs($warehouseId = null);
}