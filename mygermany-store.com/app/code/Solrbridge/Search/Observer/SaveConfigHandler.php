<?php
/**
 * Copyright © 2011-2017 Solrbridge, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveConfigHandler implements ObserverInterface
{
    public function __construct()
    {
        //Construct logic here
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Solrbridge\Search\Helper\Utility::clearSolrBridgeStaticConfigFiles();
    }
}
