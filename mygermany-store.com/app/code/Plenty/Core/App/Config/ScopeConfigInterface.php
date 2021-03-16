<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\App\Config;

/**
 * Interface ScopeConfigInterface
 * @package Plenty\Core\App\Config
 */
interface ScopeConfigInterface
{
    /**
     * Default scope type
     */
    const SCOPE_TYPE_DEFAULT    = 'default';

    /**
     * Scope types
     */
    const SCOPE_STORES          = 'stores';
    const SCOPE_GROUPS          = 'groups';
    const SCOPE_WEBSITES        = 'websites';

    const SCOPE_STORE           = 'store';
    const SCOPE_GROUP           = 'group';
    const SCOPE_WEBSITE         = 'website';

    /**
     * Retrieve config value by path and scope.
     *
     * @param (int) $profileId The ID of profile
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|string $scopeCode
     * @return mixed
     */
    public function getValue($profileId, $path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);

    /**
     * Retrieve config flag by path and scope
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|string $scopeCode
     * @return bool
     */
    public function isSetFlag($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
}
