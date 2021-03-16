<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Plugin\ImportExport\Model\Import\Adapters;

/**
 * Interface ImportAdapterFactoryInterface
 * @package Plenty\Core\Plugin\ImportExport\Model\Import\Adapters
 */
interface ImportAdapterFactoryInterface
{
    /**
     * @param array $data
     * @return \Magento\ImportExport\Model\Import\AbstractSource
     */
    public function create(array $data = []);
}