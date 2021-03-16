<?php
/**
 * Product attribute edit form observer
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Observer\Category\View;

use Magento\Config\Model\Config\Source;
use Magento\Framework\Module\Manager;
use Magento\Framework\Event\ObserverInterface;
use Solrbridge\Search\Helper\Utility;

use Solrbridge\Search\Helper\System;

class LayoutLoadBefore implements ObserverInterface
{
    protected $request;
    
    protected $helper;
    
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Solrbridge\Search\Helper\Data $helper
    ) {
        $this->request = $request;
        $this->helper = $helper;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isReplaceCategoryLayerNav = (boolean) $this->helper->getLayerNavSetting('catalog_layer_nav/replace');
        if ($isReplaceCategoryLayerNav) {
            if ('catalog_category_view' == $this->request->getFullActionName()) {
                System::getRegistry()->register('solrbridge_search_catalog_category_view', 1);
                //Logic the replace Catalog Layer Navigation with Solrbridge Navigation
                $observer->getEvent()->getLayout()->getUpdate()->addHandle('solrbridge_search_catalog_category_view');
            }
        }
    }
}
