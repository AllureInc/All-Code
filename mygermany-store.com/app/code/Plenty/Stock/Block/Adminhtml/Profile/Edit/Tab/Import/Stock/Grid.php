<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Block\Adminhtml\Profile\Edit\Tab\Import\Stock;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;

use Plenty\Core\Model\Profile;
use Plenty\Stock\Model\ResourceModel\Import\Inventory\Collection;
use Plenty\Stock\Model\ResourceModel\Import\Inventory\CollectionFactory;
use Plenty\Core\Controller\Adminhtml\Profile\RegistryConstants;

/**
 * Class Grid
 * @package Plenty\Stock\Block\Adminhtml\Profile\Edit\Tab\Import\Stock
 */
class Grid extends Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param Registry $coreRegistry
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $coreRegistry,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('inventory_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * @return Profile
     */
    protected function _getModel()
    {
        return $this->_coreRegistry->registry('plenty_profile');
    }

    /**
     * @return Extended
     */
    protected function _prepareCollection()
    {
        /** @var Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->addProfileFilter(
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_PROFILE)->getId()
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            ['header' => __('ID'), 'align' => 'left', 'index' => 'entity_id', 'width' => 10]
        )->addColumn(
            'profile_id',
            ['header' => __('Profile ID'), 'align' => 'left', 'index' => 'profile_id', 'width' => 10]
        )->addColumn(
            'item_id',
            ['header' => __('Item ID'), 'align' => 'left', 'index' => 'item_id', 'width' => 20]
        )->addColumn(
            'variation_id',
            ['header' => __('Variation ID'), 'align' => 'left', 'index' => 'variation_id', 'width' => 20]
        )->addColumn(
            'warehouse_id',
            ['header' => __('Warehouse ID'), 'align' => 'left', 'index' => 'warehouse_id', 'width' => 20]
        )->addColumn(
            'status',
            ['header' => __('Status'), 'align' => 'left', 'index' => 'status', 'width' => 20]
        )->addColumn(
            'stock_physical',
            ['header' => __('Stock Physical'), 'align' => 'left', 'index' => 'stock_physical', 'width' => 20]
        )->addColumn(
            'stock_net',
            ['header' => __('Stock Net'), 'align' => 'left', 'index' => 'stock_net', 'width' => 20]
        )->addColumn(
            'reserved_stock',
            ['header' => __('Reserved Stock'), 'align' => 'left', 'index' => 'reserved_stock', 'width' => 20]
        )->addColumn(
            'reversed_ebay',
            ['header' => __('Reserved eBay'), 'align' => 'left', 'index' => 'reversed_ebay', 'width' => 20]
        )->addColumn(
            'reorder_delta',
            ['header' => __('Reorder Delta'), 'align' => 'left', 'index' => 'reorder_delta', 'width' => 20]
        )->addColumn(
            'reordered',
            ['header' => __('Reordered'), 'align' => 'left', 'index' => 'reordered', 'width' => 20]
        )->addColumn(
            'reserve_bundle',
            ['header' => __('Reserved Bundle'), 'align' => 'left', 'index' => 'reserve_bundle', 'width' => 20]
        )->addColumn(
            'average_purchase_price',
            ['header' => __('Average Purchase Price'), 'align' => 'left', 'index' => 'average_purchase_price', 'width' => 20]
        )->addColumn(
            'message',
            ['header' => __('Message'), 'align' => 'left', 'index' => 'message', 'width' => 150]
        )->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'created_at',
                'default' => ' ---- ',
                'width' => 100
            ]
        )->addColumn(
            'updated_at',
            [
                'header' => __('Updated At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'updated_at',
                'default' => ' ---- ',
                'width' => 100
            ]
        )->addColumn(
            'collected_at',
            [
                'header' => __('Collected At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'collected_at',
                'default' => ' ---- ',
                'width' => 100
            ]
        )->addColumn(
            'processed_at',
            [
                'header' => __('Processed At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'processed_at',
                'default' => ' ---- ',
                'width' => 100
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('plenty_stock/import_inventory/grid', ['_current' => true]);
    }
}
