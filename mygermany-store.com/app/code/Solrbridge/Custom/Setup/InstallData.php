<?php
/**
 * This file execute: when there is no record in the setup_module table for the module
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Custom\Setup;

use Solrbridge\Custom\Model\Sample;
use Solrbridge\Custom\Model\SampleFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $sampleFactory;

    public function __construct(SampleFactory $sampleFactory)
    {
        $this->sampleFactory = $sampleFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $sampleData = [
            'title' => 'Sample 1',
            'content' => 'Sample content 1',
        ];

        $this->createSample()->setData($sampleData)->save();
    }

    public function createSample()
    {
        return $this->sampleFactory->create();
    }
}