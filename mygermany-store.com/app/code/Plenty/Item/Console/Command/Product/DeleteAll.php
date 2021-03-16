<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Console\Command\Product;

use Plenty\Item\Console\Command\AbstractImportCommand;
use Magento\ImportExport\Model\Import;

class DeleteAll extends AbstractImportCommand
{
    protected function configure()
    {
        $this->setName('plenty_importexport:products:deleteall')
            ->setDescription('Delete Products ');

        $this->setBehavior(Import::BEHAVIOR_DELETE);
        $this->setEntityCode('catalog_product');

        parent::configure();
    }

    /**
     * @return array
     */
    protected function getEntities()
    {

        // bin/magento plenty_importexport:products:deleteall
        $productFactory = $this->objectManager->create('Magento\Catalog\Model\ProductFactory');
        $productCollection  = $productFactory->create()->getCollection();
        $data = [];
        foreach($productCollection as $product) {
            $data[] = array(
                'sku' => $product->getSku()
            );
        }
        return $data;
    }
}