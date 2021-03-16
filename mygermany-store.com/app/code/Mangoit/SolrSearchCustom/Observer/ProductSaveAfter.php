<?php
/**
 * Copyright © 2011-2017 Solrbridge, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\SolrSearchCustom\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{
    protected $indexCollectionFactory;
    protected $helper;
    public function __construct(
        \Solrbridge\Search\Model\ResourceModel\Index\CollectionFactory $indexCollectionFactory,
        \Solrbridge\Search\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $_productloader
    ) {
        $this->indexCollectionFactory = $indexCollectionFactory;
        $this->helper = $helper;
        $this->productloader = $_productloader;
    }
    
    protected function allowUpdateSolrIndexAfterProductSave()
    {
        return (boolean) $this->helper->getGeneralSetting('indexer/update_index_after_product_save');
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->allowUpdateSolrIndexAfterProductSave()) {
            try {
                $product = $observer->getEvent()->getProduct();
                //Check to see if this product belong to which stores

                $productLoaderObj = $this->productloader->create();
                $mageProduct = $productLoaderObj->load($product->getMageproductId());
                $storeIds = $mageProduct->getStoreIds();
        
                $indexCollection = $this->indexCollectionFactory->create();
                $indexCollection->addFieldToFilter('store_id', array('in' => $storeIds));
                if ($indexCollection->getSize() > 0) {
                    foreach ($indexCollection as $index) {
                        $indexer = $index->getDoctypeHandlerModel()->getIndexer();
                        $request = array(
                            'action' => $indexer::ACTION_RE_INDEX,
                            'data_ids' => array($product->getMageproductId())
                        );
                        $indexer->start($request);
                        $indexer->execute();
                        $indexer->commit();
                    }
                }
            } catch (\Exception $e) {
                //Log error here
            }
        }
    }
}
