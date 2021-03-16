<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile\Config;

/**
 * Class BackendFactory
 * @package Plenty\Core\Model\Profile\Config
 */
class BackendFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * BackendFactory constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectmanager)
    {
        $this->_objectManager = $objectmanager;
    }

    /**
     * Create backend model by name
     *
     * @param string $modelName
     * @param array $arguments The object arguments
     * @return \Magento\Framework\App\Config\ValueInterface
     * @throws \InvalidArgumentException
     */
    public function create($modelName, array $arguments = [])
    {
        $model = $this->_objectManager->create($modelName, $arguments);
        if (!$model instanceof \Plenty\Core\App\Config\ValueInterface) {
            throw new \InvalidArgumentException('Invalid profile config field backend model: ' . $modelName);
        }
        return $model;
    }
}