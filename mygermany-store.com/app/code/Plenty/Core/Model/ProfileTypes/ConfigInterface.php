<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\ProfileTypes;

/**
 * Interface ConfigInterface
 * @package Plenty\Core\Model\ProfileTypes
 */
interface ConfigInterface
{
    /**
     * Get configuration of profile type by name
     *
     * @param string $name
     * @return array
     */
    public function getType($name);

    /**
     * Get configuration of all registered product types
     *
     * @return array
     */
    public function getAll();

    /**
     * Check whether product type is set of products
     *
     * @param string $typeId
     * @return bool
     */
    public function isProfileAdaptorSet($typeId);

    /**
     * Get composable types
     *
     * @return array
     */
    public function getComposableTypes();

    /**
     * Get list of product types that comply with condition
     *
     * @param string $customAttributeName
     * @param string $value
     * @return array
     */
    public function filter($customAttributeName, $value = 'true');
}
