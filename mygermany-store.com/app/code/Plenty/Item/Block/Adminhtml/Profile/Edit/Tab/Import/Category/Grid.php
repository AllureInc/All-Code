<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Import\Category;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;

use Plenty\Item\Model\ResourceModel\Import\Category\Collection;
use Plenty\Item\Model\ResourceModel\Import\Category\CollectionFactory;
use Plenty\Core\Controller\Adminhtml\Profile\RegistryConstants;

/**
 * Class Grid
 * @package Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Import\Category
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
            'category_id',
            ['header' => __('Category ID'), 'align' => 'left', 'index' => 'category_id', 'width' => 20]
        );

        $this->addColumn(
            'mage_id',
            ['header' => __('Mage ID'), 'align' => 'left', 'index' => 'mage_id', 'width' => 20]
        );

        $this->addColumn(
            'parent_id',
            ['header' => __('Parent ID'), 'align' => 'left', 'index' => 'parent_id', 'width' => 20]
        );

        $this->addColumn(
            'level',
            ['header' => __('Level'), 'align' => 'left', 'index' => 'level', 'width' => 20]
        );

        $this->addColumn(
            'has_children',
            ['header' => __('Has Children'), 'align' => 'left', 'index' => 'has_children', 'width' => 20]
        );

        $this->addColumn(
            'type',
            ['header' => __('Type'), 'align' => 'left', 'index' => 'type', 'width' => 20]
        );

        $this->addColumn(
            'name',
            ['header' => __('Name'), 'align' => 'left', 'index' => 'name', 'width' => 20]
        );

        $this->addColumn(
            'path',
            ['header' => __('Path'), 'align' => 'left', 'index' => 'path', 'width' => 20]
        );

        $this->addColumn(
            'preview_url',
            ['header' => __('Preview Url'), 'align' => 'left', 'index' => 'preview_url', 'width' => 20]
        );

        $this->addColumn(
            'status',
            ['header' => __('Status'), 'align' => 'left', 'index' => 'status', 'width' => 20]
        );

        $this->addColumn(
            'details',
            ['header' => __('Details'), 'align' => 'left', 'index' => 'details', 'width' => 20]
        );

        $this->addColumn(
            'message',
            ['header' => __('Message'), 'align' => 'left', 'index' => 'message', 'width' => 20]
        );

        $this->addColumn(
            'updated_by',
            ['header' => __('Updated By'), 'align' => 'left', 'index' => 'updated_by', 'width' => 20]
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
        return $this->getUrl('plenty_item/import_category/grid', ['_current' => true]);
    }
}
