<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Import\Item;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;

use Plenty\Item\Model\ResourceModel\Import\Item\Collection;
use Plenty\Item\Model\ResourceModel\Import\Item\CollectionFactory;
use Plenty\Core\Controller\Adminhtml\Profile\RegistryConstants;

/**
 * Class Grid
 * @package Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Import\Item
 * @deprecated
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
        $this->setId('attribute_grid');
        $this->setDefaultSort('collected_at');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    /**
     * @return \Plenty\Core\Model\Profile
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
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            ['header' => __('ID'), 'align' => 'left', 'index' => 'entity_id', 'width' => 10]
        );

        $this->addColumn(
            'profile_id',
            ['header' => __('Profile ID'), 'align' => 'left', 'index' => 'profile_id', 'width' => 10]
        );

        $this->addColumn(
            'item_id',
            ['header' => __('Item ID'), 'align' => 'left', 'index' => 'item_id', 'width' => 20]
        );

        $this->addColumn(
            'variation_id',
            ['header' => __('Variation ID'), 'align' => 'left', 'index' => 'variation_id', 'width' => 20]
        );

        $this->addColumn(
            'external_id',
            ['header' => __('External ID'), 'align' => 'left', 'index' => 'external_id', 'width' => 20]
        );

        $this->addColumn(
            'sku',
            ['header' => __('SKU'), 'align' => 'left', 'index' => 'sku', 'width' => 20]
        );

        $this->addColumn(
            'status',
            ['header' => __('Status'), 'align' => 'left', 'index' => 'status', 'width' => 20]
        );

        $this->addColumn(
            'is_active',
            ['header' => __('Is Active'), 'align' => 'left', 'index' => 'is_active', 'width' => 20]
        );

        $this->addColumn(
            'item_type',
            ['header' => __('Item Type'), 'align' => 'left', 'index' => 'item_type', 'width' => 20]
        );

        $this->addColumn(
            'product_type',
            ['header' => __('Product Type'), 'align' => 'left', 'index' => 'product_type', 'width' => 20]
        );

        $this->addColumn(
            'bundle_type',
            ['header' => __('Bundle Type'), 'align' => 'left', 'index' => 'bundle_type', 'width' => 20]
        );

        $this->addColumn(
            'stock_type',
            ['header' => __('Stock Type'), 'align' => 'left', 'index' => 'stock_type', 'width' => 20]
        );

        $this->addColumn(
            'attribute_set',
            ['header' => __('Attribute Set'), 'align' => 'left', 'index' => 'attribute_set', 'width' => 20]
        );

        $this->addColumn(
            'flag_one',
            ['header' => __('Flag One'), 'align' => 'left', 'index' => 'flag_one', 'width' => 20]
        );

        $this->addColumn(
            'flag_two',
            ['header' => __('Flag Two'), 'align' => 'left', 'index' => 'flag_two', 'width' => 20]
        );

        $this->addColumn(
            'position',
            ['header' => __('Position'), 'align' => 'left', 'index' => 'position', 'width' => 20]
        );

        $this->addColumn(
            'condition',
            ['header' => __('Condition'), 'align' => 'left', 'index' => 'condition', 'width' => 20]
        );

        $this->addColumn(
            'manufacturer_id',
            ['header' => __('Manufacturer ID'), 'align' => 'left', 'index' => 'manufacturer_id', 'width' => 20]
        );

        $this->addColumn(
            'message',
            ['header' => __('Message'), 'align' => 'left', 'index' => 'message', 'width' => 20]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'created_at',
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'updated_at',
            [
                'header' => __('Updated At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'updated_at',
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'collected_at',
            [
                'header' => __('Collected At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'collected_at',
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'processed_at',
            [
                'header' => __('Processed At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'processed_at',
                'default' => ' ---- '
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
        return $this->getUrl('plenty_item/import_item/grid', ['_current' => true]);
    }
}
