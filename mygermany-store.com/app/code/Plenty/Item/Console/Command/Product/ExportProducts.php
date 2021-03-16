<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Console\Command\Product;

use Plenty\Item\Console\Command\AbstractExportCommand;
use Plenty\Item\Console\Command\AbstractImportCommand;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\ImportExport\Model\Import;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;

/**
 * Class ExportProducts
 * @package Plenty\Item\Console\Command\Product
 */
class ExportProducts extends AbstractExportCommand
{
    protected function configure()
    {
        $this->setName('plenty_import_export:products:export')
            ->setDescription('Export Products ');
        $this->setEntityCode('catalog_product');

        parent::configure();
    }


}