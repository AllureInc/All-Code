<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Marketplace\Ui\Component\Listing\Columns;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;

/**
 * Class Options
 */
class Filtershipto implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $options[] = array('label'=>'Drop Shipment','value'=>'dropship_dropship');
        $options[] = array('label'=>'myGermany Warehouse','value'=>'warehouse_warehouse');
        // $this->collectionFactory->create();
        // echo "<pre>";
        // print_r($this->collectionFactory->create()->toOptionArray());
        // echo "</pre>";
        // die();
        // if ($this->options === null) {
        //     $this->options = $this->collectionFactory->create()->toOptionArray();
        // }
        return $options;
        // return $this->options;
    }
}
