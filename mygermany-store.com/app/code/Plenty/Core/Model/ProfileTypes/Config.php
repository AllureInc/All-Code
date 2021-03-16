<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\Model\ProfileTypes;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Config
 * @package Plenty\Core\Model\ProfileTypes
 */
class Config extends \Magento\Framework\Config\Data implements ConfigInterface
{
    /**
     * Constructor
     *
     * @param Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'profile_types_config',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }

    /**
     * Get configuration of profile type by name
     *
     * @param string $name
     * @return array
     */
    public function getType($name)
    {
        return $this->get('types/' . $name, []);
    }

    /**
     * Get configuration of all registered profile types
     *
     * @return array
     */
    public function getAll()
    {
        return $this->get('types');
    }

    /**
     * Check whether profile type is set of profiles
     *
     * @param string $typeId
     * @return bool
     */
    public function isProfileAdaptorSet($typeId)
    {
        return 'true' == $this->get('types/' . $typeId . '/custom_attributes/is_profile_set', false);
    }

    /**
     * Get composable types
     *
     * @return array
     */
    public function getComposableTypes()
    {
        return $this->get('composableTypes', []);
    }

    /**
     * Get list of profile types that comply with condition
     *
     * @param string $attributeName
     * @param string $value
     * @return array
     */
    public function filter($attributeName, $value = 'true')
    {
        $availableprofileTypes = [];
        foreach ($this->getAll() as $type) {
            if (!isset(
                $type['custom_attributes'][$attributeName]
            ) || $type['custom_attributes'][$attributeName] == $value
            ) {
                $availableprofileTypes[$type['name']] = $type['name'];
            }
        }
        return $availableprofileTypes;
    }
}
