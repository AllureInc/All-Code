<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Custom\Block\Sample;

use Magento\Framework\View\Element\Template;
use Solrbridge\Custom\Model\ResourceModel\Sample\Collection as SampleItemCollection;
use Magento\Store\Model\ScopeInterface;

class ItemList extends Template
{

    /**
     * @var Solrbridge\Custom\Model\ResourceModel\Sample\CollectionFactory
     */
    protected $sampleCollectionFactory;

    protected $sampleItemCollection;

    /**
     * ItemList constructor.
     * @param Template\Context $context
     * @param \Solrbridge\Custom\Model\ResourceModel\Sample\CollectionFactory $collectionFactory
    * @param \Solrbridge\Solr\Model\Core $solrCore
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Solrbridge\Custom\Model\ResourceModel\Sample\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        $this->sampleCollectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    public function getItemList()
    {
        $this->solrCore->getCoreList();
        die(__FILE__.':'.__LINE__);
        if(null === $this->sampleItemCollection)
        {
            $this->sampleItemCollection = $this->sampleCollectionFactory->create();
        }
        return $this->sampleItemCollection;
    }
}