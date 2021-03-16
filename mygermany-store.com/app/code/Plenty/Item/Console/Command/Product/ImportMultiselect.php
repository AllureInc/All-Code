<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Console\Command\Product;

use Plenty\Item\Console\Command\AbstractImportCommand;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\ImportExport\Model\Import;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;

/**
 * Class ImportMultiselect
 * @package Plenty\Item\Console\Command\Product
 */
class ImportMultiselect extends AbstractImportCommand
{


    protected function configure()
    {
        $this->setName('plenty_import_export:products:importmultiselect')
            ->setDescription('Import Simple Products with Multiselect ');

        $this->setBehavior(Import::BEHAVIOR_ADD_UPDATE);
        $this->setEntityCode('catalog_product');

        parent::configure();
    }

    /**
     * @return array
     */
    protected function getEntities()
    {

        $data = [];
        for ($i = 1; $i <= 1; $i++) {
            $data[] = array(
                'sku' => 'multiselect-' . $i,
                'attribute_set_code' => 'Default',
                'product_type' => 'simple',
                'product_websites' => 'base',
                'name' => 'multiselect Test Product ' . $i,
                'price' => '14.0000',
                'ean' => "1234",
                'multiselect' => 'Test|Test3'
            );
        }
        return $data;
    }
}