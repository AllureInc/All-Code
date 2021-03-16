<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Rest\Response;

use Magento\Framework\Data\Collection;

/**
 * Interface ConfigDataInterface
 * @package Plenty\Core\Rest\Response
 */
interface ConfigDataInterface
{
    const ID                            = 'id';
    const STATUS_ID                     = 'statusId';
    const CREATED_AT                    = 'createdAt';
    const UPDATED_AT                    = 'updatedAt';
    const CONFIG_ENTRIES                = 'entries';

    /**
     * @param array $response
     * @param $configSource
     * @return Collection
     */
    public function buildResponse(array $response, $configSource): Collection;
}