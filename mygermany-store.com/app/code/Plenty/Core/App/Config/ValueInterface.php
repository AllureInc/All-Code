<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\App\Config;

/**
 * Interface ValueInterface
 * @package Plenty\Core\App\Config
 */
interface ValueInterface
{
    /**
     * Table name
     *
     * @deprecated since it is not used
     */
    const ENTITY = 'plenty_core_profile_config';

    /**
     * Check if config data value was changed
     *
     * @todo this method should be make as protected
     * @return bool
     */
    public function isValueChanged();

    /**
     * Get old value from existing config
     *
     * @return string
     */
    public function getOldValue();

    /**
     * Get value by key for new user data from <section>/groups/<group>/fields/<field>
     *
     * @param string $key
     * @return string
     */
    public function getFieldsetDataValue($key);
}
