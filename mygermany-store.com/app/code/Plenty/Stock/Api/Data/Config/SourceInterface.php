<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Api\Data\Config;

use Plenty\Core\Api\Data\Config\SourceInterface as CoreSourceInterface;
use Plenty\Core\Model\ResourceModel\Config\Source\Collection;

/**
 * Interface SourceInterface
 * @package Plenty\Stock\Api\Data\Config
 */
interface SourceInterface extends CoreSourceInterface
{
    const CONFIG_SOURCE_MAIN_WAREHOUSE_ID = 'main_warehouse_id';

    /**
     * @param null $statusId
     * @return $this
     */
    public function collectWarehouseConfigs($statusId = null);

    /**
     * @return Collection
     */
    public function getWarehouseConfigCollection(): Collection;
}