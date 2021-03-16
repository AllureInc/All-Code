<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\App\Config;

/**
 * Interface ConfigSourceInterface
 * @package Plenty\Core\App\Config
 */
interface ConfigSourceInterface
{
    /**
     * Retrieve configuration raw data array.
     *
     * @param (int) $profileId
     * @param string $path The configuration path (e.g. section_id/group_id/field_id)
     * @return string|array Array will be returned if you use part of path (e.g. scope/scope_code/section_id)
     * ```php
     * [
     *      'group1' =>
     *      [
     *          'field1' => 'value1',
     *          'field2' => 'value2'
     *      ],
     *      'group2' =>
     *      [
     *          'field1' => 'value3'
     *      ]
     * ]
     * ```
     * And string will be returned if you use full path to field (e.g. scope/scope_code/section_id/group_id/field_id)
     * E.g. 'value1'
     */
    public function get($path = '');

    /**
     * @return int
     */
    public function getProfileId();

    /**
     * @param $id
     * @return int
     */
    public function setProfileId($id);
}