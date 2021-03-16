<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Import\Attribute;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;

use Plenty\Item\Model\ResourceModel\Import\Attribute\Collection;
use Plenty\Item\Model\ResourceModel\Import\Attribute\CollectionFactory;
use Plenty\Core\Controller\Adminhtml\Profile\RegistryConstants;

/**
 * Class Grid
 * @package Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Import\Attribute
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
     * Grid constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('attribute_grid');
        $this->setDefaultSort('collected_at', 'desc');
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
            'type',
            ['header' => __('Type'), 'align' => 'left', 'index' => 'type', 'width' => 20]
        );

        $this->addColumn(
            'position',
            ['header' => __('Position'), 'align' => 'left', 'index' => 'position', 'width' => 20]
        );

        $this->addColumn(
            'attribute_id',
            ['header' => __('Attribute ID'), 'align' => 'left', 'index' => 'attribute_id', 'width' => 20]
        );

        $this->addColumn(
            'property_id',
            ['header' => __('Property ID'), 'align' => 'left', 'index' => 'property_id', 'width' => 20]
        );

        $this->addColumn(
            'manufacturer_id',
            ['header' => __('Manufacturer ID'), 'align' => 'left', 'index' => 'manufacturer_id', 'width' => 20]
        );

        $this->addColumn(
            'attribute_code',
            ['header' => __('Attribute Code'), 'align' => 'left', 'index' => 'attribute_code', 'width' => 20]
        );

        $this->addColumn(
            'property_code',
            ['header' => __('Property Code'), 'align' => 'left', 'index' => 'property_code', 'width' => 20]
        );

        $this->addColumn(
            'property_group_id',
            ['header' => __('Property Group ID'), 'align' => 'left', 'index' => 'property_group_id', 'width' => 20]
        );

        $this->addColumn(
            'value_type',
            ['header' => __('Value Type'), 'align' => 'left', 'index' => 'value_type', 'width' => 20]
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
        return $this->getUrl('plenty_item/import_attribute/grid', ['_current' => true]);
    }
}
