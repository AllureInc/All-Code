<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Console\Command\Category;

use Plenty\Item\Console\Command\AbstractImportCommand;
use Magento\ImportExport\Model\Import;

/**
 * Class ImportCategoryMultiStoreView
 * @package Plenty\Item\Console\Command\Category
 */
class ImportCategoryMultiStoreView extends AbstractImportCommand
{
    protected function configure()
    {
        $this->setName('plenty_import_export:category:import')
            ->setDescription('Import Category');

        $this->setBehavior(Import::BEHAVIOR_APPEND);
        $this->setEntityCode('catalog_category');

        parent::configure();
    }

    /**
     * @return array
     */
    protected function getEntities()
    {
        $data[] = array(
            '_root' => 'Default Category',
            '_category' => 'TestCategory DE',
            'description' => 'Test desc',
            'is_active' => '1',
            'include_in_menu' => '1',
            'meta_description' => 'Meta Test',
            'available_sort_by' => 'position',
            'default_sort_by' => 'position',
            'is_anchor' => '1'
        );
        $data[] = array(
            '_store' => 'en',
            'name' => 'TestCategory EN',
            'description' => 'StoreViewLevel'
        );
        return $data;
    }
}



