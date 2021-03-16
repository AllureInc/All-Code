<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Plugin\ImportExport\Model\Import\Adapters;

/**
 * Class ArrayAdapterFactory
 * @package Plenty\Core\Plugin\ImportExport\Model\Import\Adapters
 */
class ArrayAdapterFactory implements ImportAdapterFactoryInterface
{
    protected $_objectManager = null;

    protected $_instanceName = null;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = 'Plenty\Core\Plugin\ImportExport\Model\Import\Adapters\ArrayAdapter'
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * @param array $data
     * @return \Plenty\Core\Plugin\ImportExport\Model\Import\Adapters\ArrayAdapterFactory
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}