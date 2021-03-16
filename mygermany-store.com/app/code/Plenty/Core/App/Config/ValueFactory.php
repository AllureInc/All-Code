<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\App\Config;

/**
 * Class ValueFactory
 * @package Plenty\Core\App\Config
 */
class ValueFactory
{
    /**
     * Object manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface|null
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * ValueFactory constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = ValueInterface::class
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param null $instanceName
     * @param array $data
     * @return mixed
     */
    public function create($instanceName = null, array $data = [])
    {
        if (null === $instanceName) {
            $instanceName = ValueInterface::class;
        }
        $model = $this->_objectManager->create($instanceName, $data);
        if (!$model instanceof ValueInterface) {
            throw new \InvalidArgumentException('Invalid config field model: ' . $this->_instanceName);
        }
        return $model;
    }
}