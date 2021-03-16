<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Api\Data\Config;

/**
 * Interface SourceInterface
 * @package Plenty\Core\Api\Data\Config
 */
interface SourceInterface
{
    const CONFIG_SOURCE_WEB_STORE           = 'web_store';
    const CONFIG_SOURCE_VAT                 = 'vat';
    const CONFIG_SOURCE_WAREHOUSE           = 'stock_warehouse';

    const ENTITY_ID                         = 'entity_id';
    const ENTRY_ID                          = 'entry_id';
    const CONFIG_SOURCE                     = 'config_source';
    const CONFIG_ENTRIES                    = 'config_entries';
    const CREATED_AT                        = 'created_at';
    const UPDATED_AT                        = 'updated_at';
    const COLLECTED_AT                      = 'collected_at';

    public function getConfigSource();

    public function getConfigEntries();

    public function getCreatedAt();

    public function getUpdatedAt();
}