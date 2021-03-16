<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Api;

use Plenty\Stock\Api\Data\Profile\StockImportInterface;

/**
 * Interface StockCollectManagementInterface
 * @package Plenty\Stock\Api
 */
interface StockCollectManagementInterface
{
    /**
     * @return StockImportInterface
     */
    public function getProfileEntity();

    /**
     * @param StockImportInterface $profileEntity
     * @return $this
     */
    public function setProfileEntity(StockImportInterface $profileEntity);

    /**
     * @return array
     */
    public function getResponse();

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null);

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function addResponse($data, $key = null);

    /**
     * @param int|null $variationId
     * @param int|null $warehouseId
     * @param string|null $lastUpdatedAt
     * @return mixed
     */
    public function execute(
        $variationId = null,
        $warehouseId = null,
        $lastUpdatedAt = null
    );
}